<?php
/**
 * data_export.php — GDPR Recht op Inzage (Art. 15)
 * Superadmin-only: zoek een coach op via e-mail en bekijk alle bewaarde persoonsgegevens.
 */
require_once dirname(__DIR__) . '/core/getconn.php';
$page_title = 'GDPR Data Export';

$email_query = trim($_GET['email'] ?? '');
$coach       = null;
$datasets    = [];
$error       = null;

if ($email_query) {
    // Haal coach op
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role, is_beta_user, oauth_provider, created_at, last_activity, account_status FROM users WHERE email = ?");
    $stmt->execute([$email_query]);
    $coach = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coach) {
        $error = "Geen gebruiker gevonden met e-mail: " . htmlspecialchars($email_query);
    } else {
        $uid  = $coach['id'];
        $tid  = null;

        // Team van de coach
        $stmtT = $pdo->prepare("SELECT t.id, t.name, t.default_format, t.timezone FROM teams t JOIN users u ON u.team_id = t.id WHERE u.id = ?");
        $stmtT->execute([$uid]);
        $team = $stmtT->fetch(PDO::FETCH_ASSOC);
        $tid  = $team['id'] ?? null;

        // Spelers
        $players = [];
        if ($tid) {
            $stmtP = $pdo->prepare("SELECT id, first_name, last_name, created_at FROM players WHERE team_id = ? AND deleted_at IS NULL ORDER BY last_name");
            $stmtP->execute([$tid]);
            $players = $stmtP->fetchAll(PDO::FETCH_ASSOC);
        }

        // Wedstrijden
        $games = [];
        if ($tid) {
            $stmtG = $pdo->prepare("SELECT id, opponent, game_date, game_format, created_at FROM games WHERE team_id = ? ORDER BY game_date DESC LIMIT 50");
            $stmtG->execute([$tid]);
            $games = $stmtG->fetchAll(PDO::FETCH_ASSOC);
        }

        // Gebruik (usage_logs) — laatste 50
        $stmtU = $pdo->prepare("SELECT action_type, cost_weight, context, created_at FROM usage_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stmtU->execute([$uid]);
        $usage = $stmtU->fetchAll(PDO::FETCH_ASSOC);

        // Performance logs (system_logs) — laatste 30
        $stmtS = $pdo->prepare("SELECT action_name, execution_time_ms, memory_usage_mb, context, created_at FROM system_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 30");
        $stmtS->execute([$uid]);
        $syslogs = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        $datasets = compact('team', 'players', 'games', 'usage', 'syslogs');
    }
}
?>
<?php require_once __DIR__ . '/../header.php'; ?>

<div class="container py-4" style="max-width: 900px;">
    <?php include __DIR__ . '/_monitoring_nav.php'; ?>

    <div class="d-flex align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-0"><i class="fa-solid fa-shield-halved text-primary me-2"></i>GDPR — Recht op Inzage</h2>
            <p class="text-muted small mb-0">Art. 15 AVG · Geef de coach een overzicht van alle bewaarde persoonsgegevens.</p>
        </div>
    </div>

    <!-- Zoekformulier -->
    <form method="GET" class="card shadow-sm mb-4">
        <div class="card-body d-flex gap-2">
            <input type="email" name="email" class="form-control" placeholder="E-mailadres van de coach..."
                   value="<?= htmlspecialchars($email_query) ?>" autofocus required>
            <button type="submit" class="btn btn-primary fw-bold text-nowrap">
                <i class="fa-solid fa-magnifying-glass me-1"></i>Opzoeken
            </button>
            <?php if ($coach): ?>
            <a href="?email=<?= urlencode($email_query) ?>&download=1" class="btn btn-outline-secondary text-nowrap">
                <i class="fa-solid fa-download me-1"></i>Exporteer
            </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error ?></div>
    <?php endif; ?>

    <?php if ($coach): ?>

    <!-- Samenvatting kaart -->
    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fa-solid fa-user me-2"></i>Accountgegevens
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Naam</small><strong><?= htmlspecialchars($coach['first_name'] . ' ' . $coach['last_name']) ?></strong></div>
                <div class="col-md-4"><small class="text-muted d-block">E-mail</small><strong><?= htmlspecialchars($coach['email']) ?></strong></div>
                <div class="col-md-4"><small class="text-muted d-block">Rol</small><span class="badge bg-secondary"><?= htmlspecialchars($coach['role']) ?></span></div>
                <div class="col-md-4"><small class="text-muted d-block">Geregistreerd</small><?= date('d/m/Y H:i', strtotime($coach['created_at'])) ?></div>
                <div class="col-md-4"><small class="text-muted d-block">Laatste activiteit</small><?= $coach['last_activity'] ? date('d/m/Y H:i', strtotime($coach['last_activity'])) : '—' ?></div>
                <div class="col-md-4"><small class="text-muted d-block">Login via</small><?= htmlspecialchars($coach['oauth_provider'] ?? 'e-mail/wachtwoord') ?></div>
                <div class="col-md-4"><small class="text-muted d-block">Accountstatus</small><span class="badge bg-<?= $coach['account_status'] === 'active' ? 'success' : 'warning text-dark' ?>"><?= htmlspecialchars($coach['account_status']) ?></span></div>
                <div class="col-md-4"><small class="text-muted d-block">Beta gebruiker</small><?= $coach['is_beta_user'] ? '<span class="badge bg-info text-dark">Ja</span>' : 'Nee' ?></div>
            </div>
        </div>
    </div>

    <!-- Team -->
    <?php if ($datasets['team']): $t = $datasets['team']; ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light"><i class="fa-regular fa-futbol me-2 text-success"></i>Team</div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4"><small class="text-muted d-block">Naam</small><?= htmlspecialchars($t['name']) ?></div>
                <div class="col-md-4"><small class="text-muted d-block">Standaard formaat</small><?= htmlspecialchars($t['default_format'] ?? '—') ?></div>
                <div class="col-md-4"><small class="text-muted d-block">Tijdzone</small><?= htmlspecialchars($t['timezone'] ?? '—') ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Spelers -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light d-flex justify-content-between">
            <span><i class="fa-solid fa-users me-2 text-info"></i>Spelers</span>
            <span class="badge bg-secondary"><?= count($datasets['players']) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($datasets['players'])): ?>
                <p class="text-muted p-3 mb-0">Geen spelers.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0 small">
                    <thead class="table-light"><tr><th>#</th><th>Naam</th><th>Aangemaakt</th></tr></thead>
                    <tbody>
                    <?php foreach ($datasets['players'] as $i => $p): ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Wedstrijden -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light d-flex justify-content-between">
            <span><i class="fa-regular fa-calendar-days me-2 text-warning"></i>Wedstrijden <small class="text-muted fw-normal">(laatste 50)</small></span>
            <span class="badge bg-secondary"><?= count($datasets['games']) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($datasets['games'])): ?>
                <p class="text-muted p-3 mb-0">Geen wedstrijden.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0 small">
                    <thead class="table-light"><tr><th>Datum</th><th>Tegenstander</th><th>Formaat</th></tr></thead>
                    <tbody>
                    <?php foreach ($datasets['games'] as $g): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($g['game_date'])) ?></td>
                            <td><?= htmlspecialchars($g['opponent']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($g['game_format'] ?? '—') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gebruik -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light d-flex justify-content-between">
            <span><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Gebruiksdata <small class="text-muted fw-normal">(laatste 50 acties)</small></span>
            <span class="badge bg-secondary"><?= count($datasets['usage']) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($datasets['usage'])): ?>
                <p class="text-muted p-3 mb-0">Geen gebruiksdata.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0 small">
                    <thead class="table-light"><tr><th>Tijdstip</th><th>Actie</th><th>Context</th></tr></thead>
                    <tbody>
                    <?php foreach ($datasets['usage'] as $u): ?>
                        <tr>
                            <td class="text-nowrap"><?= date('d/m H:i', strtotime($u['created_at'])) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($u['action_type']) ?></span></td>
                            <td class="text-muted"><?= htmlspecialchars($u['context'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Wat we NIET bewaren -->
    <div class="alert alert-success d-flex gap-2 mb-4">
        <i class="fa-solid fa-circle-check mt-1 flex-shrink-0"></i>
        <div class="small">
            <strong>Wat we niet bewaren:</strong> wachtwoord (enkel als hash), betaalgegevens, locatiedata, cookies van derden zonder toestemming.
            Ouders op de share-pagina worden niet bijgehouden in persoonsgebonden logs.
        </div>
    </div>

    <!-- Actieknop: bevestiging naar coach -->
    <div class="card border-0 bg-light mb-5">
        <div class="card-body">
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-envelope me-2"></i>Stuur antwoord naar coach</h6>
            <p class="small text-muted mb-2">Gebruik onderstaande tekst als basis voor je antwoord:</p>
            <textarea class="form-control small font-monospace" rows="6" id="gdprEmailText"
>Beste <?= htmlspecialchars($coach['first_name']) ?>,

In antwoord op je verzoek tot inzage (GDPR Art. 15) bezorgen we je hierbij een overzicht van de persoonsgegevens die Lineup Heroes over jou bewaart:

- Naam: <?= htmlspecialchars($coach['first_name'] . ' ' . $coach['last_name']) ?>

- E-mail: <?= htmlspecialchars($coach['email']) ?>

- Geregistreerd: <?= date('d/m/Y', strtotime($coach['created_at'])) ?>

- Team: <?= htmlspecialchars($datasets['team']['name'] ?? '—') ?>

- Aantal spelers: <?= count($datasets['players']) ?>

- Aantal wedstrijden: <?= count($datasets['games']) ?>

Wens je bepaalde gegevens te laten verwijderen, neem dan contact op via info@lineupheroes.com.

Met vriendelijke groeten,
Lineup Heroes</textarea>
            <button class="btn btn-sm btn-outline-secondary mt-2"
                    onclick="navigator.clipboard.writeText(document.getElementById('gdprEmailText').value); this.innerHTML='<i class=\'fa-solid fa-check me-1\'></i>Gekopieerd!'">
                <i class="fa-solid fa-copy me-1"></i>Kopieer tekst
            </button>
        </div>
    </div>

    <?php endif; // if $coach ?>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
