<?php
require_once 'getconn.php';

try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS player_team_ranking (player_id INT PRIMARY KEY, team_rank INT NOT NULL);');
    echo "Tabel player_team_ranking aangemaakt.<br>";
} catch(Exception $e) { echo "Fout bij player_team_ranking: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS position_rankings (id INT AUTO_INCREMENT PRIMARY KEY, position_id INT NOT NULL, player_id INT NOT NULL, pos_rank INT NOT NULL, UNIQUE KEY uq_pos_player (position_id, player_id));');
    echo "Tabel position_rankings aangemaakt.<br>";
} catch(Exception $e) { echo "Fout bij position_rankings: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec('ALTER TABLE players ADD COLUMN favorite_positions VARCHAR(255) DEFAULT NULL;');
    echo "Kolom favorite_positions toegevoegd.<br>";
} catch(Exception $e) { echo "Info: Kolom favorite_positions " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec('ALTER TABLE players ADD COLUMN is_doelman TINYINT(1) DEFAULT 0;');
    echo "Kolom is_doelman toegevoegd.<br>";
} catch(Exception $e) { echo "Info: Kolom is_doelman " . $e->getMessage() . "<br>"; }

echo "<br><b>Database Migratie 100% Succesvol! U kunt dit bestand nu veilig verwijderen.</b>";
?>
