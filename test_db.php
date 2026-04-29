<?php
require_once __DIR__ . '/php/core/getconn.php';
$stmt = $pdo->query("SHOW CREATE TABLE players");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'];
