<?php
require_once("game.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['player_id'])) {
    $player_id = intval($_POST['player_id']);
    foreach($_POST as $k=>$v){
      if ($k != "player_id"){
        $position = intval(str_replace('pos_', '', $k));
        $score = floatval($v);
        // Check of er al een score is in de laatste 5 dagen
        $check_sql = "SELECT id, score FROM player_scores 
                      WHERE player_id = ? AND position = ? 
                      AND score_date >= DATE_SUB(NOW(), INTERVAL 5 DAY)
                      ORDER BY score_date DESC LIMIT 1";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $player_id, $position);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();

            // Alleen updaten als de score verschilt
            if (floatval($row['score']) !== $score) {
                $update_sql = "UPDATE player_scores SET score = ?, score_date = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("di", $score, $row['id']);
                $update_stmt->execute();
            }
        } else {
            // Voeg nieuwe score toe
            $insert_sql = "INSERT INTO player_scores (player_id, position, score, score_date)
                           VALUES (?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iid", $player_id, $position, $score);
            $insert_stmt->execute();
        }
        
      }
    }
}


// Scores ophalen per speler en positie (laatste score)
$result = $conn->query("SELECT * FROM players ORDER BY first_name, last_name");
$players = [];
$player_ids = [];
while ($row = $result->fetch_assoc()) {
    $players[$row['id']]["id"] = $row['id'];
    $players[$row['id']]["first_name"] = $row['first_name'];
    $players[$row['id']]["last_name"] = $row['last_name'];
    $player_ids[] = $row['id'];
}
$scores = [];
if (!empty($player_ids)) {
    $ids_str = implode(',', $player_ids);
    $sql = "SELECT player_id, position, score
            FROM player_scores
            WHERE player_id IN ($ids_str)
              AND (player_id, position, score_date) IN (
                  SELECT player_id, position, MAX(score_date)
                  FROM player_scores
                  WHERE player_id IN ($ids_str)
                  GROUP BY player_id, position
              )";
    $result_scores = $conn->query($sql);
    while ($row = $result_scores->fetch_assoc()) {
        $scores[$row['player_id']][$row['position']] = $row['score'];
    }
}
//pr($players);
//dpr($scores);
?>

<?php 
$page_title = 'Edit Player scores';
require_once 'header.php';
?>
<div class="container mt-5">
    <h2 class="mb-4">Edit Player Scores</h2>
    <?php foreach($players as $player_id => $player) { ?>
      <form method="post" class="card p-3 mb-3">
          <input type="hidden" name="player_id" value="<?php echo $player_id; ?>">
          <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label"><?php echo !empty($player['first_name']) ? htmlspecialchars($player['first_name']) : ''; ?> <?php echo !empty($player['first_name']) ? htmlspecialchars($player['last_name']) : ''; ?></label>              
            </div>
            <?php foreach($scores[$player_id] as $position => $score) {?>
            <div class="col-md-1">
                <label class="form-label"><?php echo $position; ?></label>
                <input type="text" name="pos_<?php echo $position; ?>" class="form-control" value="<?php echo $score; ?>">
            </div>
            <?php } ?>
              <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary">Save</button>
              </div>
          </div>
      </form>
    <?php } ?>
</div>

<?php require_once 'footer.php'; ?>
