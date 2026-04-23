<?php
// admin/schema_usage.php
require_once dirname(__DIR__) . '/core/getconn.php';

$schemaId = isset($_GET['schema']) ? (int)$_GET['schema'] : 0;

if (!$schemaId) {
    header("Location: /admin/schemas");
    exit;
}

$stmtSch = $pdo->prepare("SELECT id, game_format FROM lineups WHERE id = ?");
$stmtSch->execute([$schemaId]);
$schema = $stmtSch->fetch(PDO::FETCH_ASSOC);

if (!$schema) {
    die("Schema niet gevonden.");
}

$sql = "
    SELECT 
        gl.game_id, 
        g.opponent, 
        g.game_date, 
        g.coach_id,
        t.id as team_id,
        t.name as team_name,
        u.first_name as coach_first_name,
        u.last_name as coach_last_name
    FROM game_lineups gl
    JOIN games g ON g.id = gl.game_id
    JOIN teams t ON t.id = g.team_id
    LEFT JOIN users u ON u.id = g.coach_id
    WHERE gl.schema_id = ?
    ORDER BY g.game_date DESC, g.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$schemaId]);
$usages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Schema Gebruik: #' . $schemaId;
require_once __DIR__ . '/../header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fa-solid fa-chart-network text-success me-2"></i> Gebruiksgeschiedenis</h2>
            <p class="text-muted mb-0">Overzicht van alle wedstrijden die Schema <strong>#<?= $schemaId ?></strong> (<?= htmlspecialchars($schema['game_format']) ?>) gebruiken.</p>
        </div>
        <a href="/admin/schemas" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Terug naar Schema's</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tenant (Team)</th>
                            <th>Coach</th>
                            <th>Datum</th>
                            <th>Tegenstander</th>
                            <th class="text-end pe-4">Actie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usages as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><i class="fa-solid fa-shield-halved me-2"></i> <?= htmlspecialchars($row['team_name']) ?> <small class="text-muted">(ID: <?= $row['team_id'] ?>)</small></td>
                                <td>
                                    <?php if ($row['coach_id']): ?>
                                        <i class="fa-solid fa-user me-1 text-muted"></i> <?= htmlspecialchars($row['coach_first_name'] . ' ' . $row['coach_last_name']) ?>
                                        <form method="POST" action="/admin/impersonate?action=start" class="m-0 d-inline-block ms-2">
                                            <input type="hidden" name="target_user_id" value="<?= $row['coach_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2" title="Log in als deze coach">
                                                <i class="fa-solid fa-user-secret"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Systeem/Onbekend</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $d = new DateTime($row['game_date']);
                                        echo $d->format('d-m-Y');
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($row['opponent']) ?></td>
                                <td class="text-end pe-4">
                                    <span class="badge bg-light text-dark border">Game ID: <?= $row['game_id'] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($usages)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Dit schema is nog nooit gebruikt in een opstelling.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
