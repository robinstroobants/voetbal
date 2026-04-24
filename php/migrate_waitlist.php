<?php
require_once __DIR__ . '/core/getconn.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN account_status ENUM('active', 'pending', 'suspended') NOT NULL DEFAULT 'active'");
    echo "Column added with default active.\n";
    $pdo->exec("ALTER TABLE users ALTER COLUMN account_status SET DEFAULT 'pending'");
    echo "Default set to pending for new rows.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
