<?php
$page_title = 'Ontbrekende Coaches';
require_once 'getconn.php';

$team_id = (int)$_SESSION['team_id'];

// Haal alle beschikbare coaches op voor dit team
$stmtC = $pdo->prepare("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name 
    FROM users u 
    INNER JOIN user_teams ut ON u.id = ut.user_id 
    WHERE ut.team_id = ? 
    ORDER BY u.first_name ASC
");
$stmtC->execute([$team_id]);
$coaches = $stmtC->fetchAll(PDO::FETCH_ASSOC);

// Verwerk het instellen van een coach
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign_coach') {
    $game_id = (int)$_POST['game_id'];
    $coach_id = (int)$_POST['coach_id'];

    if ($game_id && $coach_id) {
        // Veiligheidscheck team
        $check = $pdo->prepare("SELECT id FROM games WHERE id = ? AND team_id = ?");
        $check->execute([$game_id, $team_id]);
        if ($check->fetchColumn()) {
            // Update the game
            $stmtUpdate = $pdo->prepare("UPDATE games SET coach_id = ? WHERE id = ?");
            $stmtUpdate->execute([$coach_id, $game_id]);
            
            // Cascade update naar game_playtime_logs
            $stmtLogs = $pdo->prepare("UPDATE game_playtime_logs SET coach_id = ? WHERE game_id = ?");
            $stmtLogs->execute([$coach_id, $game_id]);
            
            $success_msg = "Coach succesvol toegewezen en statistieken zijn bijgewerkt!";
        }
    }
}

// Haal games zonder (geldige) coach op
$stmtGames = $pdo->prepare("
    SELECT g.id, g.opponent, g.game_date, g.format,
           (SELECT GROUP_CONCAT(p.first_name SEPARATOR ', ') 
            FROM game_selections gs 
            JOIN players p ON gs.player_id = p.id 
            WHERE gs.game_id = g.id) as selection
    FROM games g
    LEFT JOIN users c ON g.coach_id = c.id
    WHERE g.team_id = ? AND (g.coach_id IS NULL OR c.first_name IS NULL)
    ORDER BY g.game_date DESC
");
$stmtGames->execute([$team_id]);
$missingGames = $stmtGames->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i> Ontbrekende Coaches Fixen</h2>
        <a href="/games" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Terug naar Wedstrijden</a>
    </div>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success shadow-sm border-0 border-start border-success border-4 mb-4">
            <i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary fw-bold">Wedstrijden zonder coach (<?= count($missingGames) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (count($missingGames) === 0): ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold">Alles in orde!</h5>
                    <p class="text-muted">Alle wedstrijden hebben een coach toegewezen gekregen. De statistieken zijn kloppend.</p>
                </div>
            <?php else: ?>
                <p class="text-muted mb-4">Wijs hieronder snel de juiste coach toe. De statistieken worden achter de schermen direct bijgewerkt.</p>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%">Datum</th>
                                <th style="width: 25%">Tegenstander / Formaat</th>
                                <th style="width: 35%">Selectie</th>
                                <th style="width: 25%">Toewijzen aan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($missingGames as $game): ?>
                                <tr>
                                    <td class="text-muted fw-semibold"><?= date('d/m/Y', strtotime($game['game_date'])) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($game['opponent']) ?></div>
                                        <div class="badge bg-light text-dark border mt-1"><?= htmlspecialchars($game['format']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($game['selection']): ?>
                                            <p class="small mb-0 text-secondary" style="line-height: 1.4;">
                                                <?= htmlspecialchars($game['selection']) ?>
                                            </p>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Geen selectie gevonden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="action" value="assign_coach">
                                            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                            <select name="coach_id" class="form-select form-select-sm" required>
                                                <option value="">-- Kies Coach --</option>
                                                <?php foreach ($coaches as $coach): ?>
                                                    <option value="<?= $coach['id'] ?>"><?= htmlspecialchars($coach['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-check"></i> Fix</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
