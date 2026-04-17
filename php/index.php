<?php
require_once 'getconn.php';

// Haal de laatste 6 wedstrijden op
$stmt = $pdo->query("
    SELECT g.*, 
        (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id) as selection_count,
        (SELECT score FROM game_lineups gl WHERE gl.game_id = g.id AND gl.is_final = 1 LIMIT 1) as final_score
    FROM games g 
    ORDER BY g.game_date DESC
    LIMIT 6
");
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Overzicht Wedstrijden';
require_once 'header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Laatste Wedstrijden</h2>
        <a href="manage_games.php" class="btn btn-outline-primary">
            Alle wedstrijden / Beheer <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
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
                                <td colspan="6" class="text-center py-4 text-muted">Geen wedstrijden gevonden. Begin met <a href="manage_games.php">wedstrijden te plannen</a>.</td>
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
</div>

<?php require_once 'footer.php'; ?>
