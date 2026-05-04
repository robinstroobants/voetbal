<?php
require_once dirname(__DIR__) . '/core/getconn.php';

// Zorg dat de tabel bestaat (workaround voor lokale migraties)
$pdo->exec("
CREATE TABLE IF NOT EXISTS game_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    parent_email VARCHAR(255) NULL,
    parent_name VARCHAR(100) NULL,
    event_type VARCHAR(50) NOT NULL,
    player_id INT NULL,
    player_out_id INT NULL,
    event_minute INT NOT NULL DEFAULT 0,
    is_confirmed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) DEFAULT 0,
    user_agent VARCHAR(512) NULL,
    ip_address VARCHAR(45) NULL,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (player_out_id) REFERENCES players(id) ON DELETE SET NULL
);
");

// Temporary workaround to convert the ENUM to VARCHAR in the live DB
try {
    $pdo->exec("ALTER TABLE game_events MODIFY event_type VARCHAR(50) NOT NULL;");
} catch (\Exception $e) {
    // Ignore errors if it already is varchar or fails
}

// Add parent_name column if missing (idempotent migration)
try {
    $pdo->exec("ALTER TABLE game_events ADD COLUMN parent_name VARCHAR(100) NULL AFTER parent_email;");
} catch (\Exception $e) {
    // Already exists - fine
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: $_POST;

    $action = $data['action'] ?? '';

    if ($action === 'log_event') {
        $gameId = (int)($data['game_id'] ?? 0);
        $parentEmail = $data['parent_email'] ?? null;
        $parentName = isset($data['parent_name']) ? substr(trim($data['parent_name']), 0, 100) : null;
        $eventType = $data['event_type'] ?? '';
        $playerId = !empty($data['player_id']) ? (int)$data['player_id'] : null;
        $playerOutId = !empty($data['player_out_id']) ? (int)$data['player_out_id'] : null;
        $eventMinute = (int)($data['event_minute'] ?? 0);
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        if ($ipAddress) {
            $ipAddress = explode(',', $ipAddress)[0]; // neem enkel eerste IP indien x-forwarded-for meerdere IPs heeft
        }
        
        // DEBUGGING TEMPORARY
        file_put_contents(__DIR__ . '/debug_events.log', date('Y-m-d H:i:s') . " - log_event: " . print_r($data, true) . "\n", FILE_APPEND);
        
        
        // Coach overrides 'is_confirmed' to 1 immediately
        $isCoach = isset($_SESSION['user_id']) ? 1 : 0;

        if (!$gameId || !$eventType) {
            echo json_encode(['status' => 'error', 'message' => 'Missing data']);
            exit;
        }

        $force = !empty($data['force']);
        if (!$force) {
            if ($eventType === 'goal' && $playerId) {
                // Check within 120 seconds
                $stmt = $pdo->prepare("
                    SELECT e.parent_email, e.event_minute, p.first_name, p.last_name 
                    FROM game_events e
                    LEFT JOIN players p ON e.player_id = p.id
                    WHERE e.game_id = ? AND e.event_type = 'goal' AND e.player_id = ? AND e.created_at > DATE_SUB(NOW(), INTERVAL 120 SECOND) AND e.is_deleted = 0
                    ORDER BY e.created_at DESC LIMIT 1
                ");
                $stmt->execute([$gameId, $playerId]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    $playerName = trim(($existing['first_name'] ?? '') . ' ' . ($existing['last_name'] ?? ''));
                    if (!$playerName) $playerName = 'deze speler';
                    // Altijd de naam tonen, nooit 'jou' — ook als het dezelfde ouder is
                    $doorWie = $existing['parent_name'] 
                        ? htmlspecialchars($existing['parent_name']) 
                        : explode('@', $existing['parent_email'] ?? 'onbekend')[0];
                    $warningText = "Er werd zojuist al een doelpunt gelogd voor " . htmlspecialchars($playerName) . " door " . $doorWie . " (in minuut " . $existing['event_minute'] . "). Wil je dit doelpunt toch extra toevoegen?";
                    
                    echo json_encode(['status' => 'warning', 'message' => 'duplicate_warning', 'warning_text' => $warningText]);
                    exit;
                }
            } elseif (in_array($eventType, ['opp_goal', 'tegengoal'])) {
                // Inclusief parent_name voor de dedup-melding
                $stmt = $pdo->prepare("
                    SELECT parent_email, parent_name, event_minute 
                    FROM game_events 
                    WHERE game_id = ? AND event_type IN ('opp_goal', 'tegengoal') AND created_at > DATE_SUB(NOW(), INTERVAL 90 SECOND) AND is_deleted = 0
                    ORDER BY created_at DESC LIMIT 1
                ");
                $stmt->execute([$gameId]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    // Altijd de naam tonen, nooit 'jou'
                    $doorWie = $existing['parent_name'] 
                        ? htmlspecialchars($existing['parent_name']) 
                        : explode('@', $existing['parent_email'] ?? 'onbekend')[0];
                    $warningText = "Er werd zojuist al een tegendoelpunt gelogd door " . $doorWie . " (in minuut " . $existing['event_minute'] . "). Wil je dit doelpunt toch extra toevoegen?";
                    
                    echo json_encode(['status' => 'warning', 'message' => 'duplicate_warning', 'warning_text' => $warningText]);
                    exit;
                }
            }
        }

        // ── State-machine deduplicatie voor status events ──────────────────────────
        // Status events zijn idempotent: we kijken naar de huidige spelstaat,
        // niet naar wie het verstuurt. Zo kunnen meerdere browsers nooit dubbel triggeren.
        if (in_array($eventType, ['period_start', 'period_end', 'match_end'])) {
            // Haal de laatste status event op
            $stmtState = $pdo->prepare("
                SELECT event_type, id
                FROM game_events
                WHERE game_id = ? AND event_type IN ('match_start','period_start','period_end','match_end') AND is_deleted = 0
                ORDER BY id DESC LIMIT 1
            ");
            $stmtState->execute([$gameId]);
            $lastState = $stmtState->fetch(PDO::FETCH_ASSOC);
            $lastStateType = $lastState['event_type'] ?? null;

            $reject = false;
            $rejectMsg = '';

            if ($eventType === 'match_end') {
                // Weiger als match al beëindigd is
                if ($lastStateType === 'match_end') {
                    $reject = true; $rejectMsg = 'Match is al beëindigd.';
                }
            } elseif ($eventType === 'period_end') {
                // Weiger als we al gepauzeerd zijn (laatste state is period_end of match niet gestart)
                if (!$lastStateType || $lastStateType === 'period_end' || $lastStateType === 'match_end') {
                    $reject = true; $rejectMsg = 'Periode is al afgelopen of match niet gestart.';
                }
            } elseif ($eventType === 'period_start') {
                // Weiger enkel als een periode al actief is (laatste status = period_start of match_start ZONDER tussenliggende period_end)
                // match_start gevolgd door period_start is geldig (vliegende wissel na aanvang)
                $stmtCheck = $pdo->prepare("
                    SELECT event_type FROM game_events
                    WHERE game_id = ? AND event_type IN ('match_start','period_start','period_end','match_end') AND is_deleted = 0
                    ORDER BY id DESC LIMIT 2
                ");
                $stmtCheck->execute([$gameId]);
                $recentStates = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
                // Blokkeer enkel als de laatste state period_start IS en er geen period_end tussenin zit
                if (count($recentStates) >= 1 && $recentStates[0] === 'period_start') {
                    $reject = true; $rejectMsg = 'Periode loopt al — dubbele start geweigerd.';
                }
            }

            if ($reject) {
                file_put_contents(__DIR__ . '/debug_events.log', date('Y-m-d H:i:s') . " - DEDUP REJECT $eventType (last=$lastStateType) by $parentEmail\n", FILE_APPEND);
                echo json_encode(['status' => 'deduped', 'message' => 'Deduped: ' . $rejectMsg]);
                exit;
            }
        }
        // ────────────────────────────────────────────────────────────────────────────

        $stmt = $pdo->prepare("INSERT INTO game_events (game_id, parent_email, parent_name, event_type, player_id, player_out_id, event_minute, is_confirmed, user_agent, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$gameId, $parentEmail, $parentName, $eventType, $playerId, $playerOutId, $eventMinute, $isCoach, $userAgent, $ipAddress]);
        
        if (!$success) {
            $err = $stmt->errorInfo();
            file_put_contents(__DIR__ . '/debug_events.log', date('Y-m-d H:i:s') . " - DB ERROR: " . print_r($err, true) . "\n", FILE_APPEND);
        }

        // ── Feature Telemetry: ouder logt een event (share feature) ───────────────
        // user_id = 0 (niet ingelogd), team_id via game_id, context = event type
        try {
            $stmtTeam = $pdo->prepare("SELECT team_id FROM games WHERE id = ?");
            $stmtTeam->execute([$gameId]);
            $eventTeamId = (int)($stmtTeam->fetchColumn() ?: 0);
            $pdo->prepare("INSERT INTO usage_logs (user_id, team_id, action_type, cost_weight, context) VALUES (0, ?, 'game_event_log', 1, ?)")
                ->execute([$eventTeamId, $eventType]);
        } catch (\Exception $e) { /* non-blocking */ }
        // ─────────────────────────────────────────────────────────────────────────

        echo json_encode(['status' => 'success', 'event_id' => $pdo->lastInsertId()]);
        exit;
    }
    
    if ($action === 'start_block') {
        // ── Architectureel correcte shift-jump: springt direct naar target_shift_index ──
        // Berekent server-side hoeveel period_starts nodig zijn + logt auto-substitutions
        $gameId       = (int)($data['game_id'] ?? 0);
        $parentEmail  = $data['parent_email'] ?? 'auto@systeem';
        $parentName   = isset($data['parent_name']) ? substr(trim($data['parent_name']), 0, 100) : null;
        $targetIdx    = (int)($data['target_shift_index'] ?? 0); // 0-based
        $subs         = $data['subs'] ?? []; // [{player_in, player_out, minute}]
        $isCoach      = isset($_SESSION['user_id']) ? 1 : 0;
        $userAgent    = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $ipAddress    = explode(',', $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '')[0] ?: null;

        if (!$gameId) {
            echo json_encode(['status' => 'error', 'message' => 'Missing game_id']);
            exit;
        }

        // Huidige shift index = aantal block events - 1
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM game_events WHERE game_id = ? AND event_type IN ('match_start','period_start') AND is_deleted = 0");
        $stmtCount->execute([$gameId]);
        $currentShiftIndex = max(0, (int)$stmtCount->fetchColumn() - 1);

        $stepsNeeded = $targetIdx - $currentShiftIndex;
        if ($stepsNeeded <= 0) {
            echo json_encode(['status' => 'deduped', 'message' => 'Al op of voorbij target shift']);
            exit;
        }

        // Verifieer dat de huidige state een period_start toelaat
        $stmtState = $pdo->prepare("SELECT event_type FROM game_events WHERE game_id = ? AND event_type IN ('match_start','period_start','period_end','match_end') AND is_deleted = 0 ORDER BY id DESC LIMIT 1");
        $stmtState->execute([$gameId]);
        $lastState = $stmtState->fetchColumn();
        if ($lastState === 'period_start') {
            echo json_encode(['status' => 'deduped', 'message' => 'Periode loopt al']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            $stmtInsert = $pdo->prepare("INSERT INTO game_events (game_id, parent_email, parent_name, event_type, player_id, player_out_id, event_minute, is_confirmed, user_agent, ip_address) VALUES (?, ?, ?, ?, NULL, NULL, 0, ?, ?, ?)");

            for ($step = 0; $step < $stepsNeeded; $step++) {
                $stmtInsert->execute([$gameId, $parentEmail, $parentName, 'period_start', $isCoach, $userAgent, $ipAddress]);

                // Op de LAATSTE stap: log de schema-substitutions automatisch
                if ($step === $stepsNeeded - 1 && !empty($subs)) {
                    $stmtSub = $pdo->prepare("INSERT INTO game_events (game_id, parent_email, event_type, player_id, player_out_id, event_minute, is_confirmed) VALUES (?, 'auto@systeem', 'substitution', ?, ?, ?, 1)");
                    foreach ($subs as $sub) {
                        $pIn  = !empty($sub['player_in'])  ? (int)$sub['player_in']  : null;
                        $pOut = !empty($sub['player_out']) ? (int)$sub['player_out'] : null;
                        $min  = (int)($sub['minute'] ?? 0);
                        if ($pIn || $pOut) {
                            $stmtSub->execute([$gameId, $pIn, $pOut, $min]);
                        }
                    }
                }
            }

        $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'start_game') {
        // ── Game-niveau start: 1 period_start per wedstrijd (geen helften) ──
        $gameId              = (int)($data['game_id'] ?? 0);
        $parentEmail         = $data['parent_email'] ?? 'auto@systeem';
        $parentName          = isset($data['parent_name']) ? substr(trim($data['parent_name']), 0, 100) : null;
        $targetGameCounter   = (int)($data['target_game_counter'] ?? 2); // 1-based, min 2
        $isCoach             = isset($_SESSION['user_id']) ? 1 : 0;
        $userAgent           = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $ipAddress           = explode(',', $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '')[0] ?: null;

        if (!$gameId || $targetGameCounter < 2) {
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige parameters']);
            exit;
        }

        // Huidig aantal block events = huidig game_counter
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM game_events WHERE game_id = ? AND event_type IN ('match_start','period_start') AND is_deleted = 0");
        $stmtCount->execute([$gameId]);
        $currentGameCounter = (int)$stmtCount->fetchColumn();

        if ($currentGameCounter >= $targetGameCounter) {
            echo json_encode(['status' => 'deduped', 'message' => 'Wedstrijd ' . $targetGameCounter . ' al gestart']);
            exit;
        }

        // Laatste status event moet period_end zijn (vorige game gestopt)
        $stmtState = $pdo->prepare("SELECT event_type FROM game_events WHERE game_id = ? AND event_type IN ('match_start','period_start','period_end','match_end') AND is_deleted = 0 ORDER BY id DESC LIMIT 1");
        $stmtState->execute([$gameId]);
        $lastState = $stmtState->fetchColumn();

        if ($lastState === 'period_start' || $lastState === 'match_start') {
            echo json_encode(['status' => 'deduped', 'message' => 'Vorige wedstrijd is nog actief — stop eerst']);
            exit;
        }

        // Eén period_start = start van de volgende wedstrijd
        $stmtInsert = $pdo->prepare("INSERT INTO game_events (game_id, parent_email, parent_name, event_type, player_id, player_out_id, event_minute, is_confirmed, user_agent, ip_address) VALUES (?, ?, ?, 'period_start', NULL, NULL, 0, ?, ?, ?)");
        $stmtInsert->execute([$gameId, $parentEmail, $parentName, $isCoach, $userAgent, $ipAddress]);

        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($action === 'get_events') {
        $gameId = (int)($data['game_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT e.*, p1.first_name as p1_first, p1.last_name as p1_last, p2.first_name as p2_first, p2.last_name as p2_last 
                               FROM game_events e 
                               LEFT JOIN players p1 ON e.player_id = p1.id
                               LEFT JOIN players p2 ON e.player_out_id = p2.id
                               WHERE e.game_id = ? AND e.is_deleted = 0 
                               ORDER BY e.created_at ASC, e.id ASC");
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

    if ($action === 'delete_all_events') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
            exit;
        }
        $gameId = (int)($data['game_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE game_events SET is_deleted = 1 WHERE game_id = ?");
        $stmt->execute([$gameId]);
        
        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($action === 'delete_own_event') {
        $eventId = (int)($data['event_id'] ?? 0);
        $parentEmail = $data['parent_email'] ?? '';
        
        if ($eventId && $parentEmail) {
            $stmt = $pdo->prepare("UPDATE game_events SET is_deleted = 1 WHERE id = ? AND parent_email = ? AND is_confirmed = 0");
            $stmt->execute([$eventId, $parentEmail]);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($action === 'update_event_time') {
        $eventId = (int)($data['event_id'] ?? 0);
        $newTime = $data['new_time'] ?? ''; // HH:MM
        
        if ($eventId && preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $newTime)) {
            $stmt = $pdo->prepare("UPDATE game_events SET created_at = CONCAT(DATE(created_at), ' ', ?, ':00') WHERE id = ?");
            $stmt->execute([$newTime, $eventId]);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
