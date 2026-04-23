<?php
// Beveiliging voor browser-executies: vereist superadmin login OF een specifieke deploy token (voor later mbv github actions)
$is_cli = (php_sapi_name() === 'cli');
if (!$is_cli) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if the user is a logged-in superadmin OR provided the correct secret token in the URL
    $deploy_secret = 'super_secret_deploy_key_2026';
    $is_superadmin = isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
    $has_token = isset($_GET['token']) && $_GET['token'] === $deploy_secret;

    if (!$is_superadmin && !$has_token) {
        http_response_code(403);
        die("<h1>403 Forbidden</h1><p>You do not have permission to run migrations here. To automate this, pass the correct token.</p>");
    }
}

require_once __DIR__ . '/getconn.php';

echo "<h1>🚀 Database Migration Engine</h1>";

try {
    // 1. Zorg ervoor dat de migrations tabel bestaat
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS system_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL UNIQUE,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Haal al uitgevoerde migraties op
    $stmt = $pdo->query("SELECT migration_name FROM system_migrations");
    $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Scan de php/migrations map
    $migrationsDir = __DIR__ . '/migrations';
    if (!is_dir($migrationsDir)) {
        die("Geen 'migrations' map gevonden in php/.");
    }

    $files = glob($migrationsDir . '/*.sql');
    sort($files); // Logische vordenring op basis van naam bvb 001_..., 002_...

    $executedCount = 0;

    echo "<ul>";
    foreach ($files as $file) {
        $basename = basename($file);
        
        if (in_array($basename, $executedMigrations)) {
            echo "<li><span style='color: green;'>✅ Reeds uitgevoerd:</span> <code>$basename</code></li>";
            continue;
        }

        // Voer de migratie uit
        echo "<li><span style='color: orange;'>⚡ Voert uit:</span> <code>$basename</code>... ";
        
        try {
            $sql = file_get_contents($file);
            
            // 🛑 SAFETY CHECK: Block any script that contains DROP TABLE to prevent data loss
            if (stripos($sql, 'DROP TABLE') !== false) {
                die("</ul><div style='padding: 20px; background: #ffebee; border: 1px solid red; color: darkred;'><strong>FATAL SECURITY ERROR:</strong> Migratiebestand <code>$basename</code> bevat een 'DROP TABLE' instructie. Dit is strikt verboden om database-leegmaak acties te voorkomen. Het migratieproces is stopgezet ter bescherming van je data.</div>");
            }

            $pdo->exec($sql);
            
            // Markeer als succesvol afgerond
            $stmtInsert = $pdo->prepare("INSERT INTO system_migrations (migration_name) VALUES (?)");
            $stmtInsert->execute([$basename]);
            
            echo "<span style='color: green;'>KLAAR!</span></li>";
            $executedCount++;
        } catch (PDOException $e) {
            $errorCode = $e->errorInfo[1] ?? 0;
            // 1060: Duplicate column name
            // 1050: Table already exists
            // 1061: Duplicate key name
            if (in_array($errorCode, [1050, 1060, 1061])) {
                // De migratie was eigenlijk al handmatig toegepast, dus we markeren deze als succesvol voltooid
                $stmtInsert = $pdo->prepare("INSERT IGNORE INTO system_migrations (migration_name) VALUES (?)");
                $stmtInsert->execute([$basename]);
                
                echo "<span style='color: orange;'>REEDS BESTAAND (overgeslagen).</span></li>";
                $executedCount++;
            } else {
                echo "<br><strong style='color: red;'>Fout bij migratie $basename:</strong> " . $e->getMessage() . "</li>";
                // Stop de reeks indien 1 patch faalt om database verwarring te vermijden
                die("</ul><div style='padding: 20px; background: #ffebee; border: 1px solid red; color: darkred;'><strong>Gestopt:</strong> Je database schema bevat fouten. Verhelp deze voordat je opnieuw migreert.</div>");
            }
        }
    }
    echo "</ul>";

    if ($executedCount === 0) {
        echo "<div style='padding: 20px; background: #e8f5e9; border: 1px solid green; color: darkgreen;'><strong>Alles is up to date!</strong> Geen nieuwe migraties uitgevoerd.</div>";
    } else {
        echo "<div style='padding: 20px; background: #e3f2fd; border: 1px solid blue; color: darkblue;'><strong>Succesvol!</strong> Er zijn $executedCount nieuwe migraties gedraaid op deze database.</div>";
    }

} catch (PDOException $e) {
    die("Database communicatiefout: " . $e->getMessage());
}
?>
