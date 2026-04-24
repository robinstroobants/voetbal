<?php
$page_title = 'Beheer Seizoensperiodes';
require_once __DIR__ . '/core/getconn.php';

$team_id = (int)$_SESSION['team_id'];
$success = '';
$error = '';

// Bepaal huidig seizoen: als voor juli, dan vorig jaar, anders dit jaar
$current_month = (int)date('n');
$current_year = (int)date('Y');
$default_season = $current_month >= 7 ? $current_year : $current_year - 1;

$season_year = isset($_GET['season_year']) ? (int)$_GET['season_year'] : $default_season;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_periods') {
        $names = $_POST['names'] ?? [];
        $starts = $_POST['start_dates'] ?? [];
        $ends = $_POST['end_dates'] ?? [];
        
        $periods = [];
        for ($i = 0; $i < count($names); $i++) {
            $name = trim($names[$i]);
            $start = trim($starts[$i]);
            $end = trim($ends[$i]);
            
            if ($name !== '' && $start !== '' && $end !== '') {
                $periods[] = [
                    'name' => $name,
                    'start' => $start,
                    'end' => $end,
                    'start_time' => strtotime($start),
                    'end_time' => strtotime($end)
                ];
            }
        }
        
        // Sorteer periodes op start datum
        usort($periods, function($a, $b) {
            return $a['start_time'] <=> $b['start_time'];
        });
        
        $is_valid = true;
        
        if (count($periods) > 0) {
            // Valideer chronologie en gaten
            for ($i = 0; $i < count($periods); $i++) {
                if ($periods[$i]['start_time'] > $periods[$i]['end_time']) {
                    $error = "De einddatum van '" . htmlspecialchars($periods[$i]['name']) . "' ligt voor de startdatum.";
                    $is_valid = false;
                    break;
                }
                
                if ($i > 0) {
                    $prev_end = $periods[$i-1]['end_time'];
                    $curr_start = $periods[$i]['start_time'];
                    // curr_start must be prev_end + 1 day
                    $expected_start = strtotime('+1 day', $prev_end);
                    if (date('Y-m-d', $curr_start) !== date('Y-m-d', $expected_start)) {
                        $error = "Gat of overlap gedetecteerd! De periode '" . htmlspecialchars($periods[$i]['name']) . "' (start " . date('d/m/Y', $curr_start) . ") sluit niet naadloos aan op '" . htmlspecialchars($periods[$i-1]['name']) . "' (eind " . date('d/m/Y', $prev_end) . ").";
                        $is_valid = false;
                        break;
                    }
                }
            }
        }
        
        if ($is_valid) {
            try {
                $pdo->beginTransaction();
                // Verwijder alle oude periodes voor dit seizoen
                $stmtDel = $pdo->prepare("DELETE FROM team_periods WHERE team_id = ? AND season_year = ?");
                $stmtDel->execute([$team_id, $season_year]);
                
                if (count($periods) > 0) {
                    $stmtIns = $pdo->prepare("INSERT INTO team_periods (team_id, season_year, name, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
                    foreach ($periods as $p) {
                        $stmtIns->execute([$team_id, $season_year, $p['name'], $p['start'], $p['end']]);
                    }
                    $success = "De seizoensperiodes zijn succesvol en gap-free opgeslagen!";
                } else {
                    $success = "Alle periodes zijn verwijderd. Het systeem valt nu terug op the standaard juli-juni methode.";
                }
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Database fout: " . $e->getMessage();
            }
        }
    }
}

// Haal bestaande periodes op
$stmtGet = $pdo->prepare("SELECT * FROM team_periods WHERE team_id = ? AND season_year = ? ORDER BY start_date ASC");
$stmtGet->execute([$team_id, $season_year]);
$existing_periods = $stmtGet->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fa-solid fa-calendar-week me-2 text-primary"></i> Seizoensperiodes</h2>
        <a href="/settings" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Terug naar Instellingen</a>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="fa-solid fa-info-circle me-2"></i> <strong>Hoe werkt dit?</strong><br>
        Splits je seizoen op in logische blokken (bv. Voorbereiding, Najaar, Voorjaar). Je kan deze blokken later op de statistiekenpagina gebruiken als filter.<br>
        <span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1 mt-2"></i> Belangrijk:</span> Periodes moeten <strong>naadloos aansluiten</strong> op elkaar! Er mogen geen gaten of overlappen tussen de datums zitten. We raden sterk aan om op 1 juli te starten en op 30 juni te eindigen.
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Seizoen <?= $season_year ?>-<?= $season_year+1 ?></h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Ander seizoen kiezen
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item <?= $season_year == $default_season+1 ? 'active' : '' ?>" href="?season_year=<?= $default_season+1 ?>">Volgend (<?= ($default_season+1).'-'.($default_season+2) ?>)</a></li>
                    <li><a class="dropdown-item <?= $season_year == $default_season ? 'active' : '' ?>" href="?season_year=<?= $default_season ?>">Huidig (<?= $default_season.'-'.($default_season+1) ?>)</a></li>
                    <li><a class="dropdown-item <?= $season_year == $default_season-1 ? 'active' : '' ?>" href="?season_year=<?= $default_season-1 ?>">Vorig (<?= ($default_season-1).'-'.$default_season ?>)</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" id="periodsForm">
                <input type="hidden" name="action" value="save_periods">
                
                <div id="periodsContainer">
                    <?php if (count($existing_periods) === 0): ?>
                        <!-- Default template if empty -->
                        <div class="row g-2 mb-3 period-row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold">Naam Periode</label>
                                <input type="text" name="names[]" class="form-control period-name" value="Voorbereiding" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted fw-bold">Startdatum</label>
                                <input type="date" name="start_dates[]" class="form-control start-date" value="<?= $season_year ?>-07-01" required>
                            </div>
                            <div class="col-md-3 position-relative">
                                <label class="form-label small text-muted fw-bold">Einddatum</label>
                                <input type="date" name="end_dates[]" class="form-control end-date" value="<?= $season_year ?>-08-31" required>
                                <div class="quick-links mt-1 d-none text-center" style="font-size:0.75rem;">
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-7">-1w</a>
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-14">-2w</a>
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="7">+1w</a>
                                    <a href="#" class="text-decoration-none shift-date" data-days="14">+2w</a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-btn"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="row g-2 mb-3 period-row align-items-end">
                            <div class="col-md-4">
                                <input type="text" name="names[]" class="form-control period-name" value="Heenronde" required>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="start_dates[]" class="form-control start-date" value="<?= $season_year ?>-09-01" required>
                            </div>
                            <div class="col-md-3 position-relative">
                                <input type="date" name="end_dates[]" class="form-control end-date" value="<?= $season_year ?>-12-31" required>
                                <div class="quick-links mt-1 d-none text-center" style="font-size:0.75rem;">
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-7">-1w</a>
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-14">-2w</a>
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="7">+1w</a>
                                    <a href="#" class="text-decoration-none shift-date" data-days="14">+2w</a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-btn"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="row g-2 mb-3 period-row align-items-end">
                            <div class="col-md-4">
                                <input type="text" name="names[]" class="form-control period-name" value="Terugronde" required>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="start_dates[]" class="form-control start-date" value="<?= $season_year+1 ?>-01-01" required>
                            </div>
                            <div class="col-md-3 position-relative">
                                <input type="date" name="end_dates[]" class="form-control end-date" value="<?= $season_year+1 ?>-06-30" required>
                                <div class="quick-links mt-1 d-none text-center" style="font-size:0.75rem;">
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-7">-1w</a>
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-14">-2w</a>
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="7">+1w</a>
                                    <a href="#" class="text-decoration-none shift-date" data-days="14">+2w</a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-btn"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($existing_periods as $idx => $p): ?>
                        <div class="row g-2 mb-3 period-row align-items-end">
                            <div class="col-md-4">
                                <?php if($idx === 0): ?><label class="form-label small text-muted fw-bold">Naam Periode</label><?php endif; ?>
                                <input type="text" name="names[]" class="form-control period-name" value="<?= htmlspecialchars($p['name']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <?php if($idx === 0): ?><label class="form-label small text-muted fw-bold">Startdatum</label><?php endif; ?>
                                <input type="date" name="start_dates[]" class="form-control start-date" value="<?= $p['start_date'] ?>" required>
                            </div>
                            <div class="col-md-3 position-relative">
                                <?php if($idx === 0): ?><label class="form-label small text-muted fw-bold">Einddatum</label><?php endif; ?>
                                <input type="date" name="end_dates[]" class="form-control end-date" value="<?= $p['end_date'] ?>" required>
                                <div class="quick-links mt-1 d-none text-center" style="font-size:0.75rem;">
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-7">-1w</a>
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-14">-2w</a>
                                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="7">+1w</a>
                                    <a href="#" class="text-decoration-none shift-date" data-days="14">+2w</a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-btn"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between mt-4 border-top pt-4">
                    <button type="button" id="addPeriodBtn" class="btn btn-outline-secondary"><i class="fa-solid fa-plus me-2"></i> Periode Toevoegen</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i> Periodes Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('periodsContainer');
    const addBtn = document.getElementById('addPeriodBtn');

    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-3 period-row align-items-end';
        row.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="names[]" class="form-control period-name" placeholder="Naam" required>
            </div>
            <div class="col-md-3">
                <input type="date" name="start_dates[]" class="form-control start-date" required>
            </div>
            <div class="col-md-3 position-relative">
                <input type="date" name="end_dates[]" class="form-control end-date" required>
                <div class="quick-links mt-1 d-none text-center" style="font-size:0.75rem;">
                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-7">-1w</a>
                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="-14">-2w</a>
                    <a href="#" class="text-decoration-none me-1 shift-date" data-days="7">+1w</a>
                    <a href="#" class="text-decoration-none shift-date" data-days="14">+2w</a>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-btn"><i class="fa-solid fa-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-btn')) {
            e.target.closest('.period-row').remove();
            syncAllDates();
        }
        
        if (e.target.classList.contains('shift-date')) {
            e.preventDefault();
            let row = e.target.closest('.period-row');
            let endInput = row.querySelector('.end-date');
            if (endInput && endInput.value) {
                let days = parseInt(e.target.getAttribute('data-days'));
                let d = new Date(endInput.value);
                d.setDate(d.getDate() + days);
                endInput.value = d.toISOString().split('T')[0];
                syncAllDates();
            }
        }
    });
    
    // Toon de quick-links enkel bij de actieve (gefocuste) rij
    container.addEventListener('focusin', function(e) {
        if (e.target.tagName === 'INPUT') {
            container.querySelectorAll('.quick-links').forEach(el => el.classList.add('d-none'));
            let row = e.target.closest('.period-row');
            if(row) {
                let ql = row.querySelector('.quick-links');
                if(ql) ql.classList.remove('d-none');
            }
        }
    });

    container.addEventListener('change', function(e) {
        if (e.target.classList.contains('end-date') || e.target.classList.contains('start-date')) {
            syncAllDates();
        }
    });
    
    function syncAllDates() {
        let rows = Array.from(container.querySelectorAll('.period-row'));
        for(let i=0; i<rows.length - 1; i++) {
            let currentEnd = rows[i].querySelector('.end-date').value;
            if (currentEnd) {
                let nextStartInput = rows[i+1].querySelector('.start-date');
                let d = new Date(currentEnd);
                d.setDate(d.getDate() + 1);
                nextStartInput.value = d.toISOString().split('T')[0];
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
