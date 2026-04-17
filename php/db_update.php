<?php
require_once 'getconn.php';
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS game_lineups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game_id INT NOT NULL,
            schema_id INT NOT NULL,
            player_order VARCHAR(255) NOT NULL,
            score FLOAT DEFAULT 0,
            is_final TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
        );
    ");
    echo "Table game_lineups created!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
