<?php
require_once __DIR__ . '/php/core/getconn.php';
require_once __DIR__ . '/php/core/DynamicSchemaGenerator.php';
require_once __DIR__ . '/php/models/MatchManager.php';

$gameId = 120;
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$gameId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

$matchManager = new MatchManager($pdo);
$matchData = $matchManager->getGameDetails($gameId);

// Get squad
$stmt = $pdo->prepare("SELECT player_id FROM game_players WHERE game_id = ?");
$stmt->execute([$gameId]);
$squad = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Generator
$dynGen = new DynamicSchemaGenerator($pdo, $game['team_id'], $game['game_date'], $matchManager, []);
$schemaData = $dynGen->generate($gameId, $game['game_parts'], $game['format'], $matchData['game']['sub_length'], $squad, '');

$player_positions = [];
foreach ($schemaData['events'] as $event) {
    foreach ($event['lineup'] as $pos => $pid) {
        $player_positions[$pid][$pos] = true;
    }
}

$min_unique = 999;
foreach ($player_positions as $pid => $pos_array) {
    $c = count($pos_array);
    if ($c < $min_unique) $min_unique = $c;
    echo "Player $pid has $c unique positions.\n";
}
echo "Min unique positions across all players: $min_unique\n";
