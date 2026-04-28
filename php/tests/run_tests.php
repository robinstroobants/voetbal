<?php

// tests/run_tests.php
// Wrapper to set up the test database and run the tests.

$host = getenv('DB_HOST') ?: 'db';
$db = getenv('DB_NAME') ?: 'lineup_db';
$user = getenv('DB_USER') ?: 'app_user';
$pass = getenv('DB_PASS') ?: '';
$testDbName = 'lineup_test_db';

echo "🔧 Setting up Test Database ($testDbName)...\n";

try {
    // 1. Connect to live DB to get schema
    $pdoLive = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 2. Create test database
    try {
        $pdoLive->exec("CREATE DATABASE IF NOT EXISTS `$testDbName`");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            die("❌ Rechten ontbreken! Je database gebruiker '$user' heeft geen rechten om een nieuwe database aan te maken.\nOplossing: Log eenmalig in op je database als root en run:\nGRANT ALL PRIVILEGES ON `$testDbName`.* TO '$user'@'%'; CREATE DATABASE `$testDbName`;\n");
        }
        throw $e;
    }

    // 3. Connect to test DB
    $pdoTest = new PDO("mysql:host=$host;dbname=$testDbName;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 4. Copy Schema
    $tables = $pdoLive->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Disable foreign key checks temporarily during schema copy
    $pdoTest->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    foreach ($tables as $table) {
        // Drop existing table in test DB if it exists
        $pdoTest->exec("DROP TABLE IF EXISTS `$table`");
        
        // Get Create statement
        $stmt = $pdoLive->query("SHOW CREATE TABLE `$table`");
        $createStmt = $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'];
        
        // Create in test DB
        $pdoTest->exec($createStmt);
    }
    
    $pdoTest->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Test Database synchronized with live schema (copied " . count($tables) . " tables).\n\n";

} catch (PDOException $e) {
    die("❌ Error setting up test database: " . $e->getMessage() . "\n");
}

// 5. Override DB_NAME for getconn.php and PHPUnit tests
putenv("DB_NAME=$testDbName");
$_SERVER['DB_NAME'] = $testDbName;

// 6. Run the actual tests via PHPUnit
echo "🚀 Running PHPUnit Tests against Test Database...\n";
$phpunitCmd = __DIR__ . '/../vendor/bin/phpunit -c ' . __DIR__ . '/../phpunit.xml';
passthru($phpunitCmd, $returnCode);

exit($returnCode);
