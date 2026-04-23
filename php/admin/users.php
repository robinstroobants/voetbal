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
        
        if ($uid && $eml) {
            $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, is_beta_user = ? WHERE id = ?")->execute([$fname, $lname, $eml, $r, $is_beta, $uid]);
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
    }
}

// Zoeken & Filteren
$searchTerm = trim($_GET['q'] ?? '');
$queryStr = "SELECT u.id, u.first_name, u.last_name, u.email, u.role, u.is_beta_user, u.last_activity, u.created_at, 
             GROUP_CONCAT(t.name SEPARATOR ', ') as team_names 
             FROM users u 
             LEFT JOIN user_teams ut ON u.id = ut.user_id 
             LEFT JOIN teams t ON ut.team_id = t.id";

$params = [];
if ($searchTerm !== '') {
    $queryStr .= " WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR t.name LIKE ?";
    $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
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
        <div class="card-header bg-white border-bottom p-4">
            <form method="GET" action="/admin/users">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden">
                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-0 px-3" placeholder="Zoek op naam, e-mail of team..." value="<?= htmlspecialchars($searchTerm) ?>" autofocus>
                    <button class="btn btn-primary px-4 fw-bold" type="submit">Zoeken</button>
                    <?php if ($searchTerm): ?>
                        <a href="/admin/users" class="btn btn-secondary px-4 fw-bold">Wissen</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase" style="font-size: 0.8rem;">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Naam</th>
                            <th>E-mail</th>
                            <th>Workspaces</th>
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
                                        <span class="text-muted small"><i>Geen workspaces</i></span>
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
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Acties
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                               data-uid="<?= $u['id'] ?>"
                                               data-fname="<?= htmlspecialchars($u['first_name'] ?? '') ?>"
                                               data-lname="<?= htmlspecialchars($u['last_name'] ?? '') ?>"
                                               data-email="<?= htmlspecialchars($u['email']) ?>"
                                               data-role="<?= $u['role'] ?>"
                                               data-beta="<?= $u['is_beta_user'] ?>">
                                               <i class="fa-solid fa-pen text-primary me-2"></i> Bewerk Gegevens
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" 
                                               data-uid="<?= $u['id'] ?>"
                                               data-name="<?= htmlspecialchars(trim($u['first_name'] . ' ' . $u['last_name'])) ?>">
                                               <i class="fa-solid fa-key text-warning me-2"></i> Wachtwoord Reset
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="/admin/impersonate?action=start" method="POST" class="m-0 p-0">
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-success"><i class="fa-solid fa-user-secret me-2"></i> Impersonate</button>
                                                </form>
                                            </li>
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
