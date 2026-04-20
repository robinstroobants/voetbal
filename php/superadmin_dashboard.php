<?php
require_once 'getconn.php';

// Controleer of de gebruiker superadmin rechten heeft
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: index.php");
    exit;
}

$page_title = 'SaaS Beheer Dashboard';

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
    } elseif ($action === 'link_extra_team') {
        $uId = (int)$_POST['user_id'];
        $nTId = (int)$_POST['new_team_id'];
        // Koppel of negeer indien al gekoppeld
        $pdo->prepare("INSERT IGNORE INTO user_teams (user_id, team_id) VALUES (?, ?)")->execute([$uId, $nTId]);
        $success = "✅ Extra Workspace gekoppeld aan gebruiker!";
    }
}

// 1. Haal alle teams op
$stmtTeams = $pdo->query("SELECT * FROM teams ORDER BY id ASC");
$teams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

// 2. Haal alle gebruikers op, gegroepeerd
$users = [];
$usersResult = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
foreach ($usersResult as $u) {
    if ($u['team_id']) {
        $users[$u['team_id']][] = $u;
    }
}

// Fetch all linked workspaces per user for UI Badges
$allUserTeams = [];
$utResult = $pdo->query("SELECT ut.user_id, t.name, t.id as team_id FROM user_teams ut JOIN teams t ON ut.team_id = t.id")->fetchAll();
foreach ($utResult as $ut) {
    $allUserTeams[$ut['user_id']][] = $ut;
}

require_once 'header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-dark">
        <h2><i class="fa-solid fa-server text-warning me-2"></i> SaaS Tenant & Abonnementen Beheer</h2>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success fw-bold shadow-sm"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
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

            <div class="card shadow-sm border-0 border-top border-success border-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-user-plus text-success me-2"></i>Nieuwe Login Aanmaken</h5>
                    <form method="POST">
                        <input type="hidden" name="action" value="create_user">
                        <div class="mb-3">
                            <label class="form-label">Koppel aan Team</label>
                            <select name="team_id" class="form-select" required>
                                <?php foreach ($teams as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2"><input type="text" name="first_name" class="form-control" placeholder="Voornaam" required></div>
                            <div class="col-6 mb-2"><input type="text" name="last_name" class="form-control" placeholder="Achternaam"></div>
                        </div>
                        <div class="mb-2"><input type="email" name="email" class="form-control" placeholder="E-mailadres" required></div>
                        <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Wachtwoord" required></div>
                        <div class="mb-3">
                            <label class="form-label">Rechten (Rol)</label>
                            <select name="role" class="form-select">
                                <option value="coach">Coach (Normaal)</option>
                                <option value="admin">Team Admin (Settings)</option>
                                <option value="superadmin">Super Admin (Overal)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Login Opslaan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <h4 class="mb-3"><i class="fa-solid fa-building me-2"></i>Huidige SaaS Tenants</h4>
            <div class="accordion shadow-sm" id="accordionTeams">
                <?php foreach ($teams as $index => $t): 
                    $isExpired = strtotime($t['subscription_valid_until']) < time();
                ?>
                <div class="accordion-item border-0 mb-2 rounded border">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?> fw-bold d-flex justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $t['id'] ?>">
                            <span><span class="badge bg-secondary me-2">ID: <?= $t['id'] ?></span> <?= htmlspecialchars($t['name']) ?></span>
                            <span class="badge <?= $isExpired ? 'bg-danger' : 'bg-success' ?> ms-auto" style="margin-right: 15px;">
                                <?= $isExpired ? '<i class="fa-solid fa-lock"></i> Verlopen' : '<i class="fa-solid fa-check"></i> Actief' ?>
                            </span>
                        </button>
                    </h2>
                    <div id="collapse<?= $t['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#accordionTeams">
                        <div class="accordion-body bg-white rounded-bottom">
                            
                            <div class="row align-items-center p-3 mb-3" style="background:#f8f9fa; border-radius: 8px;">
                                <div class="col-md-6">
                                    <h6 class="mb-1 text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:1px;">Facturatie</h6>
                                    <div class="fs-5 fw-bold"><?= ucfirst($t['subscription_plan']) ?> <span class="ms-2 badge <?= $isExpired ? 'bg-danger' : 'bg-success' ?>"><?= date('d M Y - H:i', strtotime($t['subscription_valid_until'])) ?></span></div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <form method="POST" class="d-inline-flex gap-2">
                                        <input type="hidden" name="action" value="extend_sub">
                                        <input type="hidden" name="team_id" value="<?= $t['id'] ?>">
                                        <select name="extra_months" class="form-select form-select-sm" style="width: auto;">
                                            <option value="1">+ 1 Maand</option>
                                            <option value="3">+ 3 Maanden</option>
                                            <option value="12">+ 1 Jaar</option>
                                        </select>
                                        <button class="btn btn-warning btn-sm fw-bold"><i class="fa-solid fa-coins me-1"></i> Verleng</button>
                                    </form>
                                </div>
                            </div>

                            <h6 class="mb-2 fw-bold text-secondary">Gekoppelde Logins voor de Applicatie:</h6>
                            <?php if (empty($users[$t['id']])): ?>
                                <p class="small text-muted fst-italic">Geen gebruikers gekoppeld aan dit team. Toegang onmogelijk.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Naam</th>
                                                <th>E-mailadres</th>
                                                <th>Rechten Rol</th>
                                                <th class="text-center">BETA Access</th>
                                                <th class="text-end">Acties</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users[$t['id']] as $user): ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                    <?php if(isset($allUserTeams[$user['id']]) && count($allUserTeams[$user['id']]) > 1): ?>
                                                        <div class="small fw-semibold mt-1 text-primary">
                                                            <i class="fa-solid fa-layer-group"></i> Workspaces: 
                                                            <?php 
                                                               $wsArr = array_map(function($w) { return htmlspecialchars($w['name']); }, $allUserTeams[$user['id']]);
                                                               echo implode(', ', $wsArr);
                                                            ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a></td>
                                                <td>
                                                    <?php 
                                                        $badge = 'bg-secondary';
                                                        if($user['role'] == 'superadmin') $badge = 'bg-danger';
                                                        if($user['role'] == 'admin') $badge = 'bg-primary';
                                                    ?>
                                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($user['role']) ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="toggle_beta">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <input type="hidden" name="current_beta" value="<?= $user['is_beta_user'] ?>">
                                                        <button type="submit" class="btn btn-sm <?= $user['is_beta_user'] ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' ?>">
                                                            <i class="fa-solid <?= $user['is_beta_user'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i> 
                                                            <?= $user['is_beta_user'] ? 'BETA AAN' : 'UIT' ?>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="text-end">
                                                    <?php if($user['role'] !== 'superadmin'): ?>
                                                    <div class="d-flex justify-content-end gap-1">
                                                        <form method="POST" action="impersonate.php?action=start" class="m-0">
                                                            <input type="hidden" name="target_user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Log in als deze gebruiker">
                                                                <i class="fa-solid fa-user-secret"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" action="" class="m-0 d-flex align-items-center">
                                                            <input type="hidden" name="action" value="link_extra_team">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <select name="new_team_id" class="form-select form-select-sm d-inline-block" style="width:110px;" required>
                                                                <option value="" disabled selected>+ Team</option>
                                                                <?php foreach($teams as $teamOp): 
                                                                        // Controleer of gebruiker al in deze workspace zit
                                                                        $isIn = false;
                                                                        if(isset($allUserTeams[$user['id']])){
                                                                            foreach($allUserTeams[$user['id']] as $w) {
                                                                                if($w['team_id'] == $teamOp['id']) $isIn = true;
                                                                            }
                                                                        }
                                                                        if(!$isIn && $teamOp['id'] != $user['team_id']): 
                                                                ?>
                                                                    <option value="<?= $teamOp['id'] ?>"><?= htmlspecialchars($teamOp['name']) ?></option>
                                                                <?php endif; endforeach; ?>
                                                            </select>
                                                            <button type="submit" class="btn btn-sm btn-outline-success ms-1" title="Koppel">
                                                                <i class="fa-solid fa-link"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border">Jezelf</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
