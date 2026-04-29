<?php if (empty($teams)): ?>
    <div class="alert alert-light text-center border text-muted">Aantal resultaten gevonden voor deze zoekopdracht: 0.</div>
<?php endif; ?>
<?php foreach ($teams as $index => $t): 
    $isExpired = strtotime($t['subscription_valid_until']) < time();
?>
<div class="accordion-item border-0 mb-2 rounded border">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed fw-bold d-flex justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $t['id'] ?>" style="min-height:60px;">
            <div class="d-flex align-items-center flex-grow-1 me-3">
                <span class="badge bg-secondary me-3" style="min-width: 50px;">ID: <?= $t['id'] ?></span> 
                <span class="text-truncate"><?= htmlspecialchars($t['name']) ?></span>
            </div>
            <div class="me-3">
                <span class="badge <?= $isExpired ? 'bg-danger' : 'bg-success' ?>">
                    <?= $isExpired ? '<i class="fa-solid fa-lock me-1"></i> Verlopen' : '<i class="fa-solid fa-check me-1"></i> Actief' ?>
                </span>
            </div>
        </button>
    </h2>
    <div id="collapse<?= $t['id'] ?>" class="accordion-collapse collapse <?= $index === 0 && !isset($_GET['ajax_q']) ? 'show' : '' ?>" data-bs-parent="#accordionTeams">
        <div class="accordion-body bg-white rounded-bottom">
            
            <div class="row align-items-center p-3 mb-3" style="background:#f8f9fa; border-radius: 8px;">
                <div class="col-md-4">
                    <h6 class="mb-1 text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:1px;">Facturatie</h6>
                    <div class="fs-5 fw-bold"><?= ucfirst($t['subscription_plan']) ?> <span class="ms-2 badge <?= $isExpired ? 'bg-danger' : 'bg-success' ?>"><?= date('d M Y - H:i', strtotime($t['subscription_valid_until'])) ?></span></div>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-1 text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:1px;">Verbruik (Load)</h6>
                    <div class="fs-5 fw-bold">
                        <i class="fa-solid fa-bolt text-warning me-1"></i> <?= (int)$t['total_usage'] ?>
                    </div>
                </div>
                <div class="col-md-5 text-end flex-wrap gap-2 d-flex justify-content-end">
                    <form method="POST" class="d-inline-flex gap-2">
                        <input type="hidden" name="action" value="extend_sub">
                        <input type="hidden" name="team_id" value="<?= $t['id'] ?>">
                        <select name="extra_months" class="form-select form-select-sm" style="width: auto;">
                            <option value="1">+ 1 Maand</option>
                            <option value="3">+ 3 Maanden</option>
                            <option value="12">+ 1 Jaar</option>
                        </select>
                        <button type="submit" class="btn btn-warning btn-sm fw-bold"><i class="fa-solid fa-coins me-1"></i> Verleng</button>
                    </form>
                    <form method="POST" class="d-inline-flex ms-1" onsubmit="return confirm('ALARM: Deze actie verwijdert Tenant <?= htmlspecialchars(addslashes($t['name'])) ?> en ALLE verbonden data (Spelers, Matchen, Caches, en Wees-gebruikers). Doorgaan?');">
                        <input type="hidden" name="action" value="delete_team">
                        <input type="hidden" name="team_id" value="<?= $t['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" title="Liquideer deze Tenant">
                            <i class="fa-solid fa-trash me-1"></i> Wis
                        </button>
                    </form>
                </div>
            </div>

            <?php 
                $userCount = isset($users[$t['id']]) ? count($users[$t['id']]) : 0;
                $teamPendingInvites = $invitesByTeam[$t['id']] ?? [];
                $inviteCount = count($teamPendingInvites);
                $totalSpotsUsed = $userCount + $inviteCount;
                $isFull = $totalSpotsUsed >= 3;
            ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-users me-2"></i>Gekoppelde Logins voor de Applicatie:</h6>
                <button type="button" class="btn btn-sm <?= $isFull ? 'btn-outline-secondary disabled' : 'btn-success fw-bold text-white shadow-sm' ?>" <?= $isFull ? 'title="Limiet van 3 coaches overschreden"' : "onclick=\"openInviteModal({$t['id']})\"" ?> style="font-size: 0.8rem;">
                    <i class="fa-solid <?= $isFull ? 'fa-lock' : 'fa-envelope-open-text' ?> me-1"></i> <?= $isFull ? "Max Bereikt ($totalSpotsUsed/3)" : "Coach Uitnodigen ($totalSpotsUsed/3)" ?>
                </button>
            </div>
            <?php if (empty($users[$t['id']]) && empty($teamPendingInvites)): ?>
                <p class="small text-muted fst-italic">Geen gebruikers of uitnodigingen gekoppeld aan dit team.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Naam</th>
                                <th class="d-none d-md-table-cell">E-mailadres</th>
                                <th class="text-center" title="Verbruik (Vandaag / 7 Dagen / 30 Dagen)">Load (Vand/7d/30d)</th>
                                <th>Laatst Actief</th>
                                <th>Rechten Rol</th>
                                <th class="text-center d-none d-md-table-cell">BETA Access</th>
                                <th class="text-end">Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users[$t['id']])): ?>
                            <?php foreach ($users[$t['id']] as $user): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    <?php if(isset($allUserTeams[$user['id']]) && count($allUserTeams[$user['id']]) > 1): ?>
                                        <div class="small fw-semibold mt-1 text-primary">
                                            <i class="fa-solid fa-layer-group"></i>
                                            <?php 
                                               $wsArr = array_map(function($w) { return htmlspecialchars($w['name']); }, $allUserTeams[$user['id']]);
                                               echo implode(', ', $wsArr);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-md-table-cell"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="text-center align-middle">
                                    <div class="small fw-bold text-nowrap">
                                        <span class="text-success" title="Verbruik Vandaag"><?= (int)$user['usage_today'] ?></span> / 
                                        <span class="text-warning" title="Verbruik Laatste 7 Dagen"><?= (int)$user['usage_7d'] ?></span> / 
                                        <span class="text-danger" title="Verbruik Laatste 30 Dagen"><?= (int)$user['usage_30d'] ?></span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;" title="Totaal Verbruik">Tot: <?= (int)$user['usage_total'] ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($user['last_activity'])): ?>
                                        <small class="text-muted"><i class="fa-solid fa-clock me-1"></i><?= date('d/m/y H:i', strtotime($user['last_activity'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted fst-italic">Nooit</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if($user['role'] == 'superadmin') $badge = 'bg-danger';
                                        if($user['role'] == 'admin') $badge = 'bg-primary';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($user['role']) ?></span>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
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
                                    <div class="d-flex justify-content-end gap-1 flex-nowrap">
                                        <form method="POST" action="/admin/impersonate?action=start" class="m-0">
                                            <input type="hidden" name="target_user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Log in als deze gebruiker">
                                                <i class="fa-solid fa-user-secret"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick='openEditUserModal(<?= json_encode($user) ?>)' title="Bewerk Informatie">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="POST" class="m-0" onsubmit="return confirm('Zeker dat je <?= htmlspecialchars(addslashes($user['first_name'])) ?> wilt loskoppelen van deze ploeg? Als hij geen andere ploegen bezit, wordt zijn login gewist.');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="team_id" value="<?= $t['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Gebruiker Verwijderen">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Jezelf</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($teamPendingInvites)): ?>
                            <?php foreach ($teamPendingInvites as $invite): ?>
                            <tr>
                                <td class="text-muted fst-italic">
                                    Uitgenodigd
                                    <div class="small fw-semibold mt-1 text-warning">
                                        <i class="fa-solid fa-clock"></i> In afwachting
                                    </div>
                                </td>
                                <td class="text-muted d-none d-md-table-cell"><?= htmlspecialchars($invite['email']) ?></td>
                                <td class="text-center">-</td>
                                <td>
                                    <span class="badge bg-warning text-dark">Invited</span>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    -
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-nowrap">
                                        <?php 
                                            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                                            $inviteLink = "$protocol://{$_SERVER['HTTP_HOST']}/register.php?invite_token=" . $invite['token'];
                                        ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="navigator.clipboard.writeText('<?= $inviteLink ?>'); alert('Link gekopieerd!');" title="Kopieer de invite link">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                        <form method="POST" class="m-0" onsubmit="return confirm('Zeker dat je deze uitnodiging wilt intrekken?');">
                                            <input type="hidden" name="action" value="cancel_invite">
                                            <input type="hidden" name="invite_id" value="<?= $invite['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Uitnodiging Intrekken">
                                                <i class="fa-solid fa-xmark me-1"></i> Revoke
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php endforeach; ?>
