<?php
require_once 'getconn.php';

// 1. Create table
$pdo->exec("
CREATE TABLE IF NOT EXISTS lineups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_format VARCHAR(50) NOT NULL,
    player_count INT NOT NULL,
    legacy_id INT NOT NULL,
    parent_id INT NULL,
    schema_data JSON NOT NULL,
    is_original BOOLEAN DEFAULT 1,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES lineups(id) ON DELETE SET NULL
)
");

echo "Table `lineups` verified/created.\n";

// 2. Read and insert
$wisselschemas_dir = __DIR__ . '/wisselschemas/';
$files = glob($wisselschemas_dir . '*.php');

$duplicates = 0;
$imported = 0;

function schemas_are_identical($sch1, $sch2) {
    if (count($sch1) !== count($sch2)) return false;
    foreach ($sch1 as $idx => $s1) {
        $s2 = $sch2[$idx] ?? null;
        if (!$s2) return false;
        
        $l1 = $s1['lineup'] ?? []; ksort($l1);
        $l2 = $s2['lineup'] ?? []; ksort($l2);
        if ($l1 != $l2) return false;
        
        $b1 = $s1['bench'] ?? []; sort($b1);
        $b2 = $s2['bench'] ?? []; sort($b2);
        if ($b1 != $b2) return false;
    }
    return true;
}

$active_map = [];

foreach ($files as $file) {
    $basename = basename($file, '.php');
    if (preg_match('/^(.*)_(\d+)sp$/', $basename, $matches)) {
        $game_format = $matches[1];
        $player_count = (int)$matches[2];
        
        include $file; 
        
        if (!isset($ws) || !is_array($ws)) continue;
        
        foreach ($ws as $legacy_id => $schema) {
            $json_data = json_encode($schema);
            
            $stmt = $pdo->prepare("SELECT id, schema_data FROM lineups WHERE game_format = ? AND player_count = ?");
            $stmt->execute([$game_format, $player_count]);
            
            $is_duplicate = false;
            $new_id = null;
            
            while ($row = $stmt->fetch()) {
                $existing_schema = json_decode($row['schema_data'], true);
                if (schemas_are_identical($schema, $existing_schema)) {
                    $is_duplicate = true;
                    $new_id = $row['id'];
                    break;
                }
            }
            
            if ($is_duplicate) {
                $duplicates++;
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO lineups (game_format, player_count, legacy_id, schema_data, is_original) VALUES (?, ?, ?, ?, 1)");
                $stmtIns->execute([$game_format, $player_count, $legacy_id, $json_data]);
                $new_id = $pdo->lastInsertId();
                $imported++;
            }
            
            $active_map[] = [
                'new_id' => $new_id,
                'legacy_id' => $legacy_id,
                'game_format' => $game_format,
                'player_count' => $player_count
            ];
        }
        unset($ws); 
    }
}

echo "Imported: $imported. Duplicates skipped (merged): $duplicates.\n";

// 3. Patch historic game_lineups
echo "Patching `game_lineups`...\n";
$patched = 0;
foreach ($active_map as $map) {
    $base_format = preg_replace('/_\dgk_/', '_', $map['game_format']); 
    
    $q = "
        UPDATE game_lineups 
        SET schema_id = :new_id 
        WHERE schema_id = :legacy_id 
          AND game_id IN (
              SELECT id FROM games 
              WHERE format IN (:f1, :f2) 
                AND (SELECT COUNT(*) FROM game_selections WHERE game_id = games.id) = :pc
          )
    ";
    $stmt = $pdo->prepare($q);
    $stmt->execute([
        'new_id' => $map['new_id'],
        'legacy_id' => $map['legacy_id'],
        'f1' => $base_format,
        'f2' => $map['game_format'],
        'pc' => $map['player_count']
    ]);
    
    $patched += $stmt->rowCount();
}

// Update remaining dummy rows that might trigger constraints?
// If any backups/dummies got missed because format was manually changed, let's force a blind cleanup if schema_id > 10000 
// Actually safe to not do blind cleanup.

echo "Patched $patched historical lineup references.\n";
echo "Migration complete.\n";

?>
