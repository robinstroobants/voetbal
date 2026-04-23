<?php
// admin/manage_schemas.php
require_once __DIR__ . '/../getconn.php';

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $schemaId = (int)$_POST['schema_id'];
    
    // Safety check: verify usage count is 0
    $stmtUsage = $pdo->prepare("SELECT COUNT(*) FROM game_lineups WHERE schema_id = ?");
    $stmtUsage->execute([$schemaId]);
    $usage = (int)$stmtUsage->fetchColumn();
    
    if ($usage > 0) {
        $error = "Kan schema #$schemaId niet verwijderen omdat het nog in gebruik is door $usage wedstrijd(en).";
    } else {
        $stmtDel = $pdo->prepare("DELETE FROM lineups WHERE id = ?");
        if ($stmtDel->execute([$schemaId])) {
            $success = "Schema #$schemaId succesvol verwijderd.";
        } else {
            $error = "Fout bij het verwijderen van schema #$schemaId.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_unused_errors') {
    $ids = json_decode($_POST['schema_ids'], true);
    if (is_array($ids) && !empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        // Double check usage count = 0 to be completely safe
        $sqlCheck = "SELECT id FROM lineups l WHERE id IN ($placeholders) AND (SELECT COUNT(*) FROM game_lineups gl WHERE gl.schema_id = l.id) = 0";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute($ids);
        $safeToDel = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($safeToDel)) {
            $pl = str_repeat('?,', count($safeToDel) - 1) . '?';
            $stmtDel = $pdo->prepare("DELETE FROM lineups WHERE id IN ($pl)");
            $stmtDel->execute($safeToDel);
            $success = count($safeToDel) . " ongebruikte defecte schema's succesvol en definitief gewist.";
        }
    }
}

// Fetch all schemas with usage counts
// To get actual player count efficiently, we can use JSON_LENGTH(JSON_EXTRACT(schema_data, '$[0].lineup')) + JSON_LENGTH(JSON_EXTRACT(schema_data, '$[0].bench'))
// But we might have some old schemas where this fails, so we can calculate it in PHP or use the player_count column, but earlier we saw player_count was sometimes wrong.
// We'll calculate it in PHP.

$sql = "
    SELECT 
        l.id,
        l.game_format,
        l.player_count as fallback_playercount,
        l.is_original,
        l.schema_data,
        (SELECT COUNT(*) FROM game_lineups gl WHERE gl.schema_id = l.id) as usage_count
    FROM lineups l
    ORDER BY usage_count DESC, l.id DESC
";
$stmt = $pdo->query($sql);
$schemas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate broken schemas count for banner
$broken_schemas_count = 0;
foreach ($schemas as $row) {
    $shifts = json_decode($row['schema_data'], true);
    if (!$shifts) continue;
    
    preg_match('/_(\d+)gk_/', $row['game_format'], $matches);
    $sc_gk_count = isset($matches[1]) ? (int)$matches[1] : 0;
    
    $sc_playercount = 0;
    if (isset($shifts[0]['lineup']) && isset($shifts[0]['bench'])) {
        $sc_playercount = count($shifts[0]['lineup']) + count($shifts[0]['bench']);
    } else {
        $sc_playercount = $row['fallback_playercount'];
    }
    
    $pt = array_fill(0, $sc_playercount, 0);
    $pt_pos1 = array_fill(0, $sc_playercount, 0);
    $sc_errors = [];
    
    foreach ($shifts as $i => $shift) {
        if (!is_numeric($i)) continue;
        $dur = $shift['duration'] ?? 0;
        foreach ($shift['lineup'] ?? [] as $pos => $pid) {
            if ($pid < $sc_playercount) {
                $pt[$pid] += $dur;
                if ($pos == 1) $pt_pos1[$pid] += $dur;
            }
        }
        
        if ($i % 2 === 1) { // 2e helft
            $prevShift = $shifts[$i - 1];
            $prevBench = array_values($prevShift['bench'] ?? []);
            $currLineup = array_values($shift['lineup'] ?? []);
            foreach ($prevBench as $benchSitter) {
                if ($benchSitter < $sc_playercount && $benchSitter >= $sc_gk_count && !in_array($benchSitter, $currLineup)) {
                    $sc_errors[] = "Double Bank Penalty";
                }
            }
            
            $expIn = []; $expOut = [];
            foreach ($prevShift['lineup'] as $pos => $sp_oud) {
                if (isset($shift['lineup'][$pos])) {
                    $sp_nw = $shift['lineup'][$pos];
                    if ($sp_oud !== $sp_nw) {
                        $expIn[$pos] = $sp_nw;
                        $expOut[$pos] = $sp_oud;
                    }
                }
            }
            if ($expIn != ($shift['subs']['in'] ?? [])) $sc_errors[] = "Subs-In fout";
            if ($expOut != ($shift['subs']['out'] ?? [])) $sc_errors[] = "Subs-Out fout";
        }
        
        // Check missing/duplicates
        $allAssigned = array_merge(array_values($shift['lineup'] ?? []), array_values($shift['bench'] ?? []));
        $missing = array_diff(range(0, $sc_playercount - 1), $allAssigned);
        $duplicate = array_diff_assoc($allAssigned, array_unique($allAssigned));
        if (!empty($missing)) $sc_errors[] = "Missing players";
        if (!empty($duplicate)) $sc_errors[] = "Duplicate players";
    }
    
    $filtered = [];
    for ($p=0; $p<$sc_playercount; $p++) {
        if ($pt[$p] > 0 && $pt[$p] === $pt_pos1[$p]) continue;
        $filtered[] = $pt[$p];
    }
    if (count(array_unique($filtered)) > 2) $sc_errors[] = "Speeltijd disbalans";
    
    if (!empty($sc_errors)) {
        $broken_schemas_count++;
        $broken_schema_ids[] = $row['id'];
        if ($row['usage_count'] == 0) {
            $unused_broken_schemas[] = $row['id'];
        }
    }
}

$page_title = 'Schema Beheer';
require_once __DIR__ . '/../header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fa-solid fa-sitemap text-primary me-2"></i> Schema Bibliotheek</h2>
            <p class="text-muted mb-0">Beheer alle theorie-matrices en rotatieschema's.</p>
        </div>
        <a href="/admin" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Terug naar Admin</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success shadow-sm"><i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($broken_schemas_count > 0): ?>
        <div class="alert alert-warning shadow-sm border-0 border-start border-warning border-4 mb-4 d-flex justify-content-between align-items-center">
            <div>
                <i class="fa-solid fa-bug me-2 text-danger"></i>
                <strong>Let op:</strong> Er zijn momenteel <span class="badge bg-danger rounded-pill fs-6"><?= $broken_schemas_count ?></span> schema's in de bibliotheek die wiskundige theorie-fouten bevatten. Deze zullen falen in de Unit Tests.
            </div>
            <div class="d-flex gap-2">
                <a href="/admin/inspect_schema" class="btn btn-sm btn-outline-danger fw-bold"><i class="fa-solid fa-stethoscope me-1"></i> Bekijk in Schema Diagnose</a>
                <?php if (!empty($unused_broken_schemas)): ?>
                    <form method="POST" class="m-0" onsubmit="return confirm('Weet je zeker dat je <?= count($unused_broken_schemas) ?> ongebruikte foute schema\'s definitief wil verwijderen?');">
                        <input type="hidden" name="action" value="delete_unused_errors">
                        <input type="hidden" name="schema_ids" value="<?= htmlspecialchars(json_encode($unused_broken_schemas)) ?>">
                        <button type="submit" class="btn btn-sm btn-danger fw-bold"><i class="fa-solid fa-trash me-1"></i> Wis Ongebruikte Fouten (<?= count($unused_broken_schemas) ?>)</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body p-3 d-flex gap-3 align-items-center flex-wrap">
            <strong><i class="fa-solid fa-filter me-1 text-muted"></i> Filters:</strong>
            <select id="filterFormat" class="form-select form-select-sm w-auto">
                <option value="">Alle Formaten</option>
                <?php 
                    $uniqueFormats = array_unique(array_column($schemas, 'game_format'));
                    sort($uniqueFormats);
                    foreach($uniqueFormats as $f) {
                        echo "<option value=\"".htmlspecialchars($f)."\">".htmlspecialchars($f)."</option>";
                    }
                ?>
            </select>
            <select id="filterType" class="form-select form-select-sm w-auto">
                <option value="">Alle Types</option>
                <option value="origineel">Enkel Origineel</option>
                <option value="aangepast">Enkel Aangepast</option>
            </select>
            <div class="form-check form-switch ms-2">
                <input class="form-check-input" type="checkbox" id="filterUsed">
                <label class="form-check-label small" for="filterUsed">Verberg Ongebruikte</label>
            </div>
            <div class="form-check form-switch ms-2">
                <input class="form-check-input" type="checkbox" id="filterErrors">
                <label class="form-check-label small text-danger fw-bold" for="filterErrors">Toon enkel met fouten</label>
            </div>
            <button class="btn btn-sm btn-link text-muted ms-auto text-decoration-none" onclick="resetFilters()">Reset</button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="schemasTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 cursor-pointer" onclick="sortTable(0, 'num')">ID <i class="fa-solid fa-sort text-muted small ms-1"></i></th>
                            <th class="cursor-pointer" onclick="sortTable(1, 'str')">Formaat <i class="fa-solid fa-sort text-muted small ms-1"></i></th>
                            <th class="cursor-pointer" onclick="sortTable(2, 'num')">Spelers <i class="fa-solid fa-sort text-muted small ms-1"></i></th>
                            <th class="cursor-pointer" onclick="sortTable(3, 'str')">Type <i class="fa-solid fa-sort text-muted small ms-1"></i></th>
                            <th class="cursor-pointer text-center" onclick="sortTable(4, 'num')">Gebruik (M/K) <i class="fa-solid fa-sort text-muted small ms-1"></i></th>
                            <th class="text-end pe-4">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schemas as $row): 
                            // Determine actual player count
                            $shifts = json_decode($row['schema_data'], true);
                            if ($shifts && isset($shifts[0]['lineup']) && isset($shifts[0]['bench'])) {
                                $actual_pc = count($shifts[0]['lineup']) + count($shifts[0]['bench']);
                            } else {
                                $actual_pc = $row['fallback_playercount'];
                            }
                            $has_err = in_array($row['id'], $broken_schema_ids ?? []) ? '1' : '0';
                            $type_str = $row['is_original'] ? 'origineel' : 'aangepast';
                        ?>
                            <tr data-format="<?= htmlspecialchars($row['game_format']) ?>" data-type="<?= $type_str ?>" data-usage="<?= $row['usage_count'] ?>" data-error="<?= $has_err ?>">
                                <td class="ps-4 fw-bold">#<?= $row['id'] ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['game_format']) ?></span></td>
                                <td><i class="fa-solid fa-users me-1 text-muted"></i> <?= $actual_pc ?> spelers</td>
                                <td>
                                    <?php if ($row['is_original']): ?>
                                        <span class="badge bg-primary">Origineel Genereerd</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">Aangepast (Patch)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['usage_count'] > 0): ?>
                                        <a href="/admin/schema_usage?schema=<?= $row['id'] ?>" class="text-decoration-none">
                                            <span class="badge bg-success rounded-pill px-3 fs-6" title="<?= $row['usage_count'] ?> keer gebruikt">
                                                <i class="fa-solid fa-fire me-1"></i> <?= $row['usage_count'] ?>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="/admin/inspect_schema?schema=<?= $row['id'] ?>&format=<?= urlencode($row['game_format']) ?>" class="btn btn-sm btn-outline-primary" title="Inspecteren">
                                            <i class="fa-solid fa-magnifying-glass"></i> Inspecteren
                                        </a>
                                        <?php if ($row['usage_count'] > 0): ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary disabled" title="Kan niet verwijderen: is in gebruik">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Weet je zeker dat je schema #<?= $row['id'] ?> definitief wil verwijderen?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="schema_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Verwijderen">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($schemas)): ?>
                            <tr class="empty-state">
                                <td colspan="6" class="text-center py-5 text-muted">Geen schema's gevonden in de bibliotheek.</td>
                            </tr>
                        <?php else: ?>
                            <tr class="empty-state" style="display:none;">
                                <td colspan="6" class="text-center py-5 text-muted">Geen schema's gevonden voor deze filter(s).</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let sortDirections = [false, false, false, false, false];

function applyFilters() {
    let fFormat = document.getElementById('filterFormat').value;
    let fType = document.getElementById('filterType').value;
    let fUsed = document.getElementById('filterUsed').checked;
    let fErrors = document.getElementById('filterErrors').checked;
    
    // Save to localstorage
    localStorage.setItem('schema_filter_format', fFormat);
    localStorage.setItem('schema_filter_type', fType);
    localStorage.setItem('schema_filter_used', fUsed ? '1' : '0');
    localStorage.setItem('schema_filter_errors', fErrors ? '1' : '0');

    let table = document.getElementById("schemasTable");
    let tbody = table.getElementsByTagName("tbody")[0];
    let rows = Array.from(tbody.querySelectorAll('tr:not(.empty-state)'));
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (!row.hasAttribute('data-format')) return; // Skip if no data attributes
        let show = true;
        
        if (fFormat !== "" && row.getAttribute('data-format') !== fFormat) show = false;
        if (fType !== "" && row.getAttribute('data-type') !== fType) show = false;
        
        let usageVal = parseInt(row.getAttribute('data-usage'), 10);
        if (fUsed && usageVal === 0) show = false;
        
        if (fErrors && row.getAttribute('data-error') !== '1') show = false;
        
        row.style.display = show ? '' : 'none';
        if(show) visibleCount++;
    });
    
    // Handle empty state visibility
    let emptyRow = tbody.querySelector('.empty-state');
    if (emptyRow) {
        emptyRow.style.display = (visibleCount === 0) ? '' : 'none';
    }
}

function resetFilters() {
    document.getElementById('filterFormat').value = "";
    document.getElementById('filterType').value = "";
    document.getElementById('filterUsed').checked = false;
    document.getElementById('filterErrors').checked = false;
    applyFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    // Load from localstorage
    if (localStorage.getItem('schema_filter_format') !== null) {
        document.getElementById('filterFormat').value = localStorage.getItem('schema_filter_format');
        document.getElementById('filterType').value = localStorage.getItem('schema_filter_type');
        document.getElementById('filterUsed').checked = (localStorage.getItem('schema_filter_used') === '1' || localStorage.getItem('schema_filter_used') === 'true');
        document.getElementById('filterErrors').checked = (localStorage.getItem('schema_filter_errors') === '1' || localStorage.getItem('schema_filter_errors') === 'true');
    }
    applyFilters();
    
    // Attach events
    document.getElementById('filterFormat').addEventListener('change', applyFilters);
    document.getElementById('filterType').addEventListener('change', applyFilters);
    document.getElementById('filterUsed').addEventListener('change', applyFilters);
    document.getElementById('filterErrors').addEventListener('change', applyFilters);
});

function sortTable(columnIndex, type) {
    let table = document.getElementById("schemasTable");
    let tbody = table.getElementsByTagName("tbody")[0];
    let rows = Array.from(tbody.getElementsByTagName("tr"));
    
    // Ignore if empty state row
    if(rows.length === 1 && rows[0].cells.length === 1) return;

    let dir = sortDirections[columnIndex];
    sortDirections[columnIndex] = !dir; // Toggle

    rows.sort(function(a, b) {
        let x = a.getElementsByTagName("td")[columnIndex].innerText.toLowerCase().trim();
        let y = b.getElementsByTagName("td")[columnIndex].innerText.toLowerCase().trim();

        if (type === 'num') {
            // Extract numeric values for proper sorting
            x = parseFloat(x.replace(/[^0-9.-]+/g, "")) || 0;
            y = parseFloat(y.replace(/[^0-9.-]+/g, "")) || 0;
            return dir ? x - y : y - x;
        } else {
            if (x < y) return dir ? -1 : 1;
            if (x > y) return dir ? 1 : -1;
            return 0;
        }
    });

    // Re-append rows
    for (let row of rows) {
        tbody.appendChild(row);
    }
    
    // Reset icons
    let headers = table.getElementsByTagName("th");
    for(let i=0; i<headers.length; i++) {
        let icon = headers[i].querySelector('i.fa-sort, i.fa-sort-up, i.fa-sort-down');
        if(icon) {
            icon.className = 'fa-solid fa-sort text-muted small ms-1';
        }
    }
    
    // Set active icon
    let activeIcon = headers[columnIndex].querySelector('i');
    if(activeIcon) {
        activeIcon.className = dir ? 'fa-solid fa-sort-up text-primary small ms-1' : 'fa-solid fa-sort-down text-primary small ms-1';
    }
}
</script>

<style>
.cursor-pointer { cursor: pointer; user-select: none; }
.cursor-pointer:hover { background-color: #e9ecef !important; }
</style>

<?php require_once __DIR__ . '/../footer.php'; ?>
