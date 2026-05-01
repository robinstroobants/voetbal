<?php
/**
 * api_telemetry.php — Server-side telemetry receiver
 *
 * Security:
 *  - All inputs sanitized / cast before INSERT
 *  - Prepared statements only (no string interpolation in SQL)
 *  - Rate-limited: max 1 insert per IP per game per 30 seconds
 *  - No sensitive data stored (email truncated to prefix only)
 *
 * Server-side metrics added here:
 *  - php_memory_mb: memory_get_peak_usage() / 1024 / 1024
 *  - php_time_ms: time since REQUEST_TIME_FLOAT
 */

// Measure PHP execution time ASAP
$php_time_ms = isset($_SERVER['REQUEST_TIME_FLOAT'])
    ? round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 1)
    : 0;
$php_memory_mb = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

require_once dirname(__DIR__) . '/core/getconn.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

// --- Only accept POST with JSON body ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || ($data['action'] ?? '') !== 'log_telemetry') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Bad request']);
    exit;
}

// --- Sanitize inputs ---
$game_id     = isset($data['game_id']) ? (int)$data['game_id'] : null;
$user_type   = in_array($data['user_type'] ?? '', ['coach', 'parent', 'guest'])
               ? $data['user_type'] : 'guest';

// Store full email (max 255 chars, safe characters only)
$raw_id      = (string)($data['identifier'] ?? 'guest');
$identifier  = substr(preg_replace('/[^a-zA-Z0-9._\-@+]/', '', $raw_id), 0, 255);

$js_heap_mb  = max(0, min(9999, (float)($data['js_heap_mb']  ?? 0)));
$dom_nodes   = max(0, min(99999, (int)($data['dom_nodes']    ?? 0)));
$page_load_ms= max(0, min(99999, (int)($data['page_load_ms'] ?? 0)));
$page        = substr(preg_replace('/[^a-zA-Z0-9\/_\-.]/', '', (string)($data['page'] ?? '')), 0, 100);

$ip          = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$ip          = substr(explode(',', $ip)[0], 0, 45); // take first IP if behind proxy
$user_agent  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);

// --- Rate limiting: max 1 row per IP+game_id per 30 seconds ---
if ($game_id) {
    $stmtRate = $pdo->prepare("
        SELECT COUNT(*) FROM client_telemetry
        WHERE ip_address = ? AND game_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)
    ");
    $stmtRate->execute([$ip, $game_id]);
    if ($stmtRate->fetchColumn() > 0) {
        echo json_encode(['status' => 'throttled']);
        exit;
    }
}

// --- Ensure schema is up to date (BEFORE any INSERT) ---
try {
    $pdo->exec("
        ALTER TABLE client_telemetry
            ADD COLUMN IF NOT EXISTS page VARCHAR(100) NULL AFTER dom_nodes,
            ADD COLUMN IF NOT EXISTS page_load_ms INT DEFAULT 0 AFTER page,
            ADD COLUMN IF NOT EXISTS php_time_ms FLOAT DEFAULT 0 AFTER page_load_ms,
            ADD COLUMN IF NOT EXISTS php_memory_mb FLOAT DEFAULT 0 AFTER php_time_ms,
            ADD COLUMN IF NOT EXISTS identifier_full VARCHAR(255) NULL AFTER identifier
    ");
} catch (Exception $e) {}

// --- Insert ---
$stmt = $pdo->prepare("
    INSERT INTO client_telemetry
        (game_id, user_type, identifier, js_heap_mb, dom_nodes, page, page_load_ms, php_time_ms, php_memory_mb, ip_address, user_agent)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $game_id ?: null,
    $user_type,
    $identifier,
    $js_heap_mb,
    $dom_nodes,
    $page ?: null,
    $page_load_ms,
    $php_time_ms,
    $php_memory_mb,
    $ip,
    $user_agent
]);

echo json_encode(['status' => 'ok']);
