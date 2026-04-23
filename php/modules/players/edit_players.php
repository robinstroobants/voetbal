<?php
// Connect to the database
require_once dirname(__DIR__, 2) . '/core/getconn.php';

// Ajax Save
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    header('Content-Type: application/json');
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data["player_id"])) {
        $id = intval($data["player_id"]);
        $first_name = $data["first_name"] ?? '';
        $last_name = $data["last_name"] ?? '';
        $birthdate = trim($data["birthdate"] ?? '');
        $fav_pos = $data["favorite_positions"] ?? '';
        $is_doelman = !empty($data["is_doelman"]) ? 1 : 0;

        $birthdate_val = ($birthdate === '') ? null : $birthdate;
        $fav_pos_val = $fav_pos; // Geen NULL force, gewoon lege string toestaan voor VARCHAR

        try {
            $stmt = $pdo->prepare("UPDATE players SET first_name=?, last_name=?, birthdate=?, favorite_positions=?, is_doelman=? WHERE id=? AND team_id=?");
            if (!$stmt->execute([$first_name, $last_name, $birthdate_val, $fav_pos_val, $is_doelman, $id, $_SESSION['team_id']])) {
                echo json_encode(['success' => false, 'error' => 'Could not execute query']);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
        
        // Haal de geupdate speler op (zonder veilige crash indien updated_at nog niet bestaat)
        $stmt_check = $pdo->prepare("SELECT * FROM players WHERE id=? AND team_id=?");
        $stmt_check->execute([$id, $_SESSION['team_id']]);
        $row = $stmt_check->fetch(PDO::FETCH_ASSOC) ?: [];
        
        $date_str = '-';
        $ts_val = 0;
        if (!empty($row['updated_at'])) {
            $date_str = date("Y-m-d H:i", strtotime($row['updated_at']));
            $ts_val = strtotime($row['updated_at']);
        }
        
        echo json_encode(['success' => true, 'updated_at' => $date_str, 'ts' => $ts_val]);
        exit;
    }
}

// Fetch players
$stmtAll = $pdo->prepare("SELECT * FROM players WHERE team_id=? ORDER BY first_name, last_name");
$stmtAll->execute([$_SESSION['team_id']]);
$players = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
$players_count = count($players);

// Extraheer format requirements
$stmtF = $pdo->prepare("SELECT default_format FROM teams WHERE id = ?");
$stmtF->execute([$_SESSION['team_id']]);
$default_format = $stmtF->fetchColumn() ?: '8v8';

$max_players = 24;
if (strpos($default_format, '2v2') === 0 || strpos($default_format, '3v3') === 0) {
    $max_players = 12;
}
$remaining_players = max(0, $max_players - $players_count);
?>

<?php 
$page_title = 'Edit Players';
require_once dirname(__DIR__, 2) . '/header.php';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container mt-5 mb-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">Spelers Beheren</h2>
            <div class="text-danger fw-bold small"><i class="fa-solid fa-triangle-exclamation me-1"></i> Let op: Bij bewerken kun je maar één speler tegelijk updaten (klik op het vinkje per rij).</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSinglePlayerModal" <?= $remaining_players <= 0 ? 'disabled' : '' ?>>
                <i class="fa-solid fa-user-plus me-1"></i> Nieuwe Speler
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBulkPlayersModal" <?= $remaining_players <= 0 ? 'disabled' : '' ?>>
                <i class="fa-solid fa-file-excel me-1"></i> Bulk Import
            </button>
        </div>
    </div>
    
    <div class="card p-4 shadow-sm border-0 table-responsive overflow-visible">
        <table id="playersTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Voornaam</th>
                    <th>Achternaam</th>
                    <th>Geboortedatum</th>
                    <th class="text-center">Doelman</th>
                    <th>Fav. Posities</th>
                    <th>Laatst Gewijzigd</th>
                    <th class="text-end">Actie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($players as $row): 
                    $f_id = "f_".$row['id'];
                ?>
                <tr>
                    <td>
                        <span class="view-mode"><?= !empty($row['first_name']) ? htmlspecialchars($row['first_name']) : '-' ?></span>
                        <input type="text" name="first_name" class="form-control form-control-sm edit-mode d-none" value="<?= !empty($row['first_name']) ? htmlspecialchars($row['first_name']) : ''; ?>">
                    </td>
                    <td>
                        <span class="view-mode"><?= !empty($row['last_name']) ? htmlspecialchars($row['last_name']) : '-' ?></span>
                        <input type="text" name="last_name" class="form-control form-control-sm edit-mode d-none" value="<?= !empty($row['last_name']) ? htmlspecialchars($row['last_name']) : ''; ?>">
                    </td>
                    <td>
                        <span class="view-mode"><?= !empty($row['birthdate']) ? htmlspecialchars($row['birthdate']) : '-' ?></span>
                        <input type="text" name="birthdate" class="form-control form-control-sm edit-mode d-none" value="<?= !empty($row['birthdate']) ? htmlspecialchars($row['birthdate']) : ''; ?>" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-autoclose="true" data-date-container="body">
                    </td>
                    <td class="text-center">
                        <span class="view-mode badge <?= (!empty($row['is_doelman'])) ? 'bg-primary' : 'bg-secondary' ?>"><?= (!empty($row['is_doelman'])) ? 'Doelman' : 'Veld' ?></span>
                        <div class="form-check form-switch pt-1 d-inline-block edit-mode d-none">
                            <input class="form-check-input doelman-toggle" type="checkbox" name="is_doelman" value="1" <?= (!empty($row['is_doelman'])) ? 'checked' : ''; ?>>
                        </div>
                    </td>
                    <td>
                        <span class="view-mode"><?= !empty($row['favorite_positions']) ? htmlspecialchars($row['favorite_positions']) : '-' ?></span>
                        <div class="fav-pos-container edit-mode d-none">
                            <input type="text" name="favorite_positions" class="form-control form-control-sm border-primary fav-pos-input" placeholder="Bv. 7,11,2" value="<?= !empty($row['favorite_positions']) ? htmlspecialchars($row['favorite_positions']) : ''; ?>">
                        </div>
                    </td>
                    <?php $updated_ts = isset($row['updated_at']) ? strtotime($row['updated_at']) : 0; ?>
                    <td data-sort="<?= $updated_ts ?>">
                        <span class="small text-muted fw-medium"><?= !empty($row['updated_at']) ? date("Y-m-d H:i", strtotime($row['updated_at'])) : '-' ?></span>
                    </td>
                    <td class="text-end" style="min-width: 120px; cursor: pointer;">
                        <span class="view-mode">
                            <a href="/player_dashboard.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-2" title="Profiel & Dashboard"><i class="fa-solid fa-user"></i></a>
                            <span class="text-muted small fst-italic" title="Snel bewerken"><i class="fa-solid fa-pen"></i></span>
                        </span>
                        <button type="button" class="btn btn-primary btn-sm save-btn d-none" data-id="<?= $row['id'] ?>"><i class="fa-solid fa-check"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>

<!-- DataTables JS & jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
// Toggle functionaliteit voor doelmannen veld
    function updateVisibility(toggle) {
        const row = toggle.closest('tr');
        if (!row) return; // In case DataTables modifies DOM unexpectedly during init
        const favContainer = row.querySelector('.fav-pos-container');
        const favInput = favContainer.querySelector('.fav-pos-input');
        
        if (toggle.checked) {
            favContainer.style.visibility = 'hidden';
        } else {
            favContainer.style.visibility = 'visible';
        }
    }

    const checkDoelmanSingle = document.getElementById('checkDoelmanSingle');
    const favPosContainerSingle = document.getElementById('favPosContainerSingle');
    if(checkDoelmanSingle && favPosContainerSingle) {
        checkDoelmanSingle.addEventListener('change', function() {
            if (this.checked) {
                favPosContainerSingle.style.display = 'none';
                favPosContainerSingle.querySelector('input').value = '';
            } else {
                favPosContainerSingle.style.display = 'block';
            }
        });
    }

    // Attach listeners on body so DataTables pagination doesn't break event listeners
    document.body.addEventListener('change', function(e) {
        if (e.target.classList.contains('doelman-toggle')) {
            updateVisibility(e.target);
            if(e.target.checked) {
                // Clear fav input on check
                const favInput = e.target.closest('tr').querySelector('.fav-pos-input');
                if(favInput) favInput.value = '';
            }
        }
    });

    // Initialiseer visibility op alle initiele toggles
    document.querySelectorAll('.doelman-toggle').forEach(t => updateVisibility(t));
    
    // Zorg ervoor dat klikken op een rij inline edit mode aanzet
    $('#playersTable tbody').on('click', 'tr', function(e) {
        // Als we niet op de save-knop zelf klikken of een link, activeer focus
        if (!e.target.closest('.save-btn') && !e.target.closest('a')) {
            let tr = $(this);
            if (!tr.hasClass('editing')) {
                tr.addClass('editing');
                tr.find('.view-mode').addClass('d-none');
                tr.find('.edit-mode, .save-btn').removeClass('d-none');
            }
        }
    });

    // Ajax Save Logic
    $('#playersTable tbody').on('click', '.save-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const tr = $(this).closest('tr');
        const id = $(this).data('id');
        
        const data = {
            player_id: id,
            first_name: tr.find('input[name="first_name"]').val(),
            last_name: tr.find('input[name="last_name"]').val(),
            birthdate: tr.find('input[name="birthdate"]').val(),
            favorite_positions: tr.find('input[name="favorite_positions"]').val(),
            is_doelman: tr.find('input[name="is_doelman"]').is(':checked') ? 1 : 0
        };
        
        const btn = $(this);
        btn.html('<i class="fa-solid fa-spinner fa-spin"></i>');
        
        fetch('edit_players.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(res => {
            if(res.success) {
                // Update view models
                tr.find('.view-mode').eq(0).text(data.first_name || '-');
                tr.find('.view-mode').eq(1).text(data.last_name || '-');
                tr.find('.view-mode').eq(2).text(data.birthdate || '-');
                tr.find('.view-mode').eq(3).removeClass('bg-primary bg-secondary').addClass(data.is_doelman ? 'bg-primary' : 'bg-secondary').text(data.is_doelman ? 'Doelman' : 'Veld');
                tr.find('.view-mode').eq(4).text(data.favorite_positions || '-');
                
                // Update timestamp
                tr.find('td:nth-child(6) span.small').text(res.updated_at);
                
                // Close editing mode
                tr.removeClass('editing');
                tr.find('.edit-mode, .save-btn').addClass('d-none');
                tr.find('.view-mode').removeClass('d-none');
                btn.html('<i class="fa-solid fa-check"></i>');
                
                // Also update datatables internal cell data for sorting and perform a redraw
                let dt = $('#playersTable').DataTable();
                tr.find('td:nth-child(6)').attr('data-sort', res.ts);
                dt.cell(tr, 5).data(tr.find('td:nth-child(6)').html()).invalidate();
                dt.draw(false); // false means 'keep current paging'
            } else {
                console.error("Server Fout:", res.error);
                btn.html('<i class="fa-solid fa-triangle-exclamation text-warning"></i>');
                alert("Bewaren mislukt in database! Error: " + (res.error || "Onbekend"));
            }
        }).catch(err => {
            console.error("AJAX Error:", err);
            btn.html('<i class="fa-solid fa-triangle-exclamation text-warning"></i>');
            alert("Fout bij opslaan! Controleer database of netwerk.");
        });
    });

    // Initialiseer DataTables met State Saving
    $('#playersTable').DataTable({
        stateSave: true, // Zorgt dat de geselecteerde sortering onthouden blijft over page-reloads heen
        responsive: true,
        pageLength: 24, // Standaard 24 rijen zichtbaar
        lengthMenu: [[12, 24, 48, -1], [12, 24, 48, "Alles"]],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/nl-NL.json'
        },
        columnDefs: [
            { orderable: false, targets: [4, 6] } // Disable sorting on Fav Pos and Action column
        ]
    });
});
</script>

<script>
function submitApicall(formId) {
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const formData = new FormData(form);
    
    // Disable in modal
    const btn = form.closest('.modal-content').querySelector('.modal-footer .btn-primary');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Bezig...';
    btn.disabled = true;

    fetch('api_onboarding_add.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Fout: ' + (data.error || 'Onbekende fout'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        alert('Er is een netwerkfout opgetreden bij het toevoegen.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<!-- MODALS -->
<!-- 1. Single Player Modal -->
<div class="modal fade" id="addSinglePlayerModal" tabindex="-1" aria-labelledby="addSingleLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="addSingleLabel"><i class="fa-solid fa-user-plus me-2"></i>Eén Speler Toevoegen</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id="frmSinglePlayer">
              <input type="hidden" name="action" value="add_single_player">
              <div class="row">
                  <div class="col-6 mb-3">
                      <label class="form-label fw-bold small text-muted">VOORNAAM <span class="text-danger">*</span></label>
                      <input type="text" name="first_name" class="form-control" required placeholder="Bv. Eden">
                  </div>
                  <div class="col-6 mb-3">
                      <label class="form-label fw-bold small text-muted">ACHTERNAAM</label>
                      <input type="text" name="last_name" class="form-control" placeholder="Bv. Hazard">
                  </div>
              </div>
              <div class="mb-3">
                  <div class="form-check form-switch pt-1">
                      <input class="form-check-input" type="checkbox" name="is_doelman" id="checkDoelmanSingle" value="1">
                      <label class="form-check-label fw-bold text-dark" for="checkDoelmanSingle">Deze speler is een doelman</label>
                  </div>
              </div>
              <div class="mb-3" id="favPosContainerSingle">
                  <label class="form-label fw-bold small text-muted">FAVORIETE POSITIES (Optioneel)</label>
                  <input type="text" name="favorite_positions" class="form-control" placeholder="Bv. 7, 11 (gescheiden met komma)">
              </div>
          </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-primary" onclick="submitApicall('frmSinglePlayer')"><i class="fa-solid fa-check me-1"></i> Toevoegen</button>
      </div>
    </div>
  </div>
</div>

<!-- 2. Bulk Player Modal -->
<div class="modal fade" id="addBulkPlayersModal" tabindex="-1" aria-labelledby="addBulkLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addBulkLabel"><i class="fa-solid fa-list-ul me-2"></i>Bulk Spelers Importeren</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="alert alert-info border-0 shadow-sm">
             <i class="fa-solid fa-circle-info me-2"></i>Plak hier de namen uit bijvoorbeeld je Excel-bestand. Zet <strong>elke speler op een nieuwe regel</strong>.
             <hr class="my-2">
             <div class="small"><i class="fa-solid fa-user-shield me-1"></i> Voor dit formaat (<?= htmlspecialchars($default_format) ?>) geldt een limiet van <b><?= $max_players ?> spelers</b>. Je kunt er nu nog maximaal <b><?= $remaining_players ?></b> toevoegen.</div>
          </div>
          <form id="frmBulkPlayers">
              <input type="hidden" name="action" value="add_bulk_players">
              <div class="mb-3">
                  <textarea name="players_text" class="form-control shadow-sm" style="font-family: monospace; resize: none;" rows="12" placeholder="Jan Peeters&#10;Piet Smet&#10;Kevin De Bruyne..."></textarea>
              </div>
          </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-primary" onclick="submitApicall('frmBulkPlayers')"><i class="fa-solid fa-file-import me-1"></i> Lijst Importeren</button>
      </div>
    </div>
  </div>
</div>
