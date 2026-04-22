<?php
require_once 'getconn.php';

// Verwerk acties: Toevoegen, Bewerken, Verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete' && isset($_POST['game_id'])) {
        $check = $pdo->prepare("SELECT id FROM games WHERE id = :id AND team_id = :team_id");
        $check->execute(['id' => $_POST['game_id'], 'team_id' => $_SESSION['team_id']]);
        if ($check->fetchColumn()) {
            $pdo->prepare("DELETE FROM game_lineups WHERE game_id = :id")->execute(['id' => $_POST['game_id']]);
            $pdo->prepare("DELETE FROM game_selections WHERE game_id = :id")->execute(['id' => $_POST['game_id']]);
            $pdo->prepare("DELETE FROM games WHERE id = :id")->execute(['id' => $_POST['game_id']]);
        }
    } elseif ($action === 'save') {
        $gameId = !empty($_POST['game_id']) ? (int)$_POST['game_id'] : null;
        $opponent = trim($_POST['opponent']);
        $gameDate = $_POST['game_date'];
        $format = $_POST['format'];
        $minPos = isset($_POST['min_pos']) ? (int)$_POST['min_pos'] : 0;
        // team_id = 1 as default for now
        $coachId = !empty($_POST['coach_id']) ? (int)$_POST['coach_id'] : null;
        
        if ($gameId) {
            // Controleer of layout/format veranderd is ten opzichte van current
            $stmtCheckFmt = $pdo->prepare("SELECT format FROM games WHERE id = ?");
            $stmtCheckFmt->execute([$gameId]);
            $oldFormat = $stmtCheckFmt->fetchColumn();

            if ($oldFormat !== $format) {
                // Formaat is gewijzigd, theorie is nu nutteloos, opruimen!
                $pdo->prepare("DELETE FROM game_lineups WHERE game_id = ?")->execute([$gameId]);
            }

            $stmt = $pdo->prepare("UPDATE games SET opponent = :opp, game_date = :gd, format = :fmt, min_pos = :mpos, coach_id = :cid WHERE id = :id");
            $stmt->execute(['opp' => $opponent, 'gd' => $gameDate, 'fmt' => $format, 'mpos' => $minPos, 'cid' => $coachId, 'id' => $gameId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO games (team_id, opponent, game_date, format, min_pos, coach_id) VALUES (:team_id, :opp, :gd, :fmt, :mpos, :cid)");
            $stmt->execute(['team_id' => $_SESSION['team_id'], 'opp' => $opponent, 'gd' => $gameDate, 'fmt' => $format, 'mpos' => $minPos, 'cid' => $coachId]);
            $newGameId = $pdo->lastInsertId();

            // Duplicatie verwerken indien gevraagd
            $sourceGameId = !empty($_POST['source_game_id']) ? (int)$_POST['source_game_id'] : null;
            if ($sourceGameId) {
                // Veiligheidscheck team
                $check = $pdo->prepare("SELECT id FROM games WHERE id = ? AND team_id = ?");
                $check->execute([$sourceGameId, $_SESSION['team_id']]);
                if ($check->fetchColumn()) {
                    $pdo->prepare("INSERT INTO game_selections (game_id, player_id, is_goalkeeper) 
                                   SELECT ?, player_id, is_goalkeeper FROM game_selections WHERE game_id = ?")
                        ->execute([$newGameId, $sourceGameId]);
                }
            }
        }
    }
    // Voorkom form resubmission bij refresh
    header("Location: /games");
    exit;
}

// Haal wedstrijden op
$stmt = $pdo->prepare("
    SELECT g.*, CONCAT(c.first_name, ' ', c.last_name) AS coach_name,
        (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id) as selection_count,
        (SELECT score FROM game_lineups gl WHERE gl.game_id = g.id AND gl.is_final = 1 LIMIT 1) as final_score
    FROM games g 
    LEFT JOIN users c ON g.coach_id = c.id
    WHERE g.team_id = ?
    ORDER BY g.game_date DESC
");
$stmt->execute([$_SESSION['team_id']]);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Groepeer de matchen dynamisch op Seizoen (juli-juni) en de jeugdreeks-fases (Fase 1/2) 
$groupedGames = [];
// ... (code will calculate onboarding status here)
$stmtP = $pdo->prepare("SELECT COUNT(*) FROM players WHERE team_id = ?");
$stmtP->execute([$_SESSION['team_id']]);
$players_count = (int)$stmtP->fetchColumn();

$stmtC = $pdo->prepare("SELECT COUNT(*) FROM coaches WHERE team_id = ?");
$stmtC->execute([$_SESSION['team_id']]);
$coaches_count = (int)$stmtC->fetchColumn();

$stmtF = $pdo->prepare("SELECT default_format FROM teams WHERE id = ?");
$stmtF->execute([$_SESSION['team_id']]);
$default_format = $stmtF->fetchColumn() ?: '8v8';

$required_players = 8;
if (preg_match('/^(\d+)v\d+/', $default_format, $matches)) {
    $required_players = (int)$matches[1];
}

$onboarding_complete = ($players_count >= $required_players && $coaches_count >= 1);
foreach ($games as $game) {
    $time = strtotime($game['game_date']);
    $year = (int)date('Y', $time);
    $month = (int)date('n', $time);
    
    if ($month >= 7) {
        $season = "Seizoen " . $year . "-" . ($year + 1);
        $phase = "Najaarsronde (Fase 1)"; // Jul - Dec
    } else {
        $season = "Seizoen " . ($year - 1) . "-" . $year;
        $phase = "Voorjaarsronde (Fase 2)"; // Jan - Jun
    }
    
    if (!isset($groupedGames[$season])) $groupedGames[$season] = [];
    if (!isset($groupedGames[$season][$phase])) $groupedGames[$season][$phase] = [];
    
    $groupedGames[$season][$phase][] = $game;
}

$available_formats_all = [
    '11v11',
    '8v8_4x15',
    '8v8_3x20',
    '8v8_4x20',
    '8v8_5x15',
    '8v8_6x15',
    '8v8_7x15',
    '5v5_4x15',
    '3v3_6x10',
    '2v2_6x10'
];

function getFormatLevel($fmtStr) {
    if (strpos($fmtStr, '2v2') === 0) return 1;
    if (strpos($fmtStr, '3v3') === 0) return 2;
    if (strpos($fmtStr, '5v5') === 0) return 3;
    if (strpos($fmtStr, '8v8') === 0) return 4;
    if (strpos($fmtStr, '11v11') === 0) return 5;
    return 0;
}

$team_level = getFormatLevel($default_format);
$available_formats = [];

foreach ($available_formats_all as $fmt) {
    $fmt_level = getFormatLevel($fmt);
    // Criterium: Alleen hetzelfde niveau OF maximaal exact 1 niveau hoger 
    if ($fmt_level == $team_level || $fmt_level == $team_level + 1) {
        $available_formats[] = $fmt;
    }
}

// Haal beschikbare coaches op (de effectieve SaaS gebruikers/coaches gekoppeld aan dit team)
$stmtC = $pdo->prepare("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name 
    FROM users u 
    INNER JOIN user_teams ut ON u.id = ut.user_id 
    WHERE ut.team_id = ? 
    ORDER BY u.first_name ASC
");
$stmtC->execute([$_SESSION['team_id']]);
$coachesData = $stmtC->fetchAll(PDO::FETCH_ASSOC);

// Definieer een palet aan onderscheidende kleuren
$badgeColors = ['bg-info text-dark', 'bg-danger', 'bg-success', 'bg-warning text-dark', 'bg-primary', 'bg-dark text-white'];
$coachColorMap = [];
foreach ($coachesData as $index => $cData) {
    $coachColorMap[$cData['name']] = $badgeColors[$index % count($badgeColors)];
}

$page_title = 'Game Management';
require_once 'header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Wedstrijd Beheer</h2>
        <?php if ($onboarding_complete): ?>
            <button class="btn btn-primary shadow-sm" onclick="openGameModal()">
                <i class="fa-solid fa-plus me-2"></i>Nieuwe Wedstrijd
            </button>
        <?php else: ?>
            <button class="btn btn-secondary disabled opacity-75" title="Doorloop eerst de onboarding op het dashboard">
                <i class="fa-solid fa-lock me-2"></i>Team Incompleet
            </button>
        <?php endif; ?>
    </div>

    <?php if (!$onboarding_complete): ?>
    <div class="alert alert-warning shadow-sm border-0 border-start border-warning border-4 fw-bold mb-4">
        <i class="fa-solid fa-triangle-exclamation text-warning fs-5 align-middle me-2"></i> 
        Je voldoet nog niet aan de ploegvereisten (minstens <?= $required_players ?> spelers en 1 coach).
        <a href="/" class="alert-link text-decoration-underline ms-2">Keer terug naar het dashboard</a> om je inschrijving af te ronden!
    </div>
    <?php endif; ?>

    <!-- Games Overzicht Gegroepeerd -->
    <div class="accordion" id="seasonAccordion">
        <?php if(empty($games)): ?>
            <div class="alert alert-light text-center border">
                Geen wedstrijden gevonden! Tijd om er eentje te plannen.
            </div>
        <?php endif; ?>

        <?php 
        $season_counter = 0;
        foreach($groupedGames as $season => $phases): 
            $is_first_season = ($season_counter === 0);
        ?>
        <div class="accordion-item mb-3 border-0 shadow-sm rounded overflow-hidden">
            <h2 class="accordion-header" id="heading<?= $season_counter ?>">
                <button class="accordion-button <?= $is_first_season ? '' : 'collapsed' ?> bg-white fw-bold fs-5 text-primary border-bottom" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $season_counter ?>" aria-expanded="<?= $is_first_season ? 'true' : 'false' ?>" aria-controls="collapse<?= $season_counter ?>">
                    <i class="fa-solid fa-trophy me-2"></i> <?= htmlspecialchars($season) ?>
                </button>
            </h2>
            <div id="collapse<?= $season_counter ?>" class="accordion-collapse collapse <?= $is_first_season ? 'show' : '' ?>" aria-labelledby="heading<?= $season_counter ?>" data-bs-parent="#seasonAccordion">
                <div class="accordion-body p-0 bg-white">
                    
                    <?php 
                    // Voorjaar ligt later en komt dus boven Najaar bij DESC sorting
                    foreach(['Voorjaarsronde (Fase 2)', 'Najaarsronde (Fase 1)'] as $phase): 
                        if(!empty($phases[$phase])):
                    ?>
                    <div class="bg-light py-2 px-4 border-bottom fw-semibold text-secondary d-flex align-items-center">
                        <i class="fa-regular fa-calendar-days me-2"></i> <?= $phase ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-bottom">
                            <tbody>
                                <?php foreach($phases[$phase] as $game): ?>
                                <tr>
                                    <td class="ps-4 fw-medium text-muted" style="width: 15%"><?= date('d/m/Y', strtotime($game['game_date'])) ?></td>
                                    <td class="fw-bold text-dark">
                                        <?php if($game['coach_name']): 
                                            $cColor = isset($coachColorMap[$game['coach_name']]) ? $coachColorMap[$game['coach_name']] : 'bg-secondary text-white';
                                        ?>
                                            <span class="badge <?= $cColor ?> rounded-pill me-1"><?= htmlspecialchars($game['coach_name']) ?></span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($game['opponent']) ?>
                                    </td>
                                    <td>
                                        <?php if($game['selection_count'] > 0): ?>
                                            <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm"><?= $game['selection_count'] ?> Spelers</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 shadow-sm">Geen Selectie</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4" style="width: 25%">
                                        <a href="/games/<?= $game['id'] ?>/selection" class="btn btn-sm btn-outline-success me-1" title="Beheer Selectie">
                                            <i class="fa-solid fa-users-gear"></i>
                                        </a>
                                        <a href="/games/<?= $game['id'] ?>/duplicate" class="btn btn-sm btn-outline-warning me-1" title="Dupliceer met Selectie">
                                            <i class="fa-solid fa-copy"></i>
                                        </a>
                                        <a href="/games/<?= $game['id'] ?>/lineup" class="btn btn-sm btn-outline-primary me-1 <?= $game['selection_count'] == 0 ? 'disabled' : '' ?>" title="Bereken Opstelling">
                                            <i class="fa-solid fa-calculator"></i> Opstelling
                                        </a>
                                        <button class="btn btn-sm btn-outline-secondary me-1" title="Bewerk Data" 
                                                onclick='openGameModal(<?= json_encode($game) ?>)'>
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Wedstrijd verwijderen? Dit wist ook alle direct gekoppelde selecties.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Verwijder">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
        <?php 
            $season_counter++;
            endforeach; 
        ?>
    </div>
</div>

<!-- Add/Edit Game Modal -->
<div class="modal fade" id="gameModal" tabindex="-1" aria-labelledby="gameModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="gameForm">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="game_id" id="modal_game_id" value="">
          <input type="hidden" name="source_game_id" id="modal_source_game_id" value="">
          
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="gameModalLabel">Wedstrijd Beheren</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">TEGENSTANDER</label>
                  <input type="text" class="form-control" name="opponent" id="modal_opponent" required placeholder="BV. FC Barcelona">
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">DATUM</label>
                  <input type="date" class="form-control" name="game_date" id="modal_game_date" required>
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">MIN. POSITIES PER SPELER</label>
                  <select class="form-select" name="min_pos" id="modal_min_pos" required>
                      <option value="0">Geen minimum</option>
                      <option value="2">Minstens 2 posities (20000+ serie)</option>
                      <option value="3">Minstens 3 posities (30000+ serie)</option>
                  </select>
                  <div class="form-text">Bepaalt of het algoritme enkel schemas toelaat waar elke speler op X unieke posities speelt.</div>
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">TEAM VERANTWOORDELIJKE (Optioneel)</label>
                  <select class="form-select" name="coach_id" id="modal_coach_id">
                      <option value="">-- Geen coach geselecteerd --</option>
                      <?php foreach ($coachesData as $cd): ?>
                          <option value="<?= $cd['id'] ?>">Coach <?= htmlspecialchars(trim($cd['name'])) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">AANTAL EN FORMAAT</label>
                  <select class="form-select" name="format" id="modal_format" required>
                      <?php foreach ($available_formats as $fmt): ?>
                          <option value="<?= htmlspecialchars($fmt) ?>"><?= htmlspecialchars($fmt) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuleren</button>
            <button type="submit" class="btn btn-primary">Opslaan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function openGameModal(game = null, isDuplicate = false) {
    var modalEl = document.getElementById('gameModal');
    var modal = new bootstrap.Modal(modalEl);
    
    // Reset form
    document.getElementById('gameForm').reset();
    document.getElementById('modal_game_id').value = '';
    document.getElementById('modal_source_game_id').value = '';
    
    if (game && !isDuplicate) {
        document.getElementById('gameModalLabel').innerText = 'Wedstrijd Bewerken';
        document.getElementById('modal_game_id').value = game.id;
        document.getElementById('modal_opponent').value = game.opponent;
        document.getElementById('modal_game_date').value = game.game_date ? game.game_date.split(' ')[0] : '';
        document.getElementById('modal_format').value = game.format;
        document.getElementById('modal_min_pos').value = game.min_pos || '0';
        document.getElementById('modal_coach_id').value = game.coach_id || '';
    } else if (game && isDuplicate) {
        document.getElementById('gameModalLabel').innerText = 'Wedstrijd Dupliceren van ' + game.opponent;
        document.getElementById('modal_source_game_id').value = game.id;
        document.getElementById('modal_opponent').value = '';
        document.getElementById('modal_game_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('modal_format').value = game.format;
        document.getElementById('modal_min_pos').value = game.min_pos || '0';
        document.getElementById('modal_coach_id').value = game.coach_id || '';
    } else {
        document.getElementById('gameModalLabel').innerText = 'Nieuwe Wedstrijd Plannen';
        document.getElementById('modal_game_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('modal_format').value = '<?= htmlspecialchars($default_format) ?>';
        document.getElementById('modal_min_pos').value = '0';
        document.getElementById('modal_coach_id').value = '';
    }
    
    modal.show();
}

<?php if (isset($_GET['edit_game']) || isset($_GET['duplicate_game'])): 
    $isDup = isset($_GET['duplicate_game']);
    $urlId = $isDup ? (int)$_GET['duplicate_game'] : (int)$_GET['edit_game'];
    $evtTarget = null;
    foreach ($games as $g) {
        if ((int)$g['id'] === $urlId) {
            $evtTarget = $g;
            break;
        }
    }
    if ($evtTarget):
?>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        openGameModal(<?= json_encode($evtTarget) ?>, <?= $isDup ? 'true' : 'false' ?>);
    }, 150); // Small delay to ensure bootstrap is ready
});
<?php endif; endif; ?>
</script>

<?php require_once 'footer.php'; ?>
