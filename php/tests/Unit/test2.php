<?php
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../core/DynamicSchemaGenerator.php';
require_once __DIR__ . '/../../core/MatchManager.php';

$team_id = 1; // Assuming team_id 1
$game_date = '2026-04-27';
$mm = new MatchManager($pdo);
$gen = new DynamicSchemaGenerator($pdo, $team_id, $game_date, $mm, []);
$res = $gen->generate(120, '4x15', '8v8_4x15', '', [1,2,3,4,5,6,7,8], ''); // 8v8 format for 8 players?
print_r($res['shifts']);
