<?php
require_once dirname(__DIR__, 1) . '/core/getconn.php';
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
        $schema_id = $_POST['schema_id'] ?? 0;
        $player_order = trim($_POST['player_order'] ?? '');
        $score = (float)($_POST['score'] ?? 0);

        if ($schema_id === 'DYNAMIC' || $schema_id == 0) {
            if (!empty($_POST['dynamic_json'])) {
                // Determine format and player_count from the game record
                $stmtGame = $pdo->prepare("SELECT format FROM games WHERE id = ?");
                $stmtGame->execute([$game_id]);
                $gameFormat = $stmtGame->fetchColumn() ?: 'unknown';
                $playerCount = count(explode(',', $player_order));

                $schemaData = json_decode($_POST['dynamic_json'], true);
                if (!$schemaData) {
                    echo json_encode(["status" => "error", "message" => "Ongeldige dynamische schema data"]);
                    exit;
                }
                
                $schemaJsonStr = json_encode($schemaData);

                // Insert into lineups
                $stmtInsert = $pdo->prepare("INSERT INTO lineups (team_id, game_format, schema_data, is_original, player_count, legacy_id) VALUES (?, ?, ?, 1, ?, 0)");
                $stmtInsert->execute([$_SESSION['team_id'] ?? 1, $gameFormat, $schemaJsonStr, $playerCount]);
                $schema_id = $pdo->lastInsertId();
            } else {
                echo json_encode(["status" => "error", "message" => "Missing schema or order"]);
                exit;
            }
        } else {
            $schema_id = (int)$schema_id;
            if ($schema_id <= 0 || empty($player_order)) {
                echo json_encode(["status" => "error", "message" => "Missing schema or order"]);
                exit;
            }
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
        
        $new_id = $pdo->lastInsertId();

        echo json_encode([
            "status" => "success", 
            "message" => "Voorselectie opgeslagen!",
            "lineup_id" => $new_id
        ]);
    } 
    elseif ($action === 'set_final') {
        $lineup_id = (int)($_POST['lineup_id'] ?? 0);
        
        // Reset alle andere lineups voor the game naar 0
        $pdo->prepare("UPDATE game_lineups SET is_final = 0, finalized_by_user_id = NULL WHERE game_id = ?")->execute([$game_id]);
        
        // Set this one to final and record the user who did it
        $userId = $_SESSION['user_id'] ?? null;
        $pdo->prepare("UPDATE game_lineups SET is_final = 1, finalized_by_user_id = ? WHERE id = ?")->execute([$userId, $lineup_id]);
        
        require_once dirname(__DIR__, 1) . '/models/MatchManager.php';
        $mm = new MatchManager($pdo);
        $mm->syncGameLogs($game_id);
        
        echo json_encode(["status" => "success"]);
    }
    elseif ($action === 'unlock') {
        // Check if user is allowed to unlock
        $checkLock = $pdo->prepare("SELECT finalized_by_user_id FROM game_lineups WHERE game_id = ? AND is_final = 1");
        $checkLock->execute([$game_id]);
        $finalizer = $checkLock->fetchColumn();
        
        $is_superadmin = isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
        if ($finalizer && $finalizer != $_SESSION['user_id'] && !$is_superadmin) {
            echo json_encode(["status" => "error", "message" => "Toegang geweigerd: Enkel de coach die deze opstelling definitief heeft gemaakt kan ze ontgrendelen."]);
            exit;
        }

        // Unlock all lineups for generating mode
        $pdo->prepare("UPDATE game_lineups SET is_final = 0, finalized_by_user_id = NULL WHERE game_id = ?")->execute([$game_id]);
        
        // Breek actieve share tokens
        $pdo->prepare("UPDATE games SET share_token = NULL, share_expires_at = NULL WHERE id = ?")->execute([$game_id]);
        
        require_once dirname(__DIR__, 1) . '/models/MatchManager.php';
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
