<?php
require_once dirname(__DIR__) . '/core/getconn.php';

// Beveiliging loopt nu centraal via router.php

$page_title = 'Gebruikersbeheer';
$success = '';
$error = '';

// Acties verwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_user') {
        $uid = (int)$_POST['user_id'];
        $fname = trim($_POST['first_name'] ?? '');
        $lname = trim($_POST['last_name'] ?? '');
        $eml = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $r = $_POST['role'] ?? 'User';
        $is_beta = (int)($_POST['is_beta_user'] ?? 0);
        $acc_status = $_POST['account_status'] ?? 'active';
        
        if ($uid && $eml) {
            $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, is_beta_user = ?, account_status = ? WHERE id = ?")->execute([$fname, $lname, $eml, $r, $is_beta, $acc_status, $uid]);
            $success = "✅ Gebruiker succesvol bijgewerkt!";
        } else {
            $error = "❌ Fout: E-mailadres mag niet leeg zijn.";
        }
    } elseif ($action === 'reset_password') {
        $uid = (int)$_POST['user_id'];
        $new_password = $_POST['new_password'] ?? '';
        
        if ($uid && strlen($new_password) >= 6) {
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $uid]);
            $success = "✅ Wachtwoord is succesvol overschreven voor deze gebruiker!";
        } else {
            $error = "❌ Wachtwoord moet minimaal 6 tekens lang zijn.";
        }
    } elseif ($action === 'delete_user') {
        $uid = (int)$_POST['user_id'];
        if ($uid) {
            if ($uid === $_SESSION['user_id']) {
                $error = "❌ Fout: Je kan jezelf niet verwijderen.";
            } else {
                $pdo->prepare("DELETE FROM user_teams WHERE user_id = ?")->execute([$uid]);
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
                $success = "✅ Gebruiker definitief verwijderd uit het systeem.";
            }
        }
    } elseif ($action === 'resend_activation') {
        $uid = (int)$_POST['user_id'];
        if ($uid) {
            $stmt = $pdo->prepare("SELECT email, first_name, verification_token FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $u = $stmt->fetch();
            if ($u) {
                $token = $u['verification_token'];
                if (!$token) {
                    $token = bin2hex(random_bytes(32));
                    $pdo->prepare("UPDATE users SET verification_token = ? WHERE id = ?")->execute([$token, $uid]);
                }
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $host = $_SERVER['HTTP_HOST'];
                $verify_link = "$protocol://$host/verify.php?token=$token";
                
                $subject = "Activeer je Lineup account";
                $message = "Beste " . $u['first_name'] . ",\n\nWelkom bij Lineup!\nKlik op de onderstaande link om je account te activeren:\n$verify_link\n\nMet vriendelijke groeten,\nHet Lineup Team";
                
                require_once dirname(__DIR__) . '/core/Mailer.php';
                Mailer::send($u['email'], $subject, $message);
                $success = "✅ Activatiemail succesvol (opnieuw) verzonden naar " . htmlspecialchars($u['email']) . ".";
            }
        }
    } elseif ($action === 'send_reset_email') {
        $uid = (int)$_POST['user_id'];
        if ($uid) {
            $stmt = $pdo->prepare("SELECT email, first_name FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $u = $stmt->fetch();
            if ($u) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?")->execute([$token, $expires, $uid]);
                
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $host = $_SERVER['HTTP_HOST'];
                $reset_link = "$protocol://$host/reset_password?token=$token";
                
                $subject = "Wachtwoord herstellen - Lineup";
                $message = "Beste " . $u['first_name'] . ",\n\nEr is een verzoek ingediend door een beheerder om je wachtwoord te herstellen op Lineup.\nKlik op de onderstaande link om een nieuw wachtwoord in te stellen. Deze link is 1 uur geldig:\n$reset_link\n\nAls dit een fout is, hoef je niets te doen.\n\nMet vriendelijke groeten,\nHet Lineup Team";
                
                require_once dirname(__DIR__) . '/core/Mailer.php';
                Mailer::send($u['email'], $subject, $message);
                $success = "✅ Wachtwoord reset e-mail verzonden naar " . htmlspecialchars($u['email']) . ".";
            }
        }
    } elseif ($action === 'approve_user') {
        $uid = (int)$_POST['user_id'];
        if ($uid) {
            $stmt = $pdo->prepare("SELECT email, first_name, account_status FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $u = $stmt->fetch();
            
            if ($u && $u['account_status'] === 'pending') {
                $pdo->prepare("UPDATE users SET account_status = 'active' WHERE id = ?")->execute([$uid]);
                
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $host = $_SERVER['HTTP_HOST'];
                $login_link = "$protocol://$host/login";
                
                $subject = "Je Lineup account is goedgekeurd!";
                $message = "Beste " . $u['first_name'] . ",\n\nGoed nieuws! Je account op Lineup is zojuist goedgekeurd door een beheerder.\nJe kan nu inloggen via de onderstaande link:\n$login_link\n\nMet vriendelijke groeten,\nHet Lineup Team";
                
                require_once dirname(__DIR__) . '/core/Mailer.php';
                Mailer::send($u['email'], $subject, $message);
                $success = "✅ Gebruiker goedgekeurd en welkomstmail verzonden naar " . htmlspecialchars($u['email']) . ".";
            }
        }
    }
}

// Zoeken & Filteren
$searchTerm = trim($_GET['q'] ?? '');
$queryStr = "SELECT u.id, u.first_name, u.last_name, u.email, u.role, u.is_beta_user, u.account_status, u.is_verified, u.last_activity, u.created_at, 
             GROUP_CONCAT(t.name SEPARATOR ', ') as team_names 
             FROM users u 
             LEFT JOIN user_teams ut ON u.id = ut.user_id 
             LEFT JOIN teams t ON ut.team_id = t.id";

$params = [];
if ($searchTerm !== '') {
    $queryStr .= " WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR t.name LIKE ? OR u.account_status LIKE ?";
    $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
}

$queryStr .= " GROUP BY u.id ORDER BY u.last_activity DESC, u.created_at DESC LIMIT 100";

$stmtUsers = $pdo->prepare($queryStr);
$stmtUsers->execute($params);
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-users text-success me-2"></i>Gebruikersbeheer</h2>
            <p class="text-muted mb-0">Beheer alle SaaS-gebruikers, wijzig hun gegevens en overschrijf wachtwoorden indien nodig.</p>
        </div>
        <a href="/admin" class="btn btn-outline-secondary rounded-pill fw-bold shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Terug naar Dashboard
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success shadow-sm rounded-3 fw-bold border-0 border-start border-success border-4"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger shadow-sm rounded-3 fw-bold border-0 border-start border-danger border-4"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom p-4 rounded-top-4">
            <form method="GET" action="/admin/users">
                <div class="input-group shadow-sm rounded-3">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-search"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0 ps-0" placeholder="Zoek op naam, e-mail of team..." value="<?= htmlspecialchars($searchTerm) ?>" autofocus>
                    <button class="btn btn-primary px-3" type="submit" title="Zoeken">
                        <i class="fa-solid fa-magnifying-glass d-sm-none"></i><span class="d-none d-sm-inline fw-bold">Zoeken</span>
                    </button>
                    <?php if ($searchTerm): ?>
                        <a href="/admin/users" class="btn btn-secondary px-3" title="Wissen">
                            <i class="fa-solid fa-xmark d-sm-none"></i><span class="d-none d-sm-inline fw-bold">Wissen</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="card-body p-0 rounded-bottom-4">
            <div class="table-responsive" style="padding-bottom: 120px; min-height: 350px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase" style="font-size: 0.8rem;">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Naam</th>
                            <th>E-mail</th>
                            <th>Teams</th>
                            <th>Rol & Status</th>
                            <th>Laatst Actief</th>
                            <th class="text-end pe-4">Acties</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open mb-3" style="font-size: 3rem;"></i>
                                    <br>Geen gebruikers gevonden.
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="ps-4 text-muted small fw-bold">#<?= $u['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars(trim($u['first_name'] . ' ' . $u['last_name'])) ?: '<i>Nieuwe Gebruiker</i>' ?></div>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="text-decoration-none text-primary"><i class="fa-regular fa-envelope me-1"></i><?= htmlspecialchars($u['email']) ?></a>
                                </td>
                                <td>
                                    <?php if ($u['team_names']): ?>
                                        <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($u['team_names']) ?>">
                                            <span class="badge bg-light text-dark border"><i class="fa-solid fa-layer-group me-1 text-muted"></i> <?= htmlspecialchars($u['team_names']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small"><i>Geen teams</i></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'superadmin'): ?>
                                        <span class="badge bg-danger rounded-pill"><i class="fa-solid fa-shield-halved me-1"></i> Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill">Coach</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($u['is_beta_user']): ?>
                                        <span class="badge bg-warning text-dark rounded-pill ms-1"><i class="fa-solid fa-flask"></i> Beta</span>
                                    <?php endif; ?>
                                    
                                    <div class="mt-1">
                                        <?php if ($u['account_status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark rounded-pill"><i class="fa-solid fa-hourglass-half"></i> Wachtlijst</span>
                                        <?php elseif ($u['account_status'] === 'suspended'): ?>
                                            <span class="badge bg-danger rounded-pill"><i class="fa-solid fa-ban"></i> Geschorst</span>
                                        <?php else: ?>
                                            <span class="badge bg-success rounded-pill"><i class="fa-solid fa-check"></i> Actief</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!$u['is_verified']): ?>
                                            <span class="badge bg-light text-muted border rounded-pill ms-1" title="E-mail nog niet geverifieerd"><i class="fa-solid fa-envelope"></i> Onbevestigd</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    if ($u['last_activity']) {
                                        $la = strtotime($u['last_activity']);
                                        if (time() - $la < 600) {
                                            echo '<span class="badge bg-success"><i class="fa-solid fa-circle-dot fa-fade me-1"></i> Online</span>';
                                        } else {
                                            echo '<span class="text-muted small">' . date('d/m/Y H:i', $la) . '</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted small"><i>Nooit</i></span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="window">
                                            Acties
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <?php if ($u['account_status'] === 'pending'): ?>
                                            <li>
                                                <form method="POST" action="/admin/users" class="m-0 p-0">
                                                    <input type="hidden" name="action" value="approve_user">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-success fw-bold"><i class="fa-solid fa-check-circle me-2"></i> Goedkeuren & Mailen</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                               data-uid="<?= $u['id'] ?>"
                                               data-fname="<?= htmlspecialchars($u['first_name'] ?? '') ?>"
                                               data-lname="<?= htmlspecialchars($u['last_name'] ?? '') ?>"
                                               data-email="<?= htmlspecialchars($u['email']) ?>"
                                               data-role="<?= $u['role'] ?>"
                                               data-beta="<?= $u['is_beta_user'] ?>"
                                               data-status="<?= $u['account_status'] ?>">
                                               <i class="fa-solid fa-pen text-primary me-2"></i> Bewerk Gegevens
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" 
                                               data-uid="<?= $u['id'] ?>"
                                               data-name="<?= htmlspecialchars(trim($u['first_name'] . ' ' . $u['last_name'])) ?>">
                                               <i class="fa-solid fa-key text-warning me-2"></i> Wachtwoord Reset
                                            </a></li>
                                            <?php if (!$u['is_verified']): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="/admin/users" class="m-0 p-0">
                                                    <input type="hidden" name="action" value="resend_activation">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-primary"><i class="fa-solid fa-paper-plane me-2"></i> Activatiemail sturen</button>
                                                </form>
                                            </li>
                                            <?php elseif ($u['account_status'] !== 'pending'): ?>
                                            <li>
                                                <form method="POST" action="/admin/users" class="m-0 p-0">
                                                    <input type="hidden" name="action" value="send_reset_email">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-primary"><i class="fa-solid fa-envelope me-2"></i> Reset mail sturen</button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="/admin/impersonate?action=start" method="POST" class="m-0 p-0">
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-success"><i class="fa-solid fa-user-secret me-2"></i> Impersonate</button>
                                                </form>
                                            </li>
                                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="/admin/users" class="m-0 p-0" onsubmit="return confirm('ALARM: Zeker dat je deze gebruiker DEFINITIEF wilt wissen uit het systeem? Dit kan niet ongedaan worden gemaakt.');">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-trash me-2"></i> Verwijderen</button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (count($users) === 100): ?>
            <div class="p-3 text-center bg-light border-top text-muted small">
                <i class="fa-solid fa-info-circle me-1"></i> Er worden maximaal 100 resultaten getoond. Gebruik de zoekbalk om verder te verfijnen.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Bewerk Gebruiker -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header bg-light border-bottom-0 rounded-top-4">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen text-primary me-2"></i>Gebruiker Bewerken</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" id="edit_uid">
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted small">Voornaam</label>
                    <input type="text" class="form-control" name="first_name" id="edit_fname" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted small">Achternaam</label>
                    <input type="text" class="form-control" name="last_name" id="edit_lname" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">E-mailadres</label>
                <input type="email" class="form-control" name="email" id="edit_email" required>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted small">Rol</label>
                    <select class="form-select" name="role" id="edit_role">
                        <option value="User">Coach (Standaard)</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted small">Beta Toegang</label>
                    <select class="form-select" name="is_beta_user" id="edit_beta">
                        <option value="0">Nee</option>
                        <option value="1">Ja</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">Account Status</label>
                <select class="form-select border-primary" name="account_status" id="edit_status">
                    <option value="active">Actief (Toegang verleend)</option>
                    <option value="pending">Wachtlijst (Pending)</option>
                    <option value="suspended">Geschorst (Geen toegang)</option>
                </select>
                <div class="form-text">Zet op <b>Actief</b> om gebruikers van de wachtlijst toe te laten.</div>
            </div>
          </div>
          <div class="modal-footer border-top-0 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Annuleren</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-save me-1"></i> Opslaan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Wachtwoord Reset -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header bg-light border-bottom-0 rounded-top-4">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-key text-warning me-2"></i>Wachtwoord Overschrijven</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
          <div class="modal-body">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="reset_uid">
            
            <div class="alert alert-warning border-0 rounded-3 small">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> U staat op het punt het wachtwoord voor <strong id="reset_name_display">deze gebruiker</strong> te overschrijven. De gebruiker kan hierna niet meer inloggen met het oude wachtwoord.
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">Nieuw Wachtwoord (min. 6 tekens)</label>
                <input type="password" class="form-control" name="new_password" required minlength="6" placeholder="Bedenk een veilig wachtwoord...">
            </div>
          </div>
          <div class="modal-footer border-top-0 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Annuleren</button>
            <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold"><i class="fa-solid fa-check me-1"></i> Overschrijven</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate Edit Modal
    var editUserModal = document.getElementById('editUserModal')
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            
            document.getElementById('edit_uid').value = button.getAttribute('data-uid')
            document.getElementById('edit_fname').value = button.getAttribute('data-fname')
            document.getElementById('edit_lname').value = button.getAttribute('data-lname')
            document.getElementById('edit_email').value = button.getAttribute('data-email')
            document.getElementById('edit_role').value = button.getAttribute('data-role')
            document.getElementById('edit_beta').value = button.getAttribute('data-beta')
            
            var status = button.getAttribute('data-status');
            if(status) {
                document.getElementById('edit_status').value = status;
            } else {
                document.getElementById('edit_status').value = 'active';
            }
        })
    }
    
    // Populate Reset Password Modal
    var resetModal = document.getElementById('resetPasswordModal')
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            
            document.getElementById('reset_uid').value = button.getAttribute('data-uid')
            document.getElementById('reset_name_display').textContent = button.getAttribute('data-name')
        })
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
