<?php
$page_title = 'Team Instellingen';
require_once 'getconn.php';

$team_id = (int)$_SESSION['team_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name = trim($_POST['team_name'] ?? '');
    $default_format = trim($_POST['default_format'] ?? '8v8');

    if ($team_name) {
        $stmt = $pdo->prepare("UPDATE teams SET name = ?, default_format = ? WHERE id = ?");
        if ($stmt->execute([$team_name, $default_format, $team_id])) {
            $_SESSION['team_name'] = $team_name;
            $success = "De instellingen zijn succesvol opgeslagen.";
        } else {
            $error = "Er liep iets mis bij het opslaan.";
        }
    } else {
        $error = "Ploegnaam mag niet leeg zijn.";
    }
}

// Ophalen van bestaande team_data
$stmt = $pdo->prepare("SELECT name, default_format FROM teams WHERE id = ?");
$stmt->execute([$team_id]);
$team = $stmt->fetch();

require_once 'header.php';
?>

<div class="container mt-4 mb-5">
    <h2><i class="fa-solid fa-users-gear me-2 text-primary"></i> Team Instellingen</h2>
    <p class="text-muted">Beheer de basis instellingen van jouw team.</p>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Ploegnaam</label>
                    <input type="text" name="team_name" class="form-control" value="<?= htmlspecialchars($team['name'] ?? '') ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Standaard Wedstrijd Formaat</label>
                    <select name="default_format" class="form-select border-secondary">
                        <option value="11v11" <?= ($team['default_format'] == '11v11') ? 'selected' : '' ?>>11v11</option>
                        <option value="8v8_4x15" <?= ($team['default_format'] == '8v8_4x15') ? 'selected' : '' ?>>8v8 (4x15)</option>
                        <option value="8v8_3x20" <?= ($team['default_format'] == '8v8_3x20') ? 'selected' : '' ?>>8v8 (3x20)</option>
                        <option value="8v8_4x20" <?= ($team['default_format'] == '8v8_4x20') ? 'selected' : '' ?>>8v8 (4x20)</option>
                        <option value="8v8_5x15" <?= ($team['default_format'] == '8v8_5x15') ? 'selected' : '' ?>>8v8 (5x15)</option>
                        <option value="8v8_6x15" <?= ($team['default_format'] == '8v8_6x15') ? 'selected' : '' ?>>8v8 (6x15)</option>
                        <option value="8v8_7x15" <?= ($team['default_format'] == '8v8_7x15') ? 'selected' : '' ?>>8v8 (7x15)</option>
                        <option value="5v5_4x15" <?= ($team['default_format'] == '5v5_4x15') ? 'selected' : '' ?>>5v5 (4x15)</option>
                        <option value="3v3_6x10" <?= ($team['default_format'] == '3v3_6x10') ? 'selected' : '' ?>>3v3 (6x10)</option>
                        <option value="2v2_6x10" <?= ($team['default_format'] == '2v2_6x10') ? 'selected' : '' ?>>2v2 (6x10)</option>
                    </select>
                    <div class="form-text">De basis setting voor jouw ploeg. Deze logica kan in de verdere applicatie gebruikt/overschreven worden.</div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i> Wijzigingen Opslaan</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
