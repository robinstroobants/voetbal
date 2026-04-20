<?php

class MatchManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Haal alle data op voor een specifieke match: game info, selectie, en de actuele speler-ratings
     * gebaseerd op de datum van de wedstrijd.
     */
    public function getSelection(int $gameId): array {
        // 1. Haal basis match informatie op
        $stmtGame = $this->pdo->prepare("SELECT opponent, game_date, format, min_pos FROM games WHERE id = :id");
        $stmtGame->execute(['id' => $gameId]);
        $game = $stmtGame->fetch(PDO::FETCH_ASSOC);

        if (!$game) {
            return []; // Match not found
        }

        // 2. Query de selectie (status_id = 2 voor finale selectie, of alle)
        // Optioneel: We voegen een veld is_goalkeeper toe aan game_selections (of joinen met players info)
        $sqlSelection = "
            SELECT p.id as player_id, p.first_name, p.last_name, p.birthdate, gs.status_id, gs.is_goalkeeper
            FROM game_selections gs
            INNER JOIN players p ON gs.player_id = p.id
            WHERE gs.game_id = :gameId
        ";
        $stmtSel = $this->pdo->prepare($sqlSelection);
        $stmtSel->execute(['gameId' => $gameId]);
        $selectedPlayers = $stmtSel->fetchAll(PDO::FETCH_ASSOC);

        $doelmannen = [];
        $veldspelers = [];
        $playerScores = [];

        // 3. Haal de rating/scores op voor exact deze spelers op het moment (game_date) van de match.
        $sqlScores = "
            SELECT ps.player_id, ps.position, ps.score 
            FROM player_scores ps
            INNER JOIN players p ON ps.player_id = p.id
            INNER JOIN (
                SELECT player_id, position, MAX(score_date) as max_date
                FROM player_scores
                WHERE score_date <= :gameDate
                GROUP BY player_id, position
            ) latest ON ps.player_id = latest.player_id 
                     AND ps.position = latest.position 
                     AND ps.score_date = latest.max_date
            WHERE ps.player_id IN (
                SELECT player_id FROM game_selections WHERE game_id = :gameId
            )
        ";
        
        $stmtScores = $this->pdo->prepare($sqlScores);
        $stmtScores->execute([
            'gameDate' => $game['game_date'],
            'gameId' => $gameId
        ]);
        $scoresRows = $stmtScores->fetchAll(PDO::FETCH_ASSOC);

        // Transformeer scores naar format: [ ID => [ 'pos1' => 85, 'pos2' => 70 ] ]
        foreach ($scoresRows as $row) {
            $playerId = $row['player_id'];
            if (!isset($playerScores[$playerId])) {
                $playerScores[$playerId] = [];
            }
            $playerScores[$playerId][$row['position']] = $row['score'];
        }

        // Stap 1: Bepaal de vereiste lengte van de achternaam-substring om elke naam uniek te maken
        $displayNames = [];
        // Groepeer spelers per first_name
        $groupedByFirstName = [];
        foreach ($selectedPlayers as $p) {
            $fn = trim($p['first_name']);
            $groupedByFirstName[$fn][] = $p;
        }

        foreach ($groupedByFirstName as $fn => $playersGroup) {
            if (count($playersGroup) === 1) {
                // Unieke voornaam = makkelijk
                $displayNames[$playersGroup[0]['player_id']] = $fn;
            } else {
                // Dubbele voornaam: We zoeken de lengte (aantal karakters van last_name)
                // die nodig is om de weergave uniek te maken
                $maxLastNameLength = 0;
                foreach ($playersGroup as $p) {
                    $lnLength = mb_strlen(trim($p['last_name']));
                    if ($lnLength > $maxLastNameLength) $maxLastNameLength = $lnLength;
                }

                $neededLength = 1;
                $resolved = false;
                
                while ($neededLength <= $maxLastNameLength) {
                    $testNames = [];
                    $conflict = false;
                    foreach ($playersGroup as $p) {
                        $lnPart = mb_substr(trim($p['last_name']), 0, $neededLength);
                        $proposed = $fn . ' ' . $lnPart . '.';
                        if (in_array($proposed, $testNames)) {
                            $conflict = true;
                            break;
                        }
                        $testNames[] = $proposed;
                    }

                    if (!$conflict) {
                        // Geen conflict op deze substrings, we zijn klaar!
                        foreach ($playersGroup as $p) {
                            $lnPart = mb_substr(trim($p['last_name']), 0, $neededLength);
                            $displayNames[$p['player_id']] = $fn . ' ' . $lnPart . '.';
                        }
                        $resolved = true;
                        break;
                    }
                    $neededLength++;
                }

                // Fallback voor identieke namen (bv. twee keer exact 'Jan Smit')
                if (!$resolved) {
                    $counter = 1;
                    foreach ($playersGroup as $p) {
                        $lnPart = mb_substr(trim($p['last_name']), 0, $neededLength);
                        $displayNames[$p['player_id']] = $fn . ' ' . $lnPart . '. (' . $counter . ')';
                        $counter++;
                    }
                }
            }
        }

        // Bouw de return arrays
        $playerInfoMap = [];
        foreach ($selectedPlayers as $p) {
            $playerId = $p['player_id'];
            $displayName = $displayNames[$playerId] ?? trim($p['first_name']);

            $playerInfoMap[$playerId] = [
                'first_name' => $p['first_name'],
                'last_name' => $p['last_name'],
                'name' => trim($p['first_name'] . ' ' . $p['last_name']),
                'display_name' => $displayName,
                'birthdate' => $p['birthdate']
            ];

            if ($p['is_goalkeeper'] == 1) {
                $doelmannen[] = $playerId;
            } else {
                $veldspelers[] = $playerId;
            }
        }

        return [
            'game' => $game,
            'doelmannen' => implode(', ', $doelmannen),
            'selectie' => implode(', ', $veldspelers),
            'player_scores' => $playerScores,
            'player_info' => $playerInfoMap,
            'format' => $game['format']
        ];
    }

    /**
     * Sla een nieuwe selectie op voor een wedstrijd, overschrijft de bestaande.
     */
    public function saveSelection(int $gameId, array $playerIds, int $statusId, array $goalkeeperIds = []): bool {
        try {
            $this->pdo->beginTransaction();

            // Verwijder oude selectie (wipe and replace)
            $stmtClear = $this->pdo->prepare("DELETE FROM game_selections WHERE game_id = ? AND status_id = ?");
            $stmtClear->execute([$gameId, $statusId]);

            // Wis out-of-date opgeslagen theorie-schemas die gekoppeld zijn aan de oude spelers samenstelling
            if ($statusId == 2) {
                // We deleten enkel theorie-schemas als we effectief de 'Wedscheids Selectie' wijzigen
                $stmtClearLineups = $this->pdo->prepare("DELETE FROM game_lineups WHERE game_id = ?");
                $stmtClearLineups->execute([$gameId]);
            }

            // Insert new selection
            $stmtIns = $this->pdo->prepare("INSERT INTO game_selections (game_id, player_id, status_id, is_goalkeeper) VALUES (?, ?, ?, ?)");
            
            foreach ($playerIds as $pId) {
                $isGk = in_array($pId, $goalkeeperIds) ? 1 : 0;
                $stmtIns->execute([$gameId, $pId, $statusId, $isGk]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Haalt alle speelminuten historiek op uit de database op basis van bewaarde "finale" selecties.
     * Berekeningen gebeuren dynamisch door de wisselschemas uit te voeren met de opgeslagen speler-volgorde.
     */
    public function getHistoricalPlaytime(): array {

        $stmt = $this->pdo->query("
            SELECT l.game_id, l.schema_id, l.player_order, g.game_date, g.opponent, g.format,
                   (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id AND gs.is_goalkeeper = 1) as gk_count
            FROM game_lineups l 
            JOIN games g ON l.game_id = g.id 
            WHERE l.is_final = 1
            ORDER BY g.game_date ASC
        ");

        $pt_all_games = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date_str = date('ymd', strtotime($row['game_date']));
            $opponent_clean = str_replace(' ', '', $row['opponent']);
            $game_key = $date_str . "_" . $opponent_clean;

            // Map string-ids om naar pure dbID's 
            $players = [];
            foreach ($str_players as $name) {
                $players[] = $name;
            }
            
            $count = count($players);
            $format = $row['format'];
            $schema_id = $row['schema_id'];

            // Haal het schema op uit de nieuwe database tabel (unieke IDs)
            $stmtSch = $this->pdo->prepare("SELECT schema_data FROM lineups WHERE id = ?");
            $stmtSch->execute([$schema_id]);
            $schema_json = $stmtSch->fetchColumn();

            if (!$schema_json) {
                continue; // Schema niet gevonden in DB, overslaan
            }
            
            $schema = json_decode($schema_json, true);

            $durationTotal = 0;
            $time_played = [];
            $time_in_position = [];

            // Setup all listed players with 0 minutes initially
            foreach ($players as $pName) {
                $time_played[$pName] = 0;
            }

            foreach ($schema as $idx => $part) {
                if (!is_numeric($idx)) continue; // Negeer metadata properties ("duration", "game_counter")
                
                $dur = $part['duration'] ?? 0;
                $durationTotal += $dur;

                if (isset($part['lineup'])) {
                    foreach ($part['lineup'] as $pos => $pIndex) {
                        // $pIndex is 0-based corresponding to original shuffle array
                        if (isset($players[$pIndex])) {
                            $spelerNaam = $players[$pIndex];
                            
                            $time_played[$spelerNaam] = ($time_played[$spelerNaam] ?? 0) + $dur;
                            
                            if (!isset($time_in_position[$spelerNaam][$pos])) {
                                $time_in_position[$spelerNaam][$pos] = 0;
                            }
                            $time_in_position[$spelerNaam][$pos] += $dur;
                        }
                    }
                }
                
                if (isset($part['bench'])) {
                    foreach ($part['bench'] as $pIndex) {
                        if (isset($players[$pIndex])) {
                            $spelerNaam = $players[$pIndex];
                            // We tellen the bench playtime NIET op bij de actieve 'time_played', 
                            // dit is consistent met de legacy logica waarbij gespeeld = veld.
                            // We regisetreren het wel als bench-positie tijd.
                            if (!isset($time_in_position[$spelerNaam]['bench'])) {
                                $time_in_position[$spelerNaam]['bench'] = 0;
                            }
                            $time_in_position[$spelerNaam]['bench'] += $dur;
                        }
                    }
                }
            }

            $pt_all_games[$game_key] = [
                "duration" => $durationTotal,
                "players"  => $time_played,
                "playtime" => $time_in_position,
                "schema_id"=> $schema_id
            ];
        }

        return $pt_all_games;
    }
}
