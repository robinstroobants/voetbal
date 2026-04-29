<?php
require_once __DIR__ . '/../../../../php/core/getconn.php';
require_once __DIR__ . '/../../../../php/core/DynamicSchemaGenerator.php';
require_once __DIR__ . '/../../../../php/core/MatchManager.php';

$team_id = 1; // You need a real team ID
$mm = new MatchManager($pdo);
// game_id 120
$squad = [4, 6, 8, 9, 10, 11, 12, 13]; // Need real player ids from db, but anything might work if stats are 0
$gen = new DynamicSchemaGenerator($pdo, $team_id, '2026-04-27', $mm, []);
$res = $gen->generate(120, '4x15', '8v8_4x15', '', $squad, '');
file_put_contents('/Users/robinstroobants/work/sandbox/voetbal/php/tests/Unit/output.json', json_encode($res, JSON_PRETTY_PRINT));
