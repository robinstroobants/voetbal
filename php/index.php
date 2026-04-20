<?php
require_once 'getconn.php';

$team_id = (int)($_SESSION['team_id'] ?? 0);

if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
    header("Location: superadmin_dashboard.php");
    exit;
}

// 1. Calculate Onboarding Status
$stmtP = $pdo->prepare("SELECT COUNT(*) FROM players WHERE team_id = ?");
$stmtP->execute([$team_id]);
$players_count = (int)$stmtP->fetchColumn();

$stmtC = $pdo->prepare("SELECT COUNT(*) FROM coaches WHERE team_id = ?");
$stmtC->execute([$team_id]);
$coaches_count = (int)$stmtC->fetchColumn();

// Extraheer format requirements
$stmtF = $pdo->prepare("SELECT default_format FROM teams WHERE id = ?");
$stmtF->execute([$_SESSION['team_id']]);
$default_format = $stmtF->fetchColumn() ?: '8v8';

$required_players = 8;
if (preg_match('/^(\d+)v\d+/', $default_format, $matches)) {
    $required_players = (int)$matches[1];
}

$max_players = 24;
if (strpos($default_format, '2v2') === 0 || strpos($default_format, '3v3') === 0) {
    $max_players = 12;
}
$remaining_players = max(0, $max_players - $players_count);

$onboarding_complete = ($players_count >= $required_players && $coaches_count >= 1);

// Haal de laatste 6 wedstrijden op indien we niet in onboarding zitten
$games = [];
if ($onboarding_complete) {
    $stmt = $pdo->prepare("
        SELECT g.*, 
            (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id) as selection_count,
            (SELECT score FROM game_lineups gl WHERE gl.game_id = g.id AND gl.is_final = 1 LIMIT 1) as final_score
        FROM games g 
        WHERE g.team_id = ?
        ORDER BY g.game_date DESC
        LIMIT 6
    ");
    $stmt->execute([$team_id]);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Dashboard';
require_once 'header.php';
?>

<div class="container mt-4 mb-5">
    <?php if (!$onboarding_complete): ?>
        <!-- ONBOARDING WIZARD -->
        <div class="card shadow border-0 overflow-hidden mb-4" style="border-radius: 12px;">
            <div class="bg-primary text-white p-4">
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Welkom bij Lineup!</h3>
                <p class="mb-0 text-white-50">Laten we je team snel opstarten. Werk deze stappen af om opstellingen te kunnen maken.</p>
            </div>
            
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <!-- Stap 1: Spelers -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="d-flex align-items-start">
                            <div class="bg-<?php echo ($players_count >= $required_players) ? 'success' : 'light'; ?> text-<?php echo ($players_count >= $required_players) ? 'white' : 'secondary'; ?> rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; flex-shrink: 0;">
                                <?php if($players_count >= $required_players): ?>
                                    <i class="fa-solid fa-check fs-5"></i>
                                <?php else: ?>
                                    <span class="fs-5 fw-bold">1</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h5 class="fw-bold">Spelers Toevoegen</h5>
                                <p class="text-muted small">Je hebt minimaal <strong><?= $required_players ?></strong> spelers nodig voor jouw format (<?= htmlspecialchars($default_format) ?>).</p>
                                <div class="progress mb-3" style="height: 10px;">
                                    <?php 
                                        $perc = min(100, round(($players_count / max(1, $required_players)) * 100));
                                        $colorClass = $perc == 100 ? 'bg-success' : 'bg-primary';
                                    ?>
                                    <div class="progress-bar <?= $colorClass ?>" role="progressbar" style="width: <?= $perc ?>%;" aria-valuenow="<?= $perc ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="mb-2 fw-semibold text-secondary">Huidig aantal: <?= $players_count ?> (Minimaal <?= $required_players ?> nodig)</div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSinglePlayerModal"><i class="fa-solid fa-user-plus me-1"></i> Eén speler</button>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBulkPlayersModal"><i class="fa-solid fa-list-ul me-1"></i> Plakken uit Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stap 2: Coaches -->
                    <div class="col-md-6 border-start border-light ps-md-4">
                        <div class="d-flex align-items-start">
                            <div class="bg-<?php echo ($coaches_count >= 1) ? 'success' : 'light'; ?> text-<?php echo ($coaches_count >= 1) ? 'white' : 'secondary'; ?> rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; flex-shrink: 0;">
                                <?php if($coaches_count >= 1): ?>
                                    <i class="fa-solid fa-check fs-5"></i>
                                <?php else: ?>
                                    <span class="fs-5 fw-bold">2</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h5 class="fw-bold">Coach(es) Registreren</h5>
                                <p class="text-muted small mb-1">We hebben de namen van de trainers nodig zodat dit kloppend is op wedstrijdbladen.</p>
                                
                                <div class="mb-3 fw-semibold text-secondary">Huidig aantal: <?= $coaches_count ?></div>
                                
                                <button class="btn btn-sm <?= ($coaches_count >= 1)? 'btn-outline-success' : 'btn-success text-white' ?>" data-bs-toggle="modal" data-bs-target="#addCoachModal"><i class="fa-solid fa-chalkboard-user me-1"></i> Coach Toevoegen</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if($onboarding_complete): ?>
            <div class="card-footer bg-success text-white text-center p-3 fw-bold">
                Jouw team is klaar! Je kan nu beginnen plannen.
            </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- REGULIER DASHBOARD -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Laatste Wedstrijden</h2>
            <a href="manage_games.php" class="btn btn-outline-primary shadow-sm">
                Wedstrijden plannen <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 border-bottom">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Datum</th>
                                <th>Tegenstander</th>
                                <th>Formaat</th>
                                <th>Selectie</th>
                                <th>Score</th>
                                <th class="text-end pe-4">Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($games)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted bg-light">
                                        <i class="fa-regular fa-calendar-xmark fs-2 mb-2 d-block"></i>
                                        Nog geen wedstrijden gespeeld.<br>
                                        <a href="manage_games.php" class="btn btn-primary mt-3"><i class="fa-solid fa-plus me-1"></i> Tijd om er eentje te plannen!</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
                            <?php foreach($games as $game): ?>
                            <tr>
                                <td class="ps-4 fw-medium text-secondary"><?= date('d/m/Y', strtotime($game['game_date'])) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($game['opponent']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($game['format']) ?></span></td>
                                <td>
                                    <?php if($game['selection_count'] > 0): ?>
                                        <span class="badge bg-success rounded-pill"><?= $game['selection_count'] ?> Spelers</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark rounded-pill">0 Spelers</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($game['final_score']): ?>
                                        <span class="badge bg-info text-dark rounded-pill"><?= round($game['final_score'], 1) ?>%</span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit_selection.php?game_id=<?= $game['id'] ?>" class="btn btn-sm btn-outline-success me-1" title="Beheer Selectie">
                                        <i class="fa-solid fa-users-gear"></i> Selectie
                                    </a>
                                    <a href="lineup.php?wedstrijd=<?= $game['id'] ?>" class="btn btn-sm btn-outline-primary <?= $game['selection_count'] == 0 ? 'disabled' : '' ?>" title="Bereken Opstelling">
                                        <i class="fa-solid fa-calculator"></i> Opstelling
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MODALS VOOR ONBOARDING -->

<!-- 1. Single Player Modal -->
<div class="modal fade" id="addSinglePlayerModal" tabindex="-1" aria-labelledby="addSingleLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="addSingleLabel"><i class="fa-solid fa-user-plus me-2"></i>Eén Speler Toevoegen</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id="frmSinglePlayer">
              <input type="hidden" name="action" value="add_single_player">
              <div class="row">
                  <div class="col-6 mb-3">
                      <label class="form-label fw-bold small text-muted">VOORNAAM <span class="text-danger">*</span></label>
                      <input type="text" name="first_name" class="form-control" required placeholder="Bv. Eden">
                  </div>
                  <div class="col-6 mb-3">
                      <label class="form-label fw-bold small text-muted">ACHTERNAAM</label>
                      <input type="text" name="last_name" class="form-control" placeholder="Bv. Hazard">
                  </div>
              </div>
              <div class="mb-3">
                  <div class="form-check form-switch pt-1">
                      <input class="form-check-input" type="checkbox" name="is_doelman" id="checkDoelman" value="1">
                      <label class="form-check-label fw-bold text-dark" for="checkDoelman">Deze speler is een doelman</label>
                  </div>
              </div>
              <div class="mb-3" id="favPosContainer">
                  <label class="form-label fw-bold small text-muted">FAVORIETE POSITIES (Optioneel)</label>
                  <input type="text" name="favorite_positions" class="form-control" placeholder="Bv. 7, 11 (gescheiden met komma)">
              </div>
          </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-primary" onclick="submitApicall('frmSinglePlayer')"><i class="fa-solid fa-check me-1"></i> Toevoegen</button>
      </div>
    </div>
  </div>
</div>

<!-- 2. Bulk Player Modal -->
<div class="modal fade" id="addBulkPlayersModal" tabindex="-1" aria-labelledby="addBulkLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addBulkLabel"><i class="fa-solid fa-list-ul me-2"></i>Bulk Spelers Importeren</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="alert alert-info border-0 shadow-sm">
             <i class="fa-solid fa-circle-info me-2"></i>Plak hier de namen uit bijvoorbeeld je Excel-bestand. Zet <strong>elke speler op een nieuwe regel</strong>.
             <hr class="my-2">
             <div class="small"><i class="fa-solid fa-user-shield me-1"></i> Voor dit formaat (<?= htmlspecialchars($default_format) ?>) geldt een limiet van <b><?= $max_players ?> spelers</b>. Je kunt er nu nog maximaal <b><?= $remaining_players ?></b> toevoegen.</div>
          </div>
          <form id="frmBulkPlayers">
              <input type="hidden" name="action" value="add_bulk_players">
              <div class="mb-3">
                  <textarea name="players_text" class="form-control shadow-sm" style="font-family: monospace; resize: none;" rows="12" placeholder="Jan Peeters&#10;Piet Smet&#10;Kevin De Bruyne..."></textarea>
              </div>
          </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-primary fw-bold" onclick="submitApicall('frmBulkPlayers')"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Lijst</button>
      </div>
    </div>
  </div>
</div>

<!-- 3. Add Coach Modal -->
<div class="modal fade" id="addCoachModal" tabindex="-1" aria-labelledby="addCoachLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addCoachLabel"><i class="fa-solid fa-chalkboard-user me-2"></i>Coach Toevoegen</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id="frmCoach">
              <input type="hidden" name="action" value="add_coach">
              <div class="mb-3">
                  <label class="form-label fw-bold small text-muted">NAAM COACH <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" placeholder="Bv. Robin S." required>
                  <div class="form-text mt-2">Deze naam zal standaard als verantwoordelijke op schema's geprint kunnen worden.</div>
              </div>
          </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-success fw-bold text-white" onclick="submitApicall('frmCoach')"><i class="fa-solid fa-check me-1"></i> Toevoegen</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const checkDoelman = document.getElementById('checkDoelman');
    const favPosContainer = document.getElementById('favPosContainer');
    
    if(checkDoelman) {
        checkDoelman.addEventListener('change', function() {
            if (this.checked) {
                favPosContainer.style.display = 'none';
                favPosContainer.querySelector('input').value = ''; // Reset favorite positions
            } else {
                favPosContainer.style.display = 'block';
            }
        });
    }
});

function submitApicall(formId) {
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const fd = new FormData(form);
    const btn = event.currentTarget || event.target;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch('api_onboarding_add.php', {
        method: 'POST',
        body: fd
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Herlaad the pagina na succes om progressie bij te werken!
            window.location.reload();
        } else {
            alert("Fout: " + (data.error || "Onbekende fout"));
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert("Systeemfout bij opslaan");
        btn.innerHTML = oldHtml;
        btn.disabled = false;
    });
}
</script>

<?php require_once 'footer.php'; ?>
