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
        // team_id = 1 as default for now
        
        if ($gameId) {
            $stmt = $pdo->prepare("UPDATE games SET opponent = :opp, game_date = :gd, format = :fmt WHERE id = :id");
            $stmt->execute(['opp' => $opponent, 'gd' => $gameDate, 'fmt' => $format, 'id' => $gameId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO games (team_id, opponent, game_date, format) VALUES (1, :opp, :gd, :fmt)");
            $stmt->execute(['opp' => $opponent, 'gd' => $gameDate, 'fmt' => $format]);
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

// Simpele hardcoded format lijst gebaseerd op het veelgebruikte patroon
$available_formats = [
    '8v8_0gk_4x15', 
    '8v8_1gk_4x15',
    '8v8_2gk_4x15',
    '8v8_1gk_3x20',
    '8v8_1gk_6x15',
    '8v8_1gk_7x15',
    '8v8_2gk_5x15'
];

$page_title = 'Games Management';
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
                            <th>Formaat</th>
                            <th>Selectie</th>
                            <th>Score</th>
                            <th class="text-end pe-4">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($games)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Amai, geen wedstrijden gevonden! Tijd om er eentje te plannen.</td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php foreach($games as $game): ?>
                        <tr>
                            <td class="ps-4 fw-medium"><?= date('d/m/Y', strtotime($game['game_date'])) ?></td>
                            <td><?= htmlspecialchars($game['opponent']) ?></td>
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
                                <a href="lineup.php?wedstrijd=<?= $game['id'] ?>" class="btn btn-sm btn-outline-primary me-1 <?= $game['selection_count'] == 0 ? 'disabled' : '' ?>" title="Bereken Opstelling">
                                    <i class="fa-solid fa-calculator"></i>
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
        document.getElementById('modal_game_date').value = game.game_date;
        document.getElementById('modal_format').value = game.format;
    } else {
        document.getElementById('gameModalLabel').innerText = 'Nieuwe Wedstrijd Planen';
        document.getElementById('modal_game_date').value = new Date().toISOString().split('T')[0];
    }
    
    modal.show();
}
</script>

<?php require_once 'footer.php'; ?>
