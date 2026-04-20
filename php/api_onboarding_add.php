<?php
require_once 'getconn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$action = $_POST['action'] ?? '';
$team_id = (int)$_SESSION['team_id'];

if (!$team_id) {
    echo json_encode(['success' => false, 'error' => 'No team assigned']);
    exit;
}

$stmtT = $pdo->prepare("SELECT default_format FROM teams WHERE id = ?");
$stmtT->execute([$team_id]);
$default_format = $stmtT->fetchColumn() ?: '8v8';

$max_players = 24;
if (strpos($default_format, '2v2') === 0 || strpos($default_format, '3v3') === 0) {
    $max_players = 12;
}

$stmtP = $pdo->prepare("SELECT COUNT(*) FROM players WHERE team_id = ?");
$stmtP->execute([$team_id]);
$current_players = (int)$stmtP->fetchColumn();

if ($action === 'add_single_player') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $favorite_positions = trim($_POST['favorite_positions'] ?? '');
    $is_doelman = !empty($_POST['is_doelman']) ? 1 : 0;
    
    if (empty($first_name)) {
        echo json_encode(['success' => false, 'error' => 'Voornaam is verplicht']);
        exit;
    }
    
    if ($current_players >= $max_players) {
        echo json_encode(['success' => false, 'error' => "Limiet bereikt. Jouw format ondersteunt maximaal $max_players spelers."]);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO players (team_id, first_name, last_name, favorite_positions, is_doelman) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$team_id, $first_name, $last_name, $favorite_positions, $is_doelman]);
        $player_id = $pdo->lastInsertId();
        
        $stmtScores = $pdo->prepare("INSERT INTO player_scores (player_id, position, score, score_date) VALUES (?, ?, ?, CURDATE())");
        for ($pos = 1; $pos <= 11; $pos++) {
            if ($is_doelman) {
                $score = ($pos == 1) ? 50 : 0;
            } else {
                $score = ($pos == 1) ? 0 : 50;
            }
            $stmtScores->execute([$player_id, $pos, $score]);
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'add_bulk_players') {
    $players_text = trim($_POST['players_text'] ?? '');
    if (empty($players_text)) {
        echo json_encode(['success' => false, 'error' => 'Tekstvak is leeg']);
        exit;
    }
    
    $lines = explode("\n", str_replace("\r", "", $players_text));
    $valid_lines = [];
    foreach ($lines as $line) {
        if (trim($line) !== '') {
            $valid_lines[] = trim($line);
        }
    }
    
    $attempted_count = count($valid_lines);
    if ($attempted_count === 0) {
        echo json_encode(['success' => false, 'error' => 'Geen geldige namen gevonden.']);
        exit;
    }
    
    if (($current_players + $attempted_count) > $max_players) {
        $remaining = max(0, $max_players - $current_players);
        echo json_encode(['success' => false, 'error' => "Je probeert $attempted_count spelers toe te voegen, maar je hebt nog maar plaats voor $remaining speler(s) (Max $max_players). Pas je lijst aan."]);
        exit;
    }
    
    $added = 0;
    
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO players (team_id, first_name, last_name) VALUES (?, ?, ?)");
        $stmtScores = $pdo->prepare("INSERT INTO player_scores (player_id, position, score, score_date) VALUES (?, ?, ?, CURDATE())");
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = explode(' ', $line, 2);
            $fn = trim($parts[0] ?? '');
            $ln = trim($parts[1] ?? '');
            
            if ($fn) {
                $stmt->execute([$team_id, $fn, $ln]);
                $player_id = $pdo->lastInsertId();
                
                // Veldspeler (is_doelman = 0 default bij bulk)
                for ($pos = 1; $pos <= 11; $pos++) {
                    $score = ($pos == 1) ? 0 : 50;
                    $stmtScores->execute([$player_id, $pos, $score]);
                }
                $added++;
            }
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'added' => $added]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'add_coach') {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Naam is verplicht']);
        exit;
    }
    
    // Unieke check per team
    $check = $pdo->prepare("SELECT id FROM coaches WHERE name = ? AND team_id = ?");
    $check->execute([$name, $team_id]);
    if ($check->fetch()) {
         echo json_encode(['success' => false, 'error' => 'Deze coach bestaat al']);
         exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO coaches (team_id, name) VALUES (?, ?)");
    if ($stmt->execute([$team_id, $name])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database fout']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
?>
