<?php
require_once 'getconn.php';
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
        // Leeg the hele tabel
        $pdo->exec("TRUNCATE TABLE player_team_ranking");
        
        $stmt = $pdo->prepare("INSERT INTO player_team_ranking (player_id, team_rank) VALUES (?, ?)");
        if (isset($data['order']) && is_array($data['order'])) {
            foreach ($data['order'] as $index => $pid) {
                // Rank = index + 1 (1 is the best)
                $stmt->execute([(int)$pid, $index + 1]);
            }
        }
    } 
    elseif ($data['action'] == 'position') {
        $posId = (int)$data['position_id'];
        
        // Verwijder oude rankings voor deze positie
        $delStmt = $pdo->prepare("DELETE FROM position_rankings WHERE position_id = ?");
        $delStmt->execute([$posId]);
        
        $stmt = $pdo->prepare("INSERT INTO position_rankings (position_id, player_id, pos_rank) VALUES (?, ?, ?)");
        if (isset($data['order']) && is_array($data['order'])) {
            foreach ($data['order'] as $index => $pid) {
                // Rank = index + 1
                $stmt->execute([$posId, (int)$pid, $index + 1]);
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
