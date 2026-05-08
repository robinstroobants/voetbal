<?php
require_once dirname(__DIR__) . '/core/getconn.php';

// Zorg dat de tabel bestaat
$pdo->exec("
    CREATE TABLE IF NOT EXISTS score_matrix_snapshots (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        team_id       INT NOT NULL,
        label         VARCHAR(100) NULL,
        snapshot_data JSON NOT NULL,
        created_by    INT NULL,
        created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_team_created (team_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = $input['action'] ?? '';
$teamId = $_SESSION['team_id'] ?? 0;
$userId = $_SESSION['user_id'] ?? null;

if (!$teamId) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// ── Snapshot opslaan ───────────────────────────────────────────────────────
if ($action === 'save_snapshot') {
    $label = isset($input['label']) ? trim(substr($input['label'], 0, 100)) : null;
    $auto  = !empty($input['auto']); // auto = bij paginabezoek, geen label

    // Haal de HUIDIGE matrix op
    $stmtPlayers = $pdo->prepare("SELECT id FROM players WHERE team_id = ? AND deleted_at IS NULL");
    $stmtPlayers->execute([$teamId]);
    $playerIds = $stmtPlayers->fetchAll(PDO::FETCH_COLUMN);

    if (empty($playerIds)) {
        echo json_encode(['status' => 'error', 'message' => 'Geen spelers gevonden']);
        exit;
    }

    $ids_str = implode(',', array_map('intval', $playerIds));
    $sql = "SELECT player_id, position, score
            FROM player_scores
            WHERE player_id IN ($ids_str)
              AND (player_id, position, score_date) IN (
                  SELECT player_id, position, MAX(score_date)
                  FROM player_scores
                  WHERE player_id IN ($ids_str)
                  GROUP BY player_id, position
              )";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $matrix = [];
    foreach ($rows as $r) {
        $matrix[$r['player_id']][$r['position']] = (float)$r['score'];
    }

    if (empty($matrix)) {
        echo json_encode(['status' => 'error', 'message' => 'Matrix is leeg, niets om op te slaan']);
        exit;
    }

    // Auto-save: check of de recentste snapshot identiek is (vermijd duplicaten)
    if ($auto) {
        $last = $pdo->prepare("SELECT snapshot_data FROM score_matrix_snapshots WHERE team_id = ? ORDER BY created_at DESC LIMIT 1");
        $last->execute([$teamId]);
        $lastData = $last->fetchColumn();
        if ($lastData && json_decode($lastData, true) === $matrix) {
            echo json_encode(['status' => 'skipped', 'message' => 'Matrix ongewijzigd, geen nieuwe snapshot nodig']);
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO score_matrix_snapshots (team_id, label, snapshot_data, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$teamId, $label ?: null, json_encode($matrix), $userId]);
    $newId = $pdo->lastInsertId();

    // Bewaar max 20 snapshots per team (auto-clean oudste)
    $pdo->prepare("
        DELETE FROM score_matrix_snapshots
        WHERE team_id = ? AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM score_matrix_snapshots WHERE team_id = ? ORDER BY created_at DESC LIMIT 20
            ) sub
        )
    ")->execute([$teamId, $teamId]);

    echo json_encode(['status' => 'success', 'snapshot_id' => $newId]);
    exit;
}

// ── Historiek ophalen ─────────────────────────────────────────────────────
if ($action === 'get_history') {
    $stmt = $pdo->prepare("
        SELECT s.id, s.label, s.created_at, u.first_name, u.last_name
        FROM score_matrix_snapshots s
        LEFT JOIN users u ON s.created_by = u.id
        WHERE s.team_id = ?
        ORDER BY s.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$teamId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'snapshots' => $rows]);
    exit;
}

// ── Snapshot terugzetten als actieve matrix ───────────────────────────────
if ($action === 'restore_snapshot') {
    $snapshotId = (int)($input['snapshot_id'] ?? 0);
    if (!$snapshotId) {
        echo json_encode(['status' => 'error', 'message' => 'Geen snapshot ID']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT snapshot_data FROM score_matrix_snapshots WHERE id = ? AND team_id = ?");
    $stmt->execute([$snapshotId, $teamId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Snapshot niet gevonden']);
        exit;
    }

    $matrix = json_decode($row['snapshot_data'], true);
    if (!is_array($matrix)) {
        echo json_encode(['status' => 'error', 'message' => 'Ongeldige snapshot data']);
        exit;
    }

    // Schrijf de snapshot scores terug als nieuwe player_scores entries
    $pdo->beginTransaction();
    try {
        $insertStmt = $pdo->prepare("
            INSERT INTO player_scores (player_id, position, score, score_date)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE score = VALUES(score), score_date = NOW()
        ");

        foreach ($matrix as $playerId => $positions) {
            // Verifieer dat speler bij dit team hoort
            $check = $pdo->prepare("SELECT id FROM players WHERE id = ? AND team_id = ? AND deleted_at IS NULL");
            $check->execute([$playerId, $teamId]);
            if (!$check->fetchColumn()) continue;

            foreach ($positions as $position => $score) {
                // Voeg nieuwe score_date entry in (historiek bewaren)
                $pdo->prepare("INSERT INTO player_scores (player_id, position, score, score_date) VALUES (?, ?, ?, NOW())")
                    ->execute([$playerId, $position, $score]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Matrix hersteld vanuit snapshot #' . $snapshotId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ── Snapshot verwijderen ──────────────────────────────────────────────────
if ($action === 'delete_snapshot') {
    $snapshotId = (int)($input['snapshot_id'] ?? 0);
    $pdo->prepare("DELETE FROM score_matrix_snapshots WHERE id = ? AND team_id = ?")->execute([$snapshotId, $teamId]);
    echo json_encode(['status' => 'success']);
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Onbekende actie: ' . htmlspecialchars($action)]);
