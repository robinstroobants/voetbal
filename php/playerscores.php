<?php

/*$player_scores = array(
    "Rune"    => array(1 => 0,   4 => 100, 7 => 80, 11 => 80, 9 => 70,  2 => 90, 5 => 90, 10 => 80),
    "Thibo"   => array(1 => 0,   4 => 75,  7 => 40, 11 => 40, 9 => 0,   2 => 90, 5 => 100,10 => 70),
    "Senn"    => array(1 => 0,   4 => 90,  7 => 80, 11 => 80, 9 => 90,  2 => 75, 5 => 75, 10 => 90),
    "Seppe"   => array(1 => 0,   4 => 40,  7 => 65, 11 => 65, 9 => 0,   2 => 100,5 => 100,10 => 20),
    "Léno"    => array(1 => 0,   4 => 20,  7 => 80, 11 => 80, 9 => 70,  2 => 70, 5 => 70, 10 => 50),
    
    "Jack"    => array(1 => 0,   4 => 30,  7 => 85, 11 => 85, 9 => 70,  2 => 75, 5 => 75, 10 => 70),
    "Miel"    => array(1 => 0,   4 => 65,  7 => 80, 11 => 80, 9 => 90,  2 => 70, 5 => 70, 10 => 90),
    "Jayden"  => array(1 => 0,   4 => 0,   7 => 70, 11 => 70, 9 => 60,  2 => 65, 5 => 65, 10 => 0),
    "Arda"    => array(1 => 0,   4 => 65,  7 => 60, 11 => 60, 9 => 50,  2 => 60, 5 => 60, 10 => 0),
    "MuratC"  => array(1 => 0,   4 => 0,   7 => 65, 11 => 65, 9 => 60,  2 => 45, 5 => 45, 10 => 0),
    "NoahS"   => array(1 => 70,  4 => 0,   7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50),
    
    "Alessio" => array(1 => 50,  4 => 50,  7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50),
    "Tyrone"  => array(1 => 50,  4 => 50,  7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50),
    "Tiebe"   => array(1 => 50,  4 => 50,  7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50),
    "Otis"    => array(1 => 50,  4 => 50,  7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50),
    "MuratY"  => array(1 => 50,  4 => 50,  7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50),
    "Franklin"=> array(1 => 90,  4 => 0,   7 => 0,  11 => 0,  9 => 0,   2 => 0,  5 => 0,  10 => 0),
    "Staf"    => array(1 => 70,  4 => 0,   7 => 0,  11 => 0,  9 => 0,   2 => 0,  5 => 0,  10 => 0),
    "NoahW"   => array(1 => 50,  4 => 50,  7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50),
    "Loris"   => array(1 => 50,  4 => 50,  7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50),
    "Wannes"  => array(1 => 50,  4 => 50,  7 => 50, 11 => 50, 9 => 50,  2 => 50, 5 => 50, 10 => 50)
);
*/
// Scores ophalen per speler en positie (laatste score)
$result = $conn->query("SELECT * FROM players WHERE team = 'Brent' ORDER BY first_name, last_name");
$players = [];
$player_ids = [];
while ($row = $result->fetch_assoc()) {
    $players[$row['id']]["id"] = $row['id'];
    $players[$row['id']]["first_name"] = $row['first_name'];
    $players[$row['id']]["last_name"] = $row['last_name'];
    $players[$row['id']]["shortname"] = $row['shortname'];
    $player_ids[] = $row['id'];
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
//dpr($player_scores);
?>
