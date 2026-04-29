<?php
// Initialize autoloader and basic config
require_once __DIR__ . '/../vendor/autoload.php';

// Setup database credentials
$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'app_user';
$pass = getenv('DB_PASS') ?: 'bRng4y8TJLJwUxYHBD6q';

$devDb = 'lineup_db';
$testDb = 'lineup_test_db';

try {
    // Connect to the DEV database to read the schema
    $pdoDev = new PDO("mysql:host=$host;dbname=$devDb;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Connect without specific DB to create the test DB if it doesn't exist
    $pdoRoot = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create test database (app_user has permissions due to wildcard/setup)
    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `$testDb`");

    // Connect to TEST database
    $pdoTest = new PDO("mysql:host=$host;dbname=$testDb;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Get all tables from DEV
    $tables = $pdoDev->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    // Disable foreign key checks temporarily in TEST db
    $pdoTest->exec("SET FOREIGN_KEY_CHECKS = 0");

    foreach ($tables as $table) {
        // Read schema from DEV
        $stmt = $pdoDev->query("SHOW CREATE TABLE `$table`");
        $createRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $createSql = $createRow['Create Table'];

        // Drop existing table in TEST and recreate it
        $pdoTest->exec("DROP TABLE IF EXISTS `$table`");
        $pdoTest->exec($createSql);
    }

    // Re-enable foreign key checks
    $pdoTest->exec("SET FOREIGN_KEY_CHECKS = 1");



} catch (PDOException $e) {
    echo "Warning during test db setup: " . $e->getMessage() . "\n";
    // We don't die here, so tests can still attempt to run (and will skip themselves if DB is missing)
}
