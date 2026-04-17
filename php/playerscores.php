<?php

// Pak de daadwerkelijke spelerslijst uit de array
$playerList = $selecties[$wedstrijd];
//dpr($playerList);


// --- 1. HAAL SPECIFIEKE SPELERS (UIT SELECTIE) OP ---x
// 1.1. Maak het juiste aantal placeholders ('?', '?', '?', ...)
$placeholders = implode(',', array_fill(0, count($playerList), '?'));

// 1.2. Bereid de SQL query voor
$sql_selection = "SELECT * FROM players WHERE shortname IN ($placeholders) ORDER BY first_name, last_name";

// 1.3. Prepare en execute
$stmt_selection = $conn->prepare($sql_selection);
$stmt_selection->execute($playerList); 

// 1.4. Haal het resultaat-object op
$result_selection = $stmt_selection->get_result(); // <-- Deze gaan we gebruiken in de loop!

// --- 4. VERWERK DE RESULTATEN VAN DE EERSTE QUERY ---
$players = [];
$global_playerinfo = [];
$player_ids = [];

// Zorg dat $result_selection bestaat en rijen bevat
if ($result_selection && $result_selection->num_rows > 0) {
    
    // <-- LET OP: We gebruiken hier $result_selection!
    while ($row = $result_selection->fetch_assoc()) {
        
        $playerData = [
            "id" => $row['id'],
            "first_name" => $row['first_name'],
            "name" => $row['last_name'], 
            "shortname" => $row['shortname'],
            "birthdate" => $row['birthdate']
        ];

        // 1. Voeg toe aan $players array, gekeyd op ID
        $players[$row['id']] = $playerData;
        
        // 2. Voeg de ID toe aan de $player_ids lijst
        $player_ids[] = $row['id'];
        
        // 3. Voeg toe aan $global_playerinfo, gekeyd op shortname
        $global_playerinfo[$row['shortname']] = $playerData;
    }
}

$player_scores = [];
if (!empty($player_ids)) {
    $ids_str = implode(',', $player_ids);
    $sql = "SELECT player_id, position, score
            FROM player_scores
            WHERE player_id IN ($ids_str)
              AND (player_id, position, score_date) IN (
                  SELECT player_id, position, MAX(score_date)
                  FROM player_scores
                  WHERE player_id IN ($ids_str)
                  GROUP BY player_id, position
              )";
    $result_scores = $conn->query($sql);
    while ($row = $result_scores->fetch_assoc()) {
        $player_scores[$players[$row['player_id']]['shortname']][$row['position']] = $row['score'];
    }
}
?>
