<?php
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once dirname(__DIR__, 1) . '/core/getconn.php';
require_once dirname(__DIR__, 1) . '/models/MatchManager.php';
require_once dirname(__DIR__, 1) . '/models/game.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    die(json_encode(['success' => false, 'error' => 'Geen payload ontvangen']));
}

$gameId = (int)$data['game_id'];
$format = $data['format'];
$aantal = (int)$data['aantal'];
$originalId = (int)$data['original_schema_id'];
$blocks = $data['blocks'];
$force_update = !empty($data['force_settings_update']);

$old_schema = [];
if ($originalId > 0) {
    $stmtSchema = $pdo->prepare("SELECT schema_data FROM lineups WHERE id = ?");
    $stmtSchema->execute([$originalId]);
    $schema_json = $stmtSchema->fetchColumn();

    if (!$schema_json) {
        die(json_encode(['success' => false, 'error' => 'Origineel schema (ID '.$originalId.') onbekend!']));
    }

    $old_schema = json_decode($schema_json, true);
}
$new_schema = [];

$gk_count_schema = 0;
if (preg_match('/_(\d+)gk_/', $format, $m)) {
    $gk_count_schema = (int)$m[1];
}

foreach ($blocks as $idx => $b_data) {
    $shift_idx = (int)$b_data['shift'];
    
    // Copy the base parameters (duration, game_counter, start) from the original or payload
    if ($originalId > 0) {
        $new_block = $old_schema[$shift_idx];
    } else {
        $new_block = [
            'duration' => (int)$b_data['duration'],
            'game_counter' => (int)$b_data['game_counter'],
            'start' => $b_data['start']
        ];
    }
    
    // Update lineup en bench met the dragged data
    $new_block['lineup'] = $b_data['lineup'];
    // Make sure bench exists as array
    $new_block['bench'] = $b_data['bench'] ?? [];
    
    // Ensure keys in lineup are integers
    $clean_lineup = [];
    foreach($new_block['lineup'] as $pos => $sidx) {
        $clean_lineup[(int)$pos] = (int)$sidx;
    }
    $new_block['lineup'] = $clean_lineup;
    ksort($new_block['lineup']);
    
    // Auto-calculate subs by diffing with the previous block, if it's an odd block (mid-quarter)
    if ($shift_idx > 0 && ($shift_idx % 2 == 1)) {
        $prev_lineup = $new_schema[$shift_idx - 1]['lineup'];
        $subs_in = [];
        $subs_out = [];
        
        // Who went out?
        foreach ($prev_lineup as $pos => $pid) {
            if (!isset($clean_lineup[$pos]) || $clean_lineup[$pos] != $pid) {
                // If the player isn't in this position anymore, he must have gone out or swapped.
                // Assuming standard mid-quarter subs mean leaving the field
                $subs_out[$pos] = $pid;
            }
        }
        
        // Who came in?
        foreach ($clean_lineup as $pos => $pid) {
            if (!isset($prev_lineup[$pos]) || $prev_lineup[$pos] != $pid) {
                $subs_in[$pos] = $pid;
            }
        }
        
        if (!empty($subs_in) || !empty($subs_out)) {
            $new_block['subs'] = [
                'in' => $subs_in,
                'out' => $subs_out
            ];
        } else {
            // Remove subs rule if none exist anymore!
            unset($new_block['subs']);
        }
    }
    
    $new_schema[$shift_idx] = $new_block;
}

// 0. Payload Beveiliging: Voorkom dat half-geladen of corrupte payloads een heel schema overschrijven
if (count($new_schema) < 2) {
    die(json_encode(['success' => false, 'error' => 'Data corruptie beveiliging: Het netwerk heeft een incompleet schema (slechts 1 helft) doorgestuurd. Actie afgebroken om the database te beschermen.']));
}


// Nu berekenen we de min_pos categorie in the back ground
$min_pos = 999;
$playerPosCount = [];

foreach ($new_schema as $idx => $part) {
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

// 1. Min Posities Check (Vergelijk tegen game.min_pos)
$stmtGame = $pdo->prepare("SELECT min_pos FROM games WHERE id = ?");
$stmtGame->execute([$gameId]);
$game_min_pos = (int)$stmtGame->fetchColumn();

if ($cat < $game_min_pos && !$force_update) {
    die(json_encode([
        'success' => false,
        'requires_confirm' => true,
        'confirm_msg' => 'Je nieuwe schema degradeert sommigen tot exact ' . $cat . ' unieke positie(s). De wedstrijd had echter een drempel van minimaal ' . $game_min_pos . ' ingesteld. Wil je deze wedstrijd-instelling automatisch verlagen naar ' . ($cat > 1 ? $cat : "Geen minimum") . ' en het schema definitief opslaan?'
    ]));
}

// 2. Duplicate Check
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

$overwrite = isset($data['overwrite_mode']) ? filter_var($data['overwrite_mode'], FILTER_VALIDATE_BOOLEAN) : false;

$duplicate_id = null;
if (!$overwrite) {
    $stmtDup = $pdo->prepare("SELECT id, schema_data FROM lineups WHERE game_format = ? AND player_count = ? AND (team_id = ? OR team_id IS NULL)");
    $stmtDup->execute([$format, $aantal, $_SESSION['team_id']]);
    while ($row = $stmtDup->fetch()) {
        $db_schema = json_decode($row['schema_data'], true);
        if (schemas_are_identical($new_schema, $db_schema)) {
            $duplicate_id = $row['id'];
            break;
        }
    }
}

if ($duplicate_id !== null && !$overwrite) {
    // Schema bestaat al, we slaan niet nieuw op, we refereren enkel!
    if ($force_update && $cat < $game_min_pos) {
        $update_pos = $cat > 1 ? $cat : 0;
        $pdo->prepare("UPDATE games SET min_pos = ? WHERE id = ?")->execute([$update_pos, $gameId]);
    }
    
    // Bereken echte score voor validatie
    $volgorde = $data['volgorde'];
    $list_of_players = explode(',', $volgorde);
    
    global $events;
    $events = [];
    $events[$duplicate_id] = $new_schema;
    
    $matchManager = new MatchManager($pdo);
    $matchData = $matchManager->getSelection($gameId);
    
    // Forceren in de system globals voor game.php constructor scope
    $GLOBALS['player_scores'] = isset($matchData['player_scores']) && is_array($matchData['player_scores']) ? $matchData['player_scores'] : [];
    $GLOBALS['global_playerinfo'] = isset($matchData['player_info']) && is_array($matchData['player_info']) ? $matchData['player_info'] : [];
    
    // Zorg ervoor dat $format globaal beschikbaar is VOOR constructie
    global $events;
    $events = [];
    $events[$format][count($list_of_players)] = $new_schema;
    
    $gameObj = new Game($list_of_players, true, $format, 'none');
    $gameObj->setPlayerInfo($matchData['player_info']);
    $gameObj->setPlayerScores($matchData['player_scores']);
    // Overschrijf events expliciet om foutvrij te evalueren
    $gameObj->events = $new_schema;
    $gameObj->swapPlayers(); // FIX: Translate generic schema indices to actual player string keys!
    $gameObj->setTimePlayed(count($gameObj->events)-1);
    $gameObj->setRunQuality();
    $calculated_score = $gameObj->rating;
    
    $stmtInsert = $pdo->prepare("INSERT INTO game_lineups (game_id, schema_id, player_order, score, is_final) VALUES (?, ?, ?, ?, 0)");
    $stmtInsert->execute([$gameId, $duplicate_id, $volgorde, $calculated_score]);
    $lineup_id = $pdo->lastInsertId();
    
    echo json_encode(['success' => true, 'new_id' => $duplicate_id, 'is_duplicate' => true, 'lineup_id' => $lineup_id]);
    exit;
}

$overwrite = isset($data['overwrite_mode']) ? filter_var($data['overwrite_mode'], FILTER_VALIDATE_BOOLEAN) : false;

$new_id = null;

if ($overwrite && !empty($data['original_schema_id'])) {
    // Revisor modus: We overschrijven keihard het database record.
    $new_id = (int)$data['original_schema_id'];
    $stmtUpd = $pdo->prepare("UPDATE lineups SET schema_data = ? WHERE id = ?");
    $stmtUpd->execute([json_encode($new_schema), $new_id]);
    
    // Zorg dat the playtimelogs herrekend worden nu het schema (dat finaal kon zijn) is gewijzigd
    $mm = new MatchManager($pdo);
    $mm->syncGameLogs($gameId);
} else {
    // Nieuw schema opslaan, eventueel met parent_id = originalId
    $stmtIns = $pdo->prepare("INSERT INTO lineups (game_format, player_count, legacy_id, parent_id, schema_data, is_original, team_id) VALUES (?, ?, 0, ?, ?, 0, ?)");
    $stmtIns->execute([$format, $aantal, $originalId, json_encode($new_schema), $_SESSION['team_id']]);
    $new_id = $pdo->lastInsertId();
}

// Opschonen database
if ($force_update && $cat < $game_min_pos) {
    $update_pos = $cat > 1 ? $cat : 0;
    $pdo->prepare("UPDATE games SET min_pos = ? WHERE id = ?")->execute([$update_pos, $gameId]);
}

// Bereken score voor de opslag
$volgorde = $data['volgorde'];
$list_of_players = explode(',', $volgorde);

global $events;
$events = [];
$events[$new_id] = $new_schema;

$matchManager = new MatchManager($pdo);
$matchData = $matchManager->getSelection($gameId);

// Forceren in de system globals voor game.php constructor scope
$GLOBALS['player_scores'] = isset($matchData['player_scores']) && is_array($matchData['player_scores']) ? $matchData['player_scores'] : [];
$GLOBALS['global_playerinfo'] = isset($matchData['player_info']) && is_array($matchData['player_info']) ? $matchData['player_info'] : [];

global $events;
$events = [];
$events[$format][count($list_of_players)] = $new_schema;

$gameObj = new Game($list_of_players, true, $format, 'none');
$gameObj->setPlayerInfo($matchData['player_info']);
$gameObj->setPlayerScores($matchData['player_scores']);
// Ensure game_duration matches what setEvents expects natively:
if (preg_match('/_(\d+)x(\d+)$/', $format, $m)) {
    $gameObj->game_duration = (int)$m[2];
    $gameObj->nr_of_games = (int)$m[1];
}
$gameObj->events = $new_schema;
$gameObj->swapPlayers(); // FIX: Translate generic schema indices to actual player string keys!
$gameObj->setTimePlayed(count($gameObj->events)-1);
$gameObj->setRunQuality();
$calculated_score = $gameObj->rating;

$stmtInsert = $pdo->prepare("INSERT INTO game_lineups (game_id, schema_id, player_order, score, is_final) VALUES (?, ?, ?, ?, 0)");
$stmtInsert->execute([$gameId, $new_id, $volgorde, $calculated_score]);
$lineup_id = $pdo->lastInsertId();

echo json_encode(['success' => true, 'new_id' => $new_id, 'is_duplicate' => false, 'lineup_id' => $lineup_id, 'is_overwrite' => $overwrite]);
