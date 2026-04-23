<?php // Connect to the database

$current_file = basename($_SERVER['PHP_SELF'] ?? '');
$is_cli = (php_sapi_name() === 'cli');

// Auth is now managed centrally by router.php. getconn.php only handles database connection.
$host = $_SERVER['DB_HOST'] ?? (getenv('DB_HOST') ?: 'db');
$db = $_SERVER['DB_NAME'] ?? (getenv('DB_NAME') ?: 'lineup_db');
$user = $_SERVER['DB_USER'] ?? (getenv('DB_USER') ?: 'app_user');
$pass = $_SERVER['DB_PASS'] ?? (getenv('DB_PASS') ?: '');
try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (\Throwable $e) {
    die("Database Connection Error (MySQLi): " . $e->getMessage());
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

if (!function_exists('logPerformance')) {
    function logPerformance($actionName, $timeMs, $memoryMb, $userId = null, $context = null) {
        global $pdo;
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO system_logs (action_name, execution_time_ms, memory_usage_mb, user_id, context) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$actionName, $timeMs, $memoryMb, $userId, $context]);
        }
    }
}

// Update last_activity for the logged-in user (throttled to 5 minutes, skipping if impersonating)
if (!$is_cli && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id']) && !isset($_SESSION['original_user_id'])) {
    $now = time();
    $last_update = $_SESSION['last_activity_update'] ?? 0;
    if (($now - $last_update) > 300) { // 5 minutes
        try {
            $stmtAct = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
            $stmtAct->execute([$_SESSION['user_id']]);
            $_SESSION['last_activity_update'] = $now;
        } catch (\Throwable $t) {
            // Ignore DB errors during background activity update
        }
    }
}