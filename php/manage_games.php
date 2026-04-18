<?php
require_once 'getconn.php';

// Verwerk acties: Toevoegen, Bewerken, Verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete' && isset($_POST['game_id'])) {
        $stmt = $pdo->prepare("DELETE FROM games WHERE id = :id");
        $stmt->execute(['id' => $_POST['game_id']]);
    } elseif ($action === 'save') {
        $gameId = !empty($_POST['game_id']) ? (int)$_POST['game_id'] : null;
        $opponent = trim($_POST['opponent']);
        $gameDate = $_POST['game_date'];
        $format = $_POST['format'];
        $minPos = isset($_POST['min_pos']) ? (int)$_POST['min_pos'] : 0;
        // team_id = 1 as default for now
        
        if ($gameId) {
            $stmt = $pdo->prepare("UPDATE games SET opponent = :opp, game_date = :gd, format = :fmt, min_pos = :mpos WHERE id = :id");
            $stmt->execute(['opp' => $opponent, 'gd' => $gameDate, 'fmt' => $format, 'mpos' => $minPos, 'id' => $gameId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO games (team_id, opponent, game_date, format, min_pos) VALUES (1, :opp, :gd, :fmt, :mpos)");
            $stmt->execute(['opp' => $opponent, 'gd' => $gameDate, 'fmt' => $format, 'mpos' => $minPos]);
        }
    }
    // Voorkom form resubmission bij refresh
    header("Location: manage_games.php");
    exit;
}

// Haal wedstrijden op
$stmt = $pdo->query("
    SELECT g.*, 
        (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id) as selection_count,
        (SELECT score FROM game_lineups gl WHERE gl.game_id = g.id AND gl.is_final = 1 LIMIT 1) as final_score
    FROM games g 
    ORDER BY g.game_date DESC
");
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Simpele hardcoded format lijst
$available_formats = [
    '8v8_4x15',
    '8v8_3x20',
    '8v8_6x15',
    '8v8_7x15',
    '8v8_5x15'
];

$page_title = 'Game Management';
require_once 'header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Wedstrijd Beheer</h2>
        <button class="btn btn-primary" onclick="openGameModal()">
            <i class="fa-solid fa-plus me-2"></i>Nieuwe Wedstrijd
        </button>
    </div>

    <!-- Games Overzicht -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Datum</th>
                            <th>Tegenstander</th>
                            <th>Selectie</th>
                            <th class="text-end pe-4">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($games)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Amai, geen wedstrijden gevonden! Tijd om er eentje te plannen.</td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php foreach($games as $game): ?>
                        <tr>
                            <td class="ps-4 fw-medium"><?= date('d/m/Y', strtotime($game['game_date'])) ?></td>
                            <td><?= htmlspecialchars($game['opponent']) ?></td>
                            <td>
                                <?php if($game['selection_count'] > 0): ?>
                                    <span class="badge bg-success rounded-pill"><?= $game['selection_count'] ?> Spelers</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill">0 Spelers</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="edit_selection.php?game_id=<?= $game['id'] ?>" class="btn btn-sm btn-outline-success me-1" title="Beheer Selectie">
                                    <i class="fa-solid fa-users-gear"></i>
                                </a>
                                <a href="lineup.php?wedstrijd=<?= $game['id'] ?>" class="btn btn-sm btn-outline-primary me-1 <?= $game['selection_count'] == 0 ? 'disabled' : '' ?>" title="Bereken Opstelling">
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
        </div>
    </div>
</div>

<!-- Add/Edit Game Modal -->
<div class="modal fade" id="gameModal" tabindex="-1" aria-labelledby="gameModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="gameForm">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="game_id" id="modal_game_id" value="">
          
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="gameModalLabel">Wedstrijd Beheren</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">TEGENSTANDER</label>
                  <input type="text" class="form-control" name="opponent" id="modal_opponent" required placeholder="BV. FC Drie Ringen">
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">DATUM</label>
                  <input type="date" class="form-control" name="game_date" id="modal_game_date" required>
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">AANTAL EN FORMAAT</label>
                  <select class="form-select" name="format" id="modal_format" required>
                      <?php foreach ($available_formats as $fmt): ?>
                          <option value="<?= htmlspecialchars($fmt) ?>"><?= htmlspecialchars($fmt) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">MIN. POSITIES PER SPELER</label>
                  <select class="form-select" name="min_pos" id="modal_min_pos" required>
                      <option value="0">Geen minimum</option>
                      <option value="2">Minstens 2 posities</option>
                      <option value="3">Minstens 3 posities</option>
                  </select>
                  <div class="form-text">Bepaalt of het algoritme enkel schemas toelaat waar elke speler op X unieke posities speelt.</div>
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
function openGameModal(game = null) {
    var modalEl = document.getElementById('gameModal');
    var modal = new bootstrap.Modal(modalEl);
    
    // Reset form
    document.getElementById('gameForm').reset();
    document.getElementById('modal_game_id').value = '';
    
    if (game) {
        document.getElementById('gameModalLabel').innerText = 'Wedstrijd Bewerken';
        document.getElementById('modal_game_id').value = game.id;
        document.getElementById('modal_opponent').value = game.opponent;
        // Strip trailing time information out of the date payload to make it HTML date-input compliant
        document.getElementById('modal_game_date').value = game.game_date ? game.game_date.split(' ')[0] : '';
        document.getElementById('modal_format').value = game.format;
        document.getElementById('modal_min_pos').value = game.min_pos || '0';
    } else {
        document.getElementById('gameModalLabel').innerText = 'Nieuwe Wedstrijd Plannen';
        document.getElementById('modal_game_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('modal_min_pos').value = '0';
    }
    
    modal.show();
}

<?php if (isset($_GET['edit_game'])): 
    $editId = (int)$_GET['edit_game'];
    $editTarget = null;
    foreach ($games as $g) {
        if ((int)$g['id'] === $editId) {
            $editTarget = $g;
            break;
        }
    }
    if ($editTarget):
?>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        openGameModal(<?= json_encode($editTarget) ?>);
    }, 150); // Small delay to ensure bootstrap is ready
});
<?php endif; endif; ?>
</script>

<?php require_once 'footer.php'; ?>
