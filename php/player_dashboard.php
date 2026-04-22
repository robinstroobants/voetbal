<?php
require_once 'getconn.php';

if (!isset($_GET['id'])) {
    header("Location: edit_players.php");
    exit;
}

$player_id = intval($_GET['id']);
$team_id = $_SESSION['team_id'];

// Fetch Player
$stmt = $pdo->prepare("SELECT * FROM players WHERE id=? AND team_id=?");
$stmt->execute([$player_id, $team_id]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$player) {
    header("Location: edit_players.php");
    exit;
}

// Ajax Save Favorite Positions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (isset($data['action']) && $data['action'] === 'update_favorite_positions') {
        header('Content-Type: application/json');
        $fav_pos = $data['favorite_positions'] ?? '';
        $upd = $pdo->prepare("UPDATE players SET favorite_positions=? WHERE id=? AND team_id=?");
        if ($upd->execute([$fav_pos, $player_id, $team_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit;
    }
}

// Bepaal de zichtbare posities a.d.h.v. het format van dit team
$stmtF = $pdo->prepare("SELECT default_format FROM teams WHERE id = ?");
$stmtF->execute([$_SESSION['team_id']]);
$default_format = $stmtF->fetchColumn() ?: '8v8';

$visible_positions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]; // default
if (strpos($default_format, '5v5') === 0) {
    $visible_positions = [1, 4, 7, 9, 11];
} elseif (strpos($default_format, '8v8') === 0) {
    $visible_positions = [1, 2, 4, 5, 7, 9, 10, 11];
}

$success_msg = '';
$error_msg = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $birthdate = trim($_POST['birthdate'] ?? '');
        $fav_pos = $_POST['favorite_positions'] ?? '';
        $is_doelman = isset($_POST['is_doelman']) ? 1 : 0;
        
        $birthdate_val = ($birthdate === '') ? null : $birthdate;
        
        $upd = $pdo->prepare("UPDATE players SET first_name=?, last_name=?, birthdate=?, favorite_positions=?, is_doelman=? WHERE id=? AND team_id=?");
        if ($upd->execute([$first_name, $last_name, $birthdate_val, $fav_pos, $is_doelman, $player_id, $team_id])) {
            $success_msg = "Profiel succesvol geüpdatet.";
            // Herlaad gegevens
            $stmt->execute([$player_id, $team_id]);
            $player = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_msg = "Fout bij updaten profiel.";
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_scores') {
        foreach($_POST as $k=>$v) {
            if (strpos($k, 'pos_') === 0) {
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
        $success_msg = "Scores succesvol opgeslagen.";
    }
}

// Haal scores op
$sql = "SELECT position, score
        FROM player_scores
        WHERE player_id = ?
          AND (position, score_date) IN (
              SELECT position, MAX(score_date)
              FROM player_scores
              WHERE player_id = ?
              GROUP BY position
          )";
$stmtScores = $pdo->prepare($sql);
$stmtScores->execute([$player_id, $player_id]);
$scores = [];
// Initialiseer met 0 of default
foreach ($visible_positions as $p) {
    $scores[$p] = 0;
}
while ($r = $stmtScores->fetch(PDO::FETCH_ASSOC)) {
    if (in_array($r['position'], $visible_positions)) {
        $scores[$r['position']] = floatval($r['score']);
    }
}

// Favoriete posities ophalen
$favs = [];
if (!empty($player['favorite_positions'])) {
    $f_raw = explode(',', $player['favorite_positions']);
    foreach($f_raw as $f) {
        $favs[] = intval(trim($f));
    }
}

$page_title = 'Spelersdashboard: ' . htmlspecialchars($player['first_name'] . ' ' . $player['last_name']);
require_once 'header.php';
?>

<style>
/* CSS Voetbalveld */
.pitch-container {
    background-color: #4CAF50;
    border: 2px solid #fff;
    border-radius: 5px;
    position: relative;
    width: 100%;
    max-width: 300px;
    aspect-ratio: 2 / 3;
    margin: 0 auto;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.pitch-line {
    background-color: #fff;
    position: absolute;
}
.pitch-center-line { width: 100%; height: 2px; top: 50%; left: 0; transform: translateY(-50%); }
.pitch-center-circle { width: 60px; height: 60px; border: 2px solid #fff; border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%); position: absolute; }
.pitch-penalty-area-top { width: 120px; height: 40px; border: 2px solid #fff; border-top: 0; top: 0; left: 50%; transform: translateX(-50%); position: absolute; }
.pitch-penalty-area-bottom { width: 120px; height: 40px; border: 2px solid #fff; border-bottom: 0; bottom: 0; left: 50%; transform: translateX(-50%); position: absolute; }
.pitch-goal-area-top { width: 60px; height: 15px; border: 2px solid #fff; border-top: 0; top: 0; left: 50%; transform: translateX(-50%); position: absolute; }
.pitch-goal-area-bottom { width: 60px; height: 15px; border: 2px solid #fff; border-bottom: 0; bottom: 0; left: 50%; transform: translateX(-50%); position: absolute; }

.pitch-pos-marker {
    position: absolute;
    width: 24px;
    height: 24px;
    background-color: rgba(255, 255, 255, 0.4);
    border: 2px solid #fff;
    color: #333;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
    transform: translate(-50%, -50%);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.pitch-pos-marker.is-fav {
    background-color: #ffc107; /* Warning yellow */
    border-color: #fff;
    color: #000;
    z-index: 2;
    transform: translate(-50%, -50%) scale(1.2);
}

/* Position mappings for the pitch (percentage top/left) */
/* 1: GK (Bottom)
   4: CB (Bottom center)
   2: RB (Bottom right)
   5: LB (Bottom left)
   10: CAM (Center)
   7: RW (Top right)
   11: LW (Top left)
   9: ST (Top center)
*/
.pos-1 { top: 92%; left: 50%; }
.pos-4 { top: 78%; left: 50%; }
.pos-2 { top: 70%; left: 85%; }
.pos-5 { top: 70%; left: 15%; }
.pos-10 { top: 40%; left: 50%; }
.pos-7 { top: 25%; left: 85%; }
.pos-11 { top: 25%; left: 15%; }
.pos-9 { top: 15%; left: 50%; }

/* Score Matrix Table */
.matrix-table { width: 100%; border-collapse: separate; border-spacing: 5px; font-size: 12px; }
.matrix-table td { width: 33.33%; padding: 10px; text-align: center; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6; }
.matrix-table td.empty-cell { background-color: transparent; border: none; }
.matrix-score-input { width: 60px; text-align: center; margin: 0 auto; font-weight: bold; font-size: 12px; }
</style>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="/edit_players.php" class="btn btn-outline-secondary btn-sm mb-2"><i class="fa-solid fa-arrow-left me-1"></i> Terug naar Spelers</a>
            <h2 class="mb-0">
                <i class="fa-solid fa-user-circle me-2 text-primary"></i>
                <?= htmlspecialchars($player['first_name'] . ' ' . $player['last_name']) ?>
            </h2>
        </div>
    </div>
    
    <?php if($success_msg): ?>
        <div class="alert alert-success shadow-sm"><i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if($error_msg): ?>
        <div class="alert alert-danger shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Profiel Edit Column -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fa-solid fa-id-card me-1"></i> Spelerprofiel
                </div>
                <div class="card-body">
                   
                    
                    
                    <div id="favPosArea">
                        <h5 class="text-center mb-3">Favoriete Posities</h5>
                        
                        <div class="pitch-container mb-3">
                            <div class="pitch-line pitch-center-line"></div>
                            <div class="pitch-line pitch-center-circle"></div>
                            <div class="pitch-line pitch-penalty-area-top"></div>
                            <div class="pitch-line pitch-penalty-area-bottom"></div>
                            <div class="pitch-line pitch-goal-area-top"></div>
                            <div class="pitch-line pitch-goal-area-bottom"></div>
                            
                            <?php foreach($visible_positions as $p): ?>
                                <div class="pitch-pos-marker pos-<?= $p ?> <?= in_array($p, $favs) ? 'is-fav' : '' ?>" title="Positie <?= $p ?>" data-pos="<?= $p ?>" style="cursor: pointer;">
                                    <?= $p ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="bg-light p-3 rounded border">
                            <h6 class="text-muted small fw-bold mb-2">HUIDIGE PRIORITEIT</h6>
                            <ol id="favPosList" class="mb-0 ps-3 fw-medium text-dark">
                                <?php foreach($favs as $f): ?>
                                    <li>Positie <?= $f ?></li>
                                <?php endforeach; ?>
                                <?php if(empty($favs)): ?>
                                    <li class="text-muted fst-italic no-favs-msg" style="list-style-type: none; margin-left: -1rem;">Geen favoriete posities ingesteld.</li>
                                <?php endif; ?>
                            </ol>
                        </div>
                        <div class="alert alert-info py-2 small border-0 shadow-sm">
                            <i class="fa-solid fa-hand-pointer me-1"></i> Klik op een bolletje op het veld om een positie als favoriet in te stellen of te verwijderen. De volgorde in de lijst hieronder bepaalt de prioriteit. Wijzigingen worden <b>direct opgeslagen</b>.
                        </div>
                        
                    </div>
                    <hr class="my-4">
                     <form method="post">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label text-muted small fw-bold">VOORNAAM</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($player['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label text-muted small fw-bold">ACHTERNAAM</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($player['last_name'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">GEBOORTEDATUM</label>
                            <input type="date" name="birthdate" class="form-control" value="<?= htmlspecialchars($player['birthdate'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch pt-1">
                                <input class="form-check-input" type="checkbox" name="is_doelman" id="checkDoelman" value="1" <?= !empty($player['is_doelman']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-dark" for="checkDoelman">Deze speler is een doelman</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-save me-1"></i> Profiel Opslaan</button>
                    </form>

                </div>
            </div>
        </div>

        <!-- Score Matrix Column -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-chess-board me-1"></i> Score Matrix</span>
                    <span class="badge bg-light text-dark"><?= htmlspecialchars($default_format) ?></span>
                </div>
                <div class="card-body bg-light">
                    <form id="scoreMatrixForm" method="post">
                        <input type="hidden" name="action" value="update_scores">
                        
                        <div class="mb-4 d-flex justify-content-center">
                            <table class="matrix-table" style="max-width: 450px;">
                                <!-- Rij 5: 9 (ST) -->
                                <tr>
                                    <td colspan="3" class="<?= !in_array(9, $visible_positions) ? 'empty-cell' : '' ?>">
                                        <?php if(in_array(9, $visible_positions)): ?>
                                            <div class="text-muted small fw-bold mb-1">Positie 9 (Spits)</div>
                                            <input type="number" step="1" class="form-control matrix-score-input" name="pos_9" value="<?= $scores[9] ?? 0 ?>">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- Rij 4: 11 (LW), 7 (RW) -->
                                <tr>
                                    <td class="<?= !in_array(11, $visible_positions) ? 'empty-cell' : '' ?>">
                                        <?php if(in_array(11, $visible_positions)): ?>
                                            <div class="text-muted small fw-bold mb-1">Positie 11 (LA)</div>
                                            <input type="number" step="1" class="form-control matrix-score-input" name="pos_11" value="<?= $scores[11] ?? 0 ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td class="empty-cell"></td>
                                    <td class="<?= !in_array(7, $visible_positions) ? 'empty-cell' : '' ?>">
                                        <?php if(in_array(7, $visible_positions)): ?>
                                            <div class="text-muted small fw-bold mb-1">Positie 7 (RA)</div>
                                            <input type="number" step="1" class="form-control matrix-score-input" name="pos_7" value="<?= $scores[7] ?? 0 ?>">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- Rij 3: 10 (CAM) -->
                                <tr>
                                    <td colspan="3" class="<?= !in_array(10, $visible_positions) ? 'empty-cell' : '' ?>">
                                        <?php if(in_array(10, $visible_positions)): ?>
                                            <div class="text-muted small fw-bold mb-1">Positie 10 (CAM)</div>
                                            <input type="number" step="1" class="form-control matrix-score-input" name="pos_10" value="<?= $scores[10] ?? 0 ?>">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- Rij 2: 5 (LB), 2 (RB) -->
                                <tr>
                                    <td class="<?= !in_array(5, $visible_positions) ? 'empty-cell' : '' ?>">
                                        <?php if(in_array(5, $visible_positions)): ?>
                                            <div class="text-muted small fw-bold mb-1">Positie 5 (LV)</div>
                                            <input type="number" step="1" class="form-control matrix-score-input" name="pos_5" value="<?= $scores[5] ?? 0 ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td class="empty-cell"></td>
                                    <td class="<?= !in_array(2, $visible_positions) ? 'empty-cell' : '' ?>">
                                        <?php if(in_array(2, $visible_positions)): ?>
                                            <div class="text-muted small fw-bold mb-1">Positie 2 (RV)</div>
                                            <input type="number" step="1" class="form-control matrix-score-input" name="pos_2" value="<?= $scores[2] ?? 0 ?>">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- Rij 1: 4 (CB) -->
                                <tr>
                                    <td colspan="3" class="<?= !in_array(4, $visible_positions) ? 'empty-cell' : '' ?>">
                                        <?php if(in_array(4, $visible_positions)): ?>
                                            <div class="text-muted small fw-bold mb-1">Positie 4 (CV)</div>
                                            <input type="number" step="1" class="form-control matrix-score-input" name="pos_4" value="<?= $scores[4] ?? 0 ?>">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- Rij 0: 1 (GK) -->
                                <tr>
                                    <td colspan="3" class="<?= !in_array(1, $visible_positions) ? 'empty-cell' : '' ?>">
                                        <?php if(in_array(1, $visible_positions)): ?>
                                            <div class="text-muted small fw-bold mb-1">Positie 1 (Doelman)</div>
                                            <input type="number" step="1" class="form-control matrix-score-input" name="pos_1" value="<?= $scores[1] ?? 0 ?>">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-dark px-5"><i class="fa-solid fa-save me-1"></i> Matrix Opslaan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Hide/show favorite positions area based on doelman status
    const checkDoelman = document.getElementById('checkDoelman');
    const favPosArea = document.getElementById('favPosArea');
    
    function updateFavPosVisibility() {
        if (checkDoelman.checked) {
            favPosArea.style.display = 'none';
        } else {
            favPosArea.style.display = 'block';
        }
    }
    
    if (checkDoelman && favPosArea) {
        checkDoelman.addEventListener('change', updateFavPosVisibility);
        updateFavPosVisibility();
    }
    
    // Interactive Pitch Logic
    const markers = document.querySelectorAll('.pitch-pos-marker');
    const favList = document.getElementById('favPosList');
    let currentFavs = [<?php echo implode(',', $favs); ?>];
    const playerId = <?= $player_id ?>;

    function renderFavList() {
        favList.innerHTML = '';
        if (currentFavs.length === 0) {
            favList.innerHTML = '<li class="text-muted fst-italic no-favs-msg" style="list-style-type: none; margin-left: -1rem;">Geen favoriete posities ingesteld.</li>';
        } else {
            currentFavs.forEach(pos => {
                const li = document.createElement('li');
                li.textContent = 'Positie ' + pos;
                favList.appendChild(li);
            });
        }
    }

    function saveFavPosToServer() {
        fetch('player_dashboard.php?id=' + playerId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'update_favorite_positions',
                favorite_positions: currentFavs.join(',')
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert('Fout bij opslaan favoriete posities: ' + (data.error || 'Onbekende fout'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Netwerkfout bij opslaan favoriete posities.');
        });
    }

    markers.forEach(marker => {
        marker.addEventListener('click', function() {
            const pos = parseInt(this.getAttribute('data-pos'));
            const idx = currentFavs.indexOf(pos);
            
            if (idx > -1) {
                // Remove
                currentFavs.splice(idx, 1);
                this.classList.remove('is-fav');
            } else {
                // Add to end of list
                currentFavs.push(pos);
                this.classList.add('is-fav');
            }
            
            renderFavList();
            saveFavPosToServer();
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
