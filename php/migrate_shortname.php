<?php
require_once 'getconn.php';

try {
    // 1. Haal mapping op
    $players = $pdo->query("SELECT id, shortname, first_name FROM players")->fetchAll(PDO::FETCH_ASSOC);
    $shortnameToId = [];
    foreach ($players as $p) {
        if (!empty($p['shortname'])) {
            $shortnameToId[$p['shortname']] = $p['id'];
        }
        $shortnameToId[$p['first_name']] = $p['id'];
    }

    // 2. Update de oude game_lineups die nog shortnames bevatten
    $lineups = $pdo->query("SELECT id, player_order FROM game_lineups")->fetchAll();
    $updateStmt = $pdo->prepare("UPDATE game_lineups SET player_order = ? WHERE id = ?");

    $migrated_count = 0;
    foreach ($lineups as $l) {
        $names = explode(',', $l['player_order']);
        $ids = [];
        $changed = false;
        foreach ($names as $name) {
            $name = trim($name);
            if (is_numeric($name)) {
                $ids[] = $name;
            } else {
                if (isset($shortnameToId[$name])) {
                    $ids[] = $shortnameToId[$name];
                    $changed = true;
                } else {
                    $ids[] = $name; // Fallback
                }
            }
        }
        if ($changed) {
            $updateStmt->execute([implode(',', $ids), $l['id']]);
            $migrated_count++;
        }
    }

    // 3. Verwijder kolom shortname
    try {
        $pdo->exec("ALTER TABLE players DROP COLUMN shortname");
    } catch(Exception $e) {}

    echo "Lineups geconverteerd naar pure IDs: $migrated_count\n";
    echo "Kolom shortname succesvol geëlimineerd.\n";
} catch(Exception $e) {
    echo "Fout: " . $e->getMessage();
}
?>
