<?php
require_once dirname(__DIR__) . '/core/getconn.php';
require_once dirname(__DIR__) . '/core/auth.php';
requireLogin();

$page_title = "Client Telemetry Dashboard";

// Ophalen van stats over de afgelopen 24 uur per user_type
$stats_stmt = $pdo->query("SELECT 
        user_type,
        COUNT(*) as total_reports, 
        AVG(js_heap_mb) as avg_js_heap, 
        MAX(js_heap_mb) as max_js_heap,
        AVG(dom_nodes) as avg_dom,
        MAX(dom_nodes) as max_dom,
        COUNT(DISTINCT identifier) as unique_users
    FROM client_telemetry 
    WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY user_type");
$stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ophalen laatste 100 log regels
$logs_stmt = $pdo->query("SELECT * FROM client_telemetry ORDER BY created_at DESC LIMIT 100");
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
        .high-mem { color: #dc3545; font-weight: bold; }
        .normal-mem { color: #198754; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-mobile-screen text-primary me-2"></i> Client Telemetry</h2>
        <div>
            <a href="performance.php" class="btn btn-outline-secondary"><i class="fa-solid fa-server me-1"></i> Server Performance</a>
            <a href="../index.php" class="btn btn-secondary">Terug naar Home</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Geheugen & DOM (Afgelopen 24 uur)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>User Type</th>
                                    <th>Unieke Gebruikers</th>
                                    <th>Totaal Pings</th>
                                    <th>Gemiddeld Geheugen (MB)</th>
                                    <th>Max Geheugen (MB)</th>
                                    <th>Gem. DOM Nodes</th>
                                    <th>Max DOM Nodes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($stats)): ?>
                                <tr><td colspan="7">Geen data verzameld in de afgelopen 24 uur.</td></tr>
                                <?php else: ?>
                                    <?php foreach($stats as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['user_type']) ?></strong></td>
                                        <td><?= $row['unique_users'] ?></td>
                                        <td><?= $row['total_reports'] ?></td>
                                        <td><?= round($row['avg_js_heap'], 1) ?> MB</td>
                                        <td class="<?= $row['max_js_heap'] > 150 ? 'high-mem' : 'normal-mem' ?>">
                                            <?= round($row['max_js_heap'], 1) ?> MB
                                        </td>
                                        <td><?= round($row['avg_dom']) ?></td>
                                        <td><?= $row['max_dom'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-0"><i class="fa-solid fa-info-circle me-1"></i> * Let op: JS Heap (geheugen) werkt uitsluitend in Chromium browsers (Chrome, Edge, Opera). Safari & Firefox sturen altijd 0.0 MB terug.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Laatste 100 Pings</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Tijdstip</th>
                            <th>Game ID</th>
                            <th>User Type</th>
                            <th>Identifier (Email/Guest)</th>
                            <th>Geheugen (JS Heap)</th>
                            <th>DOM Nodes</th>
                            <th>Apparaat / Browser</th>
                            <th>IP Adres</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($logs)): ?>
                        <tr><td colspan="8" class="text-center py-3">Geen recente pings</td></tr>
                        <?php else: ?>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap"><?= date('H:i:s', strtotime($log['created_at'])) ?></td>
                                <td><?= $log['game_id'] ? '#' . $log['game_id'] : '-' ?></td>
                                <td><span class="badge bg-<?= $log['user_type'] == 'guest' ? 'secondary' : ($log['user_type'] == 'coach' ? 'danger' : 'primary') ?>"><?= htmlspecialchars($log['user_type']) ?></span></td>
                                <td><?= htmlspecialchars($log['identifier'] ?: '-') ?></td>
                                <td>
                                    <?php if ($log['js_heap_mb'] > 0): ?>
                                        <span class="<?= $log['js_heap_mb'] > 100 ? 'text-danger fw-bold' : 'text-success' ?>"><?= round($log['js_heap_mb'], 1) ?> MB</span>
                                    <?php else: ?>
                                        <span class="text-muted small">Onbekend (Safari/Firefox)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $log['dom_nodes'] ?></td>
                                <td><small class="text-muted" title="<?= htmlspecialchars($log['user_agent']) ?>"><?= htmlspecialchars(substr($log['user_agent'], 0, 50)) ?>...</small></td>
                                <td><small><?= htmlspecialchars($log['ip_address'] ?: '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
