<?php
// Connect to the database
require_once 'getconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["player_id"])) {
    $id = intval($_POST["player_id"]);
    $first_name = $conn->real_escape_string($_POST["first_name"]);
    $last_name = $conn->real_escape_string($_POST["last_name"]);
    $birthdate = trim($_POST["birthdate"]);
    $shortname = $conn->real_escape_string($_POST["shortname"]);
    $fav_pos = $conn->real_escape_string($_POST["favorite_positions"]);
    $is_doelman = isset($_POST["is_doelman"]) ? 1 : 0;

    $birthdate_val = ($birthdate === '') ? null : $birthdate;
    $fav_pos_val = ($fav_pos === '') ? null : $fav_pos;

    $stmt = $conn->prepare("UPDATE players SET first_name=?, last_name=?, shortname=?, birthdate=?, favorite_positions=?, is_doelman=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("sssssii", $first_name, $last_name, $shortname, $birthdate_val, $fav_pos_val, $is_doelman, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch players
$result = $conn->query("SELECT * FROM players ORDER BY first_name, last_name");
?>

<?php 
$page_title = 'Edit Players';
require_once 'header.php';
?>
<div class="container mt-5 mb-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h2>Spelers Bewerken</h2>
        <span class="text-muted"><i class="fa-solid fa-circle-info me-1"></i> Favoriete posities (kommagescheiden in volgorde van voorkeur, bv: 9,10,7)</span>
    </div>
    
    <?php while ($row = $result->fetch_assoc()): ?>
    <form method="post" class="card p-3 mb-2 shadow-sm border-0">
        <input type="hidden" name="player_id" value="<?php echo $row['id']; ?>">
        <div class="row g-2 align-items-center">
          <div class="col-md-2">
              <label class="form-label text-muted small mb-1">Voornaam</label>
              <input type="text" name="first_name" class="form-control form-control-sm" value="<?php echo !empty($row['first_name']) ? htmlspecialchars($row['first_name']) : ''; ?>">
          </div>
          <div class="col-md-2">
              <label class="form-label text-muted small mb-1">Achternaam</label>
              <input type="text" name="last_name" class="form-control form-control-sm" value="<?php echo !empty($row['last_name']) ? htmlspecialchars($row['last_name']) : ''; ?>">
          </div>
          <div class="col-md-2">
              <label class="form-label text-muted small mb-1">Shortname</label>
              <input type="text" name="shortname" class="form-control form-control-sm" value="<?php echo !empty($row['shortname']) ? htmlspecialchars($row['shortname']) : ''; ?>">
          </div>
          <div class="col-md-2">
              <label class="form-label text-muted small mb-1">Geboortedatum</label>
              <input type="text" name="birthdate" class="form-control form-control-sm datepicker" value="<?php echo !empty($row['birthdate']) ? htmlspecialchars($row['birthdate']) : ''; ?>">
          </div>
          <div class="col-md-1">
              <label class="form-label text-muted small mb-1">Doelman</label>
              <div class="form-check form-switch pt-1 ms-1">
                  <input class="form-check-input" type="checkbox" name="is_doelman" value="1" <?php echo (!empty($row['is_doelman'])) ? 'checked' : ''; ?>>
              </div>
          </div>
          <div class="col-md-2">
              <label class="form-label text-muted small mb-1 text-primary"><i class="fa-solid fa-star me-1"></i>Fav. Posities (top -> flop)</label>
              <input type="text" name="favorite_positions" class="form-control form-control-sm border-primary" placeholder="Bv. 8,10,4" value="<?php echo !empty($row['favorite_positions']) ? htmlspecialchars($row['favorite_positions']) : ''; ?>">
          </div>
        
          <div class="col-md-1 d-flex align-items-end mt-4">
              <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-check"></i></button>
          </div>
        </div>
    </form>
    <?php endwhile; ?>
</div>

<?php require_once 'footer.php'; ?>
