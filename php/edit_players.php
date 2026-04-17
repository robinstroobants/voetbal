<?php
// Connect to the database
require_once 'getconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["player_id"])) {
    $id = intval($_POST["player_id"]);
    $first_name = $conn->real_escape_string($_POST["first_name"]);
    $last_name = $conn->real_escape_string($_POST["last_name"]);
    $birthdate = trim($_POST["birthdate"]);
    $shortname = $conn->real_escape_string($_POST["shortname"]);

    $birthdate_val = ($birthdate === '') ? null : $birthdate;

    $stmt = $conn->prepare("UPDATE players SET first_name=?, last_name=?, shortname=?, birthdate=? WHERE id=?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $shortname, $birthdate_val, $id);
    $stmt->execute();
    $stmt->close();
}


// Fetch players
$result = $conn->query("SELECT * FROM players ORDER BY first_name, last_name");
?>

<?php 
$page_title = 'Edit Players';
require_once 'header.php';
?>
<div class="container mt-5">
    <h2 class="mb-4">Edit Player Information</h2>
    <?php while ($row = $result->fetch_assoc()): ?>
    <form method="post" class="card p-3 mb-3">
        <input type="hidden" name="player_id" value="<?php echo $row['id']; ?>">
        <div class="row g-3 align-items-center">
          <div class="col-md-3">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" class="form-control" value="<?php echo !empty($row['first_name']) ? htmlspecialchars($row['first_name']) : ''; ?>">
          </div>
          <div class="col-md-3">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" class="form-control" value="<?php echo !empty($row['last_name']) ? htmlspecialchars($row['last_name']) : ''; ?>">
          </div>
          <div class="col-md-2">
          <label class="form-label">Shortname</label>
          <input type="text" name="shortname" class="form-control" value="<?php echo !empty($row['shortname']) ? htmlspecialchars($row['shortname']) : ''; ?>">
          </div>
          <div class="col-md-3">
              <label class="form-label">Birthdate</label>
              <input type="text" name="birthdate" class="form-control datepicker" value="<?php echo !empty($row['birthdate']) ? htmlspecialchars($row['birthdate']) : ''; ?>">
          </div>
        
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>
    <?php endwhile; ?>
</div>

<?php require_once 'footer.php'; ?>
