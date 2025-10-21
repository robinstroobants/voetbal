<?php
// Connect to the database
$host = 'db';
$db = 'lineup_db';
$user = 'app_user';
$pass = 'bRng4y8TJLJwUxYHBD6q';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["player_id"])) {
    $id = intval($_POST["player_id"]);
    $first_name = $conn->real_escape_string($_POST["first_name"]);
    $last_name = $conn->real_escape_string($_POST["last_name"]);
    $birthdate = trim($_POST["birthdate"]);
    $shortname = $conn->real_escape_string($_POST["shortname"]);

    // Controleer of birthdate leeg is
    if ($birthdate === '') {
        $birthdate_sql = "NULL";
    } else {
        $birthdate_sql = "'" . $conn->real_escape_string($birthdate) . "'";
    }

    $sql = "UPDATE players SET first_name='$first_name', last_name='$last_name', shortname='$shortname', birthdate=$birthdate_sql WHERE id=$id";
    $conn->query($sql);
}


// Fetch players
$result = $conn->query("SELECT * FROM players ORDER BY first_name, last_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Players</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
<script>
    $(function () {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    });
</script>
</body>
</html>
