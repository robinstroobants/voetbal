<?php // Connect to the database

$current_file = basename($_SERVER['PHP_SELF'] ?? '');
$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli && !in_array($current_file, ['login.php', 'logout.php', 'run_migrations.php'])) {
    require_once __DIR__ . '/auth.php';
}

$host = $_SERVER['DB_HOST'] ?? (getenv('DB_HOST') ?: 'db');
$db = $_SERVER['DB_NAME'] ?? (getenv('DB_NAME') ?: 'lineup_db');
$user = $_SERVER['DB_USER'] ?? (getenv('DB_USER') ?: 'app_user');
$pass = $_SERVER['DB_PASS'] ?? (getenv('DB_PASS') ?: '');
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    function logPerformance($actionName, $timeMs, $memoryMb, $userId = null) {
        global $pdo;
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO system_logs (action_name, execution_time_ms, memory_usage_mb, user_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$actionName, $timeMs, $memoryMb, $userId]);
        }
    }
}