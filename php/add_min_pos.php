<?php
require_once 'getconn.php';
try {
    $pdo->exec("ALTER TABLE games ADD COLUMN min_pos INT DEFAULT 0;");
    echo "Added min_pos column";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
