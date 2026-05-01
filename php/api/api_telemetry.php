<?php
require_once dirname(__DIR__) . '/core/getconn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action']) || $data['action'] !== 'log_telemetry') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
    exit;
}

try {
    // Create table if not exists
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS client_telemetry (
        id INT AUTO_INCREMENT PRIMARY KEY,
        game_id INT NULL,
        user_type VARCHAR(50) NOT NULL,
        identifier VARCHAR(255) NULL,
        js_heap_mb FLOAT DEFAULT 0,
        dom_nodes INT DEFAULT 0,
        user_agent VARCHAR(512),
        ip_address VARCHAR(45) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $gameId = !empty($data['game_id']) ? (int)$data['game_id'] : null;
    $userType = $data['user_type'] ?? 'unknown';
    $identifier = $data['identifier'] ?? null;
    $jsHeapMb = isset($data['js_heap_mb']) ? (float)$data['js_heap_mb'] : 0;
    $domNodes = isset($data['dom_nodes']) ? (int)$data['dom_nodes'] : 0;
    
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
    $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    if ($ipAddress) {
        $ipAddress = explode(',', $ipAddress)[0];
    }

    $stmt = $pdo->prepare("INSERT INTO client_telemetry (game_id, user_type, identifier, js_heap_mb, dom_nodes, user_agent, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$gameId, $userType, $identifier, $jsHeapMb, $domNodes, $userAgent, $ipAddress]);

    echo json_encode(['status' => 'success']);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
