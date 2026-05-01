<?php
require_once __DIR__ . '/php/core/getconn.php';
$stmt = $pdo->query("SELECT * FROM game_events ORDER BY id DESC LIMIT 20");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($events);
