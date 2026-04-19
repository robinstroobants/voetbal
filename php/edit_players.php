<?php
// Connect to the database
require_once 'getconn.php';

// Ajax Save
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    header('Content-Type: application/json');
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data["player_id"])) {
        $id = intval($data["player_id"]);
        $first_name = $conn->real_escape_string($data["first_name"] ?? '');
        $last_name = $conn->real_escape_string($data["last_name"] ?? '');
        $birthdate = trim($data["birthdate"] ?? '');
        $fav_pos = $conn->real_escape_string($data["favorite_positions"] ?? '');
        $is_doelman = !empty($data["is_doelman"]) ? 1 : 0;

        $birthdate_val = ($birthdate === '') ? null : $birthdate;
        $fav_pos_val = ($fav_pos === '') ? null : $fav_pos;

        // updated_at is automatically refreshed via ON UPDATE CURRENT_TIMESTAMP
        $stmt = $conn->prepare("UPDATE players SET first_name=?, last_name=?, birthdate=?, favorite_positions=?, is_doelman=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("ssssii", $first_name, $last_name, $birthdate_val, $fav_pos_val, $is_doelman, $id);
            $stmt->execute();
            $stmt->close();
        }
        
        $res = $conn->query("SELECT updated_at, UNIX_TIMESTAMP(updated_at) as ts FROM players WHERE id=$id");
        $row = $res->fetch_assoc();
        
        $ts_val = isset($row['ts']) ? (int)$row['ts'] : 0;
        $date_str = !empty($row['updated_at']) ? date("Y-m-d H:i", strtotime($row['updated_at'])) : date("Y-m-d H:i");
        
        echo json_encode(['success' => true, 'updated_at' => $date_str, 'ts' => $ts_val]);
        exit;
    }
}

// Fetch players
$result = $conn->query("SELECT * FROM players ORDER BY first_name, last_name");
?>

<?php 
$page_title = 'Edit Players';
require_once 'header.php';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container mt-5 mb-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h2>Spelers Bewerken</h2>
        <span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Let op: Je kunt maar één speler tegelijk updaten (klik Opslaan per rij).</span>
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
                <?php while ($row = $result->fetch_assoc()): 
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
                        <input type="date" name="birthdate" class="form-control form-control-sm edit-mode d-none" value="<?= !empty($row['birthdate']) ? htmlspecialchars($row['birthdate']) : ''; ?>">
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
                    <td class="text-end" style="min-width: 90px; cursor: pointer;">
                        <span class="view-mode text-muted small fst-italic"><i class="fa-solid fa-pen"></i></span>
                        <button type="button" class="btn btn-primary btn-sm save-btn d-none" data-id="<?= $row['id'] ?>"><i class="fa-solid fa-check"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>

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
        // Als we niet op de save-knop zelf klikken, activeer focus
        if (!e.target.closest('.save-btn')) {
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
            }
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
