<?php
require_once 'getconn.php';
$page_title = 'Schema Auto-Fixer Migration';
require_once 'header.php'; // Genereer admin UI

$action = $_GET['action'] ?? '';
$msg = '';

if ($action === 'fix') {
    $file_basename = basename($_GET['file'] ?? '');
    $schemaId = (int)($_GET['schema'] ?? 0);
    $shift_idx = (int)($_GET['shift'] ?? -1);
    $dup = (int)($_GET['dup'] ?? -1);
    $mis = (int)($_GET['mis'] ?? -1);

    if ($file_basename && $schemaId > 0 && $shift_idx >= 0 && $dup >= 0 && $mis >= 0) {
        $wissel_file = __DIR__ . '/wisselschemas/' . $file_basename;
        if (file_exists($wissel_file)) {
            $ws = [];
            require $wissel_file;

            if (isset($ws[$schemaId][$shift_idx])) {
                $replaced = false;
                
                // Prioriteit 1: Vervang de dubbele speler op de bank door de ontbrekende speler
                if (isset($ws[$schemaId][$shift_idx]['bench'])) {
                    foreach ($ws[$schemaId][$shift_idx]['bench'] as $b_key => $pid) {
                        if ($pid === $dup) {
                            $ws[$schemaId][$shift_idx]['bench'][$b_key] = $mis;
                            $replaced = true;
                            break;
                        }
                    }
                }

                // Prioriteit 2: Als we hem niet op de bank vonden, speelde hij 2x. Vervang een van zijn veldposities.
                if (!$replaced && isset($ws[$schemaId][$shift_idx]['lineup'])) {
                    foreach ($ws[$schemaId][$shift_idx]['lineup'] as $l_key => $pid) {
                        if ($pid === $dup) {
                            $ws[$schemaId][$shift_idx]['lineup'][$l_key] = $mis;
                            $replaced = true;
                            break;
                        }
                    }
                }

                if ($replaced) {
                    // Update het bestand!
                    $file_content = "<?php\n\$ws_fname = '" . str_replace("sp.php", "", basename($wissel_file)) . "';\n";
                    $file_content .= "\$ws = " . var_export($ws, true) . ";\n";
                    
                    if (file_put_contents($wissel_file, $file_content) !== false) {
                        opcache_invalidate($wissel_file, true); // <--- BOOM! Fix OPcache.
                        $msg = "<div class='alert alert-success mt-3'><i class='fa-solid fa-check'></i> <strong>Gefixt:</strong> In Schema $schemaId (Shift $shift_idx) is de ge-kloonde speler ($dup) overschreven met de ontbrekende speler ($mis)!</div>";
                    } else {
                        $msg = "<div class='alert alert-danger mt-3'>Fout bij het wegschrijven naar $file_basename. CHMOD rechten?</div>";
                    }
                } else {
                    $msg = "<div class='alert alert-danger mt-3'>Kon speler $dup niet vervangen. Niet gevonden in de array?</div>";
                }
            }
        }
    }
}

// Analyseer ALLE arrays on the fly
$files = glob(__DIR__ . '/wisselschemas/*sp.php');
$found_errors = [];

foreach ($files as $file) {
    if (basename($file) === '11v11_1gk_2x45_16sp.php') continue; // Example skip if needed

    // Extract playercount from filename
    preg_match('/_(\d+)sp\.php$/', basename($file), $matches);
    $playercount = isset($matches[1]) ? (int)$matches[1] : 0;
    
    $ws = [];
    require $file; // Laadt de interne array

    foreach ($ws as $schemaId => $shifts) {
        foreach ($shifts as $i => $shift) {
            if (!is_numeric($i)) continue;
            
            $fieldPlayers = array_values($shift['lineup'] ?? []);
            $benchPlayers = array_values($shift['bench'] ?? []);
            $allAssignedPlayers = array_merge($fieldPlayers, $benchPlayers);
            
            $missing = array_diff(range(0, $playercount - 1), $allAssignedPlayers);
            $duplicate = array_diff_assoc($allAssignedPlayers, array_unique($allAssignedPlayers));
            
            if (!empty($missing) && !empty($duplicate)) {
                $found_errors[] = [
                    'file' => basename($file),
                    'schema' => $schemaId,
                    'shift' => $i,
                    'missing' => array_values($missing)[0], // Neem 1ste
                    'duplicate' => array_values($duplicate)[0] // Neem 1ste
                ];
            }
        }
    }
}

?>

<div class="container mt-4 mb-5">
    <div class="d-flex align-items-center mb-4">
        <i class="fa-solid fa-screwdriver-wrench text-dark fs-1 me-3"></i>
        <div>
            <h2 class="mb-0">Schema Auto-Fixer</h2>
            <p class="text-muted mb-0">Detecteert en herstelt vermiste/gekloonde speler-variabelen over al je configuratie bestanden.</p>
        </div>
    </div>
    
    <?= $msg ?>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Bestand</th>
                        <th>Matrix Schema</th>
                        <th>Probleem Indicator</th>
                        <th class="text-end pe-4">Migratie Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($found_errors) === 0): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="fa-solid fa-check-circle text-success fs-1 mb-2"></i><br>
                            Geen gekloonde / vermiste spelers meer gedetecteerd in je database.<br>Alles wiskundig zuiver!
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($found_errors as $err): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-secondary"><?= $err['file'] ?></td>
                            <td>
                                <code><?= $err['schema'] ?></code> <small class="text-muted">(ID)</small><br>
                                <small>Shift-index: <?= $err['shift'] ?></small>
                            </td>
                            <td>
                                <span class="badge bg-danger shadow-sm py-2 px-3">
                                    Speler <?= $err['duplicate'] ?> speelt dubbel
                                </span>
                                <i class="fa-solid fa-arrow-right-arrow-left text-muted mx-2"></i>
                                <span class="badge bg-warning text-dark shadow-sm py-2 px-3">
                                    Speler <?= $err['missing'] ?> staat wees
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <?php $baseFormat = str_replace('.php', '', $err['file']); ?>
                                <a href="inspect_schema.php?format=<?= urlencode($baseFormat) ?>&schema=<?= $err['schema'] ?>" target="_blank" class="btn btn-sm btn-outline-primary fw-bold shadow-sm me-2">
                                    <i class="fa-solid fa-eye me-1"></i> Inspect Server
                                </a>
                                <a href="?action=fix&file=<?= urlencode($err['file']) ?>&schema=<?= $err['schema'] ?>&shift=<?= $err['shift'] ?>&dup=<?= $err['duplicate'] ?>&mis=<?= $err['missing'] ?>" class="btn btn-sm btn-success fw-bold shadow-sm">
                                    <i class="fa-solid fa-wand-sparkles me-1"></i> Auto-Fix!
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
