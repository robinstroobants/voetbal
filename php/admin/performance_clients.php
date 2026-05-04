<?php
require_once dirname(__DIR__) . '/core/getconn.php';

$page_title = "Client Telemetry Dashboard";

// Zorg eerst dat schema up-to-date is VOOR de SELECTs
// Elk apart om te vermijden dat een bestaande kolom de rest blokkeert
$migrations = [
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS page VARCHAR(100) NULL",
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS page_load_ms INT DEFAULT 0",
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS php_time_ms FLOAT DEFAULT 0",
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS php_memory_mb FLOAT DEFAULT 0",
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS identifier_full VARCHAR(255) NULL",
];
foreach ($migrations as $sql) {
    try { $pdo->exec($sql); } catch (Exception $e) { /* kolom bestaat al */ }
}

// Stats per user_type (24 uur)
$stats = $pdo->query("
    SELECT user_type,
        COUNT(*) as total_reports,
        COUNT(DISTINCT identifier) as unique_users,
        ROUND(AVG(NULLIF(js_heap_mb,0)),1) as avg_js_heap,
        ROUND(MAX(js_heap_mb),1) as max_js_heap,
        ROUND(AVG(dom_nodes)) as avg_dom,
        MAX(dom_nodes) as max_dom,
        ROUND(AVG(NULLIF(page_load_ms,0))) as avg_load_ms,
        MAX(page_load_ms) as max_load_ms,
        ROUND(AVG(NULLIF(php_time_ms,0)),1) as avg_php_ms,
        ROUND(AVG(NULLIF(php_memory_mb,0)),1) as avg_php_mem
    FROM client_telemetry
    WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY user_type
")->fetchAll(PDO::FETCH_ASSOC);

// Laatste 100 logs
$logs = $pdo->query("
    SELECT * FROM client_telemetry ORDER BY created_at DESC LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once __DIR__ . '/../header.php'; ?>
<style>
    .high-mem { color: #dc3545; font-weight: bold; }
    .normal-mem { color: #198754; }
    .slow-load { color: #fd7e14; font-weight: bold; }
    th { white-space: nowrap; font-size: 0.82rem; }
    td { font-size: 0.85rem; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-mobile-screen text-primary me-2"></i> Client Telemetry</h2>
        <div>
            <a href="/admin/performance" class="btn btn-outline-secondary"><i class="fa-solid fa-server me-1"></i> Server Performance</a>
            <a href="/admin" class="btn btn-secondary">Terug naar Home</a>
        </div>
    </div>
    <?php include __DIR__ . '/_monitoring_nav.php'; ?>

    <!-- Samenvatting per user_type -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Samenvatting afgelopen 24 uur — per gebruikerstype</h5>
                </div>
                <div class="card-body pb-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>User Type</th>
                                    <th>Unieke gebruikers</th>
                                    <th>Totaal pings</th>
                                    <th title="Enkel Chromium browsers (Chrome/Edge). Safari/Firefox sturen 0.">JS Heap gem. <i class="fa-solid fa-circle-info text-warning ms-1"></i></th>
                                    <th title="Enkel Chromium browsers.">JS Heap max. <i class="fa-solid fa-circle-info text-warning ms-1"></i></th>
                                    <th>DOM nodes gem.</th>
                                    <th>Pagina laadtijd gem.</th>
                                    <th>Pagina laadtijd max.</th>
                                    <th>PHP exec gem. (ms)</th>
                                    <th>PHP geheugen gem. (MB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($stats)): ?>
                                <tr><td colspan="10">Geen data in de afgelopen 24 uur.</td></tr>
                                <?php else: ?>
                                    <?php foreach($stats as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['user_type']) ?></strong></td>
                                        <td><?= $row['unique_users'] ?></td>
                                        <td><?= $row['total_reports'] ?></td>
                                        <td><?= $row['avg_js_heap'] > 0 ? $row['avg_js_heap'].' MB' : '<span class="text-muted small">—</span>' ?></td>
                                        <td class="<?= $row['max_js_heap'] > 150 ? 'high-mem' : 'normal-mem' ?>">
                                            <?= $row['max_js_heap'] > 0 ? $row['max_js_heap'].' MB' : '<span class="text-muted small">—</span>' ?>
                                        </td>
                                        <td><?= $row['avg_dom'] ?: '—' ?></td>
                                        <td class="<?= $row['avg_load_ms'] > 3000 ? 'slow-load' : '' ?>">
                                            <?= $row['avg_load_ms'] ? $row['avg_load_ms'].' ms' : '—' ?>
                                        </td>
                                        <td class="<?= $row['max_load_ms'] > 5000 ? 'slow-load' : '' ?>">
                                            <?= $row['max_load_ms'] ? $row['max_load_ms'].' ms' : '—' ?>
                                        </td>
                                        <td><?= $row['avg_php_ms'] ? $row['avg_php_ms'].' ms' : '—' ?></td>
                                        <td><?= $row['avg_php_mem'] ? $row['avg_php_mem'].' MB' : '—' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-1">
                        <i class="fa-solid fa-circle-info text-warning me-1"></i>
                        <strong>JS Heap</strong> werkt enkel in Chromium (Chrome/Edge/Opera) — Safari & Firefox sturen altijd 0. 
                        <strong>Pagina laadtijd</strong> en <strong>PHP metrics</strong> werken in alle browsers.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Laatste 100 logs -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Laatste 100 pings</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tijdstip</th>
                            <th>Pagina</th>
                            <th>Type</th>
                            <th>Identifier</th>
                            <th title="Chromium only">JS Heap</th>
                            <th>DOM</th>
                            <th>Laadtijd (browser)</th>
                            <th>PHP exec</th>
                            <th>PHP mem</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($logs)): ?>
                        <tr><td colspan="10" class="text-center py-3">Geen pings</td></tr>
                        <?php else: ?>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap"><?= date('d/m H:i:s', strtotime($log['created_at'])) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($log['page'] ?? '-') ?></small></td>
                                <td><span class="badge bg-<?= $log['user_type'] == 'guest' ? 'secondary' : ($log['user_type'] == 'coach' ? 'danger' : 'primary') ?>"><?= htmlspecialchars($log['user_type']) ?></span></td>
                                <td><?= htmlspecialchars($log['identifier'] ?: '-') ?></td>
                                <td>
                                    <?php if (($log['js_heap_mb'] ?? 0) > 0): ?>
                                        <span class="<?= $log['js_heap_mb'] > 100 ? 'text-danger fw-bold' : 'text-success' ?>"><?= round($log['js_heap_mb'], 1) ?> MB</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $log['dom_nodes'] ?? '—' ?></td>
                                <td class="<?= ($log['page_load_ms'] ?? 0) > 3000 ? 'slow-load' : '' ?>">
                                    <?= isset($log['page_load_ms']) && $log['page_load_ms'] > 0 ? $log['page_load_ms'].' ms' : '—' ?>
                                </td>
                                <td><?= isset($log['php_time_ms']) && $log['php_time_ms'] > 0 ? round($log['php_time_ms'],1).' ms' : '—' ?></td>
                                <td><?= isset($log['php_memory_mb']) && $log['php_memory_mb'] > 0 ? round($log['php_memory_mb'],1).' MB' : '—' ?></td>
                                <td><small><?= htmlspecialchars($log['ip_address'] ?? '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
