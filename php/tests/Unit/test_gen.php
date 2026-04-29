<?php
require_once __DIR__ . '/../../../../php/core/DynamicSchemaGenerator.php';

// Mock PDO
class MockPDO extends PDO {
    public function __construct() {}
    public function prepare($query, $options = []) { return new MockStmt(); }
    public function query($query) { return new MockStmt(); }
}
class MockStmt {
    public function execute($params = null) { return true; }
    public function fetchAll($mode = null) { return []; }
}

$pdo = new MockPDO();
$team_id = 1;
$game_date = '2026-04-27';

// Mock MatchManager
class MockMM {
    public function getSquad($game_id) {
        return [1, 2, 3, 4, 5, 6, 7, 8];
    }
    public function getGameDetails($game_id) {
        return ['game_format' => '5v5_4x15', 'format' => '5v5_4x15', 'game_parts' => '4x15', 'id' => 120];
    }
}
$mm = new MockMM();

$gen = new DynamicSchemaGenerator($pdo, $team_id, $game_date, $mm, []);
$res = $gen->generate(120, '4x15', '5v5_4x15', '', [1,2,3,4,5,6,7,8], '');

print_r($res['shifts']);
