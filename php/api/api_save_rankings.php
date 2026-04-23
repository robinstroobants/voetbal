<?php
require_once dirname(__DIR__, 1) . '/core/getconn.php';
header('Content-Type: application/json');

// Ontvang JSON Payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();

    if ($data['action'] == 'team') {
        // Verwijder alleen rankings voor de spelers in dit team
        $delTeam = $pdo->prepare("DELETE ptr FROM player_team_ranking ptr JOIN players p ON ptr.player_id = p.id WHERE p.team_id = ?");
        $delTeam->execute([$_SESSION['team_id']]);
        
        $valStmt = $pdo->prepare("SELECT id FROM players WHERE id=? AND team_id=?");
        $stmt = $pdo->prepare("INSERT INTO player_team_ranking (player_id, team_rank) VALUES (?, ?)");
        if (isset($data['order']) && is_array($data['order'])) {
            foreach ($data['order'] as $index => $pid) {
                // Valideer of de speler bij het team hoort (veiligheid)
                $valStmt->execute([(int)$pid, $_SESSION['team_id']]);
                if ($valStmt->fetchColumn()) {
                    // Rank = index + 1 (1 is the best)
                    $stmt->execute([(int)$pid, $index + 1]);
                }
            }
        }
    } 
    elseif ($data['action'] == 'position') {
        $posId = (int)$data['position_id'];
        
        // Verwijder oude rankings voor deze positie voor DIT team
        $delStmt = $pdo->prepare("DELETE pr FROM position_rankings pr JOIN players p ON pr.player_id = p.id WHERE pr.position_id = ? AND p.team_id = ?");
        $delStmt->execute([$posId, $_SESSION['team_id']]);
        
        $valStmt = $pdo->prepare("SELECT id FROM players WHERE id=? AND team_id=?");
        
        $stmt = $pdo->prepare("INSERT INTO position_rankings (position_id, player_id, pos_rank) VALUES (?, ?, ?)");
        if (isset($data['order']) && is_array($data['order'])) {
            foreach ($data['order'] as $index => $pid) {
                $valStmt->execute([(int)$pid, $_SESSION['team_id']]);
                if ($valStmt->fetchColumn()) {
                    // Rank = index + 1
                    $stmt->execute([$posId, (int)$pid, $index + 1]);
                }
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
