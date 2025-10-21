<?php // Connect to the database
$host = 'db';
$db = 'lineup_db';
$user = 'app_user';
$pass = 'bRng4y8TJLJwUxYHBD6q';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}