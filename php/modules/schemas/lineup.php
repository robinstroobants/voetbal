<?php require_once __DIR__ . '/generator.php'; ?>
  <?php require_once dirname(__DIR__, 2) . '/header.php'; ?>
  <?php 
  // Helper om speler ID naar leesbare naam om te zetten voor weergave
  function getPlayerName($id) {
      global $global_playerinfo;
      if (isset($global_playerinfo[$id])) {
          return htmlspecialchars($global_playerinfo[$id]['display_name'] ?? $global_playerinfo[$id]['first_name']);
      }
      return htmlspecialchars($id);
  }
  
  // --- PATTERN LOGIC FOR DYNAMIC GENERATOR MODAL ---
  $search_format = $format ?? '';
  $gk_arr = array_filter(array_map('trim', explode(',', $doelmannen ?? '')));
  $gk_count = count($gk_arr);
  $aantal = count($squad ?? []);

  if (strpos($search_format, 'gk') === false) {
      if (preg_match('/^(\d+v\d+)_(\d+x\d+.*)$/', $search_format, $matches)) {
          $search_format = $matches[1] . '_' . $gk_count . 'gk_' . $matches[2];
      }
  }

  $nr_of_games = 4;
  $game_duration_min = 15;
  $sub_duration_min_parsed = 15;
  if (preg_match('/_(\d+)x(\d+)(?:_([0-9.]+)min)?$/', $search_format, $m)) {
      $nr_of_games = (int)$m[1];
      $game_duration_min = (int)$m[2];
      $sub_duration_min_parsed = isset($m[3]) ? (float)$m[3] : $game_duration_min;
  }

  $patterns = [];
  if ($sub_duration_min_parsed != $game_duration_min) {
      $patterns['default'] = ['name' => "Standaard: Wissel om de {$sub_duration_min_parsed}m"];
  }
  $patterns['no_sub'] = ['name' => "Niet wisselen ($nr_of_games wedstrijden van {$game_duration_min}m)"];
  if ($game_duration_min % 2 == 0 || $game_duration_min == 15) {
      $patterns['half'] = ['name' => "Wissel halverwege (Helften van " . ($game_duration_min / 2) . "m)"];
  }
  if ($game_duration_min == 15 && $nr_of_games >= 2) {
      $patterns['custom_10_5_end'] = ['name' => "W1&W2 helften, W3(10m-5m), W4(5m-10m)"];
      $patterns['custom_10_5_start'] = ['name' => "W1(10m-5m), W2(5m-10m), W3&W4 helften"];
      $patterns['custom_10_5_all'] = ['name' => "Afwisselend 10m-5m en 5m-10m per wedstrijd"];
      $patterns['custom_5_10_all'] = ['name' => "Afwisselend 5m-10m en 10m-5m per wedstrijd"];
  }
  $selected_pattern_key = isset($patterns['half']) ? 'half' : 'default';
  if (!isset($patterns[$selected_pattern_key])) {
      $selected_pattern_key = array_key_first($patterns);
  }
  
  $hasActivePeriod = false;
  if (isset($_SESSION['team_id']) && $_SESSION['team_id']) {
      $stmtPeriodsCheck = $pdo->prepare("SELECT COUNT(*) FROM team_periods WHERE team_id = ?");
      $stmtPeriodsCheck->execute([$_SESSION['team_id']]);
      if ($stmtPeriodsCheck->fetchColumn() > 0) {
          $hasActivePeriod = true;
      }
  }
  ?>
  
  <?php if (isset($_GET['generate']) && $_GET['generate'] == 1): ?>
  <script>
  if (typeof gtag === 'function') {
      gtag('event', 'generate_formation', { 
          'formation_type': '<?= htmlspecialchars($search_format) ?>',
          'ai_type': '<?= (isset($_GET["dynamic"]) && $_GET["dynamic"] == 1) ? "EqualPlay AI" : "ProLineup AI" ?>',
          'coach_id': '<?= htmlspecialchars($_SESSION["user_id"] ?? "") ?>'
      });
  }
  </script>
  <?php endif; ?>
    
  <main>
    <div class="container">
      <?php if (!defined('PUBLIC_SHARE_MODE')): ?>
      <div class="d-flex justify-content-between align-items-center pt-3 pb-2">
          <h5 class="mb-0 flex-grow-1" id="dynamic-page-title">
              <i class="fa-solid fa-futbol me-2 text-primary"></i> <?= htmlspecialchars($matchData['game']['opponent'] ?? 'Opstelling') ?>
          </h5>
          <a href="/games/<?= $gameId ?>/schema" class="btn btn-outline-secondary btn-sm">
              <i class="fa-solid fa-arrow-left me-1"></i> Terug naar Dashboard
          </a>
      </div>
      <?php else: ?>
          <h4 class="mb-2 mt-2 text-center" id="dynamic-page-title">
              <i class="fa-solid fa-futbol me-2 text-primary"></i> <?= htmlspecialchars($matchData['game']['opponent'] ?? 'Opstelling') ?>
          </h4>
      <?php endif; ?>
      
      <?= $generator_error_html ?? '' ?>
      
      
      
      <?php if (!empty($top_selected_options)): ?>

          <?php if (isset($dynamic_analysis)): 
              // Check active period
              $stmtPeriod = $pdo->prepare("SELECT id, name, start_date, end_date FROM team_periods WHERE team_id = ? AND ? BETWEEN start_date AND end_date");
              $stmtPeriod->execute([$_SESSION['team_id'], $matchData['game']['game_date']]);
              $activePeriod = $stmtPeriod->fetch(PDO::FETCH_ASSOC);
              $use_period = isset($_GET['use_period']) && $_GET['use_period'] == 1;
              
              // Season dates
              $stmtSeasonStart = $pdo->prepare("SELECT MIN(game_date) FROM games WHERE team_id = ? AND game_date <= ?");
              $stmtSeasonStart->execute([$_SESSION['team_id'], $matchData['game']['game_date']]);
              $seasonStartDate = $stmtSeasonStart->fetchColumn();
              
              $statsBasisHtml = '<div class="p-2 bg-white rounded border mb-2 text-muted" style="font-size: 0.75rem;">';
              $statsBasisHtml .= '<i class="fa-solid fa-database me-1"></i><strong>Statistieken basis:</strong> ';
              if ($use_period && $activePeriod) {
                  $statsBasisHtml .= htmlspecialchars(date('d/m/Y', strtotime($activePeriod['start_date']))) . ' t.e.m. ' . htmlspecialchars(date('d/m/Y', strtotime($activePeriod['end_date'])));
              } else {
                  $startStr = ($seasonStartDate) ? date('d/m/Y', strtotime($seasonStartDate)) : '?';
                  $statsBasisHtml .= htmlspecialchars($startStr) . ' t.e.m. ' . htmlspecialchars(date('d/m/Y', strtotime($matchData['game']['game_date'])));
              }
              $statsBasisHtml .= '</div>';
              $use_period = isset($_GET['use_period']) && $_GET['use_period'] == 1;
              $compensate = !isset($_GET['compensate']) || $_GET['compensate'] == 1; // Default ON

              // --- Bereken wiskundige theorie net zoals in schema_builder ---
              $playPositions = [1, 2, 4, 5, 7, 10, 11, 9];
              if (strpos($search_format, '5v5') !== false) {
                  $playPositions = [1, 4, 7, 11, 9];
              }
              $fieldPositions = array_filter($playPositions, fn($p) => $p != 1);

              $numFieldPlayers = $dynamic_analysis['field_players'];
              $numFieldPositions = ($gk_count > 0) ? (count($fieldPositions)) : (count($fieldPositions) + 1);
              
              $totalBlocks = $dynamic_analysis['shifts'];
              $block_dur = $dynamic_analysis['shift_duration'];
              $totalFieldBlocks = $numFieldPositions * $totalBlocks;
              
              $base_blocks = ($numFieldPlayers > 0) ? floor($totalFieldBlocks / $numFieldPlayers) : 0;
              $extra_blocks = ($numFieldPlayers > 0) ? $totalFieldBlocks % $numFieldPlayers : 0;
              $players_extra = $extra_blocks;
              $players_base = $numFieldPlayers - $players_extra;
              $base_mins = $base_blocks * $block_dur;
              $extra_mins = $base_mins + $block_dur;
              
              // Ophalen van 'Speeltijd vorige wedstrijd' voor de geselecteerde spelers
              $lastMatchPlaytimes = [];
              $squad_ids = array_column($dynamic_analysis['player_stats'], 'pid');
              if (!empty($squad_ids)) {
                  $placeholders = implode(',', array_fill(0, count($squad_ids), '?'));
                  $queryLastGame = "
                      SELECT p.player_id, p.seconds_played, p.seconds_gk, p.seconds_bank, g.id as game_id, g.opponent, g.game_date, g.is_home
                      FROM game_playtime_logs p
                      JOIN games g ON p.game_id = g.id
                      WHERE p.player_id IN ($placeholders) 
                        AND g.team_id = ? 
                        AND g.game_date < ?
                      ORDER BY g.game_date DESC, g.id DESC
                  ";
                  $paramsLastGame = array_merge($squad_ids, [$_SESSION['team_id'], $matchData['game']['game_date']]);
                  $stmtLast = $pdo->prepare($queryLastGame);
                  $stmtLast->execute($paramsLastGame);
                  
                  while ($row = $stmtLast->fetch(PDO::FETCH_ASSOC)) {
                      $pid = $row['player_id'];
                      if (!isset($lastMatchPlaytimes[$pid])) {
                          $lastMatchPlaytimes[$pid] = [
                              'mins' => round($row['seconds_played'] / 60, 1),
                              'opponent' => $row['opponent'],
                              'date' => date('d-m-Y', strtotime($row['game_date'])),
                              'location' => isset($row['is_home']) && $row['is_home'] == 1 ? 'Thuis' : 'Uit',
                              'bank' => round($row['seconds_bank'] / 60, 1),
                              'gk' => round($row['seconds_gk'] / 60, 1)
                          ];
                      }
                  }
              }
              
              $minutesGroups = [];
              foreach ($dynamic_analysis['player_stats'] as $stat) {
                  $pid = $stat['pid'];
                  if ($stat['is_gk']) continue; // Exclude fixed GKs from field history grouping
                  
                  $gameInfo = $lastMatchPlaytimes[$pid] ?? null;
                  $mins = $gameInfo['mins'] ?? 0;
                  
                  if (!isset($minutesGroups[(string)$mins])) {
                      $minutesGroups[(string)$mins] = [];
                  }
                  
                  $pName = htmlspecialchars(getPlayerName($pid));
                  if ($gameInfo) {
                      $titleText = htmlspecialchars("Gespeeld tegen " . $gameInfo['opponent'] . " op " . $gameInfo['date']);
                      $contentHtml = htmlspecialchars(
                          "<div class='small'>" .
                          "<b>Tegenstander:</b> " . htmlspecialchars($gameInfo['opponent']) . "<br>" .
                          "<b>Locatie:</b> " . $gameInfo['location'] . "<br>" .
                          "<b>Datum:</b> " . $gameInfo['date'] . "<br>" .
                          "<b>Veld:</b> " . $gameInfo['mins'] . "m<br>" .
                          "<b>Doelman:</b> " . $gameInfo['gk'] . "m<br>" .
                          "<b>Bank:</b> " . $gameInfo['bank'] . "m" .
                          "</div>"
                      );
                      $minutesGroups[(string)$mins][] = "<strong class='text-dark' style='cursor: pointer; text-decoration: none; border-bottom: 1px solid transparent;' title='" . $titleText . "' data-bs-toggle='popover' data-bs-trigger='focus' tabindex='0' data-bs-html='true' data-bs-title='Match Details' data-bs-content='" . $contentHtml . "'>$pName</strong>";
                  } else {
                      $minutesGroups[(string)$mins][] = "<strong>$pName</strong>";
                  }
              }
              
              krsort($minutesGroups);
              $lastMatchHtml = '';
              if (!empty($minutesGroups)) {
                  $lastMatchHtml = '<div class="p-2 bg-white rounded border mb-2">';
                  $lastMatchHtml .= '<p class="mb-1 fw-bold text-dark" style="font-size: 0.8rem;"><i class="fa-solid fa-clock-rotate-left text-secondary me-1"></i>Speeltijd vorige wedstrijd</p>';
                  foreach ($minutesGroups as $mins => $names) {
                      $lastMatchHtml .= '<p class="mb-0 text-muted" style="font-size: 0.75rem;">' . $mins . 'm: ' . implode(', ', $names) . '</p>';
                  }
                  $lastMatchHtml .= '</div>';
              }
              
              // Render Historiek (Speelminuten & Posities)
              $use_period = isset($_GET['use_period']) && $_GET['use_period'] == 1;
              
              $gkRatioHtml = '<div class="p-2 bg-white rounded border mb-2">';
              $gkRatioHtml .= '<p class="mb-1 fw-bold text-dark" style="font-size: 0.8rem;"><i class="fa-solid fa-clock-rotate-left text-warning me-1"></i>Historiek (Speelminuten & Posities)</p>';
              $gkRatioHtml .= '<div class="table-responsive"><table class="table table-sm table-borderless mb-0 text-nowrap" style="font-size: 0.75rem;">';
              
              $headerHtml = '<tr><th class="py-0 text-muted border-end">Speler</th>';
              if ($use_period) {
                  $headerHtml .= '<th class="py-0 text-muted text-center border-end">Periode</th>';
              }
              $headerHtml .= '<th class="py-0 text-muted text-center border-end">Seizoen</th>';
              
              // Add columns for positions
              foreach ($playPositions as $pos) {
                  $posLabel = $pos == 1 ? 'GK' : $pos;
                  $headerHtml .= '<th class="py-0 text-muted text-center">' . $posLabel . '</th>';
              }
              $headerHtml .= '</tr>';
              $gkRatioHtml .= $headerHtml;
              
              $gkSortedStats = $dynamic_analysis['player_stats'] ?? [];
              // Sort alphabetically
              usort($gkSortedStats, fn($a, $b) => strcmp(getPlayerName($a['pid']), getPlayerName($b['pid'])));
              
              foreach ($gkSortedStats as $stat) {
                  if ($stat['is_gk']) continue;
                  $pid = $stat['pid'];
                  $name = htmlspecialchars(getPlayerName($pid));
                  
                  $pctSeasonField = number_format((float)($stat['pct_season'] ?? 0) * 100, 0) . '%';
                  $seasonPlayed = calctime($pt_stats[$pid]['played'] ?? 0);
                  $seasonTotal = calctime($pt_stats[$pid]['available'] ?? 0);
                  $seasonHtml = "<span title=\"Speeltijd: {$seasonPlayed} / {$seasonTotal}\" data-bs-toggle=\"tooltip\" style=\"cursor:help; border-bottom:1px dotted #ccc;\">{$pctSeasonField}</span>";
                  
                  $rowHtml = "<tr><td class='py-0 border-end'><strong>$name</strong></td>";
                  if ($use_period) {
                      $pctPeriodField = number_format((float)($stat['pct_period'] ?? 0) * 100, 0) . '%';
                      $periodPlayed = calctime($pt_stats[$pid]['period_played'] ?? 0);
                      $periodTotal = calctime($pt_stats[$pid]['period_available'] ?? 0);
                      $pctPeriodHtml = "<span title=\"Speeltijd: {$periodPlayed} / {$periodTotal}\" data-bs-toggle=\"tooltip\" style=\"cursor:help; border-bottom:1px dotted #ccc;\">{$pctPeriodField}</span>";
                      $rowHtml .= "<td class='py-0 text-center border-end'>{$pctPeriodHtml}</td>";
                  }
                  $rowHtml .= "<td class='py-0 text-center border-end'>{$seasonHtml}</td>";
                  
                  foreach ($playPositions as $pos) {
                      $posPct = 0;
                      $posTimeStr = "";
                      if (isset($pt_stats[$pid]['positions'][$pos]['percentage'])) {
                          $posPct = $pt_stats[$pid]['positions'][$pos]['percentage'];
                          $posTimeStr = calctime($pt_stats[$pid]['positions'][$pos]['time'] ?? 0);
                      }
                      
                      if ($posPct > 0) {
                          $inner = number_format($posPct, 0) . '%';
                          if ($pos == 1) $inner = '<span class="text-warning fw-bold">' . $inner . '</span>';
                          $posHtml = '<span title="Gespeeld: ' . htmlspecialchars($posTimeStr) . '" data-bs-toggle="tooltip" style="cursor:help; border-bottom:1px dotted #ccc;">' . $inner . '</span>';
                      } else {
                          $posHtml = '<span class="text-muted">-</span>';
                      }
                      $rowHtml .= "<td class='py-0 text-center'>{$posHtml}</td>";
                  }
                  
                  $rowHtml .= "</tr>";
                  $gkRatioHtml .= $rowHtml;
              }
              $gkRatioHtml .= '</table></div></div>';
              
              // Group AI results by mins_game
              $aiMinsGroups = [];
              foreach ($dynamic_analysis['player_stats'] as $stat) {
                  if ($stat['is_gk']) continue;
                  $m = (string)$stat['mins_game'];
                  if (!isset($aiMinsGroups[$m])) $aiMinsGroups[$m] = [];
                  $aiMinsGroups[$m][] = "<strong>" . htmlspecialchars(getPlayerName($stat['pid'])) . "</strong>";
              }
              krsort($aiMinsGroups);
          ?>
              <div class="card mb-4 border-info shadow-sm d-print-none" style="border-width: 2px;">
                  <div class="card-header bg-info text-white fw-bold d-flex align-items-center py-2" style="font-size: 0.9rem; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#fairshiftCollapse" aria-expanded="true">
                      <i class="fa-solid fa-lightbulb text-light me-2"></i> EqualPlay Pre-Game Analyse
                      <i class="fa-solid fa-chevron-down ms-auto"></i>
                  </div>
                  <div class="collapse show" id="fairshiftCollapse">
                      <div class="card-body bg-light text-dark p-2">
                          <div class="row g-2">
                              <!-- Linker kolom: Wiskunde & Huidige match -->
                              <div class="col-md-6">
                                  <div class="mb-2 p-1 px-2 bg-white rounded border">
                                      <p class="mb-1" style="font-size: 0.75rem; line-height: 1.2;"><i class="fa-solid fa-calculator text-muted me-1"></i><strong><?= htmlspecialchars($search_format) ?></strong> <span class="text-muted mx-1">|</span> <i class="fa-solid fa-users text-secondary me-1"></i><?= $numFieldPlayers ?> veldspelers <span class="text-muted mx-1">|</span> <i class="fa-solid fa-street-view text-secondary me-1"></i><?= $numFieldPositions ?> posities<?= ($gk_count === 0 ? ' <span class="text-muted mx-1">|</span> <span class="text-primary fw-bold"><i class="fa-solid fa-hands-bubbles ms-1"></i> Roterende Doelman</span>' : '') ?>:</p>
                                      <?php if ($players_extra > 0): ?>
                                      <ul class="mb-0 text-dark" style="font-size: 0.75rem; line-height: 1.2; padding-left: 20px;">
                                          <li><strong><?= $players_extra ?> spelers</strong>: <?= $extra_mins ?>m</li>
                                          <li><strong><?= $players_base ?> spelers</strong>: <?= $base_mins ?>m</li>
                                      </ul>
                                      <?php else: ?>
                                      <p class="mb-0 text-success fw-bold" style="font-size: 0.75rem;"><i class="fa-solid fa-check-circle me-1"></i>Alle spelers spelen exact <?= $base_mins ?>m.</p>
                                      <?php endif; ?>
                                  </div>
                                  
                                  <div class="d-flex flex-column gap-1 mb-2">
                                      <div class="d-flex flex-wrap gap-1">
                                          <?php if (isset($_GET['dynamic']) && $_GET['dynamic'] == 1 && $gk_count === 0): ?>
                                          <div class="d-flex align-items-center p-1 px-2 bg-white rounded border flex-grow-1">
                                              <span class="small text-muted" style="font-size: 0.75rem;"><i class="fa-solid fa-rotate text-info me-1"></i>Doelman roteert</span>
                                          </div>
                                          <?php endif; ?>

                                          <?php
                                          $min_pos_req = (int)($matchData['game']['min_pos'] ?? 0);
                                          if ($min_pos_req > 0):
                                          ?>
                                          <div class="d-flex align-items-center p-1 px-2 bg-white rounded border flex-grow-1">
                                              <span class="small text-muted" style="font-size: 0.75rem;"><i class="fa-solid fa-people-arrows text-warning me-1"></i>Min. <?= $min_pos_req ?> pos/speler</span>
                                          </div>
                                          <?php endif; ?>
                                      </div>
                                  </div>
                                  
                                  <?php if ($players_extra > 0): ?>
                                  <p class="mb-2 mt-2 fw-bold" style="font-size: 0.8rem;"><i class="fa-solid fa-robot text-success me-1"></i> EqualPlay Indeling:</p>
                                  <?php 
                                  $is_first = true;
                                  foreach ($aiMinsGroups as $mins => $names): 
                                      $color = $is_first ? "text-success" : "text-danger";
                                      $icon = $is_first ? "fa-arrow-up" : "fa-arrow-down";
                                      $is_first = false;
                                  ?>
                                  <div class="p-2 bg-white rounded border mb-2">
                                      <p class="mb-1 fw-bold <?= $color ?>" style="font-size: 0.8rem;"><i class="fa-solid <?= $icon ?> me-1"></i><?= $mins ?> minuten</p>
                                      <p class="mb-0 text-muted" style="font-size: 0.75rem;">Toegewezen aan: <?= implode(', ', $names) ?></p>
                                  </div>
                                  <?php endforeach; ?>
                                  <?php endif; ?>
                                  
                                  <?= $lastMatchHtml ?>
                              </div>
                              
                              <!-- Rechter kolom: Historiek en Vorige Wedstrijden -->
                              <div class="col-md-6">
                                  <div class="d-flex align-items-center p-1 px-2 bg-white rounded border mb-2">
                                      <div class="form-check form-switch mb-0 w-100 d-flex justify-content-between align-items-center">
                                          <label class="form-check-label small fw-bold text-dark mb-0" style="font-size: 0.75rem;" for="toggleCompensateFairshift">Compenseer speeltijd vorige match?</label>
                                          <input class="form-check-input ms-2" style="transform: scale(0.8); margin-top:0;" type="checkbox" id="toggleCompensateFairshift" value="1" <?= $compensate ? 'checked' : '' ?> onchange="window.location.href='?generate=1&dynamic=1<?= $use_period ? '&use_period=1' : '' ?>&compensate=' + (this.checked ? '1' : '0')">
                                      </div>
                                  </div>
                                  <?php if ($activePeriod): ?>
                                  <div class="d-flex align-items-center p-1 px-2 bg-white rounded border mb-2">
                                      <div class="form-check form-switch mb-0 w-100 d-flex justify-content-between align-items-center">
                                          <label class="form-check-label small fw-bold text-dark mb-0" style="font-size: 0.75rem;" for="togglePeriodFairshift">Gebruik periode-stats i.p.v. seizoen?</label>
                                          <input class="form-check-input ms-2" style="transform: scale(0.8); margin-top:0;" type="checkbox" id="togglePeriodFairshift" value="1" <?= $use_period ? 'checked' : '' ?> onchange="window.location.href='?generate=1&dynamic=1<?= $compensate ? '&compensate=1' : '&compensate=0' ?>&use_period=' + (this.checked ? '1' : '0')">
                                      </div>
                                  </div>
                                  <?php endif; ?>
                                  <?= $statsBasisHtml ?>
                                  <?= $gkRatioHtml ?>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          <?php endif; ?>
      <?php endif; ?>

      <?php if (!empty($top_selected_options) && !defined('PUBLIC_SHARE_MODE')): ?> 
      <ul class="nav nav-tabs mt-4 mb-3 d-print-none justify-content-center" id="lineupTabs" role="tablist">
          <?php foreach ($top_selected_options as $t_idx => $t_opt): 
                  $t_lineup = $t_opt['team'];
                  // Compileer tab-specifieke titel op basis van de elementen uit deze exacte loop interpolatie
                  $tab_title = $wedstrijd . "_" . count($t_lineup->playernames) . "sp_" . $t_opt["total_points"] . "_" . substr(md5(implode(", ", $t_lineup->playernames)),0,8) . "_" . $t_opt["ws_id"] . " (" . $t_lineup->rating . "%)";
          ?>
              <li class="nav-item d-print-none" role="presentation">
                  <button class="nav-link <?= $t_idx == 0 ? 'active' : '' ?>" id="tab-btn-<?= $t_idx ?>" data-title="<?= htmlspecialchars($tab_title) ?>" data-bs-toggle="tab" data-bs-target="#tab-pane-<?= $t_idx ?>" type="button" role="tab">
                      Optie <?= $t_idx + 1 ?> <span class="badge bg-secondary"><?= round($t_opt['rating'], 2) ?>%</span>
                  </button>
              </li>
          <?php endforeach; ?>
          
          <?php if ($shuffle_type === 'coach'): ?>
              <?php if (isset($preview_lineup) && $preview_lineup): ?>
                  <li class="nav-item d-print-none" role="presentation">
                      <?php if (isset($_GET['preview'])): ?>
                      <a href="<?= build_url($base_url, ['wedstrijd' => $gameId]) ?>" class="btn btn-secondary ms-3 btn-sm mt-1">
                          <i class="fa-solid fa-xmark"></i> Sluit Preview
                      </a>
                      <a href="/games/<?= $gameId ?>/editor?preview=<?= $_GET['preview'] ?>" class="btn btn-warning ms-2 btn-sm mt-1">
                          <i class="fa-solid fa-pen"></i> Bewerk Schema
                      </a>
                      <?php endif; ?>
                      <button class="btn btn-success ms-2 btn-sm mt-1" onclick="setFinalLineup(<?= $gameId ?>, <?= $preview_lineup['id'] ?>)">
                          <i class="fa-solid fa-check"></i> Maak Definitief
                      </button>
                  </li>
              <?php elseif (!isset($locked_lineup) && isset($_GET['dynamic'])): 
                  $namen_tonen_str = implode(", ", array_map('getPlayerName', array_keys($lineup->playerindex)));
                  $dynamic_json_str = isset($dynamic_schema_parts) ? json_encode($dynamic_schema_parts) : '""';
              ?>
                  <li class="nav-item d-print-none" role="presentation">
                      <button class="btn btn-sm btn-outline-success ms-3 mt-1" onclick='savePreselection(this, <?= json_encode((int)$gameId) ?>, <?= json_encode($selected['ws_id'] ?? 0) ?>, <?= json_encode(implode(',', array_keys($lineup->playerindex))) ?>, <?= json_encode((float)($t_opt['rating'] ?? 0)) ?>, <?= json_encode($namen_tonen_str) ?>, <?= isset($_GET["dynamic"]) && $_GET["dynamic"] == 1 ? json_encode("EqualPlay AI") : json_encode("ProLineup AI") ?>, <?= $dynamic_json_str ?>)'>
                          <i class="fa-solid fa-floppy-disk"></i> Bewaar EqualPlay Schema in Voorselecties
                      </button>
                  </li>
              <?php else: 
                  $can_unlock = false;
                  $finalizer_name = "een coach";
                  if (isset($locked_lineup)) {
                      $finalizer_name = $locked_lineup['finalizer_name'] ?? 'een coach';
                      if (!isset($locked_lineup['finalized_by_user_id']) || 
                          $locked_lineup['finalized_by_user_id'] == $_SESSION['user_id'] || 
                          (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin')) {
                          $can_unlock = true;
                      }
                  } else {
                      $can_unlock = true; // Fallback if somehow locked_lineup is null here
                  }
              ?>
                  <?php if (!defined('PUBLIC_SHARE_MODE')): ?>
                  <li class="nav-item d-print-none" role="presentation" id="lineup-action-buttons">
                      <?php if ($can_unlock): ?>
                      <button class="btn btn-warning ms-3 btn-sm mt-1" onclick="unlockLineups(<?= $gameId ?>)">
                          <i class="fa-solid fa-lock-open"></i> Ontgrendel Wedstrijd
                      </button>
                      <?php else: ?>
                      <span class="d-inline-block ms-3 mt-1" tabindex="0" data-bs-toggle="tooltip" title="Definitief gemaakt door <?= htmlspecialchars($finalizer_name) ?>. Enkel deze coach kan de wedstrijd ontgrendelen.">
                          <button class="btn btn-secondary btn-sm disabled" style="pointer-events: none;">
                              <i class="fa-solid fa-lock"></i> Wedstrijd Vergrendeld
                          </button>
                      </span>
                      <?php endif; ?>
                      <button onclick="window.print()" class="btn btn-outline-danger btn-sm ms-2 mt-1">
                          <i class="fa-solid fa-file-pdf me-1"></i> Opslaan als PDF
                      </button>
                      
                      <div class="btn-group ms-2 mt-1">
                          <button type="button" class="btn btn-primary btn-sm" id="btnShareLink" onclick="generateShareLink(<?= $gameId ?>, 24)">
                              <i class="fa-solid fa-share-nodes me-1"></i> Deel met Ouders (24u)
                          </button>
                          <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Kies tijdslimiet</span>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header">Kopieer Magic Link</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="generateShareLink(<?= $gameId ?>, 24, this); return false;">Geldig voor 24 uur</a></li>
                            <li><a class="dropdown-item" href="#" onclick="generateShareLink(<?= $gameId ?>, 72, this); return false;">Geldig voor 3 dagen</a></li>
                            <li><a class="dropdown-item" href="#" onclick="generateShareLink(<?= $gameId ?>, 168, this); return false;">Geldig voor 1 week</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="generateShareLink(<?= $gameId ?>, 0, this); return false;">Altijd geldig</a></li>
                          </ul>
                      </div>
                  </li>
                  <?php endif; ?>
              <?php endif; ?>
          <?php endif; ?>
          
          <?php if (!defined('PUBLIC_SHARE_MODE')): ?>
          <li class="nav-item d-print-none" role="presentation">
              <button class="nav-link" id="tab-events-btn" data-title="Wedstrijdverslag" data-bs-toggle="tab" data-bs-target="#tab-events" type="button" role="tab">
                  <i class="fa-solid fa-list-check text-primary me-1"></i> Wedstrijdverslag 
                  <span class="badge bg-danger rounded-pill d-none ms-1" id="badge-unconfirmed-events"></span>
              </button>
          </li>
          <?php endif; ?>
      </ul>
      <?php endif; // End !empty($top_selected_options) ?>
      
      <?php if (isset($preview_lineup) && $preview_lineup && !defined('PUBLIC_SHARE_MODE')): ?>
          <div class="alert alert-info text-center d-print-none">
              <i class="fa-solid fa-eye"></i> Je bekijkt momenteel een opgeslagen voorselectie in preview modus. Klik op 'Maak Definitief' hierboven om deze op te slaan als finale selectie.
              <?php if (!isset($_GET['preview'])): ?>
                  <br><small>(Automatisch ingeladen om wachttijden te vermijden. Klik <a href="<?= build_url($base_url, ['wedstrijd' => $gameId, 'generate' => 1]) ?>" class="fw-bold">hier</a> om toch compleet nieuwe opties te genereren.)</small>
              <?php endif; ?>
          </div>
      <?php endif; ?>
      
      <?php if (!empty($top_selected_options)): ?>
      <div class="tab-content <?php echo defined('PUBLIC_SHARE_MODE') ? '' : 'mt-3'; ?>" id="lineupTabsContent">
      <?php foreach ($top_selected_options as $tab_idx => $t_opt): 
          $lineup = $t_opt['team'];
          $selected = $t_opt;
      ?>
      <div class="tab-pane fade <?= $tab_idx == 0 ? 'show active d-print-block' : 'd-print-none' ?>" id="tab-pane-<?= $tab_idx ?>" role="tabpanel" tabindex="0">
      
      <div class="d-print-none text-center mb-4 mt-3">
          <?php if (!defined('PUBLIC_SHARE_MODE')): ?>
          <button type="button" class="btn btn-sm btn-outline-info shadow-sm fw-bold" onclick="document.getElementById('posities-<?= $tab_idx ?>').scrollIntoView({behavior: 'smooth'})">
              <i class="fa-solid fa-clock-rotate-left me-1"></i> Bekijk Positieverdeling
          </button>
          <?php endif; ?>
          
          <?php if (!defined('PUBLIC_SHARE_MODE') && !$locked_lineup): ?>
              <?php if ($shuffle_type !== 'coach'): 
                  $namen_tonen_str = implode(", ", array_map('getPlayerName', array_keys($lineup->playerindex)));
              ?>
                  <button class="btn btn-sm btn-outline-success ms-2 shadow-sm" onclick='savePreselection(this, <?= json_encode((int)$gameId) ?>, <?= json_encode($selected['ws_id'] ?? 0) ?>, <?= json_encode(implode(',', array_keys($lineup->playerindex))) ?>, <?= json_encode((float)($t_opt['rating'] ?? 0)) ?>, <?= json_encode($namen_tonen_str) ?>, <?= isset($_GET["dynamic"]) && $_GET["dynamic"] == 1 ? json_encode("EqualPlay AI") : json_encode("ProLineup AI") ?>)'>
                      <i class="fa-solid fa-floppy-disk"></i> Bewaar #<?= $tab_idx + 1 ?> in Voorselecties
                  </button>
              <?php endif; ?>
              
              <?php if (Permissions::hasPermission(Permissions::PERM_USE_THEORY_WIZARD)): ?>
                  <a href="/schema_editor?game_id=<?= $gameId ?>&schema_id=<?= $selected['ws_id'] ?>&volgorde=<?= urlencode(implode(',', array_keys($lineup->playerindex))) ?>" class="btn btn-sm btn-outline-warning ms-2 shadow-sm">
                      <i class="fa-solid fa-pen-ruler"></i> Bewerk <?= $shuffle_type !== 'coach' ? 'dit' : 'Huidig' ?> Schema
                  </a>
              <?php endif; ?>
          <?php endif; ?>
      </div>
      
      <?php
    
        if ($building_lineup){
          pr($lineup->playernames,"player index");
        }
        
        $newpage = "";
        ?>
       <div class="row">
         <div class="col-12">
            <p class="mb-2">
              <?php 
                $namen_tonen = array_map('getPlayerName', array_keys($lineup->playerindex));
                echo "$total_players spelers aanwezig: " . implode(", ", $namen_tonen);
                if (!defined('PUBLIC_SHARE_MODE')) {
                    echo "<small class='d-print-none'> // Schema " . htmlspecialchars($selected["ws_id"]) . " &middot; " . $lineup->rating . "%</small>"; 
                }
                if (isset($selected["run"]) && $selected["run"] > 0 && !defined('PUBLIC_SHARE_MODE')) {
                  echo "<br><small class='text-muted d-print-none'><i class='fa-solid fa-code-branch'></i> Berekende geldige combinaties: " . number_format($selected["run"], 0, ',', '.') . "</small>"; 
                }
              ?>
            </p>
          </div>
        </div>
        
        <div id="game-1" class="scheme-with-<?php echo $lineup->available_players; ?>-players ">
         <?php
          $last_game_counter = 1;
          $total_duration = 0;
      
          $next_bench = array();
          $next_bench_keys = array();
        
          $game_block_labels = json_decode($matchData['game']['block_labels'] ?? '[]', true) ?: [];
        
          foreach ($lineup->game_parts as $game_counter => $game_parts){
            if ($game_counter == 40) {
             ?><hr style="margin-top: 30px" class="<?php echo $lineup->available_players > 5 ? "new-print-page":"";?>"><?php
            }
            if ($game_counter == 50) {
             ?><hr style="margin-top: 30px" class="<?php echo $lineup->available_players > 5 ? "new-print-page":"";?>"><?php
            }
            echo "<div class='row'>";
            echo "<div class='col-12'>";

            if (array_key_exists($wedstrijd, $game_titles)) {
              if ($game_counter == 5) {
               ?><hr style="margin-top: 30px" class="new-print-page"><?php
              }
              echo "<h5>Wedstrijd $game_counter &middot; " . $game_titles[$wedstrijd][$game_counter]["title"] . " <small>(" . $game_titles[$wedstrijd][$game_counter]["info"] . ")</small></h5>";  
            } else {
              $block_idx = $game_counter - 1;
              if (!empty($game_block_labels[$block_idx])) {
                  echo "<h5>" . htmlspecialchars($game_block_labels[$block_idx]) . "</h5>";
              } else {
                  echo "<h5>Wedstrijd $game_counter</h5>";
              }
            }
            echo "</div>";
            foreach ($game_parts as $game_idx){
              $part_score = 0;
              $part_max = 0;
              // Score voor dit partje berekenen:
              foreach($lineup->events[$game_idx]["lineup"] as $_pos => $_playerid){
                $part_score += $lineup->events[$game_idx]["duration"] * ($player_scores[$_playerid][$_pos] ?? 0);
                $part_max += $lineup->events[$game_idx]["duration"] * 100;
              }
              if ($building_lineup){
                
                
                $last_bench_order = $lineup->bench_order;
                foreach($last_bench_order as $_k=>$_bplayer){
                  //throw him out
                  if (in_array($_bplayer,$lineup->events[$game_idx]["bench"])){
                    //pecho("removing $_bplayer");
                    unset($last_bench_order[$_k]);
                  }
                }
            
                //pr($lineup->events[$game_idx]["bench"]);
                foreach($lineup->events[$game_idx]["bench"] as $_b){
                  $benchcount[$_b] = ($benchcount[$_b] ?? 0) + 1;                  
                  $benchlist[] = $_b . ": " . $lineup->events[$game_idx]["duration"];
                  array_push($last_bench_order,$_b);
                }
                $lineup->bench_order = $last_bench_order;
                array_push($lineup->bench_order_history,$last_bench_order);
              }
            
            
              $game = $lineup->events[$game_idx];
              /*if (array_key_exists($game_idx+1,$lineup->events)){
                $next_part = $lineup->events[$game_idx+1];
              }*/
            
              //dpr($next_part);
              $nr_of_parts = count($game_parts);
              $nr_of_games = count($lineup->events);
              $smcol_width = 12;
              //$col_width = 12/$nr_of_parts;  
              $col_width = 6;
            
              ?>
              <div class="col-<?php echo $smcol_width ?> col-md-<?php echo $col_width ?> p-2">
                 <div class="border border-secondary p-2 h-100 bg-white text-dark">
                <table width="100%">
                  <tr>  
                    <td align="left" valign="top" rowspan="6">
                      <h4 class="mb-1"><small><?php echo $game["start"]; ?></small></h4>
                      <?php
                      if (array_key_exists("subs",$game) || array_key_exists("positions",$game)){
                        echo "<strong>Wissels:</strong><br/>";
                      } else {
                        if (count($game["bench"])){
                          echo "<strong>Rust:</strong><br/>";
                        }
                      }
                      if ($building_lineup){
                        /* test of alle spelers een plaats hebben */
                        foreach ($lineup->playernames as $_name) {
                          if(!in_array($_name,$game["lineup"]) and !in_array($_name,$game["bench"])){
                            echo "<h1>" . $_name . " zit niet in de selectie</h1>";
                            pr($game["bench"],"bench");
                            dpr($game["lineup"],"lineup");
                          }
                        }
                      
                        /* test of bankspelers niet op het veld staan */
                        $result=array_intersect($game["lineup"],$game["bench"]);
                        if (count($result)){
                         pr($result,"Speler op veld EN op de bank!");
                         pr($game["lineup"],"lineup");
                         dpr($game["bench"],"bench");
                        }
                      
                        /* test of subs identifiers juist zijn ingevuld */
                        if (array_key_exists("subs",$game)){
                            //check in ook effectief op het veld
                            foreach($game["subs"]["in"] as $_pos => $_name){
                              if(!in_array($_name,$game["lineup"])){
                                pr($game["lineup"],$_name . " moet invallen maar zit niet in de lineup");
                              }
                            }
                            //check out ook effectief NIET op het veld
                            foreach($game["subs"]["out"] as $_pos => $_name){
                              if(in_array($_name,$game["lineup"])){
                                pr($game["lineup"],$_name . " moet op de bank maar staat nog in de lineup:");
                                pr($game["bench"],"bench");
                              }
                            }
                          
                            //check out ook effectief op de bank
                            foreach($game["subs"]["out"] as $_pos => $_name){
                              if(!in_array($_name,$game["bench"])){
                                pr($game["lineup"],$_name . " zou op de bank moeten zitten maar staat er niet tussen");
                              }
                            }
                          
                            //check of spelers die in komen de vorige wedstrijd op de bank zaten
                            /* test of bankspelers niet op het veld staan */
                            $result=array_diff($game["subs"]["in"],$previous_game["bench"]);
                            if (count($result)){
                             pr($result,"Spelers die invallen zaten net niet op de bank, subs:");
                             dpr($previous_game["bench"],"bench");
                            }
                          
                            //pr($lineup->playernames);
                            foreach($game["subs"]["in"] as $_pos => $_name){
                              $previous_game = $game;
                              if(!in_array($_name,$game["lineup"])){
                                dpr($game["lineup"],$_name . " moet invallen maar zit niet in de lineup");
                              }
                            }
                          
                         
                        }
                      
                      }
                   
                    
                      $used_indexes = array();
                      $next_bench = array();
                      $next_bench_keys = array();
                      if (array_key_exists("subs",$game)){
                        echo "<ul>";
                        ksort($game["subs"]);
                        //pr($game["subs"]);
                        $in_keys = array_keys($game["subs"]["in"]);
                        $out_keys = array_keys($game["subs"]["out"]);
                        $left = array_diff($in_keys,$out_keys);
                  
                        foreach($game["subs"]["out"] as $pos => $_player){
                          echo "<li>$pos ";
                          if (array_key_exists($pos,$game["subs"]["in"])){
                            echo "<strong>" . getPlayerName($game["subs"]["in"][$pos]);
                          } else {
                            //find player that will come on this position
                            //dpr($game);
                            if (array_key_exists($pos,$game["positions"])){
                              echo getPlayerName($game["positions"][$pos]["player"]);
                              echo " <i class='fa-solid fa-bolt' aria-hidden='true'></i> ";
                            }                      
                          }
                          if (array_key_exists($pos,$game["subs"]["in"])){
                            echo "</strong> ";
                            //echo "<i class='fa-solid fa-arrow-right'></i> ";
                          } 
                          echo "<s>" . getPlayerName($game["subs"]["out"][$pos]) . "</s>";
                          echo "</li>";
                        }
                        foreach($left as $pos){
                          echo "<li><strong>$pos ";
                          if (array_key_exists($pos,$game["subs"]["in"])){
                            echo getPlayerName($game["subs"]["in"][$pos]);
                          } else {
                            //find player that will come on this positions 
                            if (array_key_exists($pos,$game["positions"])){
                              echo getPlayerName($game["positions"][$pos]["player"]);
                            }                      
                          }
                          echo "</strong> ";
                          if (array_key_exists($pos,$game["subs"]["out"])){
                            echo "<s>" . getPlayerName($game["subs"]["out"][$pos]) . "</s>";
                          }
                          echo "</li>";
                        }
                        echo "</ul>";
                        
                        // (Code voor 'blijven staan' verwijderd)
                      
                        if (array_key_exists($game_idx+1,$lineup->events) && array_key_exists("subs",$lineup->events[$game_idx+1])){
                          $next_bench = $lineup->events[$game_idx+1]["subs"]["out"];
                          $next_bench_keys = array_keys($next_bench);
                        }
                      } else {
                        echo "<ul>";
                        foreach($game["bench"] as $_player){
                          if ($game["duration"] > 300) {
                            if (in_array($_player,$next_bench)){
                              pr($next_bench,$_player . " zat net ook al op de bank");  
                            }
                          }
                          echo "<li>" . getPlayerName($_player) . "</li>";
                        }
                        echo "</ul>";
                        // er zijn geen subs ==> font styling op diegene die er uit gaan
                        // haal next game op indien het bestaat
                        if (array_key_exists($game_idx+1,$lineup->events) && array_key_exists("subs",$lineup->events[$game_idx+1])){
                          $next_bench = $lineup->events[$game_idx+1]["subs"]["out"];
                          $next_bench_keys = array_keys($next_bench);
                        }
                      }
                      ?>
                    </td>

                    <td align="center" colspan="3" class="lineup-col <?php echo in_array(9,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">9</span><br/><?php echo getPlayerName($game['lineup'][9] ?? 0); ?></td>
                  </tr>
                  <tr>
                    <td align="center" class="lineup-col <?php echo in_array(11,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">11</span><br/><?php echo getPlayerName($game['lineup'][11] ?? 0); ?></td>
                    <td align="center" class="lineup-col"></td>
                    <td align="center" class="lineup-col <?php echo in_array(7,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">7</span><br/><?php echo getPlayerName($game['lineup'][7] ?? 0); ?></td>
                  </tr>
                  <tr>
                    <td align="center" colspan="3" class="lineup-col <?php echo in_array(10,$next_bench_keys) ? "fw-bold" : "";?>">
                      <?php if (array_key_exists(10,$game['lineup'])) { ?>
                      <span class="pos-num">10</span><br/><?php echo getPlayerName($game['lineup'][10]); ?>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr>
                    <td align="center" class="lineup-col <?php echo in_array(5,$next_bench_keys) ? "fw-bold" : "";?>">
                      <?php if (array_key_exists(5,$game['lineup'])) { ?>
                      <span class="pos-num">5</span><br/><?php echo getPlayerName($game['lineup'][5]); ?>
                      <?php } ?></td>
                    <td align="center" class="lineup-col"></td>
                    <td align="center" class="lineup-col <?php echo in_array(2,$next_bench_keys) ? "fw-bold" : "";?>">
                      <?php 
                      if (array_key_exists(2,$game['lineup'])) { ?>
                      <span class="pos-num">2</span><br/><?php echo getPlayerName($game['lineup'][2]); ?>
                      <?php } ?>
                    </td>
                  </tr>
                
                  <tr><td align="center" colspan="3" class="lineup-col <?php echo in_array(4,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">4</span><br/><?php echo getPlayerName($game['lineup'][4] ?? 0); ?></td></tr>
                  <tr><td align="center" colspan="3" class="lineup-col <?php echo in_array(1,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">1</span><br/><?php echo getPlayerName($game['lineup'][1] ?? 0); ?></td></tr>
            
                </table>
          
                <?php 
                
                if ($show_part_score){
                  // Stap 1: Bereken het percentage (alleen als $part_max groter is dan 0)
                  if ($part_max > 0) {
                      $percentage = ($part_score / $part_max) * 100;
                  } else {
                      $percentage = 0; // Voorkom "Division by zero" error
                  }
                  echo number_format($percentage, 2, ',', '.') . '%';
                
                }
                if($building_lineup && isset($previous_game)) {
                    $pos_wissels = array();
    
                    // Loop door de huidige opstelling
                    foreach ($game["lineup"] as $pos => $speler) {
                        // Check of de speler ook in de vorige opstelling stond
                        $vorige_pos = array_search($speler, $previous_game["lineup"]);
        
                        // Als hij erin stond, maar op een andere positie
                        if ($vorige_pos !== false && $vorige_pos != $pos) {
                            $pos_wissels[] = "<strong>" . getPlayerName($speler) . "</strong>: van $vorige_pos naar $pos";
                        }
                    }

                    // Toon de lijst met positiewissels
                    echo "<div style='background: #e1f5fe; padding: 10px; border: 1px solid #b3e5fc; margin-top: 10px;'>";
                    echo "<strong><i class='fa-solid fa-arrows-split-up-and-left'></i> Positiewissels (bleven in het veld):</strong><br/>";
                    if (!empty($pos_wissels)) {
                        echo "<span style='color: #01579b;'>" . implode(" | ", $pos_wissels) . "</span>";
                    } else {
                        echo "<span class='text-muted'>Geen interne verschuivingen.</span>";
                    }
                    echo "</div>";
                }
                
                if($building_lineup) { 
                    // 1. Bereken wie er nog niet op de bank heeft gezeten
                    // We pakken alle spelers uit de index en kijken welke NIET in benchcount staan
                    $all_players = array_keys($lineup->playerindex);
                    $rested_players = array_keys($benchcount ?? []);
                    $not_rested = array_diff($all_players, $rested_players);

                    // 2. Toon de benchcount (bestaand)
                    pr($benchcount, "Aantal keer gerust per speler");

                    // 3. Toon de 'Nog niet gerust' lijst
                    echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeeba; margin-top: 10px;'>";
                    echo "<strong>Nog NIET op de bank gezeten:</strong><br/>";
                    if (!empty($not_rested)) {
                        echo "<span style='color: #856404;'>" . implode(", ", $not_rested) . "</span>";
                    } else {
                        echo "<span style='color: green;'>✅ Iedereen heeft al minstens één keer gerust.</span>";
                    }
                    echo "</div>";
                }
                ?>
          
              </div>
              </div>
        
      
        
              <?php
              $total_duration = $total_duration + $game["duration"];
              $previous_game = $game;
            
            }
            echo "</div>"; // Closes inner row and outer game container
          }
          
          if ($building_lineup && ($game_idx <= count($lineup->events)-1)){
            $last_bench_history = array_pop($lineup->bench_order_history);
            foreach($last_bench_history as $player_on_the_bench){
              if (isset($lineup->playerinfo[$player_on_the_bench]) && is_array($lineup->playerinfo[$player_on_the_bench]) && array_key_exists("pos",$lineup->playerinfo[$player_on_the_bench])){
                $available_positions_for_player = $lineup->playerinfo[$player_on_the_bench]["pos"];
              } else {
                // indien nog geen posities gedefinieerd dan is alles ok.
                $available_positions_for_player = $lineup->positions;
              }
            
              echo $lineup->playerindex[$player_on_the_bench];
              echo " &middot; " . $player_on_the_bench;
              echo  " (" . implode(",",$available_positions_for_player) .  ") ";
              echo $lineup->time_since_last_sub[$game_idx][$player_on_the_bench] . " sec -- pt: " . $lineup->time_played_by_index[$game_idx][$player_on_the_bench];
              echo "<br/>";
            }
          }
        ?>
      
        </div>
        <?php if (!defined('PUBLIC_SHARE_MODE')): ?>
        <div class="timetable row mt-4 do_not_break pt-4 <?php echo !$show_position_stats ? 'new-print-page' : ''; ?>" id="posities-<?= $tab_idx ?>">
          <div class="col-12 d-print-none text-end mb-2">
              <a href="javascript:void(0)" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-up"></i> Terug naar schema</a>
          </div>
          <?php
          
          $offset_class = "";
          switch(count($lineup->time_in_position)){
            case 5:
              $timecolwidth = 2;
              $offset_class = "offset-md-1 ";
              break;
            case 6:
            case 7:
            case 9:
              $timecolwidth = 4;
              break;
            case 11:
              $timecolwidth = 2;
              break;
            default:
              $timecolwidth = 3;
          }
        
          //DEBUG array
          $playertime_to_print = array();

          $sorted_time_in_position = $lineup->time_in_position;
          uasort($sorted_time_in_position, function($a, $b) {
              $count_a = 0;
              foreach($a as $k => $v) { if ($v > 0 && $k !== 'total') $count_a++; }
              $count_b = 0;
              foreach($b as $k => $v) { if ($v > 0 && $k !== 'total') $count_b++; }
              return $count_b <=> $count_a; // Aflopend sorteren
          });

          foreach($sorted_time_in_position as $player=>$playtime) { 
          
            $score_for_player = 0;
            $playertime_to_print[] = "\"" . $player ."\" => " . $lineup->total_playtime[$player];
            $available_positions_for_player = array();
            if (isset($lineup->playerinfo[$player]) && is_array($lineup->playerinfo[$player]) && array_key_exists("pos",$lineup->playerinfo[$player])){
              $available_positions_for_player = $lineup->playerinfo[$player]["pos"];
            } else {
              // indien nog geen posities gedefinieerd dan is alles ok.
              $available_positions_for_player = $lineup->positions;
            }
            ?>
            <div class="col-6 col-sm-6 col-md-<?php echo $timecolwidth;?> <?php echo $offset_class; ?>">
              <h5 class="d-flex justify-content-between align-items-center mb-3 ps-2">
                <span class="text-primary"><?php echo getPlayerName($player); ?></span>
                <span class="badge bg-primary rounded-pill"><?php echo calctime($lineup->total_playtime[$player]);?></span>
              </h5>
              <ul class="list-group mb-3">
                <?php foreach($playtime as $pos=>$seconds){ 
                  if (is_numeric($pos)){
                    $score_for_player += ($seconds * $player_scores[$player][$pos]); // aantal seconden in die positie maal de score voor die positie. 
                  }
                  if ($seconds > 0 && $pos != "total"){ 
                    $extra_class = "";
                  
                    $out_of_position = 0;
                    if ($pos !="bench" && !in_array($pos,$available_positions_for_player)){
                      //dpr($available_positions_for_player,$pos);
                      $extra_class = "fst-italic";
                    }
                  ?>
                  <li class="list-group-item d-flex justify-content-between <?php echo $extra_class; ?> lh-sm">
                    <div>
                      <h6 class="my-0"><?php echo $pos; ?></h6>
                    </div>
                    <span class="text-muted"><?php echo calctime($seconds) ; ?></span>
                  </li>
                <?php } 
                }
              
                if ($building_lineup == 1) {
                  ?>
                  <li class="list-group-item d-flex justify-content-between <?php echo $extra_class; ?> lh-sm">
                    <div>
                      <h6 class="my-0">Rating <?php echo getPlayerName($player); ?></h6>
                    </div>
                    <span class="text-muted"><?php echo round($score_for_player/$playtime["total"]); ?></span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between bg-light lh-sm">
                    <div>
                      <h6 class="my-0">Pos: <?php echo implode(",",$available_positions_for_player); ?></h6>
                    </div>
                  </li>
                  <?php
                }
                ?>
              
              </ul>
            </div>
          
            <?php
            $offset_class = ""; //enkel eerste kolom
           } 
           ?>
          </div>
      <?php if ($show_position_stats) {?> 
      
      <div class="row mt-4 timetable new-print-page d-none d-md-flex" style="">
        <div class="col">
          <table id="player-overview" class="table table-bordered table-sm table-hover text-center align-middle bg-white text-dark border-secondary">
            <tr>
              <thead>
                <th scope="col">#</th>
                <th scope="col">speeltijd</th>
                <th scope="col">speler</th>
                <th scope="col"><i class="fa-solid fa-clock"></i></th>
                <th scope="col"><i class="fa-solid fa-clock"></i> - 1</th>
                <?php foreach ($lineup->positions as $pos): ?>
                  <th scope="col"><?= $pos ?></th>
                <?php endforeach; ?>
              </thead>
            </tr>
            <?php             
            foreach($lineup->playernames as $i=> $player){ 
              $historical_key = $global_playerinfo[$player]['id'] ?? $player;
              
                $score_for_player = 0;
                
                // Check eerst of de speler wel in de stats zit (voor het geval Franklin nieuw is of eruit lag)
                if (isset($pt_stats[$historical_key]) && isset($pt_stats[$historical_key]["played"]) && isset($pt_stats[$historical_key]["available"]) && $pt_stats[$historical_key]["available"] > 0) {
                    $pt_play_perc = ($pt_stats[$historical_key]["played"] / $pt_stats[$historical_key]["available"]) * 100;
                } else {
                    $pt_play_perc = 0; 
                }           ?>
              <tr>
                <td><?php echo $i+1; ?></td>
                <td><strong><?php echo round($pt_play_perc,1);?>%</strong> <small>(<?php echo round(($pt_stats[$historical_key]["played"] ?? 0)/60)  ."/" . round(($pt_stats[$historical_key]["available"] ?? 0)/60);?>)</small></td>
                <td><?php echo getPlayerName($player); ?></td>
                <td title="speeltijd deze wedstrijd"><?php echo calctime($lineup->total_playtime[$player]);?></td>
                <?php
                // data vorige wedstrijd ophalen
                
                ?>
                
                <td title="speeltijd <?php echo $pt_stats[$historical_key]["previous_game_title"] ?? '';?>">
                  <small><i>
                    <?php 
                            if (isset($pt_stats[$historical_key]) && isset($pt_stats[$historical_key]["previous_game_key"]) && isset($pt_stats[$historical_key]["time_per_game"][$pt_stats[$historical_key]["previous_game_key"]])) {
                                echo calctime($pt_stats[$historical_key]["time_per_game"][$pt_stats[$historical_key]["previous_game_key"]]); 
                            } else {
                                echo "-"; // Franklin krijgt hier een streepje
                            }
                        ?>
                        </i></small>
                  <?php 

                    //TODO kijken of $vorige_wedstrijd nog nodig is...
                  /*
                  if ($vorige_wedstrijd !== null && isset($vorige_wedstrijd["players"][$player])): ?>
                    <small><i><?php echo calctime($vorige_wedstrijd["players"][$player]); ?></i></small>

                  <?php else: ?>
                    /
                  <?php endif; 
                  */
                  ?>
                </td>
                <?php foreach ($lineup->positions as $pos): ?>
                  <td>
                    <?php if (isset($pt_stats[$historical_key]["positions"][$pos])) { ?>
                    <span title="<?php echo calctime($pt_stats[$historical_key]["positions"][$pos]["time"]); ?>"><?php echo $pt_stats[$historical_key]["positions"][$pos]["percentage"]; ?>%</span>
                    <?php } ?>
                    
                    <?php // isset($lineup->time_in_position[$player][$pos]) ? calctime($lineup->time_in_position[$player][$pos]) : 0 ?></td>
                <?php endforeach; ?>
              </tr>
              <?php
              /* 

                - speeltijd deze wedstrijd: <span class="badge bg-primary rounded-pill"><?php echo calctime($lineup->total_playtime[$player]);?></span> 
                - naam <?php echo $player; ?>
                $lineup->time_in_position[$player][$pos]
              

                */  
              ?>
            <?php }
           ?>

          </table>
        </div>
      </div>
    
    <?php } ?>
    <?php endif; ?>
     

      
          </div> <!-- End tab-pane -->
      <?php endforeach; ?>
      
      <?php if (!defined('PUBLIC_SHARE_MODE')): ?>
      <div class="tab-pane fade" id="tab-events" role="tabpanel">
          <?php require_once __DIR__ . '/events_dashboard.php'; ?>
      </div>
      <?php endif; ?>
      
      </div> <!-- End tab-content -->
      <?php endif; ?>
      
    
      
    </div> <!-- End container -->

    <script>
    function savePreselection(btnElem, gameId, schemaId, playerOrder, score, playerNamesStr, toolName, dynamicJson = null) {
        var defaultHtml = btnElem.innerHTML;
        btnElem.innerHTML = '<i class="feather-check"></i> Aan het opslaan...';
        btnElem.disabled = true;

        var fd = new FormData();
        fd.append('action', 'save_preselection');
        fd.append('game_id', gameId);
        fd.append('schema_id', schemaId);
        fd.append('player_order', playerOrder);
        fd.append('score', score);
        if (toolName) {
            fd.append('generator_tool', toolName);
        }
        if (dynamicJson) {
            fd.append('dynamic_json', JSON.stringify(dynamicJson));
        }
        
        fetch('/api/api_save_lineup.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'success') {
                btnElem.innerHTML = '<i class="feather-check"></i> Opgeslagen (Voorselectie)';
                btnElem.classList.remove('btn-outline-success');
                btnElem.classList.add('btn-success');
                
                // Voeg dynamisch toe aan de tabel
                var tbody = document.getElementById('saved-lineups-tbody');
                var container = document.getElementById('saved-lineups-container');
                if (tbody && container) {
                    var newRow = document.createElement('tr');
                    newRow.id = 'sl-row-' + data.lineup_id;
                    
                    var scoreFormatted = parseFloat(score).toFixed(2);
                    
                    newRow.innerHTML = `
                        <td class="align-middle"><strong>#${schemaId}</strong></td>
                        <td class="align-middle">${scoreFormatted}%</td>
                        <td class="align-middle text-muted small">${playerNamesStr}</td>
                        <td class="text-end align-middle">
                            <a href="/games/${gameId}/lineup?preview=${data.lineup_id}" class="btn btn-sm btn-info text-white">
                                <i class="fa-solid fa-eye"></i> Bekijk
                            </a>
                            <button class="btn btn-sm btn-success ms-1" onclick="setFinalLineup(${gameId}, ${data.lineup_id})">
                                <i class="fa-solid fa-check"></i> Maak Definitief
                            </button>
                            <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteLineup(${gameId}, ${data.lineup_id})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    `;
                    
                    tbody.appendChild(newRow);
                    container.style.display = ''; // Maak container zichtbaar indien verborgen
                }
                
            } else {
                alert("Fout: " + data.message);
                btnElem.innerHTML = defaultHtml;
                btnElem.disabled = false;
            }
        }).catch(e => {
            alert("Er liep iets mis bij het opslaan: " + e);
            btnElem.innerHTML = defaultHtml;
            btnElem.disabled = false;
        });
    }

    function unlockLineups(gameId) {
        if (!confirm("Zeker dat je deze wedstrijd wil ontgrendelen?")) return;
        var fd = new FormData();
        fd.append('action', 'unlock');
        fd.append('game_id', gameId);
        fetch('/api/api_save_lineup.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'success') {
                window.location.href = '/games/' + gameId + '/schema';
            } else {
                alert(data.message || "Er liep iets mis.");
            }
        });
    }

    function generateShareLink(gameId, hours, btnElem = null) {
        var originalText = '';
        if (btnElem) {
            originalText = btnElem.innerHTML;
            btnElem.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Bezig...';
        } else {
            var mainBtn = document.getElementById('btnShareLink');
            if (mainBtn) {
                originalText = mainBtn.innerHTML;
                mainBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Bezig...';
            }
        }
        
        var fd = new FormData();
        fd.append('game_id', gameId);
        fd.append('expires_in', hours);
        
        fetch('/ajax/generate_share_link.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (btnElem) btnElem.innerHTML = originalText;
            var mainBtn = document.getElementById('btnShareLink');
            
            if (data.success) {
                if (typeof gtag === 'function') {
                    gtag('event', 'share_link_generated', { 'duration_hours': hours });
                }
                navigator.clipboard.writeText(data.link).then(() => {
                    if (mainBtn) {
                        mainBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Link Gekopieerd!';
                        mainBtn.classList.remove('btn-primary');
                        mainBtn.classList.add('btn-success');
                        setTimeout(() => {
                            mainBtn.innerHTML = '<i class="fa-solid fa-share-nodes me-1"></i> Deel met Ouders';
                            mainBtn.classList.remove('btn-success');
                            mainBtn.classList.add('btn-primary');
                        }, 3000);
                    }
                }).catch(err => {
                    alert("Kopiëren mislukt. Hier is de link: " + data.link);
                    if (mainBtn) mainBtn.innerHTML = originalText;
                });
            } else {
                alert("Fout: " + data.error);
                if (mainBtn) mainBtn.innerHTML = originalText;
            }
        }).catch(e => {
            alert("Er is een fout opgetreden.");
            if (btnElem) btnElem.innerHTML = originalText;
            var mainBtn = document.getElementById('btnShareLink');
            if (mainBtn) mainBtn.innerHTML = originalText;
        });
    }

    function setFinalLineup(gameId, lineupId) {
        if (!confirm("Maak deze opstelling definitief. Meteen renderen als Coach Mode?")) return;
        var fd = new FormData();
        fd.append('action', 'set_final');
        fd.append('game_id', gameId);
        fd.append('lineup_id', lineupId);
        fetch('/api/api_save_lineup.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (typeof gtag === 'function') {
                gtag('event', 'lineup_finalized');
            }
            window.location.href = '/games/' + gameId + '/lineup';
        });
    }

    function deleteLineup(gameId, lineupId) {
        if (!confirm("Deze voorselectie weggooien?")) return;
        var fd = new FormData();
        fd.append('action', 'delete');
        fd.append('game_id', gameId);
        fd.append('lineup_id', lineupId);
        fetch('/api/api_save_lineup.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            window.location.href = '/games/' + gameId + '/schema';
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        var titleDisplay = document.getElementById("dynamic-page-title");
        var tabs = document.querySelectorAll("#lineupTabs button[data-bs-toggle='tab']");
        tabs.forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                var newTitle = e.target.getAttribute("data-title");
                // titleDisplay.innerText = newTitle; // Laten we overgeslagen: de h5 titel blijft kort voor mobiele weergave
                if (newTitle) {
                    document.title = newTitle; // Zet wel de document title up-to-date voor PDF generatie / Printing!
                }
                
                var actionBtns = document.getElementById("lineup-action-buttons");
                if (actionBtns) {
                    var targetId = e.target.getAttribute("data-bs-target");
                    if (targetId === "#tab-events") {
                        actionBtns.style.display = "none";
                    } else {
                        actionBtns.style.display = "block";
                    }
                }
                
                // Save active tab
                var targetId = e.target.getAttribute("data-bs-target");
                if (targetId) {
                    localStorage.setItem('activeLineupTab_<?= $gameId ?>', targetId);
                }
            });
        });
        
        // Restore active tab
        var activeTab = localStorage.getItem('activeLineupTab_<?= $gameId ?>');
        if (activeTab) {
            var tabBtn = document.querySelector("#lineupTabs button[data-bs-target='" + activeTab + "']");
            if (tabBtn) {
                var bsTab = new bootstrap.Tab(tabBtn);
                bsTab.show();
            }
        }
    });
    </script>
    
    <?php if (isset($_GET['print']) && $_GET['print'] == 1): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Korte timeout om fonts, icoontjes en layouting toe te staan om te renderen
        setTimeout(function() {
            window.print();
        }, 500);
    });
    </script>
    <?php endif; ?>
    
    </main>

<?php if ($show_pt_array) { 
    $selection_used = implode(",", array_keys($lineup->playerindex));
    $playertime_to_print = [];
    foreach($lineup->time_played as $_p => $_time) {
        $playertime_to_print[] = '"' . $_p . '" => ' . $_time;
    }

    $playtime_details = [];
    foreach($lineup->time_in_position as $_p => $_positions) {
        $pos_data = [];
        foreach($_positions as $_pos => $_time) {
            if (is_numeric($_pos)) {
                $pos_data[] = $_pos . "=>" . $_time;
            }
        }
        $playtime_details[] = '"' . $_p . '" => array(' . implode(', ', $pos_data) . ')';
    }
    ?>
    <pre class="no-print d-print-none" style="white-space: pre-wrap; word-break: break-all;">
"<?php echo $wedstrijd; ?>" => array("selection" => "<?php echo $selection_used; ?>", "ws_id" => <?php echo $wisselschema_index[$format] ?? 0; ?>, "rating" => "<?php echo $lineup->rating; ?>%", "score" => <?php echo $lineup->score; ?>, "duration" => <?php echo ($lineup->total_duration * 60); ?>, "players" => array(<?php echo implode(", ", $playertime_to_print); ?>), "playtime" => array(<?php echo implode(", ", $playtime_details); ?>)),
    </pre>
<?php } ?>

<?php 
// Log View Usage if we didn't just generate something heavy
if (!isset($should_log_usage) || !$should_log_usage) {
    if (!defined('PUBLIC_SHARE_MODE') && isset($_SESSION['user_id'])) {
        $gen_time_ms = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000);
        $penalty_seconds = floor($gen_time_ms / 1000); 
        $mem_peak_mb = memory_get_peak_usage() / 1024 / 1024;
        $penalty_memory = floor($mem_peak_mb / 2);
        
        $baseCost = 1; // View cost is just 1
        $loadPenalty = ($penalty_seconds * 1) + $penalty_memory;
        $finalCost = $baseCost + $loadPenalty;
        
        $pdo->prepare("INSERT INTO usage_logs (user_id, team_id, action_type, cost_weight) VALUES (?, ?, 'lineup_view', ?)")
            ->execute([$_SESSION['user_id'] ?? 0, $_SESSION['team_id'] ?? 0, $finalCost]);
    }
}
?>

<?php if (defined('PUBLIC_SHARE_MODE')): ?>
    <?php require_once __DIR__ . '/parents_ui.php'; ?>
<?php endif; ?>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>

<!-- Generate Modal -->