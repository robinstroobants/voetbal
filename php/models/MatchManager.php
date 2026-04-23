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
        if ((isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') || (defined('PUBLIC_SHARE_MODE') && PUBLIC_SHARE_MODE)) {
            $stmtGame = $this->pdo->prepare("SELECT opponent, game_date, format, min_pos FROM games WHERE id = :id");
            $stmtGame->execute(['id' => $gameId]);
        } else {
            $stmtGame = $this->pdo->prepare("SELECT opponent, game_date, format, min_pos FROM games WHERE id = :id AND team_id = :team_id");
            $stmtGame->execute(['id' => $gameId, 'team_id' => $_SESSION['team_id']]);
        }
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
        $useTransaction = !$this->pdo->inTransaction();
        try {
            if ($useTransaction) {
                $this->pdo->beginTransaction();
            }

            // Verwijder oude selectie (volledige wipe and replace voor deze match)
            $stmtClear = $this->pdo->prepare("DELETE FROM game_selections WHERE game_id = ?");
            $stmtClear->execute([$gameId]);

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

            if ($useTransaction) {
                $this->pdo->commit();
            }
            return true;
        } catch (Exception $e) {
            if ($useTransaction) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Haalt alle speelminuten historiek op uit de database op basis van bewaarde "finale" selecties.
     * Berekeningen gebeuren dynamisch door de wisselschemas uit te voeren met de opgeslagen speler-volgorde.
     */
    public function getHistoricalPlaytime(?int $teamId = null): array {
        if ($teamId === null && isset($_SESSION['team_id'])) {
            $teamId = (int)$_SESSION['team_id'];
        }

        $query = "
            SELECT l.game_id, l.schema_id, l.player_order, g.game_date, g.opponent, g.format,
                   (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id AND gs.is_goalkeeper = 1) as gk_count
            FROM game_lineups l 
            JOIN games g ON l.game_id = g.id 
            WHERE l.is_final = 1
        ";
        $params = [];
        if ($teamId) {
            $query .= " AND g.team_id = ? ";
            $params[] = $teamId;
        }
        $query .= " ORDER BY g.game_date ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

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

    /**
     * Synchroniseer de speelminuten logtabellen voor een specifieke wedstrijd
     * op basis van de actueel "finale" opstelling.
     */
    public function syncGameLogs(int $gameId): void {
        // 1. Verwijder altijd eerst de bestaande logs voor deze game
        $this->pdo->prepare("DELETE FROM game_playtime_logs WHERE game_id = ?")->execute([$gameId]);
        $this->pdo->prepare("DELETE FROM game_shift_logs WHERE game_id = ?")->execute([$gameId]);

        // 2. Zoek de finale opstelling voor deze game
        $stmtL = $this->pdo->prepare("
            SELECT gl.schema_id, gl.player_order, g.coach_id, l.schema_data 
            FROM game_lineups gl
            JOIN games g ON g.id = gl.game_id
            JOIN lineups l ON l.id = gl.schema_id
            WHERE gl.game_id = ? AND gl.is_final = 1
        ");
        $stmtL->execute([$gameId]);
        $lData = $stmtL->fetch(PDO::FETCH_ASSOC);

        if (!$lData || empty($lData['schema_data']) || empty($lData['player_order'])) {
            return; // Geen finale opstelling, dus ook geen speelminuten
        }

        $coach_id = $lData['coach_id'];
        $player_order = explode(',', $lData['player_order']);
        $schema_data = json_decode($lData['schema_data'], true);

        if (!is_array($schema_data)) {
            return;
        }

        $totals = [];
        foreach ($player_order as $pid) {
            $pid = trim($pid);
            if (!empty($pid)) {
                $totals[$pid] = ['played' => 0, 'bank' => 0, 'gk' => 0];
            }
        }

        $stmtShift = $this->pdo->prepare("INSERT INTO game_shift_logs (game_id, player_id, shift_index, position, duration_seconds) VALUES (?, ?, ?, ?, ?)");

        foreach ($schema_data as $shift_idx => $shift) {
            if (!is_numeric($shift_idx)) continue;
            
            $duration = (int)($shift['duration'] ?? 0);
            $players_on_field = [];

            // Veldspelers en Doelman
            if (isset($shift['lineup']) && is_array($shift['lineup'])) {
                foreach ($shift['lineup'] as $pos => $p_idx) {
                    if (isset($player_order[$p_idx])) {
                        $real_pid = trim($player_order[$p_idx]);
                        if (empty($real_pid)) continue;
                        
                        $stmtShift->execute([$gameId, $real_pid, $shift_idx, (string)$pos, $duration]);
                        
                        $totals[$real_pid]['played'] += $duration;
                        $players_on_field[] = $real_pid;
                        if ((int)$pos === 1) { // pos 1 is doelman
                            $totals[$real_pid]['gk'] += $duration;
                        }
                    }
                }
            }

            // Iedereen die niet op het veld staat, zit op de bank
            foreach ($player_order as $pid) {
                $pid = trim($pid);
                if (!empty($pid) && !in_array($pid, $players_on_field)) {
                    $stmtShift->execute([$gameId, $pid, $shift_idx, 'BANK', $duration]);
                    $totals[$pid]['bank'] += $duration;
                }
            }
        }

        // Sla geaggregeerde totalen op
        $stmtTotals = $this->pdo->prepare("INSERT INTO game_playtime_logs (game_id, player_id, coach_id, seconds_played, seconds_bank, seconds_gk) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($totals as $pid => $t) {
            $stmtTotals->execute([$gameId, $pid, $coach_id, $t['played'], $t['bank'], $t['gk']]);
        }
    }

    /**
     * Haalt cumulatieve speelminuten op voor een specifieke selectie aan spelers
     * tot net VÓÓR een bepaalde wedstrijd datum.
     */
    public function getSeasonStatsForSelection(int $teamId, string $gameDate, array $playerIds): array {
        if (empty($playerIds)) return [];

        $placeholders = implode(',', array_fill(0, count($playerIds), '?'));
        
        $sql = "
            SELECT p.player_id, 
                   SUM(p.seconds_played) as total_played, 
                   SUM(p.seconds_bank) as total_bank,
                   SUM(p.seconds_gk) as total_gk
            FROM game_playtime_logs p
            JOIN games g ON p.game_id = g.id
            WHERE g.team_id = ? 
              AND g.game_date < ?
              AND p.player_id IN ($placeholders)
            GROUP BY p.player_id
        ";

        $params = array_merge([$teamId, $gameDate], $playerIds);
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pid = $row['player_id'];
            $results[$pid] = [
                'played' => (int)$row['total_played'],
                'bank' => (int)$row['total_bank'],
                'gk' => (int)$row['total_gk']
            ];
            // Total available time = the time they were on the match sheet (played + bank)
            $results[$pid]['available'] = $results[$pid]['played'] + $results[$pid]['bank'];
        }
        
        // Ensure all players have an entry even if no historical data
        foreach ($playerIds as $pid) {
            if (!isset($results[$pid])) {
                $results[$pid] = ['played' => 0, 'bank' => 0, 'gk' => 0, 'available' => 0];
            }
        }

        return $results;
    }
}
