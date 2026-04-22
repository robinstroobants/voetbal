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

// Ophalen laatste 100 log regels
$logs_stmt = $pdo->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 100");
$logs = $logs_stmt->fetchAll(PDO::FETCH_ASSOC);
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

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Tijdstip</th>
                                <th>Actie / Algoritme</th>
                                <th>Details / Configuratie</th>
                                <th>Executie (ms)</th>
                                <th>Geheugen (MB)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="5" class="text-muted py-4">Nog geen data beschikbaar... Genereer enkele lineups!</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): 
                                    $timeClass = 'fast';
                                    if ($log['execution_time_ms'] > 1000) $timeClass = 'slow';
                                    elseif ($log['execution_time_ms'] > 300) $timeClass = 'medium';
                                    
                                    $memClass = '';
                                    if ($log['memory_usage_mb'] > 10) $memClass = 'text-danger fw-bold';
                                ?>
                                <tr>
                                    <td><?= date('d/m H:i:s', strtotime($log['created_at'])) ?></td>
                                    <td><span class="badge bg-secondary opacity-75"><?= htmlspecialchars($log['action_name']) ?></span></td>
                                    <td><small class="text-muted fw-semibold" style="font-size:0.7rem; letter-spacing:0.3px;"><?= htmlspecialchars($log['context'] ?? '--') ?></small></td>
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
