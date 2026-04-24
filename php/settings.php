<?php
$page_title = 'Team Instellingen';
require_once __DIR__ . '/core/getconn.php';

$team_id = (int)$_SESSION['team_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save_settings';
    
    if ($action === 'save_settings') {
        $team_name = trim($_POST['team_name'] ?? '');
        $default_format = trim($_POST['default_format'] ?? '8v8');
        $default_game_parts = trim($_POST['default_game_parts'] ?? '4x15');
        $meeting_time_offset = (int)($_POST['meeting_time_offset'] ?? 45);

        if ($team_name) {
            $stmt = $pdo->prepare("UPDATE teams SET name = ?, default_format = ?, default_game_parts = ?, meeting_time_offset = ? WHERE id = ?");
            if ($stmt->execute([$team_name, $default_format, $default_game_parts, $meeting_time_offset, $team_id])) {
                $_SESSION['team_name'] = $team_name;
                $_SESSION['default_format'] = $default_format;
                $_SESSION['default_game_parts'] = $default_game_parts;
                $_SESSION['meeting_time_offset'] = $meeting_time_offset;
                $success = "De instellingen zijn succesvol opgeslagen.";
            } else {
                $error = "Er liep iets mis bij het opslaan.";
            }
        } else {
            $error = "Ploegnaam mag niet leeg zijn.";
        }
    } elseif ($action === 'invite_coach') {
        $invite_email = filter_var($_POST['invite_email'] ?? '', FILTER_SANITIZE_EMAIL);
        if ($invite_email) {
            $stmtC = $pdo->prepare("SELECT COUNT(*) FROM user_teams WHERE team_id = ?");
            $stmtC->execute([$team_id]);
            $c_coaches = (int)$stmtC->fetchColumn();

            $stmtI = $pdo->prepare("SELECT COUNT(*) FROM team_invitations WHERE team_id = ? AND expires_at > NOW()");
            $stmtI->execute([$team_id]);
            $p_invites = (int)$stmtI->fetchColumn();

            if (($c_coaches + $p_invites) >= 3) {
                $error = "De limiet van 3 coaches per team is bereikt.";
            } else {
                $stmtCheck = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ?");
                $stmtCheck->execute([$invite_email]);
                $existing_user = $stmtCheck->fetch();

                if ($existing_user) {
                    $stmtLinkCheck = $pdo->prepare("SELECT 1 FROM user_teams WHERE user_id = ? AND team_id = ?");
                    $stmtLinkCheck->execute([$existing_user['id'], $team_id]);
                    if ($stmtLinkCheck->fetchColumn()) {
                        $error = "Deze gebruiker heeft al toegang tot dit team.";
                    } else {
                        $pdo->prepare("INSERT IGNORE INTO user_teams (user_id, team_id) VALUES (?, ?)")->execute([$existing_user['id'], $team_id]);
                        
                        $teamName = $_SESSION['team_name'];
                        $subject = "Je bent toegevoegd aan team " . $teamName;
                        $message = "Beste " . ($existing_user['first_name'] ?: 'Coach') . ",\n\nJe bent zojuist toegevoegd als co-coach voor het team: $teamName.\n\nLog in op Lineup om je nieuwe Workspace te bekijken.\n\nMet vriendelijke groeten,\nHet Lineup Team";
                        
                        require_once __DIR__ . '/Mailer.php';
                        Mailer::send($invite_email, $subject, $message);
                        
                        $success = "Gebruiker succesvol gekoppeld! Er is een notificatie gemaild.";
                    }
                } else {
                    $stmtInvCheck = $pdo->prepare("SELECT id FROM team_invitations WHERE email = ? AND team_id = ? AND expires_at > NOW()");
                    $stmtInvCheck->execute([$invite_email, $team_id]);
                    if ($stmtInvCheck->fetchColumn()) {
                        $error = "Er staat al een uitnodiging open voor dit e-mailadres.";
                    } else {
                        $token = bin2hex(random_bytes(32));
                        $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
                        $pdo->prepare("INSERT INTO team_invitations (team_id, email, token, expires_at) VALUES (?, ?, ?, ?)")
                            ->execute([$team_id, $invite_email, $token, $expires_at]);
                        
                        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                        $host = $_SERVER['HTTP_HOST'];
                        $invite_link = "$protocol://$host/register.php?invite_token=$token";
                        $teamName = $_SESSION['team_name'];
                        
                        $subject = "Uitnodiging om coach te worden van " . $teamName;
                        $message = "Hallo,\n\nJe bent uitgenodigd om co-coach te worden van het team: $teamName.\n\nKlik op de onderstaande link om gratis een account aan te maken en direct aan de slag te gaan:\n$invite_link\n\nDeze link is 7 dagen geldig.\n\nMet vriendelijke groeten,\nHet Lineup Team";
                        
                        require_once __DIR__ . '/Mailer.php';
                        Mailer::send($invite_email, $subject, $message);

                        $success = "Uitnodiging succesvol verstuurd naar $invite_email!";
                    }
                }
            }
        }
    } elseif ($action === 'cancel_invite') {
        $inv_id = (int)$_POST['invite_id'];
        $pdo->prepare("DELETE FROM team_invitations WHERE id = ? AND team_id = ?")->execute([$inv_id, $team_id]);
        $success = "Uitnodiging geannuleerd.";
    } elseif ($action === 'remove_coach') {
        $rem_user_id = (int)$_POST['user_id'];
        
        $stmtOwner = $pdo->prepare("SELECT user_id FROM teams WHERE id = ?");
        $stmtOwner->execute([$team_id]);
        $owner_id = (int)$stmtOwner->fetchColumn();

        if ($rem_user_id === $owner_id) {
            $error = "De eigenaar van het team kan niet verwijderd worden.";
        } elseif ($rem_user_id !== (int)$_SESSION['user_id']) { 
            $pdo->prepare("DELETE FROM user_teams WHERE user_id = ? AND team_id = ?")->execute([$rem_user_id, $team_id]);
            $success = "Coach verwijderd uit het team.";
        } else {
            $error = "Je kan je eigen toegang hier niet intrekken.";
        }
    }
}

// Ophalen van bestaande team_data
$stmt = $pdo->prepare("SELECT name, default_format, default_game_parts, meeting_time_offset, user_id as owner_id FROM teams WHERE id = ?");
$stmt->execute([$team_id]);
$team = $stmt->fetch();

// Ophalen van beschikbare formats
$stmtFormats = $pdo->query("SELECT DISTINCT game_format FROM lineups");
$available_parts_by_format = [];
while ($row = $stmtFormats->fetchColumn()) {
    if (preg_match('/^(\d+v\d+)_(\d+gk_)?(\d+x\d+)$/', $row, $matches)) {
        $f = $matches[1];
        $p = $matches[3];
        if (!isset($available_parts_by_format[$f])) {
            $available_parts_by_format[$f] = [];
        }
        if (!in_array($p, $available_parts_by_format[$f])) {
            $available_parts_by_format[$f][] = $p;
        }
    }
}
$json_available_parts = json_encode($available_parts_by_format);

// Ophalen van co-coaches
$stmtCoaches = $pdo->prepare("SELECT u.id, u.email, u.first_name, u.last_name FROM user_teams ut JOIN users u ON ut.user_id = u.id WHERE ut.team_id = ?");
$stmtCoaches->execute([$team_id]);
$active_coaches = $stmtCoaches->fetchAll(PDO::FETCH_ASSOC);

// Ophalen van pending invites
$stmtInvites = $pdo->prepare("SELECT id, email, expires_at FROM team_invitations WHERE team_id = ? AND expires_at > NOW()");
$stmtInvites->execute([$team_id]);
$pending_invites = $stmtInvites->fetchAll(PDO::FETCH_ASSOC);

$total_slots = 3;
$used_slots = count($active_coaches) + count($pending_invites);
$available_slots = max(0, $total_slots - $used_slots);

require_once __DIR__ . '/header.php';
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
                <input type="hidden" name="action" value="save_settings">
                <div class="row">
                    <div class="col-md-3 mb-4">
                         <label class="form-label fw-bold">Ploegnaam</label>
                         <input type="text" name="team_name" class="form-control" value="<?= htmlspecialchars($team['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold">Format</label>
                        <select name="default_format" id="default_format" class="form-select border-secondary">
                            <option value="11v11" <?= ($team['default_format'] == '11v11') ? 'selected' : '' ?>>11v11</option>
                            <option value="8v8" <?= ($team['default_format'] == '8v8') ? 'selected' : '' ?>>8v8</option>
                            <option value="5v5" <?= ($team['default_format'] == '5v5') ? 'selected' : '' ?>>5v5</option>
                            <option value="3v3" <?= ($team['default_format'] == '3v3') ? 'selected' : '' ?>>3v3</option>
                            <option value="2v2" <?= ($team['default_format'] == '2v2') ? 'selected' : '' ?>>2v2</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-3 mt-md-0">
                        <label class="form-label fw-bold">Wedstrijdduur</label>
                        <select name="default_game_parts" id="default_game_parts" class="form-select border-secondary">
                            <!-- Opties worden ingevuld via JS -->
                        </select>
                    </div>
                    <div class="col-md-3 mt-3 mt-md-0">
                        <label class="form-label fw-bold">Samenkomsttijd</label>
                        <div class="input-group">
                            <input type="number" name="meeting_time_offset" class="form-control" value="<?= htmlspecialchars($team['meeting_time_offset'] ?? 45) ?>" min="0" required>
                            <span class="input-group-text">min voor de match</span>
                        </div>
                    </div>



                </div>

               
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const availableParts = <?= $json_available_parts ?>;
                    const formatSelect = document.getElementById('default_format');
                    const partsSelect = document.getElementById('default_game_parts');
                    const currentParts = '<?= $team['default_game_parts'] ?>';

                    function updateParts() {
                        const selectedFormat = formatSelect.value;
                        partsSelect.innerHTML = '';
                        
                        let parts = availableParts[selectedFormat] || [];
                        if (parts.length === 0) {
                            // Fallbacks if no schema exists yet
                            parts = ['4x15', '3x20', '2x45'];
                        }

                        parts.forEach(part => {
                            const option = document.createElement('option');
                            option.value = part;
                            option.textContent = part;
                            if (part === currentParts) {
                                option.selected = true;
                            }
                            partsSelect.appendChild(option);
                        });
                    }

                    formatSelect.addEventListener('change', updateParts);
                    updateParts(); // Initial call
                });
                </script>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i> Opslaan</button>

                
            </form>
        </div>
    </div>

    <!-- PERIODES SECTION -->
    <h3 class="mt-5"><i class="fa-solid fa-calendar-week me-2 text-primary"></i> Seizoensperiodes</h3>
    <p class="text-muted">Splits je statistieken en speelminuten op in gedetailleerde periodes (bv. Voorbereiding, Najaar, 6 weken, ...).</p>
    
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <strong>Beheer Periodes</strong><br>
                <small class="text-muted">Stel start- en einddatums in voor periodes binnen het actieve seizoen.</small>
            </div>
            <a href="/settings/periods" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-right me-2"></i> Beheren</a>
        </div>
    </div>

    <!-- UITNODIGINGEN SECTION -->
    <h3 class="mt-5"><i class="fa-solid fa-user-plus me-2 text-primary"></i> Team Coaches</h3>
    <p class="text-muted">Nodig tot 3 extra stafleden uit om dit team (Workspace) samen te beheren.</p>
    
    <div class="card shadow-sm border-0 mt-3">
        <ul class="list-group list-group-flush">
            <!-- Actieve Coaches -->
            <?php foreach($active_coaches as $c): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div>
                    <strong class="d-block text-dark"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?> <span class="badge bg-success ms-1" style="font-size:0.6rem;">Actief</span></strong>
                    <span class="text-muted small"><?= htmlspecialchars($c['email']) ?></span>
                </div>
                <?php if($c['id'] == $team['owner_id']): ?>
                    <span class="badge bg-primary text-white border"><i class="fa-solid fa-crown me-1"></i>Eigenaar</span>
                <?php elseif($c['id'] == $_SESSION['user_id']): ?>
                    <span class="badge bg-light text-muted border">Jezelf</span>
                <?php else: ?>
                <form method="POST" onsubmit="return confirm('Weet je zeker dat je deze coach uit het team wil verwijderen?');" class="m-0">
                    <input type="hidden" name="action" value="remove_coach">
                    <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Ontkoppel</button>
                </form>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
            
            <!-- Pending Invites -->
            <?php foreach($pending_invites as $inv): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                <div>
                    <strong class="d-block text-secondary">Uitgenodigd <span class="badge bg-warning text-dark ms-1" style="font-size:0.6rem;">Pending</span></strong>
                    <span class="text-muted small"><?= htmlspecialchars($inv['email']) ?> (Vervalt: <?= date('d/m', strtotime($inv['expires_at'])) ?>)</span>
                </div>
                <form method="POST" class="m-0">
                    <input type="hidden" name="action" value="cancel_invite">
                    <input type="hidden" name="invite_id" value="<?= $inv['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i> Annuleer</button>
                </form>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="card-footer bg-white border-top-0 pt-0 pb-4 px-4">
            <hr class="mt-0 mb-4">
            <?php if($available_slots > 0): ?>
            <form method="POST" class="row g-2 align-items-end">
                <input type="hidden" name="action" value="invite_coach">
                <div class="col-md-8">
                    <label class="form-label fw-bold small text-muted">NIEUWE MEDE-COACH UITNODIGEN</label>
                    <input type="email" name="invite_email" class="form-control" placeholder="E-mailadres" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-regular fa-paper-plane me-2"></i> Verstuur Invite</button>
                </div>
                <div class="col-12 mt-2 form-text text-success">Je hebt nog <?php echo $available_slots; ?> vrije coach slot(s).</div>
            </form>
            <?php else: ?>
            <div class="alert alert-warning mb-0 border-0 shadow-sm d-flex align-items-center">
                <i class="fa-solid fa-lock me-3 fs-3"></i>
                <div>
                    <strong>Teamlimiet bereikt.</strong><br>
                    Je hebt de maximale capaciteit van 3 coaches voor dit team bereikt. Verwijder of annuleer een coach om een nieuwe uit te nodigen.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
