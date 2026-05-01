<?php
require_once dirname(__DIR__) . "/core/getconn.php";
$pdo->exec("
CREATE TABLE IF NOT EXISTS client_telemetry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NULL,
    user_type VARCHAR(50) NOT NULL,
    identifier VARCHAR(255) NULL,
    js_heap_mb FLOAT DEFAULT 0,
    dom_nodes INT DEFAULT 0,
    user_agent VARCHAR(512),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");
echo "Table created";
