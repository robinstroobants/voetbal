<?php
require_once __DIR__ . '/core/getconn.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS usage_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        team_id INT NOT NULL,
        action_type ENUM('ai_generation', 'manual_builder', 'pdf_export') NOT NULL,
        cost_weight INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (team_id),
        INDEX (action_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "Usage logs table created.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
