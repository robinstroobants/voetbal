<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'getconn.php';

$dir = __DIR__ . '/wisselschemas';
$files = glob($dir . '/*sp.php');

$stmt = $pdo->query("
    SELECT l.id as lineup_id, l.game_id, l.schema_id, g.format,
           (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id) as count,
           (SELECT SUM(is_goalkeeper) FROM game_selections gs WHERE gs.game_id = g.id) as gk_count
    FROM game_lineups l
    JOIN games g ON l.game_id = g.id
");
$all_lineups = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getWisselFileName($format, $count, $gk_count) {
    if (strpos($format, 'gk') === false) {
        if (preg_match('/^(\d+v\d+)_(\d+x\d+)$/', $format, $matches)) {
            $format = $matches[1] . '_' . (int)$gk_count . 'gk_' . $matches[2];
        }
    }
    return $format . '_' . $count . 'sp.php';
}

$lineup_mappings = [];
foreach ($all_lineups as $l) {
    $fname = getWisselFileName($l['format'], $l['count'], $l['gk_count']);
    if (!isset($lineup_mappings[$fname])) $lineup_mappings[$fname] = [];
    $lineup_mappings[$fname][] = $l;
}

$outputLog = "";

foreach ($files as $file) {
    $fname = basename($file);
    $ws = [];
    include $file;
    if (empty($ws)) continue;

    $indexed_ws = [];
    $id_mapping = [];
    
    $counters = [
        1 => 10000,
        2 => 20000,
        3 => 30000
    ];

    $gk_count_schema = 0;
    if (preg_match('/_(\d+)gk_/', $fname, $m)) {
        $gk_count_schema = (int)$m[1];
    }

    foreach ($ws as $old_id => $schema) {
        $min_pos = 999;
        $playerPosCount = [];
        
        foreach ($schema as $idx => $part) {
            if (!is_numeric($idx) || empty($part['lineup'])) continue;
            foreach ($part['lineup'] as $pos => $pid) {
                if ($pid >= $gk_count_schema) { // Veldspeler
                    $playerPosCount[$pid][$pos] = true;
                }
            }
        }
        
        if (empty($playerPosCount)) {
            $min_pos = 1;
        } else {
            foreach ($playerPosCount as $pid => $arr) {
                if (count($arr) < $min_pos) $min_pos = count($arr);
            }
        }
        
        $cat = $min_pos;
        if ($cat < 1) $cat = 1;
        if ($cat > 3) $cat = 3;

        $new_id = $counters[$cat]++;
        $id_mapping[$old_id] = $new_id;
        $indexed_ws[$new_id] = $schema;
    }

    $file_content = "<?php\n\$ws_fname = '" . str_replace("sp.php","",$fname) . "';\n";
    $file_content .= "\$ws = " . var_export($indexed_ws, true) . ";\n";
    file_put_contents($file, $file_content);

    if (isset($lineup_mappings[$fname])) {
        foreach ($lineup_mappings[$fname] as $l) {
            $old = (int)$l['schema_id'];
            if (isset($id_mapping[$old]) && $id_mapping[$old] !== $old) {
                $newId = $id_mapping[$old];
                $pdo->prepare("UPDATE game_lineups SET schema_id = ? WHERE id = ?")
                    ->execute([$newId, $l['lineup_id']]);
                $outputLog .= "Updated lineup DB ID {$l['lineup_id']} for {$fname}: Schema {$old} -> {$newId}\n";
            }
        }
    }
    $outputLog .= "Reindexed {$fname}. (Total schemas: " . count($ws) . ")\n";
}

echo nl2br($outputLog);
echo "\nDONE!";
