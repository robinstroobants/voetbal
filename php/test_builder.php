<?php
require_once __DIR__ . '/core/getconn.php';
$gameId = 111;

$stmtGame = $pdo->prepare("SELECT id, team_id, format, date_format(game_date, '%d/%m/%Y') as game_date_formatted FROM games WHERE id = ?");
$stmtGame->execute([$gameId]);
$game = $stmtGame->fetch(PDO::FETCH_ASSOC);

$format = $game['format'];
$stmtSquad = $pdo->prepare("SELECT player_id, is_goalkeeper FROM game_selections WHERE game_id = ?");
$stmtSquad->execute([$gameId]);

$squad = [];
$gk_arr = [];
$sel_arr = [];
while ($row = $stmtSquad->fetch(PDO::FETCH_ASSOC)) {
    $pid = (int)$row['player_id'];
    if ($row['is_goalkeeper'] == 1) {
        $gk_arr[] = $pid;
    } else {
        $sel_arr[] = $pid;
    }
}
$squad = array_merge($gk_arr, $sel_arr);
$aantal = count($squad);
$gk_count = count($gk_arr);

$search_format = $format;
if (strpos($format, 'gk') === false) {
    if (preg_match('/^(\d+v\d+)_(\d+x\d+.*)$/', $format, $matches)) {
        $search_format = $matches[1] . '_' . $gk_count . 'gk_' . $matches[2];
    }
}

$playPositions = [1, 2, 4, 5, 7, 9, 10, 11];
if (strpos($search_format, '5v5') !== false) {
    $playPositions = [1, 2, 4, 5, 9];
}
$fieldPositions = array_filter($playPositions, fn($p) => $p != 1);
$numFieldPositions = count($fieldPositions);

$fixedGkIdPHP = $gk_count === 1 ? (int)reset($gk_arr) : null;
$numFieldPlayers = count($squad) - ($fixedGkIdPHP !== null ? 1 : 0);

$totalBlocks = 4; // Assuming 4 blocks for 4x15
$totalFieldBlocks = $numFieldPositions * $totalBlocks;

echo "Format: $format\n";
echo "Search Format: $search_format\n";
echo "Squad count: " . count($squad) . "\n";
echo "GK count: $gk_count\n";
echo "Fixed GK ID: " . var_export($fixedGkIdPHP, true) . "\n";
echo "Num Field Players: $numFieldPlayers\n";
echo "Num Field Positions: $numFieldPositions\n";
echo "Total Field Blocks: $totalFieldBlocks\n";

if ($numFieldPlayers > 0 && $totalFieldBlocks > 0 && $fixedGkIdPHP !== null) {
    $extra_blocks = $totalFieldBlocks % $numFieldPlayers;
    echo "Extra blocks: $extra_blocks\n";
    if ($extra_blocks > 0) {
        echo "PREGAME WILL RENDER\n";
    } else {
        echo "NO PREGAME (extra blocks == 0)\n";
    }
} else {
    echo "NO PREGAME (condition failed)\n";
}
