<?php
require_once dirname(__DIR__) . '/core/getconn.php';

// Zorg dat de tabel bestaat (workaround voor lokale migraties)
$pdo->exec("
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
    user_agent VARCHAR(512) NULL,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (player_out_id) REFERENCES players(id) ON DELETE SET NULL
);
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: $_POST;

    $action = $data['action'] ?? '';

    if ($action === 'log_event') {
        $gameId = (int)($data['game_id'] ?? 0);
        $parentEmail = $data['parent_email'] ?? null;
        $eventType = $data['event_type'] ?? '';
        $playerId = !empty($data['player_id']) ? (int)$data['player_id'] : null;
        $playerOutId = !empty($data['player_out_id']) ? (int)$data['player_out_id'] : null;
        $eventMinute = (int)($data['event_minute'] ?? 0);
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        
        // Coach overrides 'is_confirmed' to 1 immediately
        $isCoach = isset($_SESSION['user_id']) ? 1 : 0;

        if (!$gameId || !$eventType) {
            echo json_encode(['status' => 'error', 'message' => 'Missing data']);
            exit;
        }

        // Deduplicatie logica (bij "goal" of "assist")
        if (in_array($eventType, ['goal', 'assist']) && $playerId) {
            // Check voor bestaande zelfde event in zelfde minuut
            $stmt = $pdo->prepare("SELECT id, parent_email FROM game_events WHERE game_id = ? AND event_type = ? AND player_id = ? AND event_minute = ? AND is_deleted = 0");
            $stmt->execute([$gameId, $eventType, $playerId, $eventMinute]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['parent_email'] !== $parentEmail) {
                    // Gegroepeerde melding van 2 verschillende ouders -> we loggen geen nieuwe, we doen niks extra
                    echo json_encode(['status' => 'success', 'message' => 'Deduplicated (Other parent)']);
                    exit;
                }
                // Zelfde ouder? Mag wel (Robin scoort echt 2 keer in 1 minuut)
            }
        }

        $stmt = $pdo->prepare("INSERT INTO game_events (game_id, parent_email, event_type, player_id, player_out_id, event_minute, is_confirmed, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$gameId, $parentEmail, $eventType, $playerId, $playerOutId, $eventMinute, $isCoach, $userAgent]);

        echo json_encode(['status' => 'success', 'event_id' => $pdo->lastInsertId()]);
        exit;
    }
    
    if ($action === 'get_events') {
        $gameId = (int)($data['game_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT e.*, p1.first_name as p1_first, p1.last_name as p1_last, p2.first_name as p2_first, p2.last_name as p2_last 
                               FROM game_events e 
                               LEFT JOIN players p1 ON e.player_id = p1.id
                               LEFT JOIN players p2 ON e.player_out_id = p2.id
                               WHERE e.game_id = ? AND e.is_deleted = 0 
                               ORDER BY e.created_at ASC");
        $stmt->execute([$gameId]);
        echo json_encode(['status' => 'success', 'events' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'update_event_status') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
            exit;
        }
        $eventId = (int)($data['event_id'] ?? 0);
        $gameId = (int)($data['game_id'] ?? 0);
        $statusAction = $data['status_action'] ?? '';
        
        if ($statusAction === 'confirm') {
            $stmt = $pdo->prepare("UPDATE game_events SET is_confirmed = 1 WHERE id = ? AND game_id = ?");
            $stmt->execute([$eventId, $gameId]);
        } elseif ($statusAction === 'reject') {
            $stmt = $pdo->prepare("UPDATE game_events SET is_deleted = 1 WHERE id = ? AND game_id = ?");
            $stmt->execute([$eventId, $gameId]);
        }
        
        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($action === 'confirm_all_events') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
            exit;
        }
        $gameId = (int)($data['game_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE game_events SET is_confirmed = 1 WHERE game_id = ? AND is_deleted = 0 AND is_confirmed = 0");
        $stmt->execute([$gameId]);
        
        echo json_encode(['status' => 'success']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
