<?php
require_once dirname(__DIR__, 2) . '/core/getconn.php';

// Bepaal de zichtbare posities a.d.h.v. het format van dit team
$stmtF = $pdo->prepare("SELECT default_format FROM teams WHERE id = ?");
$stmtF->execute([$_SESSION['team_id']]);
$default_format = $stmtF->fetchColumn() ?: '8v8';

$visible_positions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]; // default
if (strpos($default_format, '2v2') === 0 || strpos($default_format, '3v3') === 0) {
    // 2v2 en 3v3 hebben the matrix helemaal niet nodig!
    require_once dirname(__DIR__, 2) . '/header.php';
    echo '<div class="container mt-5">';
    echo '<div class="alert alert-info shadow-sm text-center py-5">';
    echo '  <i class="fa-solid fa-face-smile-wink fa-3x text-primary mb-3"></i>';
    echo '  <h3>Fun Formats hebben geen Matrix nodig!</h3>';
    echo '  <p class="mb-0">Bij 2v2 en 3v3 draait het volledig om plezier. De exacte opstelling of matrix scores maken hier niets uit en the generator verdeelt de speeltijd gewoon eerlijk.</p>';
    echo '  <a href="/" class="btn btn-primary mt-4"><i class="fa-solid fa-arrow-left me-2"></i>Terug naar dashboard</a>';
    echo '</div>';
    echo '</div>';
    require_once dirname(__DIR__, 2) . '/footer.php';
    exit;
} elseif (strpos($default_format, '5v5') === 0) {
    $visible_positions = [1, 4, 7, 9, 11];
} elseif (strpos($default_format, '8v8') === 0) {
    $visible_positions = [1, 2, 4, 5, 7, 9, 10, 11];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_matrix') {
    // Reset alle scores voor het hele team
    $stmtPlayers = $pdo->prepare("SELECT id, is_doelman FROM players WHERE team_id = ? AND deleted_at IS NULL");
    $stmtPlayers->execute([$_SESSION['team_id']]);
    $teamPlayers = $stmtPlayers->fetchAll(PDO::FETCH_ASSOC);
    $allPos = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];

    foreach ($teamPlayers as $p) {
        $pId = $p['id'];
        foreach ($allPos as $position) {
            if (!empty($p['is_doelman'])) {
                // Doelman: krijg enkel op pos 1 een startscore
                $score = ($position == 1) ? 50 : 0;
            } else {
                // Veldspeler: krijgt overal een startscore behalve niet in de goal (pos 1)
                $score = ($position == 1) ? 0 : 50;
            }

            $check_sql = "SELECT id, score FROM player_scores 
                          WHERE player_id = ? AND position = ? 
                          AND score_date >= DATE_SUB(NOW(), INTERVAL 5 DAY)
                          ORDER BY score_date DESC LIMIT 1";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$pId, $position]);
            $row = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                if (floatval($row['score']) !== (float)$score) {
                    $pdo->prepare("UPDATE player_scores SET score = ?, score_date = NOW() WHERE id = ?")->execute([$score, $row['id']]);
                }
            } else {
                $pdo->prepare("INSERT INTO player_scores (player_id, position, score, score_date) VALUES (?, ?, ?, NOW())")->execute([$pId, $position, $score]);
            }
        }
    }
    header("Location: edit_scores.php?success=reset");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['player_id']) && !isset($_POST['action'])) {
    $player_id = intval($_POST['player_id']);
    
    // Validatie of speler wel in dit team zit
    $val = $pdo->prepare("SELECT id FROM players WHERE id=? AND team_id=?");
    $val->execute([$player_id, $_SESSION['team_id']]);
    if ($val->fetchColumn()) {
        foreach($_POST as $k=>$v){
            if ($k != "player_id"){
                $position = intval(str_replace('pos_', '', $k));
                $score = floatval($v);
                
                // Check of er al een score is in de laatste 5 dagen
                $check_sql = "SELECT id, score FROM player_scores 
                              WHERE player_id = ? AND position = ? 
                              AND score_date >= DATE_SUB(NOW(), INTERVAL 5 DAY)
                              ORDER BY score_date DESC LIMIT 1";

                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->execute([$player_id, $position]);
                $row = $check_stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    // Alleen updaten als de score verschilt
                    if (floatval($row['score']) !== $score) {
                        $update_sql = "UPDATE player_scores SET score = ?, score_date = NOW() WHERE id = ?";
                        $update_stmt = $pdo->prepare($update_sql);
                        $update_stmt->execute([$score, $row['id']]);
                    }
                } else {
                    // Voeg nieuwe score toe
                    $insert_sql = "INSERT INTO player_scores (player_id, position, score, score_date)
                                   VALUES (?, ?, ?, NOW())";
                    $insert_stmt = $pdo->prepare($insert_sql);
                    $insert_stmt->execute([$player_id, $position, $score]);
                }
            }
        }
    }
}

// Scores ophalen per speler en positie (laatste score), gefilterd op team
$stmtAll = $pdo->prepare("SELECT * FROM players WHERE team_id = ? AND deleted_at IS NULL ORDER BY first_name, last_name");
$stmtAll->execute([$_SESSION['team_id']]);
$players = [];
$player_ids = [];
while ($row = $stmtAll->fetch(PDO::FETCH_ASSOC)) {
    $players[$row['id']]["id"] = $row['id'];
    $players[$row['id']]["first_name"] = $row['first_name'];
    $players[$row['id']]["last_name"] = $row['last_name'];
    $player_ids[] = $row['id'];
}

$scores = [];
if (!empty($player_ids)) {
    $ids_str = implode(',', $player_ids);
    $sql = "SELECT player_id, position, score
            FROM player_scores
            WHERE player_id IN ($ids_str)
              AND (player_id, position, score_date) IN (
                  SELECT player_id, position, MAX(score_date)
                  FROM player_scores
                  WHERE player_id IN ($ids_str)
                  GROUP BY player_id, position
              )";
    $result_scores = $pdo->query($sql);
    while ($row = $result_scores->fetch(PDO::FETCH_ASSOC)) {
        $scores[$row['player_id']][$row['position']] = $row['score'];
    }
}
?>

<?php 
$page_title = 'Edit Player scores';
require_once dirname(__DIR__, 2) . '/header.php';
?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Score Matrix</h2>
        <form method="post" onsubmit="return confirm('Weet je zeker dat je de matrix voor alle spelers wilt resetten naar baseline? Veldspelers=50, Doelmannen krijgen 0 (uitgezonderd op positie 1).');">
            <input type="hidden" name="action" value="reset_matrix">
            <button type="submit" class="btn btn-outline-danger shadow-sm fw-bold">
                <i class="fa-solid fa-rotate-left me-2"></i>Matrix Reset
            </button>
        </form>
    </div>
    
    <?php if (isset($_GET['success']) && $_GET['success'] === 'reset'): ?>
        <div class="alert alert-success shadow-sm">
            <i class="fa-solid fa-check-circle me-2"></i> Matrix succesvol gereset!
        </div>
    <?php endif; ?>

    <!-- Score Matrix Snapshot Historiek -->
    <div class="card shadow-sm border-0 mb-4" id="snapshotPanel">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Matrix Historiek</span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="saveSnapshot()" id="btnSaveSnapshot">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Snapshot Opslaan
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="loadHistory()">
                    <i class="fa-solid fa-rotate me-1"></i>Ververs
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="snapshotList" class="p-3 text-muted small">Laden...</div>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm mb-4">
        <i class="fa-solid fa-circle-info me-2"></i>Je bekijkt the posities voor <b><?= htmlspecialchars($default_format) ?></b>. Andere posities worden op de achtergrond bewaard en doorgerekend.
    </div>

    <?php foreach($players as $player_id => $player) { ?>
      <form method="post" class="card shadow-sm border-0 mb-3 px-4 py-3">
          <input type="hidden" name="player_id" value="<?php echo $player_id; ?>">
          <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="fw-bold fs-6 text-dark"><?php echo !empty($player['first_name']) ? htmlspecialchars($player['first_name']) : ''; ?> <?php echo !empty($player['last_name']) ? htmlspecialchars($player['last_name']) : ''; ?></div>              
            </div>
            <?php foreach($scores[$player_id] as $position => $score) {
                 if (in_array($position, $visible_positions)):
            ?>
            <div class="col-1 text-center" style="width: 70px;">
                <label class="form-label text-muted small fw-bold mb-1">P<?= $position ?></label>
                <input type="text" name="pos_<?php echo $position; ?>" class="form-control form-control-sm text-center px-1" value="<?php echo $score; ?>">
            </div>
            <?php 
                endif;
            } ?>
              <div class="col-md-2 d-flex align-items-end ms-auto">
                  <button type="submit" class="btn btn-primary btn-sm px-4">Save</button>
              </div>
          </div>
      </form>
    <?php } ?>
</div>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>

<script>
// ── Auto-save bij paginabezoek als er nog geen backup is ─────────────────
document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
});

function loadHistory() {
    fetch('/api/api_score_snapshots.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get_history'})
    })
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('snapshotList');
        if (!data.snapshots || data.snapshots.length === 0) {
            list.innerHTML = '<em class="text-muted">Nog geen snapshots. Pagina wordt automatisch opgeslagen...</em>';
            // Geen backup → auto-save
            autoSaveIfNeeded();
            return;
        }

        // Controleer of er al een recente auto-save is (< 10 min oud)
        const newest = new Date(data.snapshots[0].created_at + 'Z');
        const ageMs = Date.now() - newest.getTime();
        if (ageMs > 10 * 60 * 1000) {
            autoSaveIfNeeded();
        }

        let html = '<table class="table table-sm table-hover mb-0">';
        html += '<thead class="table-light"><tr><th>Datum</th><th>Label</th><th>Door</th><th></th></tr></thead><tbody>';
        data.snapshots.forEach(s => {
            const dt = new Date(s.created_at + 'Z');
            const dtStr = dt.toLocaleDateString('nl-BE') + ' ' + dt.toLocaleTimeString('nl-BE', {hour: '2-digit', minute: '2-digit'});
            const label = s.label || '<span class="text-muted fst-italic">auto</span>';
            const by = s.first_name ? (s.first_name + ' ' + (s.last_name || '')) : '<span class="text-muted">systeem</span>';
            html += `<tr>
                <td class="small">${dtStr}</td>
                <td class="small">${label}</td>
                <td class="small">${by}</td>
                <td class="text-end">
                    <button class="btn btn-xs btn-outline-success me-1" style="padding:1px 8px;font-size:0.75rem;"
                        onclick="restoreSnapshot(${s.id}, '${dtStr}')">
                        <i class="fa-solid fa-rotate-left"></i> Herstel
                    </button>
                    <button class="btn btn-xs btn-outline-danger" style="padding:1px 8px;font-size:0.75rem;"
                        onclick="deleteSnapshot(${s.id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        html += '</tbody></table>';
        list.innerHTML = html;
    })
    .catch(() => {
        document.getElementById('snapshotList').innerHTML = '<span class="text-danger">Kon historiek niet laden.</span>';
    });
}

function autoSaveIfNeeded() {
    fetch('/api/api_score_snapshots.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'save_snapshot', auto: true})
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            loadHistory(); // Ververs de lijst
        }
    })
    .catch(() => {});
}

function saveSnapshot() {
    const label = prompt('Label voor deze snapshot (optioneel):');
    if (label === null) return; // Gebruiker klikte "Annuleren"
    const btn = document.getElementById('btnSaveSnapshot');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Opslaan...';

    fetch('/api/api_score_snapshots.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'save_snapshot', label: label, auto: false})
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Snapshot Opslaan';
        if (data.status === 'success') {
            loadHistory();
        } else {
            alert('Fout: ' + (data.message || 'Onbekend'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Snapshot Opslaan';
        alert('Verbindingsfout.');
    });
}

function restoreSnapshot(id, dtStr) {
    if (!confirm(`Matrix herstellen naar versie van ${dtStr}?\n\nDit overschrijft de huidige scores. De huidige staat wordt eerst automatisch opgeslagen.`)) return;
    
    // Sla eerst de huidige staat op als auto-backup
    fetch('/api/api_score_snapshots.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'save_snapshot', label: 'Auto-backup voor herstel', auto: false})
    })
    .then(() => {
        return fetch('/api/api_score_snapshots.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'restore_snapshot', snapshot_id: id})
        });
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Matrix hersteld! De pagina wordt herladen.');
            location.reload();
        } else {
            alert('Fout: ' + (data.message || 'Onbekend'));
        }
    })
    .catch(() => alert('Verbindingsfout.'));
}

function deleteSnapshot(id) {
    if (!confirm('Snapshot verwijderen?')) return;
    fetch('/api/api_score_snapshots.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_snapshot', snapshot_id: id})
    })
    .then(() => loadHistory())
    .catch(() => {});
}
</script>
