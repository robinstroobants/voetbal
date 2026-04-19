<?php // Connect to the database
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