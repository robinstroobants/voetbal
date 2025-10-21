<?php
$host = 'db';
$db   = 'lineup_db';
$user = 'app_user';
$pass = 'bRng4y8TJLJwUxYHBD6q';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

$result = $conn->query("SELECT first_name, last_name, birthdate FROM players");
echo "<h1>Spelerslijst</h1><ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . " (" . $row['birthdate'] . ")</li>";
}
echo "</ul>";
?>
