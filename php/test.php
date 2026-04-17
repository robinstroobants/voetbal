<?php
require_once 'getconn.php';

$result = $conn->query("SELECT first_name, last_name, birthdate FROM players");
echo "<h1>Spelerslijst</h1><ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . " (" . $row['birthdate'] . ")</li>";
}
echo "</ul>";
?>
