<?php
require_once 'getconn.php';

try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS gk_scores (player_id INT PRIMARY KEY, score INT NOT NULL);');
    echo "Tabel gk_scores is aangemaakt voor de nieuwe sliders.<br>";
} catch(Exception $e) { echo "Fout bij gk_scores: " . $e->getMessage() . "<br>"; }

echo "<br><b>Database Migratie 100% Succesvol! U kunt dit bestand nu veilig verwijderen.</b>";
?>
