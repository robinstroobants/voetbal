<?php
$page_title = 'Statistieken per Periode';
require_once 'getconn.php';

$team_id = (int)$_SESSION['team_id'];

$current_month = (int)date('n');
$current_year = (int)date('Y');
$default_season = $current_month >= 7 ? $current_year : $current_year - 1;

$season_year = isset($_GET['season_year']) ? (int)$_GET['season_year'] : $default_season;

// Haal periodes op voor dit seizoen
$stmtPeriods = $pdo->prepare("SELECT id, name, start_date, end_date FROM team_periods WHERE team_id = ? AND season_year = ? ORDER BY start_date ASC");
$stmtPeriods->execute([$team_id, $season_year]);
$periods = $stmtPeriods->fetchAll(PDO::FETCH_ASSOC);

// Bepaal de geselecteerde filter: 'all' of een specifieke period_id
$selected_period_id = $_GET['period_id'] ?? 'all';

$filter_start = $season_year . "-07-01 00:00:00";
$filter_end = ($season_year + 1) . "-06-30 23:59:59";
$filter_name = "Heel Seizoen ($season_year - " . ($season_year+1) . ")";

if ($selected_period_id !== 'all') {
    foreach ($periods as $p) {
        if ($p['id'] == $selected_period_id) {
            $filter_start = $p['start_date'] . " 00:00:00";
            $filter_end = $p['end_date'] . " 23:59:59";
            $filter_name = $p['name'] . " (" . date('d/m/Y', strtotime($p['start_date'])) . " - " . date('d/m/Y', strtotime($p['end_date'])) . ")";
            break;
        }
    }
}

// Query voor de statistieken
// We tellen hoeveel matchen de speler speelde
$stmtStats = $pdo->prepare("
    SELECT 
        p.id, 
        p.first_name, 
        p.last_name, 
        COALESCE(SUM(gpl.seconds_played), 0) as total_seconds,
        COALESCE(SUM(gpl.seconds_gk), 0) as gk_seconds,
        COALESCE(SUM(gpl.seconds_bank), 0) as bank_seconds,
        COUNT(gpl.id) as matches_played
    FROM players p
    LEFT JOIN games g ON g.team_id = p.team_id AND g.game_date BETWEEN ? AND ?
    LEFT JOIN game_playtime_logs gpl ON p.id = gpl.player_id AND gpl.game_id = g.id
    WHERE p.team_id = ?
    GROUP BY p.id
    ORDER BY total_seconds DESC, matches_played DESC, p.first_name ASC
");
$stmtStats->execute([$filter_start, $filter_end, $team_id]);
$stats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

// Controleer of het team meer dan 1 coach heeft
$stmtNumCoaches = $pdo->prepare("SELECT COUNT(*) FROM user_teams WHERE team_id = ?");
$stmtNumCoaches->execute([$team_id]);
$has_multiple_coaches = $stmtNumCoaches->fetchColumn() > 1;

$coach_stats = [];
if ($has_multiple_coaches) {
    $stmtCoachStats = $pdo->prepare("
        SELECT 
            p.id as player_id,
            COALESCE(u.first_name, 'Onbekend') as coach_name,
            SUM(gpl.seconds_played) as coach_seconds
        FROM players p
        JOIN game_playtime_logs gpl ON p.id = gpl.player_id
        JOIN games g ON gpl.game_id = g.id
        LEFT JOIN users u ON gpl.coach_id = u.id
        WHERE p.team_id = ? AND g.game_date BETWEEN ? AND ?
        GROUP BY p.id, u.id
    ");
    $stmtCoachStats->execute([$team_id, $filter_start, $filter_end]);
    $coachData = $stmtCoachStats->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($coachData as $row) {
        $pid = $row['player_id'];
        if (!isset($coach_stats[$pid])) $coach_stats[$pid] = [];
        $coach_stats[$pid][$row['coach_name']] = $row['coach_seconds'];
    }
}

require_once 'header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-chart-line me-2 text-primary"></i> Speelminuten Statistieken</h2>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">Seizoen</label>
                    <select name="season_year" class="form-select" onchange="this.form.period_id.value='all'; this.form.submit();">
                        <option value="<?= $default_season+1 ?>" <?= $season_year == $default_season+1 ? 'selected' : '' ?>><?= ($default_season+1).'-'.($default_season+2) ?></option>
                        <option value="<?= $default_season ?>" <?= $season_year == $default_season ? 'selected' : '' ?>><?= $default_season.'-'.($default_season+1) ?> (Huidig)</option>
                        <option value="<?= $default_season-1 ?>" <?= $season_year == $default_season-1 ? 'selected' : '' ?>><?= ($default_season-1).'-'.$default_season ?></option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Periode Filter</label>
                    <select name="period_id" class="form-select" onchange="this.form.submit()">
                        <option value="all">Heel Seizoen (1 juli - 30 juni)</option>
                        <?php if(count($periods) > 0): ?>
                            <optgroup label="Aangepaste Periodes">
                                <?php foreach($periods as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $selected_period_id == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?> (<?= date('d/m', strtotime($p['start_date'])) ?> - <?= date('d/m', strtotime($p['end_date'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-5 text-end">
                    <?php if(count($periods) === 0): ?>
                        <a href="/manage_periods.php?season_year=<?= $season_year ?>" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fa-solid fa-plus me-1"></i> Periodes Instellen
                        </a>
                        <div class="form-text mt-1 text-muted">Je hebt voor dit seizoen nog geen periodes (zoals Voorbereiding of Najaar) ingesteld.</div>
                    <?php else: ?>
                        <a href="/manage_periods.php?season_year=<?= $season_year ?>" class="btn btn-outline-secondary btn-sm mt-2">
                            <i class="fa-solid fa-pen me-1"></i> Periodes Beheren
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary fw-bold"><?= htmlspecialchars($filter_name) ?></h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Speler</th>
                        <th class="text-center" title="Matchen Gespeeld"><i class="fa-solid fa-hashtag text-secondary"></i> Matchen</th>
                        <th class="text-center" title="Totaal Minuten op het Veld of in Doel"><i class="fa-solid fa-stopwatch text-success"></i> Gespeeld</th>
                        <th class="text-center" title="Waarvan Doelman (Minuten)"><i class="fa-solid fa-hands text-muted"></i> Doelman</th>
                        <th class="text-center" title="Totaal Minuten op de Bank"><i class="fa-solid fa-chair text-warning"></i> Bank</th>
                        <?php if ($has_multiple_coaches): ?>
                        <th class="text-center" title="Verdeling van speeltijd per coach"><i class="fa-solid fa-chalkboard-user text-info"></i> Coach %</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($stats as $s): ?>
                        <tr>
                            <td class="fw-bold">
                                <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
                            </td>
                            <td class="text-center fw-semibold">
                                <?= $s['matches_played'] ?>
                            </td>
                            <td class="text-center fw-bold text-success">
                                <?= round($s['total_seconds'] / 60, 1) ?> <span class="small fw-normal opacity-75">min</span>
                            </td>
                            <td class="text-center text-muted">
                                <?= round($s['gk_seconds'] / 60, 1) ?> <span class="small fw-normal opacity-75">min</span>
                            </td>
                            <td class="text-center text-warning fw-semibold">
                                <?= round($s['bank_seconds'] / 60, 1) ?> <span class="small fw-normal opacity-75">min</span>
                            </td>
                            <?php if ($has_multiple_coaches): ?>
                            <td class="text-center" style="font-size: 0.85rem;">
                                <?php 
                                    $pId = $s['id'];
                                    $totC = $s['total_seconds'];
                                    if ($totC > 0 && !empty($coach_stats[$pId])) {
                                        $breakdown = [];
                                        foreach ($coach_stats[$pId] as $cName => $cSec) {
                                            $perc = round(($cSec / $totC) * 100);
                                            // Alleen tonen als > 0%
                                            if ($perc > 0) {
                                                $breakdown[] = "<span class='badge bg-light text-dark border'>$cName: $perc%</span>";
                                            }
                                        }
                                        echo implode(' ', $breakdown);
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(count($stats) === 0): ?>
                        <tr>
                            <td colspan="<?= $has_multiple_coaches ? 6 : 5 ?>" class="text-center py-4 text-muted">Geen spelers gevonden voor dit team.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
