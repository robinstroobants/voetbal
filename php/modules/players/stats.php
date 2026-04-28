<?php
$page_title = 'Statistieken per Periode';
require_once dirname(__DIR__, 2) . '/core/getconn.php';

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
        COUNT(gpl.id) as matches_played,
        GROUP_CONCAT(CASE WHEN gpl.id IS NOT NULL THEN g.format ELSE NULL END) as played_formats
    FROM players p
    LEFT JOIN games g ON g.team_id = p.team_id AND g.game_date BETWEEN ? AND ?
    LEFT JOIN game_playtime_logs gpl ON p.id = gpl.player_id AND gpl.game_id = g.id
    WHERE p.team_id = ?
    GROUP BY p.id
    ORDER BY total_seconds DESC, matches_played DESC, p.first_name ASC
");
$stmtStats->execute([$filter_start, $filter_end, $team_id]);
$stats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

require_once dirname(__DIR__, 2) . '/models/MatchManager.php';
require_once dirname(__DIR__, 2) . '/models/game.php';

$matchManager = new MatchManager($pdo);
$pt_all_games = $matchManager->getHistoricalPlaytime($team_id);

$filtered_pt_games = [];
foreach ($pt_all_games as $key => $g) {
    $parts = explode('_', $key, 2);
    $date_part = $parts[0] ?? '';
    if (strlen($date_part) === 6) {
        $yy = substr($date_part, 0, 2);
        $mm = substr($date_part, 2, 2);
        $dd = substr($date_part, 4, 2);
        $game_dt = "20{$yy}-{$mm}-{$dd} 00:00:00";
        if ($game_dt >= $filter_start && $game_dt <= $filter_end) {
            $filtered_pt_games[$key] = $g;
        }
    }
}
$pos_stats = build_playtime_stats($filtered_pt_games, []);

$playPositions = [];
foreach ($pos_stats as $pid => $stat) {
    if (isset($stat['positions'])) {
        foreach ($stat['positions'] as $pos => $data) {
            if ($pos !== 'bench' && !in_array($pos, $playPositions)) {
                $playPositions[] = $pos;
            }
        }
    }
}
sort($playPositions);

foreach ($stats as &$s) {
    // Bereken eigen aanwezige max time (voor info popover en percentages)
    $own_max_seconds = 0;
    if (!empty($s['played_formats'])) {
        $formats = explode(',', $s['played_formats']);
        foreach ($formats as $f) {
            if (preg_match('/_(\d+)x(\d+)/', $f, $m)) {
                $own_max_seconds += (int)$m[1] * (int)$m[2] * 60;
            }
        }
    }
    $s['own_max_seconds'] = $own_max_seconds;
    
    // Voeg positie-percentages toe
    $s['pos_percentages'] = [];
    $pid = $s['id'];
    if (isset($pos_stats[$pid]['positions'])) {
        foreach ($playPositions as $pos) {
            $s['pos_percentages'][$pos] = $pos_stats[$pid]['positions'][$pos]['percentage'] ?? 0;
        }
    }
}
unset($s);

function formatTimeStats($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    $parts = [];
    if ($hours > 0) $parts[] = $hours . 'u';
    if ($minutes > 0 || $hours > 0) $parts[] = str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'm';
    $parts[] = str_pad($secs, 2, '0', STR_PAD_LEFT) . 's';
    return implode(' ', $parts);
}

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

require_once dirname(__DIR__, 2) . '/header.php';
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
                        <a href="/settings/periods?season_year=<?= $season_year ?>" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fa-solid fa-plus me-1"></i> Periodes Instellen
                        </a>
                        <div class="form-text mt-1 text-muted">Je hebt voor dit seizoen nog geen periodes (zoals Voorbereiding of Najaar) ingesteld.</div>
                    <?php else: ?>
                        <a href="/settings/periods?season_year=<?= $season_year ?>" class="btn btn-outline-secondary btn-sm mt-2">
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
        <div class="w-100" style="overflow-x: visible;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light sticky-top" style="z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <tr>
                        <th>Speler</th>
                        <th class="text-center" title="Matchen Gespeeld"><i class="fa-solid fa-hashtag text-secondary"></i> Matchen</th>
                        <th class="text-center" title="Totaal Minuten op het Veld of in Doel"><i class="fa-solid fa-stopwatch text-success"></i> Gespeeld</th>
                        <?php foreach($playPositions as $pos): ?>
                        <th class="text-center text-muted" style="font-size: 0.85rem;" title="Percentage gespeeld op positie <?= $pos == 1 ? 'GK' : $pos ?>"><?= $pos == 1 ? 'GK' : $pos ?></th>
                        <?php endforeach; ?>
                        <?php if ($has_multiple_coaches): ?>
                        <th class="text-center" title="Verdeling van speeltijd per coach"><i class="fa-solid fa-chalkboard-user text-info"></i> Coach %</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($stats as $s): ?>
                        <tr>
                            <td class="fw-bold">
                                <?= htmlspecialchars(stripslashes($s['first_name'] . ' ' . $s['last_name'])) ?>
                            </td>
                            <td class="text-center fw-semibold">
                                <?= $s['matches_played'] ?>
                            </td>
                            <td class="text-center fw-bold text-success">
                                <?php
                                    $perc = $s['own_max_seconds'] > 0 ? round(($s['total_seconds'] / $s['own_max_seconds']) * 100) : 0;
                                    $total_time = formatTimeStats($s['total_seconds']);
                                    $bench_time = formatTimeStats($s['bank_seconds']);
                                    $gk_time = formatTimeStats($s['gk_seconds']);
                                    $own_max_time = formatTimeStats($s['own_max_seconds']);
                                    $avg_min_match = $s['matches_played'] > 0 ? round(($s['total_seconds'] / 60) / $s['matches_played']) : 0;

                                    $popover_content = "
                                        <div class='small' style='min-width: 180px;'>
                                            <div class='d-flex justify-content-between mb-1'>
                                                <span class='text-muted'>Gespeeld:</span>
                                                <span class='fw-bold text-success'>$total_time</span>
                                            </div>
                                            <div class='d-flex justify-content-between mb-1'>
                                                <span class='text-muted'>Tijd op de bank:</span>
                                                <span class='fw-bold text-warning'>$bench_time</span>
                                            </div>
                                            <div class='d-flex justify-content-between mb-2 pb-2 border-bottom'>
                                                <span class='text-muted'>Selectie max. speeltijd:</span>
                                                <span class='fw-bold'>$own_max_time</span>
                                            </div>
                                            <div class='d-flex justify-content-between mb-1'>
                                                <span class='text-muted'>Aantal matchen geselecteerd:</span>
                                                <span class='fw-bold'>{$s['matches_played']}</span>
                                            </div>
                                            <div class='d-flex justify-content-between mb-1'>
                                                <span class='text-muted'>Als doelman:</span>
                                                <span class='fw-bold text-info'>$gk_time</span>
                                            </div>
                                            <div class='d-flex justify-content-between mt-2 pt-2 border-top'>
                                                <span class='text-muted'>Gemiddeld / match:</span>
                                                <span class='fw-bold'>$avg_min_match min</span>
                                            </div>
                                        </div>
                                    ";
                                ?>
                                <span tabindex="0" 
                                      data-bs-toggle="popover" 
                                      data-bs-placement="top" 
                                      data-bs-html="true"
                                      data-bs-trigger="hover focus"
                                      title="<i class='fa-solid fa-circle-info text-primary me-1'></i> Playtime Details" 
                                      data-bs-content="<?= htmlspecialchars($popover_content, ENT_QUOTES, 'UTF-8') ?>"
                                      style="cursor: help; border-bottom: 1px dashed #198754; padding-bottom: 2px;">
                                    <?= $perc ?>%
                                </span>
                            </td>
                            <?php foreach($playPositions as $pos): 
                                $pct = $s['pos_percentages'][$pos] ?? 0;
                            ?>
                            <td class="text-center" style="font-size: 0.85rem;">
                                <?= $pct > 0 ? round($pct).'%' : '<span class="text-muted opacity-50">-</span>' ?>
                            </td>
                            <?php endforeach; ?>
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
                            <td colspan="<?= ($has_multiple_coaches ? 4 : 3) + count($playPositions) ?>" class="text-center py-4 text-muted">Geen spelers gevonden voor dit team.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>

