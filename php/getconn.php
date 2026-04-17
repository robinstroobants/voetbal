<?php // Connect to the database
$host = getenv('DB_HOST') ?: 'db';
$db = getenv('DB_NAME') ?: 'lineup_db';
$user = getenv('DB_USER') ?: 'app_user';
$pass = getenv('DB_PASS') ?: 'bRng4y8TJLJwUxYHBD6q';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}