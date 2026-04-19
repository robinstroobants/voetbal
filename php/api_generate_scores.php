<?php
require_once 'getconn.php';
header('Content-Type: application/json');

try {
    $pdo->beginTransaction();

    // 1. Haal spelers op met hun favo posities
    $players = $pdo->query("SELECT id, favorite_positions, is_doelman FROM players")->fetchAll(PDO::FETCH_ASSOC);
    $players_data = [];
    foreach ($players as $p) {
        $favs = [];
        if (!empty($p['favorite_positions'])) {
            $favs = array_map('trim', explode(',', $p['favorite_positions']));
        }
        $p['favs'] = $favs;
        $players_data[$p['id']] = $p;
    }

    // 2. Haal de Team Ranking op
    $teamRanks = $pdo->query("SELECT player_id, team_rank FROM player_team_ranking")->fetchAll(PDO::FETCH_KEY_PAIR);
    $total_players = count($teamRanks) > 0 ? count($teamRanks) : 1;

    // 3. Haal Range van posities op (Positie Rankings)
    $posRanks = $pdo->query("SELECT position_id, player_id, pos_rank FROM position_rankings")->fetchAll(PDO::FETCH_ASSOC);
    
    // Groepeer per positie
    $positions = [];
    foreach ($posRanks as $pr) {
        $positions[$pr['position_id']][] = $pr;
    }

    // 4. Score Berekenings Logica
    $new_scores = []; 
    
    foreach ($positions as $posId => $ranks) {
        foreach ($ranks as $pr) {
            $pid = $pr['player_id'];
            $player = $players_data[$pid] ?? null;
            
            // Filter doelmannen uit veldspeler-wiskunde
            if (!$player || (isset($player['is_doelman']) && $player['is_doelman'] == 1)) {
                continue; 
            }

            // --- Regel 1: Positie Ranking (Top = ~85, zakt langzaam)
            $p_rank = (int)$pr['pos_rank'];
            $base_pos = max(40, 85 - (($p_rank - 1) * 5)); // Bij 1ste start je met 85, per plek daaronder -5 (met een vloer van 40)

            // --- Regel 2: Team Bonus (Hoe sterspeler ben je algemeen?)
            $t_rank = $teamRanks[$pid] ?? $total_players;
            $bonus_team = max(0, 15 - (($t_rank - 1) * (15 / $total_players))); // #1 team ster = +15, laatste = 0

            // --- Regel 3: Favoriete Positie Bonus
            $bonus_fav = 0;
            if ($player) {
                // favs string bevatte de positie
                $favIndex = array_search((string)$posId, $player['favs']);
                if ($favIndex !== false) {
                    if ($favIndex == 0) $bonus_fav = 10;        // Lievelingspositie 1
                    elseif ($favIndex == 1) $bonus_fav = 5;     // Lievelingspositie 2
                    else $bonus_fav = 2;                        // Vanaf plaats 3 etc..
                }
            }

            // Sommatie (Max 100)
            $final_score = round($base_pos + $bonus_team + $bonus_fav);
            if ($final_score > 100) $final_score = 100;

            $new_scores[$pid][$posId] = $final_score;
        }
    }

    // Om te voorkomen dat database eindeloos groeit bij spelen met ranking-dashboard
    // WISSEN we de manueel gegenereerde scores van VANDAAG, en vervangen we die met de nieuwe.
    $pdo->exec("DELETE FROM player_scores WHERE DATE(score_date) = CURDATE()");
    
    // Voeg nieuwe matrix data in
    $stmt = $pdo->prepare("INSERT INTO player_scores (player_id, position, score, score_date) VALUES (?, ?, ?, NOW())");
    
    // Haal een complete lijst van alle unieke posities op die geselecteerd waren of bestaan
    $all_known_positions = array_keys($positions);

    foreach ($players_data as $pid => $player) {
        if ($player['is_doelman'] == 1) {
            continue; // Doelmannen worden met rust gelaten.
        }

        foreach ($all_known_positions as $posId) {
            // Als speler GEEN score opgebouwd heeft voor deze pos (staat niet in pos_ranking), geef '0' (Mag niet spelen)
            $score = $new_scores[$pid][$posId] ?? 0;
            $stmt->execute([$pid, $posId, $score]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
