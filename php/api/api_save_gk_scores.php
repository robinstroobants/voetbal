<?php
require_once dirname(__DIR__, 1) . '/core/getconn.php';
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['player_id']) || !isset($data['score'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

try {
    // Valideer team
    $val = $pdo->prepare("SELECT id FROM players WHERE id = ? AND team_id = ?");
    $val->execute([(int)$data['player_id'], $_SESSION['team_id']]);
    if ($val->fetchColumn()) {
        $stmt = $pdo->prepare("INSERT INTO gk_scores (player_id, score) VALUES (?, ?) ON DUPLICATE KEY UPDATE score = ?");
        $stmt->execute([(int)$data['player_id'], (int)$data['score'], (int)$data['score']]);
        
        // Also remove from gk_scores if an 'extra handschoen' is deselected, handled by passing score = 0 perhaps?
        if ((int)$data['score'] === 0) {
            $del = $pdo->prepare("DELETE FROM gk_scores WHERE player_id = ?");
            $del->execute([(int)$data['player_id']]);
        }
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
