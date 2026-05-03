<?php
require_once dirname(__DIR__) . '/core/getconn.php';

// Idempotente migratie: context kolom toevoegen indien nog niet aanwezig
try { $pdo->exec("ALTER TABLE usage_logs ADD COLUMN IF NOT EXISTS context VARCHAR(255) NULL DEFAULT NULL"); } catch (\Exception $e) {}

$page_title = "Feature Load Dashboard";

// ── Tijdvenster selectie ──────────────────────────────────────────────────────
$window = (int)($_GET['hours'] ?? 24);
if (!in_array($window, [1, 6, 24, 168, 720])) $window = 24;
$windowLabel = match($window) { 1 => '1 uur', 6 => '6 uur', 24 => '24 uur', 168 => '7 dagen', 720 => '30 dagen', default => "$window uur" };
// ─────────────────────────────────────────────────────────────────────────────

// ── Feature definities met labels en kleur ────────────────────────────────────
$featureConfig = [
    'prolineup_ai'      => ['label' => 'ProLineup AI',      'color' => 'danger',  'icon' => 'fa-brain',        'desc' => 'Zwaar backtracking algoritme voor automatische opstellingen'],
    'equalplay_ai'      => ['label' => 'EqualTime AI',      'color' => 'warning', 'icon' => 'fa-scale-balanced','desc' => 'Gelijke speeltijd verdeling (wiskundig, lichter)'],
    'lineup_lab_save'   => ['label' => 'LineupLab Save',    'color' => 'primary', 'icon' => 'fa-floppy-disk',  'desc' => 'Coach slaat manueel gebouwd schema op'],
    'lineup_lab_view'   => ['label' => 'LineupLab Open',    'color' => 'info',    'icon' => 'fa-hammer',        'desc' => 'Coach opent de manuele schema builder'],
    'lineup_view'       => ['label' => 'Opstelling Bekijk', 'color' => 'secondary','icon' => 'fa-eye',          'desc' => 'Coach bekijkt een gegenereerde opstelling'],
    'share_link_generate'=>['label' => 'Share Link',        'color' => 'success', 'icon' => 'fa-share-nodes',  'desc' => 'Coach maakt een deellink aan voor ouders'],
    'share_view'        => ['label' => 'Share Bekeken',     'color' => 'success', 'icon' => 'fa-users',         'desc' => 'Ouder opent de publieke share link'],
    'game_event_log'    => ['label' => 'Event Gelogd',      'color' => 'dark',    'icon' => 'fa-futbol',        'desc' => 'Ouder logt een event (doelpunt, wissel, ...)'],
];
// ─────────────────────────────────────────────────────────────────────────────

// ── Query 1: Feature totalen per action_type ──────────────────────────────────
$featureStats = $pdo->query("
    SELECT action_type,
        COUNT(*) as total_calls,
        COUNT(DISTINCT CASE WHEN user_id > 0 THEN user_id ELSE NULL END) as unique_coaches,
        COUNT(DISTINCT team_id) as unique_teams,
        SUM(cost_weight) as total_cost,
        ROUND(AVG(cost_weight), 1) as avg_cost,
        MAX(cost_weight) as max_cost
    FROM usage_logs
    WHERE created_at > DATE_SUB(NOW(), INTERVAL {$window} HOUR)
    GROUP BY action_type
    ORDER BY total_cost DESC
")->fetchAll(PDO::FETCH_ASSOC);

$statsByAction = [];
$totalCostAll = 0;
foreach ($featureStats as $row) {
    $statsByAction[$row['action_type']] = $row;
    $totalCostAll += (int)$row['total_cost'];
}
// ─────────────────────────────────────────────────────────────────────────────

// ── Query 2: Top teams per feature ────────────────────────────────────────────
$topTeamsPerFeature = [];
$stmtTopTeams = $pdo->query("
    SELECT ul.action_type, t.name as team_name, ul.team_id,
        COUNT(*) as calls, SUM(ul.cost_weight) as cost
    FROM usage_logs ul
    LEFT JOIN teams t ON ul.team_id = t.id
    WHERE ul.created_at > DATE_SUB(NOW(), INTERVAL {$window} HOUR)
      AND ul.team_id > 0
    GROUP BY ul.action_type, ul.team_id
    ORDER BY ul.action_type, cost DESC
");
foreach ($stmtTopTeams->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = $row['action_type'];
    if (!isset($topTeamsPerFeature[$key])) $topTeamsPerFeature[$key] = [];
    if (count($topTeamsPerFeature[$key]) < 3) {
        $topTeamsPerFeature[$key][] = $row;
    }
}
// ─────────────────────────────────────────────────────────────────────────────

// ── Query 3: Load per uur (sparkline data) ────────────────────────────────────
$hourlyLoad = [];
$hoursBack = min($window, 48); // Max 48 datapunten
$stmtHourly = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:00') as hour_bucket,
        action_type, SUM(cost_weight) as cost
    FROM usage_logs
    WHERE created_at > DATE_SUB(NOW(), INTERVAL {$hoursBack} HOUR)
    GROUP BY hour_bucket, action_type
    ORDER BY hour_bucket ASC
");
foreach ($stmtHourly->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $hourlyLoad[$row['hour_bucket']][$row['action_type']] = (int)$row['cost'];
}
// ─────────────────────────────────────────────────────────────────────────────

// ── Query 4: Game event breakdown (context = event_type) ─────────────────────
$eventBreakdown = $pdo->query("
    SELECT context, COUNT(*) as cnt, COUNT(DISTINCT team_id) as teams
    FROM usage_logs
    WHERE action_type = 'game_event_log'
      AND created_at > DATE_SUB(NOW(), INTERVAL {$window} HOUR)
      AND context IS NOT NULL
    GROUP BY context
    ORDER BY cnt DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
// ─────────────────────────────────────────────────────────────────────────────

// ── Query 5: Top zware users (coaches met hoogste cost) ───────────────────────
$topUsers = $pdo->query("
    SELECT u.first_name, u.last_name, u.email, t.name as team_name,
        ul.user_id, ul.team_id,
        SUM(ul.cost_weight) as total_cost,
        COUNT(*) as total_calls,
        COUNT(DISTINCT ul.action_type) as feature_count
    FROM usage_logs ul
    JOIN users u ON ul.user_id = u.id
    LEFT JOIN teams t ON ul.team_id = t.id
    WHERE ul.created_at > DATE_SUB(NOW(), INTERVAL {$window} HOUR)
      AND ul.user_id > 0
    GROUP BY ul.user_id, ul.team_id
    ORDER BY total_cost DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
// ─────────────────────────────────────────────────────────────────────────────

require_once __DIR__ . '/../header.php';
?>
<style>
.feature-card { transition: transform 0.15s, box-shadow 0.15s; cursor: default; }
.feature-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important; }
.load-bar-wrap { height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
.load-bar { height: 100%; border-radius: 4px; transition: width 0.4s ease; }
.team-pill { display: inline-block; background: #f1f3f5; border: 1px solid #dee2e6; border-radius: 20px; padding: 2px 10px; font-size: 0.72rem; }
.sparkline-container { height: 50px; }
</style>

<div class="container-fluid mt-4 mb-5 px-4">

    <h2 class="mb-0"><i class="fa-solid fa-chart-area text-primary me-2"></i> Feature Load Dashboard</h2>
    <p class="text-muted mb-0 small mt-1">Welke features veroorzaken de meeste serverbelasting?</p>

    <!-- Tijdvenster switcher -->
    <div class="d-flex align-items-center gap-2 mb-4">
        <span class="text-muted small fw-bold me-1">Venster:</span>
        <div class="btn-group btn-group-sm" role="group">
            <?php foreach ([1=>'1u', 6=>'6u', 24=>'24u', 168=>'7d', 720=>'30d'] as $h => $lbl): ?>
            <a href="/admin/feature_load?hours=<?= $h ?>" class="btn <?= $window === $h ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $lbl ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/_monitoring_nav.php'; ?>

    <!-- Totale load summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-dark d-flex align-items-center py-2 px-3 mb-0 border-0 shadow-sm rounded-3">
                <i class="fa-solid fa-bolt text-warning me-2"></i>
                <strong>Totale compute cost</strong> afgelopen <?= $windowLabel ?>:
                <span class="badge bg-warning text-dark fs-6 ms-2"><?= number_format($totalCostAll) ?></span>
                <span class="text-muted ms-2 small">(hogere waarde = meer serverbelasting)</span>
            </div>
        </div>
    </div>

    <!-- Feature kaarten grid -->
    <div class="row g-3 mb-4">
    <?php foreach ($featureConfig as $actionKey => $cfg):
        $s = $statsByAction[$actionKey] ?? ['total_calls'=>0,'unique_coaches'=>0,'unique_teams'=>0,'total_cost'=>0,'avg_cost'=>0,'max_cost'=>0];
        $pct = $totalCostAll > 0 ? round(($s['total_cost'] / $totalCostAll) * 100) : 0;
        $topTeams = $topTeamsPerFeature[$actionKey] ?? [];
    ?>
    <div class="col-md-6 col-xl-3">
        <div class="card feature-card shadow-sm border-0 h-100">
            <div class="card-header bg-<?= $cfg['color'] ?> text-white py-2 d-flex align-items-center">
                <i class="fa-solid <?= $cfg['icon'] ?> me-2"></i>
                <span class="fw-bold"><?= $cfg['label'] ?></span>
                <span class="ms-auto badge bg-white text-<?= $cfg['color'] ?>"><?= number_format((int)$s['total_cost']) ?> cost</span>
            </div>
            <div class="card-body py-2 px-3">
                <!-- Load bar -->
                <div class="load-bar-wrap mb-2 mt-1">
                    <div class="load-bar bg-<?= $cfg['color'] ?>" style="width: <?= $pct ?>%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span><?= $pct ?>% van totale load</span>
                    <span><?= number_format((int)$s['total_calls']) ?> calls</span>
                </div>
                <!-- Stats rij -->
                <div class="row g-1 text-center mb-2">
                    <div class="col-4">
                        <div class="p-1 bg-light rounded">
                            <div class="fw-bold small"><?= (int)$s['unique_coaches'] ?></div>
                            <div style="font-size:0.65rem" class="text-muted">coaches</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-1 bg-light rounded">
                            <div class="fw-bold small"><?= (int)$s['unique_teams'] ?></div>
                            <div style="font-size:0.65rem" class="text-muted">teams</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-1 bg-light rounded">
                            <div class="fw-bold small"><?= $s['avg_cost'] ?></div>
                            <div style="font-size:0.65rem" class="text-muted">avg cost</div>
                        </div>
                    </div>
                </div>
                <!-- Top teams -->
                <?php if (!empty($topTeams)): ?>
                <div style="font-size: 0.72rem;" class="text-muted mb-1">Top teams:</div>
                <?php foreach ($topTeams as $t): ?>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="team-pill"><?= htmlspecialchars($t['team_name'] ?: 'Team #'.$t['team_id']) ?></span>
                    <span class="small text-muted"><?= $t['cost'] ?> cost / <?= $t['calls'] ?> calls</span>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-muted small">Geen data in dit venster</div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white border-0 py-1 px-3">
                <small class="text-muted"><i class="fa-solid fa-circle-info me-1"></i><?= $cfg['desc'] ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <div class="row g-4">

        <!-- Game Events breakdown -->
        <?php if (!empty($eventBreakdown)): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fa-solid fa-futbol me-2"></i><strong>Game Events Breakdown</strong>
                    <small class="ms-2 text-white-50">(door ouders gelogd)</small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Event Type</th>
                                <th class="text-center">Calls</th>
                                <th class="text-center">Teams</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventBreakdown as $ev): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($ev['context']) ?></span></td>
                                <td class="text-center fw-bold"><?= $ev['cnt'] ?></td>
                                <td class="text-center text-muted"><?= $ev['teams'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Top Heavy Users -->
        <div class="col-md-<?= !empty($eventBreakdown) ? '8' : '12' ?>">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-2">
                    <i class="fa-solid fa-user-gear text-danger me-2"></i>
                    <strong>Zwaarste Gebruikers</strong>
                    <small class="ms-2 text-muted">afgelopen <?= $windowLabel ?></small>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($topUsers)): ?>
                    <div class="text-center text-muted py-4">Geen data in dit venster.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Coach</th>
                                    <th>Team</th>
                                    <th class="text-center">Calls</th>
                                    <th class="text-center">Features</th>
                                    <th class="text-end pe-3">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $maxCost = max(array_column($topUsers, 'total_cost')) ?: 1;
                                foreach ($topUsers as $u):
                                    $barPct = round(($u['total_cost'] / $maxCost) * 100);
                                    $barColor = $u['total_cost'] > $maxCost * 0.7 ? 'danger' : ($u['total_cost'] > $maxCost * 0.3 ? 'warning' : 'success');
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold small"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                        <div class="text-muted" style="font-size:0.72rem"><?= htmlspecialchars($u['email']) ?></div>
                                    </td>
                                    <td class="small align-middle"><?= htmlspecialchars($u['team_name'] ?? '—') ?></td>
                                    <td class="text-center align-middle"><?= number_format($u['total_calls']) ?></td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-light text-dark border"><?= $u['feature_count'] ?></span>
                                    </td>
                                    <td class="text-end pe-3 align-middle">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="load-bar-wrap flex-grow-1" style="max-width:80px">
                                                <div class="load-bar bg-<?= $barColor ?>" style="width: <?= $barPct ?>%"></div>
                                            </div>
                                            <strong class="text-<?= $barColor ?>"><?= number_format($u['total_cost']) ?></strong>
                                        </div>
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
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
