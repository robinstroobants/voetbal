<?php
require_once __DIR__ . '/../getconn.php';
$page_title = "Performance Dashboard";

// Ophalen van stats over de afgelopen 24 uur
$stats_stmt = $pdo->query("SELECT 
        COUNT(*) as total_runs, 
        AVG(execution_time_ms) as avg_ms, 
        MAX(execution_time_ms) as max_ms,
        AVG(memory_usage_mb) as avg_mem,
        MAX(memory_usage_mb) as max_mem
    FROM system_logs 
    WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Ophalen laatste 100 log regels (bewaren we voor detail view indien nodig)
$logs_stmt = $pdo->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 50");
$logs = $logs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ophalen Zwaarste Gebruikers (Afgelopen 7 Dagen)
$heaviest_users_stmt = $pdo->query("
    SELECT 
        u.id, u.first_name, u.last_name, u.email, u.role, t.name as team_name,
        COUNT(sl.id) as total_runs,
        SUM(sl.execution_time_ms) as total_ms,
        AVG(sl.execution_time_ms) as avg_ms,
        MAX(sl.memory_usage_mb) as max_mem
    FROM system_logs sl
    JOIN users u ON sl.user_id = u.id
    LEFT JOIN teams t ON u.team_id = t.id
    WHERE sl.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY u.id
    ORDER BY total_ms DESC
    LIMIT 10
");
$heaviest_users = $heaviest_users_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Lineup App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .fast { color: #198754; font-weight: bold; }
        .medium { color: #fd7e14; font-weight: bold; }
        .slow { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fa-solid fa-gauge-high text-primary me-2"></i>Performance Dashboard</h3>
            <div>
                <span class="badge bg-success" id="live-indicator"><i class="fa-solid fa-circle-dot fa-fade me-1"></i>Live Updates</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Runs (24u)</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_runs']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Gem. Snelheid</h6>
                        <h3 class="mb-0 <?= $stats['avg_ms'] > 1000 ? 'slow' : ($stats['avg_ms'] > 300 ? 'medium' : 'fast') ?>">
                            <?= number_format($stats['avg_ms'], 0) ?> <small class="text-muted" style="font-size: 0.5em;">ms</small>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Piek Snelheid (Max)</h6>
                        <h3 class="mb-0 text-danger"><?= number_format($stats['max_ms'], 0) ?> <small class="text-muted" style="font-size: 0.5em;">ms</small></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Piek Geheugen</h6>
                        <h3 class="mb-0"><?= number_format($stats['max_mem'], 1) ?> <small class="text-muted" style="font-size: 0.5em;">MB</small></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-users-gear text-secondary me-2"></i>Top 10 Zwaarste Gebruikers (Afgelopen 7 Dagen)</h5>
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-start ps-4">Gebruiker</th>
                                        <th>Team</th>
                                        <th>Aantal Runs</th>
                                        <th>Totale Laadtijd (s)</th>
                                        <th>Gem. Laadtijd (ms)</th>
                                        <th class="pe-4">Max Geheugen (MB)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($heaviest_users)): ?>
                                        <tr><td colspan="6" class="text-muted py-4">Nog geen data beschikbaar...</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($heaviest_users as $u): 
                                            $total_sec = $u['total_ms'] / 1000;
                                        ?>
                                        <tr>
                                            <td class="text-start ps-4">
                                                <div class="fw-bold"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($u['email']) ?> <span class="badge bg-light text-dark border"><?= $u['role'] ?></span></div>
                                            </td>
                                            <td><?= htmlspecialchars($u['team_name'] ?? 'Geen') ?></td>
                                            <td class="fw-semibold"><?= number_format($u['total_runs']) ?></td>
                                            <td class="text-danger fw-bold"><?= number_format($total_sec, 1) ?> s</td>
                                            <td><?= number_format($u['avg_ms'], 0) ?> ms</td>
                                            <td class="pe-4"><?= number_format($u['max_mem'], 1) ?> MB</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list text-secondary me-2"></i>Recente Ruwe Logs (Laatste 50)</h5>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-center align-middle" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Tijdstip</th>
                                <th>Actie / Algoritme</th>
                                <th>Details</th>
                                <th>Executie (ms)</th>
                                <th>Geheugen (MB)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="5" class="text-muted py-4">Nog geen data beschikbaar...</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): 
                                    $timeClass = 'fast';
                                    if ($log['execution_time_ms'] > 1000) $timeClass = 'slow';
                                    elseif ($log['execution_time_ms'] > 300) $timeClass = 'medium';
                                    
                                    $memClass = '';
                                    if ($log['memory_usage_mb'] > 10) $memClass = 'text-danger fw-bold';
                                ?>
                                <tr>
                                    <td class="text-muted"><?= date('d/m H:i:s', strtotime($log['created_at'])) ?></td>
                                    <td><span class="badge bg-secondary opacity-75"><?= htmlspecialchars($log['action_name']) ?></span></td>
                                    <td><small class="text-muted fw-semibold" style="letter-spacing:0.3px;"><?= htmlspecialchars($log['context'] ?? '--') ?></small></td>
                                    <td class="<?= $timeClass ?>"><?= number_format($log['execution_time_ms'], 1) ?> ms</td>
                                    <td class="<?= $memClass ?>"><?= number_format($log['memory_usage_mb'], 2) ?> MB</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh logic (elke 5 seconden) -->
    <script>
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    </script>
    <?php include __DIR__ . '/../footer.php'; ?>
