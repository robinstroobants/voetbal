<?php
require_once dirname(__DIR__) . '/core/getconn.php';

// Beveiliging loopt nu centraal via router.php

$page_title = 'SaaS Beheer Dashboard';
$success = '';
$error = '';

// Acties verwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_team') {
        $name = trim($_POST['team_name'] ?? '');
        $plan = $_POST['subscription_plan'] ?? 'trial';
        $valid_months = (int)($_POST['valid_months'] ?? 1);
        
        if ($name) {
            $valid_until = date('Y-m-d H:i:s', strtotime("+$valid_months months"));
            $stmt = $pdo->prepare("INSERT INTO teams (name, subscription_plan, subscription_valid_until, is_active) VALUES (?, ?, ?, 1)");
            $stmt->execute([$name, $plan, $valid_until]);
            $success = "✅ Team '$name' is succesvol aangemaakt!";
        }
    } elseif ($action === 'create_user') {
        $team_id = (int)$_POST['team_id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        if ($team_id && $first_name && $email && $password) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (team_id, email, first_name, last_name, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$team_id, $email, $first_name, $last_name, $hash, $role]);
            $success = "✅ Gebruiker $first_name is toegevoegd!";
        }
    } elseif ($action === 'extend_sub') {
        $team_id = (int)$_POST['team_id'];
        $extra_months = (int)($_POST['extra_months'] ?? 1);
        
        $stmt = $pdo->prepare("SELECT subscription_valid_until FROM teams WHERE id = ?");
        $stmt->execute([$team_id]);
        $current_valid = $stmt->fetchColumn();
        
        if ($current_valid) {
            // Als datum in het verleden is, verleng vanaf VANDAAG. Anders vanaf HUIDIGE datum.
            $base_time = strtotime($current_valid);
            if ($base_time < time()) $base_time = time();
            
            $new_date = date('Y-m-d H:i:s', strtotime("+$extra_months months", $base_time));
            $pdo->prepare("UPDATE teams SET subscription_valid_until = ? WHERE id = ?")->execute([$new_date, $team_id]);
            $success = "✅ Abonnement verlengd tot " . date('d-m-Y', strtotime($new_date));
        }
    } elseif ($action === 'toggle_beta') {
        $user_id = (int)$_POST['user_id'];
        $current_beta = (int)($_POST['current_beta'] ?? 0);
        $new_beta = $current_beta ? 0 : 1;
        $pdo->prepare("UPDATE users SET is_beta_user = ? WHERE id = ?")->execute([$new_beta, $user_id]);
        $success = "✅ BETA status bijgewerkt voor gebruiker!";
    } elseif ($action === 'delete_team') {
        $team_id = (int)$_POST['team_id'];
        
        $stmtU = $pdo->prepare("SELECT user_id FROM user_teams WHERE team_id = ?");
        $stmtU->execute([$team_id]);
        $affected_users = $stmtU->fetchAll(PDO::FETCH_COLUMN);

        $games = $pdo->prepare("SELECT id FROM games WHERE team_id = ?");
        $games->execute([$team_id]);
        $gameIds = $games->fetchAll(PDO::FETCH_COLUMN);
        if ($gameIds) {
            $inQ = implode(',', array_fill(0, count($gameIds), '?'));
            $pdo->prepare("DELETE FROM game_lineups WHERE game_id IN ($inQ)")->execute($gameIds);
            $pdo->prepare("DELETE FROM game_selections WHERE game_id IN ($inQ)")->execute($gameIds);
        }
        $pdo->prepare("DELETE FROM games WHERE team_id = ?")->execute([$team_id]);

        $players = $pdo->prepare("SELECT id FROM players WHERE team_id = ?");
        $players->execute([$team_id]);
        $playerIds = $players->fetchAll(PDO::FETCH_COLUMN);
        if ($playerIds) {
            $inQ = implode(',', array_fill(0, count($playerIds), '?'));
            $pdo->prepare("DELETE FROM player_scores WHERE player_id IN ($inQ)")->execute($playerIds);
            $pdo->prepare("DELETE FROM gk_scores WHERE player_id IN ($inQ)")->execute($playerIds);
        }
        $pdo->prepare("DELETE FROM players WHERE team_id = ?")->execute([$team_id]);

        $pdo->prepare("DELETE FROM coaches WHERE team_id = ?")->execute([$team_id]);
        $pdo->prepare("DELETE FROM team_invitations WHERE team_id = ?")->execute([$team_id]);
        $pdo->prepare("DELETE FROM user_teams WHERE team_id = ?")->execute([$team_id]);
        $pdo->prepare("DELETE FROM teams WHERE id = ?")->execute([$team_id]);

        foreach($affected_users as $uid) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM user_teams WHERE user_id = ?");
            $check->execute([$uid]);
            if ($check->fetchColumn() == 0) {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
            }
        }
        $success = "✅ Tenant omgeving volledig geliquideerd.";
    } elseif ($action === 'edit_user') {
        $uid = (int)$_POST['user_id'];
        $fname = trim($_POST['first_name']);
        $lname = trim($_POST['last_name']);
        $eml = trim($_POST['email']);
        $r = $_POST['role'];
        $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?")->execute([$fname, $lname, $eml, $r, $uid]);
        $success = "✅ Gebruiker succesvol bijgewerkt!";
    } elseif ($action === 'delete_user') {
        $uid = (int)$_POST['user_id'];
        $tid = (int)$_POST['team_id'];
        
        $pdo->prepare("DELETE FROM user_teams WHERE user_id = ? AND team_id = ?")->execute([$uid, $tid]);
        
        $check = $pdo->prepare("SELECT COUNT(*) FROM user_teams WHERE user_id = ?");
        $check->execute([$uid]);
        if ($check->fetchColumn() == 0) {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
        }
        $success = "✅ Gebruiker ontkoppeld (en definitief gewist indien geen andere workspaces).";
    } elseif ($action === 'invite_coach') {
        $tid = (int)$_POST['team_id'];
        $invite_email = filter_var($_POST['invite_email'] ?? '', FILTER_SANITIZE_EMAIL);
        if ($invite_email && $tid) {
            $stmtC = $pdo->prepare("SELECT COUNT(*) FROM user_teams WHERE team_id = ?");
            $stmtC->execute([$tid]);
            $c_coaches = (int)$stmtC->fetchColumn();

            $stmtI = $pdo->prepare("SELECT COUNT(*) FROM team_invitations WHERE team_id = ? AND expires_at > NOW()");
            $stmtI->execute([$tid]);
            $p_invites = (int)$stmtI->fetchColumn();

            if (($c_coaches + $p_invites) >= 3) {
                $error = "De limiet van 3 coaches per team is bereikt voor deze workspace.";
            } else {
                $stmtCheck = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ?");
                $stmtCheck->execute([$invite_email]);
                $existing_user = $stmtCheck->fetch();

                if ($existing_user) {
                    $stmtLinkCheck = $pdo->prepare("SELECT 1 FROM user_teams WHERE user_id = ? AND team_id = ?");
                    $stmtLinkCheck->execute([$existing_user['id'], $tid]);
                    if ($stmtLinkCheck->fetchColumn()) {
                        $error = "Deze gebruiker heeft al toegang tot dit team.";
                    } else {
                        $pdo->prepare("INSERT IGNORE INTO user_teams (user_id, team_id) VALUES (?, ?)")->execute([$existing_user['id'], $tid]);
                        $success = "✅ Bestaande gebruiker succesvol gekoppeld aan de workspace.";
                    }
                } else {
                    $stmtInvCheck = $pdo->prepare("SELECT id FROM team_invitations WHERE email = ? AND team_id = ? AND expires_at > NOW()");
                    $stmtInvCheck->execute([$invite_email, $tid]);
                    if ($stmtInvCheck->fetchColumn()) {
                        $error = "Er staat al een open uitnodiging voor dit e-mailadres in deze workspace.";
                    } else {
                        $token = bin2hex(random_bytes(32));
                        $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
                        $pdo->prepare("INSERT INTO team_invitations (team_id, email, token, expires_at) VALUES (?, ?, ?, ?)")
                            ->execute([$tid, $invite_email, $token, $expires_at]);
                        
                        $stmtTN = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
                        $stmtTN->execute([$tid]);
                        $teamName = $stmtTN->fetchColumn();

                        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                        $host = $_SERVER['HTTP_HOST'];
                        $invite_link = "$protocol://$host/register.php?invite_token=$token";
                        
                        $subject = "SaaS Uitnodiging om coach te worden van " . $teamName;
                        $message = "Hallo,\n\nJe bent via de administrator uitgenodigd om co-coach te worden van het team: $teamName.\n\nKlik op de onderstaande link om gratis je account te activeren:\n$invite_link\n\nDeze link is 7 dagen geldig.";
                        
                        require_once dirname(__DIR__) . '/core/Mailer.php';
                        Mailer::send($invite_email, $subject, $message);

                        $success = "✅ Uitnodigingslink succesvol verstuurd naar $invite_email!";
                    }
                }
            }
        }
    } elseif ($action === 'cancel_invite') {
        $inv_id = (int)$_POST['invite_id'];
        if ($inv_id) {
            $pdo->prepare("DELETE FROM team_invitations WHERE id = ?")->execute([$inv_id]);
            $success = "✅ Uitnodiging succesvol ingetrokken!";
        }
    } elseif ($action === 'cleanup_dummies') {
        $stmtDummies = $pdo->prepare("SELECT id FROM games WHERE opponent LIKE '%DUMMY REVISOR MATCH%' AND created_at < NOW() - INTERVAL 1 HOUR");
        $stmtDummies->execute();
        $dummyIds = $stmtDummies->fetchAll(PDO::FETCH_COLUMN);

        // Fallback: Als er uitsluitend nieuwe dummy tests zijn en de gebruiker forceert de purge (of indien er geen timestamps inzaten oorspronkelijk).
        if (empty($dummyIds) && isset($_POST['force_all'])) {
            $stmtDummies = $pdo->prepare("SELECT id FROM games WHERE opponent LIKE '%DUMMY REVISOR MATCH%' OR is_theory = 1");
            $stmtDummies->execute();
            $dummyIds = $stmtDummies->fetchAll(PDO::FETCH_COLUMN);
        } elseif (isset($_POST['include_theories'])) {
            $stmtDummies = $pdo->prepare("SELECT id FROM games WHERE (opponent LIKE '%DUMMY REVISOR MATCH%' AND created_at < NOW() - INTERVAL 1 HOUR) OR is_theory = 1");
            $stmtDummies->execute();
            $dummyIds = $stmtDummies->fetchAll(PDO::FETCH_COLUMN);
        }

        if ($dummyIds) {
            $inQ = implode(',', array_fill(0, count($dummyIds), '?'));
            $pdo->prepare("DELETE FROM game_lineups WHERE game_id IN ($inQ)")->execute($dummyIds);
            $pdo->prepare("DELETE FROM game_selections WHERE game_id IN ($inQ)")->execute($dummyIds);
            $pdo->prepare("DELETE FROM games WHERE id IN ($inQ)")->execute($dummyIds);
            $success = "✅ " . count($dummyIds) . " dummy test sessies/theorieën succesvol opgeruimd!";
        } else {
            $success = "ℹ️ Geen dummy wedstrijden gevonden om op te ruimen.";
        }
    }
}

// Definieer Zoeken & Ajax parameters (Stap 1)
$searchTerm = $_GET['ajax_q'] ?? '';
$isAjax = isset($_GET['ajax_q']);

$queryStr = "SELECT t.*, 
            (SELECT SUM(cost_weight) FROM usage_logs WHERE team_id = t.id) as total_usage
             FROM teams t";
$params = [];

if ($searchTerm !== '') {
    $queryStr .= " WHERE t.name LIKE ? OR t.id IN (SELECT ut.team_id FROM user_teams ut JOIN users u ON ut.user_id = u.id WHERE u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
}

$queryStr .= " ORDER BY t.id DESC LIMIT 10";

$stmtTeams = $pdo->prepare($queryStr);
$stmtTeams->execute($params);
$teams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

// 2. Haal alle gebruikers op, gegroepeerd
$users = [];
$usersResult = $pdo->query("
    SELECT u.*, ut.team_id as workspace_id 
    FROM users u 
    LEFT JOIN user_teams ut ON u.id = ut.user_id 
    ORDER BY u.created_at DESC
")->fetchAll();

foreach ($usersResult as $u) {
    $tId = $u['workspace_id'] ?: $u['team_id'];
    if ($tId) {
        $users[$tId][$u['id']] = $u; 
    }
}

// Fetch all linked workspaces per user for UI Badges
$allUserTeams = [];
$utResult = $pdo->query("SELECT ut.user_id, t.name, t.id as team_id FROM user_teams ut JOIN teams t ON ut.team_id = t.id")->fetchAll();
foreach ($utResult as $ut) {
    if (!isset($allUserTeams[$ut['user_id']])) $allUserTeams[$ut['user_id']] = [];
    $allUserTeams[$ut['user_id']][] = $ut;
}

// Haal actieve open invites op
$invitesByTeam = [];
$stmtInv = $pdo->query("SELECT id, team_id, email, token, created_at, expires_at FROM team_invitations WHERE expires_at > NOW() AND status = 'pending'");
foreach($stmtInv as $r) {
    if (!isset($invitesByTeam[$r['team_id']])) {
        $invitesByTeam[$r['team_id']] = [];
    }
    $invitesByTeam[$r['team_id']][] = $r;
}

// Haal dummy metrics op voor dashboard
$stmtDummyC = $pdo->query("SELECT SUM(CASE WHEN opponent LIKE '%DUMMY REVISOR MATCH%' AND created_at < NOW() - INTERVAL 1 HOUR THEN 1 ELSE 0 END) as expired_count, SUM(CASE WHEN is_theory = 1 THEN 1 ELSE 0 END) as theory_count, COUNT(*) as total_count FROM games WHERE opponent LIKE '%DUMMY REVISOR MATCH%' OR is_theory = 1");
$dummyStats = $stmtDummyC->fetch(PDO::FETCH_ASSOC);

// Haal globale admin stats op
$admin_stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_users_7d,
        (SELECT COUNT(*) FROM team_invitations WHERE status = 'pending') as invites_pending,
        (SELECT COUNT(*) FROM team_invitations WHERE status = 'accepted') as invites_accepted,
        (SELECT COUNT(*) FROM users WHERE account_status = 'pending') as waitlist_pending
")->fetch(PDO::FETCH_ASSOC);

// Als het een Ajax verzoek is, onderbreek the HTML rest render en spuug enkel the partial uit
if ($isAjax) {
    include __DIR__ . '/_teams_list.php';
    exit;
}

require_once __DIR__ . '/../header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-dark">
        <h2><i class="fa-solid fa-server text-warning me-2"></i> SaaS Tenant & Abonnementen Beheer</h2>
    </div>

    <!-- Systeem Alerts / Revisor Dummies -->
    <?php if ($dummyStats && $dummyStats['total_count'] > 0): ?>
    <div class="alert bg-black bg-opacity-25 border border-warning border-opacity-50 text-white d-flex align-items-center justify-content-between rounded-3 mb-4">
        <div>
            <h6 class="fw-bold mb-1"><i class="fa-solid fa-spider text-warning me-2"></i> Tijdelijke Revisor Sessies & Theorieën Gevonden (<?= $dummyStats['total_count'] ?>)</h6>
            <div class="small text-white text-opacity-75">
                Deze onzichtbare (dummy) wedstrijden worden door het systeem tijdelijk in de database geplaatst zodra een coach de "schema editor" debugt, of wanneer een Standalone Theorie wordt opgeslagen.
                Er zijn momenteel <strong><?= (int)$dummyStats['expired_count'] ?></strong> revisor dummies ouder dan 1 uur en <strong><?= (int)$dummyStats['theory_count'] ?></strong> theorieën. 
            </div>
        </div>
        <form method="POST" class="ms-3 flex-shrink-0 d-flex align-items-center">
            <input type="hidden" name="action" value="cleanup_dummies">
            <div class="form-check form-switch me-3">
                <input class="form-check-input mt-0" type="checkbox" role="switch" id="includeTheories" name="include_theories" value="1">
                <label class="form-check-label small fw-bold text-nowrap text-white" for="includeTheories">Incl. Theorieën</label>
            </div>
            <button type="submit" name="force_all" value="1" class="btn btn-sm btn-warning fw-bold text-dark" onclick="return confirm('Dit opruimen wist definitief de geselecteerde verborgen tests/theorieën. OK?')">
                <i class="fa-solid fa-broom me-1"></i> Opruimen
            </button>
        </form>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <div class="card shadow-sm border-0 bg-primary text-white h-100 position-relative" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body d-flex align-items-center">
                    <i class="fa-solid fa-user-plus fa-2x opacity-50 me-3"></i>
                    <div>
                        <h6 class="mb-1 opacity-75 fw-normal">Nieuwe Gebruikers (7d)</h6>
                        <h3 class="mb-0 fw-bold"><?= $admin_stats['new_users_7d'] ?></h3>
                    </div>
                </div>
                <a href="#superadminSearch" class="stretched-link" onclick="setTimeout(() => document.getElementById('superadminSearch').focus(), 100);"></a>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card shadow-sm border-0 bg-warning text-dark h-100 position-relative" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body d-flex align-items-center">
                    <i class="fa-solid fa-hourglass-half fa-2x opacity-50 me-3"></i>
                    <div>
                        <h6 class="mb-1 opacity-75 fw-normal">Wachtlijst (Pending)</h6>
                        <h3 class="mb-0 fw-bold"><?= $admin_stats['waitlist_pending'] ?></h3>
                    </div>
                </div>
                <a href="/admin/users?q=pending" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fa-solid fa-envelope-circle-check fa-2x opacity-50 me-3"></i>
                    <div>
                        <h6 class="mb-1 opacity-75 fw-normal">Team Invitaties</h6>
                        <h4 class="mb-0 fw-bold">
                            <span title="Geaccepteerd"><?= $admin_stats['invites_accepted'] ?> <i class="fa-solid fa-check small opacity-75"></i></span> 
                            <span class="opacity-50 mx-2">|</span> 
                            <span title="Openstaand"><?= $admin_stats['invites_pending'] ?> <i class="fa-solid fa-clock small opacity-75"></i></span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success fw-bold shadow-sm"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger fw-bold shadow-sm"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4 border-top border-primary border-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Nieuw Team Aanmaken</h5>
                    <form method="POST">
                        <input type="hidden" name="action" value="create_team">
                        <div class="mb-3">
                            <label class="form-label">Team Naam</label>
                            <input type="text" name="team_name" class="form-control" required placeholder="Bv. U13 Barcelona">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Plan</label>
                                <select name="subscription_plan" class="form-select">
                                    <option value="trial">Trial</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Maanden Geldig</label>
                                <input type="number" name="valid_months" class="form-control" value="1" min="1">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Team Opslaan</button>
                    </form>
                </div>
            </div>

        </div>

        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <h4 class="mb-0"><i class="fa-solid fa-building me-2"></i>Huidige SaaS Tenants</h4>
                <div style="width: 300px; position:relative;">
                    <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left:12px; top:12px;"></i>
                    <input type="search" id="superadminSearch" class="form-control rounded-pill ps-5 bg-white shadow-sm border-0" placeholder="Zoek club of coach...">
                </div>
            </div>
            <div class="accordion shadow-sm" id="accordionTeams">
                <?php include __DIR__ . '/_teams_list.php'; ?>
            </div>
        </div>

    </div>
</div>

<!-- Invite User Modal -->
<div class="modal fade" id="inviteUserModal" tabindex="-1" aria-labelledby="inviteUserLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="invite_coach">
        <input type="hidden" name="team_id" id="invite_team_id" value="">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="inviteUserLabel"><i class="fa-solid fa-paper-plane me-2"></i>Gebruiker Uitnodigen</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">E-mailadres</label>
                <input type="email" name="invite_email" class="form-control" required placeholder="email@voorbeeld.com">
                <div class="form-text">Er wordt (indien onbekend) een veilige uitnodiging met link verzonden (7 dagen geldig).</div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuleren</button>
          <button type="submit" class="btn btn-success fw-bold">Stuur Uitnodiging</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="edit_user">
        <input type="hidden" name="user_id" id="edit_user_id" value="">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editUserLabel"><i class="fa-solid fa-pen me-2"></i>Gebruiker Bewerken</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Voornaam</label>
                <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Achternaam</label>
                <input type="text" name="last_name" id="edit_last_name" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">E-mailadres</label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Rol</label>
                <select name="role" id="edit_role" class="form-select">
                    <option value="coach">Coach</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuleren</button>
          <button type="submit" class="btn btn-warning fw-bold">Opslaan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let searchTimeout;
document.getElementById('superadminSearch').addEventListener('input', function(e) {
    const term = e.target.value.trim();
    clearTimeout(searchTimeout);
    
    // Add simple loading indicator classes if needed
    document.getElementById('accordionTeams').style.opacity = '0.5';

    searchTimeout = setTimeout(function() {
        fetch('superadmin_dashboard.php?ajax_q=' + encodeURIComponent(term))
            .then(res => res.text())
            .then(html => {
                document.getElementById('accordionTeams').innerHTML = html;
                document.getElementById('accordionTeams').style.opacity = '1';
            })
            .catch(err => {
                console.error(err);
                document.getElementById('accordionTeams').style.opacity = '1';
            });
    }, 300); // 300ms debounce
});

function openInviteModal(team_id) {
    document.getElementById('invite_team_id').value = team_id;
    var myModal = new bootstrap.Modal(document.getElementById('inviteUserModal'));
    myModal.show();
}

function openEditUserModal(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_first_name').value = user.first_name;
    document.getElementById('edit_last_name').value = user.last_name || '';
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    var myModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    myModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const accordionTeams = document.getElementById('accordionTeams');
    if (!accordionTeams) return;

    // Bewaar het geopende paneel in LocalStorage
    accordionTeams.addEventListener('shown.bs.collapse', function (e) {
        localStorage.setItem('superadmin_last_tenant', e.target.id);
    });

    // Herstel de staat tijdens page-load (tenzij we aan het zoeken zijn)
    function restoreState() {
        if (document.getElementById('superadminSearch').value.trim() !== '') return;
        
        const lastOpen = localStorage.getItem('superadmin_last_tenant');
        if (lastOpen) {
            const target = document.getElementById(lastOpen);
            if (target) {
                // Sluit eventuele andere open tabs die door backend als default zijn opengezet
                accordionTeams.querySelectorAll('.collapse.show').forEach(el => {
                    if (el.id !== lastOpen) {
                        el.classList.remove('show');
                        const relatedBtn = accordionTeams.querySelector(`[data-bs-target="#${el.id}"]`);
                        if (relatedBtn) relatedBtn.classList.add('collapsed');
                    }
                });
                
                // Open de werkelijke target
                target.classList.add('show');
                const btn = document.querySelector(`[data-bs-target="#${lastOpen}"]`);
                if (btn) {
                    btn.classList.remove('collapsed');
                    setTimeout(() => btn.scrollIntoView({ behavior: 'smooth', block: 'center' }), 150);
                }
            }
        }
    }
    
    restoreState();
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
