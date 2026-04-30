<?php
if (!isset($gameId)) return;

// Haal alle events op voor deze match
$stmtEv = $pdo->prepare("
    SELECT e.*, p.first_name, p.last_name, p2.first_name as out_first, p2.last_name as out_last
    FROM game_events e
    LEFT JOIN players p ON e.player_id = p.id
    LEFT JOIN players p2 ON e.player_out_id = p2.id
    WHERE e.game_id = ? AND e.is_deleted = 0
    ORDER BY e.created_at ASC
");
$stmtEv->execute([$gameId]);
$events = $stmtEv->fetchAll(PDO::FETCH_ASSOC);

$unconfirmedCount = 0;
foreach ($events as $ev) {
    if (!$ev['is_confirmed']) $unconfirmedCount++;
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12 col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i> Wedstrijdverslag & Wachtkamer</h5>
                        <p class="text-muted small mt-1">Hier verschijnen alle acties die jij of de ouders (via de live share-link) hebben doorgegeven.</p>
                    </div>
                    <?php if (!empty($events)): ?>
                        <button class="btn btn-sm btn-outline-danger fw-bold shadow-sm" onclick="deleteAllEvents()"><i class="fa-solid fa-trash-can me-1"></i> Alles Wissen</button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    
                    <?php if (empty($events)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-clipboard-list fs-1 opacity-25 mb-3"></i>
                            <h6>Nog geen gebeurtenissen gelogd.</h6>
                            <p class="small">Zodra de match gestart wordt of er goals vallen, verschijnen ze hier in de tijdlijn.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline-container" style="position: relative; border-left: 2px solid #e9ecef; margin-left: 20px; padding-left: 20px;">
                                <?php 
                                $blockCounter = 0;
                                foreach ($events as $ev): 
                                    $icon = 'fa-circle-info';
                                    $color = 'text-secondary';
                                    $bg = 'bg-light';
                                    $title = 'Onbekend: [' . htmlspecialchars($ev['event_type']) . ']';
                                    $desc = '';
                                    
                                    if ($ev['event_type'] === 'match_start' || $ev['event_type'] === 'period_start') {
                                        $blockCounter++;
                                    }
                                    
                                    switch ($ev['event_type']) {
                                        case 'match_start':
                                            $icon = 'fa-play'; $color = 'text-primary'; $bg = 'bg-primary-subtle';
                                            $title = 'Wedstrijd/Blok ' . $blockCounter . ' Gestart';
                                            $desc = 'De wedstrijd is afgetrapt.';
                                            break;
                                        case 'period_start':
                                            $icon = 'fa-forward-step'; $color = 'text-success'; $bg = 'bg-success-subtle';
                                            $title = 'Wedstrijd/Blok ' . $blockCounter . ' Gestart';
                                            $desc = 'De klok loopt weer voor het nieuwe wisselblok.';
                                            break;
                                        case 'period_end':
                                            $icon = 'fa-pause'; $color = 'text-warning'; $bg = 'bg-warning-subtle';
                                            $title = 'Rust / Einde Helft';
                                            if ($ev['parent_email'] === 'auto@systeem') $title .= ' (auto)';
                                            $desc = 'De wedstrijd is gepauzeerd / afgefloten.';
                                            break;
                                        case 'goal':
                                            $icon = 'fa-futbol'; $color = 'text-success'; $bg = 'bg-success-subtle';
                                            $title = 'Goal!';
                                            $desc = 'Gescoord door <strong>' . htmlspecialchars($ev['first_name'] . ' ' . $ev['last_name']) . '</strong>';
                                            if (!empty($ev['out_first'])) {
                                                $desc .= ' <span class="text-muted small">(Assist: ' . htmlspecialchars($ev['out_first']) . ')</span>';
                                            }
                                            break;
                                        case 'opp_goal':
                                        case 'tegengoal':
                                        case '':
                                            $icon = 'fa-futbol'; $color = 'text-danger'; $bg = 'bg-danger-subtle';
                                            $title = 'Tegendoelpunt';
                                            $desc = 'De tegenstander heeft gescoord.';
                                            break;
                                        case 'substitution':
                                            $icon = 'fa-rotate'; $color = 'text-info'; $bg = 'bg-info-subtle';
                                            $title = 'Individuele Wissel';
                                            $desc = '<strong class="text-success">' . htmlspecialchars($ev['first_name'] . ' ' . $ev['last_name']) . '</strong> IN, <strong class="text-danger">' . htmlspecialchars($ev['out_first'] . ' ' . $ev['out_last']) . '</strong> UIT.';
                                            break;
                                        case 'match_end':
                                            $icon = 'fa-stop'; $color = 'text-danger'; $bg = 'bg-danger-subtle';
                                            $title = 'Einde Wedstrijd';
                                            if ($ev['parent_email'] === 'auto@systeem') $title .= ' (auto)';
                                            $desc = 'De wedstrijd is definitief beëindigd.';
                                            break;
                                    }
                                    
                                    $isConfirmed = (bool)$ev['is_confirmed'];
                                    $timeStr = date('H:i', strtotime($ev['created_at']));
                                    $isStatusEvent = in_array($ev['event_type'], ['match_start', 'period_start', 'period_end', 'match_end']);
                                ?>
                                <div class="timeline-item mb-4" style="position: relative;">
                                    <div class="timeline-icon <?= $bg ?> <?= $color ?> rounded-circle d-flex align-items-center justify-content-center border border-white border-3 shadow-sm" style="width: 40px; height: 40px; position: absolute; left: -40px; top: 0; z-index: 1;">
                                        <i class="fa-solid <?= $icon ?>"></i>
                                    </div>
                                    <div class="card border-0 <?= $isConfirmed ? 'bg-light opacity-75' : 'shadow-sm border border-warning' ?>">
                                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <?php if ($isStatusEvent): ?>
                                                        <span class="badge bg-dark"><?= $timeStr ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-dark"><?= $ev['event_minute'] ?>'</span>
                                                    <?php endif; ?>
                                                    <h6 class="mb-0 fw-bold"><?= $title ?></h6>
                                                    <?php if (!$isConfirmed): ?>
                                                        <span class="badge bg-warning text-dark border"><i class="fa-solid fa-clock me-1"></i> Wachtkamer</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="small text-muted mb-1"><?= $desc ?></div>
                                                <?php if (!empty($ev['parent_email'])): ?>
                                                    <div class="small text-secondary" style="font-size: 0.7rem;"><i class="fa-regular fa-user me-1"></i> Gemeld door: <?= htmlspecialchars($ev['parent_email']) ?></div>
                                                <?php else: ?>
                                                    <div class="small text-secondary" style="font-size: 0.7rem;"><i class="fa-solid fa-user-tie me-1"></i> Gemeld door coach</div>
                                                <?php endif; ?>
                                            </div>
                                        
                                        <div class="d-flex flex-column gap-2">
                                            <?php if (!$isConfirmed): ?>
                                                <button class="btn btn-sm btn-success shadow-sm fw-bold" onclick="updateEventStatus(<?= $ev['id'] ?>, 'confirm')"><i class="fa-solid fa-check"></i></button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="updateEventStatus(<?= $ev['id'] ?>, 'reject')"><i class="fa-solid fa-trash"></i></button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-danger" onclick="updateEventStatus(<?= $ev['id'] ?>, 'reject')" title="Toch verwijderen"><i class="fa-solid fa-trash"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($unconfirmedCount > 0): ?>
                        <div class="text-center mt-4">
                            <button class="btn btn-primary fw-bold px-4 rounded-pill shadow" onclick="confirmAllEvents()">
                                <i class="fa-solid fa-check-double me-2"></i> Keur Alle (<?= $unconfirmedCount ?>) Goed
                            </button>
                        </div>
                        <?php endif; ?>
                        
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Expose for badge update in the tab
window.unconfirmedEventsCount = <?= $unconfirmedCount ?>;

document.addEventListener("DOMContentLoaded", () => {
    let badge = document.getElementById("badge-unconfirmed-events");
    if (badge && window.unconfirmedEventsCount > 0) {
        badge.innerText = window.unconfirmedEventsCount;
        badge.classList.remove("d-none");
    }
});

function updateEventStatus(eventId, actionStr) {
    if (actionStr === 'reject' && !confirm("Weet je zeker dat je dit event wil verwijderen?")) return;
    
    let fd = new FormData();
    fd.append('action', 'update_event_status');
    fd.append('event_id', eventId);
    fd.append('status_action', actionStr); // 'confirm' of 'reject'
    fd.append('game_id', <?= $gameId ?>);
    
    btnLoading(event.currentTarget);
    
    fetch('/api/api_game_events.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            location.reload();
        } else {
            alert('Fout: ' + (data.message || 'Onbekend'));
            location.reload();
        }
    }).catch(err => {
        alert('Netwerk fout');
        location.reload();
    });
}

function confirmAllEvents() {
    if (!confirm("Weet je zeker dat je alle openstaande meldingen wil valideren? Ze tellen dan officieel mee in de statistieken.")) return;
    
    let fd = new FormData();
    fd.append('action', 'confirm_all_events');
    fd.append('game_id', <?= $gameId ?>);
    
    fetch('/api/api_game_events.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            location.reload();
        } else {
            alert('Fout: ' + (data.message || 'Onbekend'));
        }
    }).catch(err => {
        alert('Netwerk fout');
    });
}

function deleteAllEvents() {
    if (!confirm("Weet je zeker dat je het hele wedstrijdverslag (alle events) wilt verwijderen? Dit kan niet ongedaan worden gemaakt.")) return;
    
    let fd = new FormData();
    fd.append('action', 'delete_all_events');
    fd.append('game_id', <?= $gameId ?>);
    
    fetch('/api/api_game_events.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            location.reload();
        } else {
            alert('Fout: ' + (data.message || 'Onbekend'));
        }
    }).catch(err => {
        alert('Netwerk fout');
    });
}

function btnLoading(btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
}
</script>
