<?php
require_once 'getconn.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid method"]);
    exit;
}

$action = $_POST['action'] ?? '';
$game_id = (int)($_POST['game_id'] ?? 0);

if ($game_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid game_id"]);
    exit;
}

try {
    // Valideer of de game wel aan dit team toebehoort
    $checkTeam = $pdo->prepare("SELECT id FROM games WHERE id = ? AND team_id = ?");
    $checkTeam->execute([$game_id, $_SESSION['team_id']]);
    if (!$checkTeam->fetchColumn()) {
        echo json_encode(["status" => "error", "message" => "Toegang geweigerd: deze match behoort niet tot uw team."]);
        exit;
    }

    if ($action === 'save_preselection') {
        $schema_id = (int)($_POST['schema_id'] ?? 0);
        $player_order = trim($_POST['player_order'] ?? '');
        $score = (float)($_POST['score'] ?? 0);

        if ($schema_id <= 0 || empty($player_order)) {
            echo json_encode(["status" => "error", "message" => "Missing schema or order"]);
            exit;
        }

        // Controleer of deze zelfde voorselectie al bestaat voor de wedstrijd
        $checkStmt = $pdo->prepare("SELECT id FROM game_lineups WHERE game_id = ? AND schema_id = ? AND player_order = ?");
        $checkStmt->execute([$game_id, $schema_id, $player_order]);
        if ($checkStmt->fetch()) {
             echo json_encode(["status" => "error", "message" => "Deze specifieke opstelling is al bewaard voor deze wedstrijd."]);
             exit;
        }

        // Add to game_lineups
        $stmt = $pdo->prepare("INSERT INTO game_lineups (game_id, schema_id, player_order, score, is_final) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$game_id, $schema_id, $player_order, $score]);

        echo json_encode(["status" => "success", "message" => "Voorselectie opgeslagen!"]);
    } 
    elseif ($action === 'set_final') {
        $lineup_id = (int)($_POST['lineup_id'] ?? 0);
        
        // Reset alle andere lineups voor the game naar 0
        $pdo->prepare("UPDATE game_lineups SET is_final = 0 WHERE game_id = ?")->execute([$game_id]);
        
        // Set this one to final
        $pdo->prepare("UPDATE game_lineups SET is_final = 1 WHERE id = ?")->execute([$lineup_id]);
        
        require_once 'MatchManager.php';
        $mm = new MatchManager($pdo);
        $mm->syncGameLogs($game_id);
        
        echo json_encode(["status" => "success"]);
    }
    elseif ($action === 'unlock') {
        // Unlock all lineups for generating mode
        $pdo->prepare("UPDATE game_lineups SET is_final = 0 WHERE game_id = ?")->execute([$game_id]);
        
        require_once 'MatchManager.php';
        $mm = new MatchManager($pdo);
        $mm->syncGameLogs($game_id);
        
        echo json_encode(["status" => "success"]);
    }
    elseif ($action === 'delete') {
        $lineup_id = (int)($_POST['lineup_id'] ?? 0);
        $pdo->prepare("DELETE FROM game_lineups WHERE id = ?")->execute([$lineup_id]);
        echo json_encode(["status" => "success"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
