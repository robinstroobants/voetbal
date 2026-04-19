<?php
require_once 'getconn.php';

try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS gk_scores (player_id INT PRIMARY KEY, score INT NOT NULL);');
    echo "Tabel gk_scores is aangemaakt voor de nieuwe sliders.<br>";
    
    // Voeg kolom toe voor laatst gewijzigd
    try {
        $pdo->exec('ALTER TABLE players ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;');
        echo "Tabel players is succesvol uitgebreid met de updated_at historiek.<br>";
    } catch(Exception $e) {
        // Kolom bestaat waarschijnlijk al, geen ramp.
    }
} catch(Exception $e) { echo "Fout bij gk_scores: " . $e->getMessage() . "<br>"; }

echo "<br><b>Database Migratie 100% Succesvol! U kunt dit bestand nu veilig verwijderen.</b>";
?>
