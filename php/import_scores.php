<?php
require_once("game.php");
require_once("getconn.php");

// Datum van invoer (je kunt dit dynamisch maken of per score aanpassen)
$score_date = date('Y-m-d');
$player_scores = array(
  "Jack" => array(1 => 0, 2 => 80, 4 => 55, 5 => 60, 7 => 90, 11 => 85, 10 => 30, 9 => 75),
  
  //"Seppe" => array(1 => 70, 2 => 95, 4 => 85, 5 => 85, 7 => 75, 11 => 80, 10 => 70, 9 => 55),
  //"Vinn" => array(1 => 50, 2 => 65, 4 => 65, 5 => 60, 7 => 85, 11 => 85, 10 => 35, 9 => 85),
  
  /*
  "Seppe" => array(1 => 0, 2 => 95, 4 => 85, 5 => 85, 7 => 75, 11 => 80, 10 => 70, 9 => 55),
  "Jayden" => array(1 => 0, 2 => 75, 4 => 85, 5 => 55, 7 => 75, 11 => 70, 10 => 35, 9 => 71),
  "Tiebe" => array(1 => 0, 2 => 75, 4 => 80, 5 => 70, 7 => 75, 11 => 70, 10 => 80, 9 => 85),
  
    "Alessio" => array(1 => 0, 2 => 55, 4 => 80, 5 => 50, 7 => 55, 11 => 55, 10 => 70, 9 => 85),
    "Vinn" => array(1 => 0, 2 => 65, 4 => 65, 5 => 60, 7 => 85, 11 => 85, 10 => 35, 9 => 85),
    "Otis" => array(1 => 0, 2 => 75, 4 => 65, 5 => 85, 7 => 70, 11 => 75, 10 => 80, 9 => 70),
    "Tiebe" => array(1 => 0, 2 => 75, 4 => 80, 5 => 70, 7 => 75, 11 => 70, 10 => 80, 9 => 85),
    "Loris" => array(1 => 0, 2 => 65, 4 => 90, 5 => 75, 7 => 80, 11 => 85, 10 => 90, 9 => 80),
    "Tyrone" => array(1 => 0, 2 => 55, 4 => 35, 5 => 70, 7 => 80, 11 => 85, 10 => 45, 9 => 65),
    "Staf" => array(1 => 70, 2 => 0, 4 => 0, 5 => 0, 7 => 0, 11 => 0, 10 => 0, 9 => 0),
    "Wannes" => array(1 => 0, 2 => 75, 4 => 40, 5 => 70, 7 => 90, 11 => 90, 10 => 75, 9 => 85),
    "Scout" => array(1 => 0, 2 => 75, 4 => 30, 5 => 65, 7 => 85, 11 => 85, 10 => 70, 9 => 70),
    "NoahW" => array(1 => 0, 2 => 60, 4 => 25, 5 => 55, 7 => 60, 11 => 60, 10 => 0, 9 => 50),
    "MuratY" => array(1 => 0, 2 => 40, 4 => 0, 5 => 45, 7 => 65, 11 => 60, 10 => 0, 9 => 65),
    "MuratC" => array(1 => 0, 2 => 70, 4 => 0, 5 => 65, 7 => 60, 11 => 60, 10 => 0, 9 => 65),
    "Thibo" => array(1 => 0, 2 => 90, 4 => 85, 5 => 100, 7 => 65, 11 => 65, 10 => 70, 9 => 50),
    "Seppe" => array(1 => 0, 2 => 95, 4 => 60, 5 => 85, 7 => 75, 11 => 80, 10 => 70, 9 => 55),
    "Senn" => array(1 => 0, 2 => 75, 4 => 80, 5 => 70, 7 => 80, 11 => 80, 10 => 100, 9 => 80),
    "Léno" => array(1 => 0, 2 => 80, 4 => 20, 5 => 75, 7 => 80, 11 => 80, 10 => 70, 9 => 75),
    "Arda" => array(1 => 0, 2 => 75, 4 => 40, 5 => 70, 7 => 65, 11 => 60, 10 => 0, 9 => 50),
    "NoahS" => array(1 => 70, 2 => 50, 4 => 0, 5 => 40, 7 => 50, 11 => 50, 10 => 50, 9 => 50),
    "Jack" => array(1 => 0, 2 => 90, 4 => 45, 5 => 70, 7 => 85, 11 => 85, 10 => 30, 9 => 80),
    "Jayden" => array(1 => 0, 2 => 60, 4 => 0, 5 => 55, 7 => 75, 11 => 70, 10 => 35, 9 => 71),
    "Rune" => array(1 => 0, 2 => 70, 4 => 100, 5 => 65, 7 => 85, 11 => 85, 10 => 90, 9 => 70),
    "Miel" => array(1 => 0, 2 => 65, 4 => 50, 5 => 60, 7 => 75, 11 => 70, 10 => 65, 9 => 95),
    "Franklin" => array(1 => 90, 2 => 0, 4 => 0, 5 => 0, 7 => 0, 11 => 0, 10 => 0, 9 => 0),*/
);
  
foreach ($player_scores as $first_name => $scores) {
    $stmt = $conn->prepare("SELECT id FROM players WHERE shortname = ?");
    $stmt->bind_param("s", $first_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $player_id = $row['id'];

        foreach ($scores as $position => $score) {
            $insert = $conn->prepare("INSERT INTO player_scores (player_id, position, score, score_date) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiis", $player_id, $position, $score, $score_date);
            $insert->execute();
        }
    } else {
        echo "Speler '$first_name' niet gevonden in de database.<br>";
    }
    $stmt->close();
}

$conn->close();
echo "Import voltooid.";
?>
