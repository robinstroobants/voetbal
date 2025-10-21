<?php
require_once("game.php");
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

// Datum van invoer (je kunt dit dynamisch maken of per score aanpassen)
$score_date = date('Y-m-d');

$player_scores = array(
  "Jayden"  => array(1 => 0,   4 => 0,   7 => 70, 11 => 70, 9 => 71,  2 => 65, 5 => 65, 10 => 0),
  
  //"Jack"    => array(1 => 0,   4 => 40,  7 => 85, 11 => 85, 9 => 67,  2 => 87, 5 => 80, 10 => 50),
  
  //"Scout"    => array(1 => 0,   4 => 30,  7 => 85, 11 => 85, 9 => 70,  2 => 75, 5 => 75, 10 => 70),
  
  //"Rune"    => array(1 => 0,   4 => 100, 7 => 75, 11 => 75, 9 => 70,  2 => 70, 5 => 70, 10 => 95),
//  "MuratC"  => array(1 => 0,   4 => 0,   7 => 55, 11 => 55, 9 => 65,  2 => 45, 5 => 45, 10 => 0),
  /*    "Thibo"   => array(1 => 0,   4 => 75,  7 => 30, 11 => 30, 9 => 0,   2 => 90, 5 => 100,10 => 70),
      "Seppe"   => array(1 => 0,   4 => 50,  7 => 65, 11 => 65, 9 => 0,   2 => 100,5 => 100,10 => 20),
      "Senn"    => array(1 => 0,   4 => 90,  7 => 80, 11 => 80, 9 => 90,  2 => 70, 5 => 70, 10 => 90),
      "Léno"    => array(1 => 0,   4 => 20,  7 => 80, 11 => 80, 9 => 70,  2 => 80, 5 => 80, 10 => 60),
      "Arda"    => array(1 => 0,   4 => 40,  7 => 60, 11 => 60, 9 => 50,  2 => 60, 5 => 60, 10 => 0),
      "Jack"    => array(1 => 0,   4 => 30,  7 => 85, 11 => 85, 9 => 70,  2 => 75, 5 => 75, 10 => 70),
      "Miel"    => array(1 => 0,   4 => 50,  7 => 80, 11 => 80, 9 => 90,  2 => 70, 5 => 70, 10 => 90),
      "Jayden"  => array(1 => 0,   4 => 0,   7 => 70, 11 => 70, 9 => 60,  2 => 65, 5 => 65, 10 => 0),
      "Rune"    => array(1 => 0,   4 => 100, 7 => 90, 11 => 90, 9 => 90,  2 => 90, 5 => 90, 10 => 95),
      /*
    "NoahS"   => array(1 => 70, 4 => 0, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50),
    "Alessio" => array(1 => 50, 4 => 50, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50),
    "Tyrone"  => array(1 => 50, 4 => 50, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50),
    "Tiebe"   => array(1 => 50, 4 => 50, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50),
    "Otis"    => array(1 => 50, 4 => 50, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50),
    "MuratY"  => array(1 => 50, 4 => 50, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50),
    "Franklin"=> array(1 => 90, 4 => 0, 7 => 0, 11 => 0, 9 => 0, 2 => 0, 5 => 0, 10 => 0),
    "Staf"    => array(1 => 70, 4 => 0, 7 => 0, 11 => 0, 9 => 0, 2 => 0, 5 => 0, 10 => 0),
    "NoahW"   => array(1 => 50, 4 => 50, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50),
    "Loris"   => array(1 => 50, 4 => 50, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50),
    "Wannes"  => array(1 => 50, 4 => 50, 7 => 50, 11 => 50, 9 => 50, 2 => 50, 5 => 50, 10 => 50)*/
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
