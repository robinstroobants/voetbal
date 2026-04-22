<?php
require 'php/getconn.php';
$stmt = $pdo->query("UPDATE lineups SET team_id = NULL WHERE is_original = 1");
echo "Updated lineups team_id: " . $stmt->rowCount() . " rows.\n";

// Fix game formats: Replace _1gk_ and _2gk_ with _
$stmtFormat = $pdo->query("UPDATE games SET format = REPLACE(format, '_1gk_', '_') WHERE format LIKE '%_1gk_%'");
echo "Updated game format 1gk: " . $stmtFormat->rowCount() . " rows.\n";

$stmtFormat2 = $pdo->query("UPDATE games SET format = REPLACE(format, '_2gk_', '_') WHERE format LIKE '%_2gk_%'");
echo "Updated game format 2gk: " . $stmtFormat2->rowCount() . " rows.\n";

// Fix missing coaches
// 1. Fetch all coaches
$stmtCoaches = $pdo->query("SELECT id, team_id FROM users WHERE role = 'coach'");
$teamCoaches = [];
while ($row = $stmtCoaches->fetch(PDO::FETCH_ASSOC)) {
    if (!isset($teamCoaches[$row['team_id']])) {
        $teamCoaches[$row['team_id']] = [];
    }
    $teamCoaches[$row['team_id']][] = $row['id'];
}

// 2. Fetch games without coach
$stmtMissing = $pdo->query("SELECT id, team_id FROM games WHERE coach_id IS NULL OR coach_id = 0");
$missingCoaches = $stmtMissing->fetchAll(PDO::FETCH_ASSOC);

$updatedCoaches = 0;
foreach ($missingCoaches as $game) {
    $tid = $game['team_id'];
    if (isset($teamCoaches[$tid]) && count($teamCoaches[$tid]) > 0) {
        $defaultCoachId = $teamCoaches[$tid][0]; // Assign to the first coach found for the team
        $update = $pdo->prepare("UPDATE games SET coach_id = ? WHERE id = ?");
        $update->execute([$defaultCoachId, $game['id']]);
        $updatedCoaches++;
    }
}
echo "Updated missing coaches: " . $updatedCoaches . " rows.\n";
