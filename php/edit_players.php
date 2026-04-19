<?php
// Connect to the database
require_once 'getconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["player_id"])) {
    $id = intval($_POST["player_id"]);
    $first_name = $conn->real_escape_string($_POST["first_name"]);
    $last_name = $conn->real_escape_string($_POST["last_name"]);
    $birthdate = trim($_POST["birthdate"]);
    $fav_pos = $conn->real_escape_string($_POST["favorite_positions"]);
    $is_doelman = isset($_POST["is_doelman"]) ? 1 : 0;

    $birthdate_val = ($birthdate === '') ? null : $birthdate;
    $fav_pos_val = ($fav_pos === '') ? null : $fav_pos;

    // updated_at is automatically refreshed via ON UPDATE CURRENT_TIMESTAMP
    $stmt = $conn->prepare("UPDATE players SET first_name=?, last_name=?, birthdate=?, favorite_positions=?, is_doelman=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("ssssii", $first_name, $last_name, $birthdate_val, $fav_pos_val, $is_doelman, $id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Redirect to prevent form-resubmission popups on refresh, returning exactly to the page
    header("Location: edit_players.php");
    exit;
}

// Fetch players
$result = $conn->query("SELECT *, UNIX_TIMESTAMP(updated_at) as updated_ts FROM players ORDER BY first_name, last_name");
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
                        <input type="text" form="<?= $f_id ?>" name="first_name" class="form-control form-control-sm" value="<?= !empty($row['first_name']) ? htmlspecialchars($row['first_name']) : ''; ?>">
                    </td>
                    <td>
                        <input type="text" form="<?= $f_id ?>" name="last_name" class="form-control form-control-sm" value="<?= !empty($row['last_name']) ? htmlspecialchars($row['last_name']) : ''; ?>">
                    </td>
                    <td>
                        <input type="text" form="<?= $f_id ?>" name="birthdate" class="form-control form-control-sm datepicker" value="<?= !empty($row['birthdate']) ? htmlspecialchars($row['birthdate']) : ''; ?>">
                    </td>
                    <td class="text-center">
                        <div class="form-check form-switch pt-1 d-inline-block">
                            <input class="form-check-input doelman-toggle" form="<?= $f_id ?>" type="checkbox" name="is_doelman" value="1" <?= (!empty($row['is_doelman'])) ? 'checked' : ''; ?>>
                        </div>
                    </td>
                    <td>
                        <div class="fav-pos-container">
                            <input type="text" form="<?= $f_id ?>" name="favorite_positions" class="form-control form-control-sm border-primary fav-pos-input" placeholder="Bv. 7,11,2" value="<?= !empty($row['favorite_positions']) ? htmlspecialchars($row['favorite_positions']) : ''; ?>">
                        </div>
                    </td>
                    <td data-sort="<?= (int)$row['updated_ts'] ?>">
                        <span class="small text-muted"><?= !empty($row['updated_at']) ? date("d/m H:i", strtotime($row['updated_at'])) : '-' ?></span>
                    </td>
                    <td class="text-end">
                        <form method="post" id="<?= $f_id ?>" style="display:inline;">
                            <input type="hidden" name="player_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-check me-1"></i>Opslaan</button>
                        </form>
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

    // Initialiseer DataTables met State Saving
    $('#playersTable').DataTable({
        stateSave: true, // Zorgt dat de geselecteerde sortering onthouden blijft over page-reloads heen
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/nl-NL.json'
        },
        columnDefs: [
            { orderable: false, targets: [4, 6] } // Disable sorting on Fav Pos and Action column
        ]
    });
});
</script>
