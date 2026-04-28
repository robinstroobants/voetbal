<?php
// migrate_events.php - Voer dit eenmalig online uit om de tabellen aan te maken.
require_once __DIR__ . '/php/core/getconn.php';

$sql = "
CREATE TABLE IF NOT EXISTS game_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    parent_email VARCHAR(255) NULL,
    event_type VARCHAR(50) NOT NULL,
    player_id INT NULL,
    player_out_id INT NULL,
    event_minute INT NOT NULL DEFAULT 0,
    is_confirmed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (player_out_id) REFERENCES players(id) ON DELETE SET NULL
);
";

try {
    $pdo->exec($sql);
    echo "<h2 style='color:green;'>Migratie Succesvol!</h2>";
    echo "<p>De tabel <b>game_events</b> is aangemaakt. Je kan dit bestand (migrate_events.php) nu verwijderen van de server voor de veiligheid.</p>";
} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Migratie Gefaald</h2>";
    echo "<p>Foutmelding: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
