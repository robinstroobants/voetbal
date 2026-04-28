<?php
require_once __DIR__ . '/core/getconn.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS usage_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        team_id INT NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        cost_weight INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (team_id),
        INDEX (action_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    
    // Zorg ervoor dat bestaande tabellen ook geüpdatet worden indien ze al bestonden als ENUM
    $pdo->exec("ALTER TABLE usage_logs MODIFY action_type VARCHAR(50) NOT NULL;");
    
    echo "Usage logs table created / updated.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
