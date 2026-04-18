<?php
require_once 'getconn.php';
require_once 'MatchManager.php';
$matchManager = new MatchManager($pdo);
$matchData = $matchManager->getSelection(33);

$selectie = array_filter(array_map('trim', explode(',', $matchData['selectie'])));
$gk = array_filter(array_map('trim', explode(',', $matchData['doelmannen'])));
$squad = array_values(array_merge($gk, $selectie));
$player_scores = $matchData['player_scores'];

$map = []; 
for($i=0; $i<8; $i++) {
    // 1 to 8 mappping to players 1 through 8
    $map[$i+1] = $squad[$i+1];
}

$payload = [
    'squad' => $squad,
    'player_scores' => $player_scores,
    'map' => $map
];

file_put_contents('match33_data.json', json_encode($payload, JSON_PRETTY_PRINT));
echo "JSON dumped for Match 33.";
