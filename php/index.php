<?php require_once 'generator.php'; ?>
  <?php require_once 'header.php'; ?>
    
  <main>
    <div class="container">
      <div class="d-flex justify-content-between align-items-center p-3">
        
          <?php if ($vorige_key): ?>
              <?php $query_params['wedstrijd'] = $vorige_key; ?>
              <a href="<?= build_url($base_url, $query_params); ?>" class="btn btn-wedstrijd d-print-none"><i class="fa-solid fa-arrow-left"></i>&nbsp;<?= htmlspecialchars($vorige_key) ?></a>
          <?php else: ?>
            
          <?php endif; ?>
          <h5 class="mb-0 w-100 text-center" id="dynamic-page-title"><?= htmlspecialchars($page_title) ?></h5>

          <?php if ($volgende_key): ?>
              <?php $query_params['wedstrijd'] = $volgende_key; ?>
              <a href="<?= build_url($base_url, $query_params) ?>" class="btn btn-wedstrijd d-print-none align-middle"><i class="fa-solid fa-arrow-right align-middle me-1"></i>&nbsp;<?= htmlspecialchars($volgende_key) ?></a>
          <?php else: ?>
          <?php endif; ?>
      </div>
      <?php if (!empty($top_selected_options)): ?> 
      <ul class="nav nav-tabs mt-4 mb-3 d-print-none justify-content-center" id="lineupTabs" role="tablist">
          <?php foreach ($top_selected_options as $t_idx => $t_opt): 
                  $t_lineup = $t_opt['team'];
                  // Compileer tab-specifieke titel op basis van de elementen uit deze exacte loop interpolatie
                  $tab_title = $wedstrijd . "_" . count($t_lineup->playernames) . "sp_" . $t_opt["total_points"] . "_" . substr(md5(implode(", ", $t_lineup->playernames)),0,8) . "_" . $t_opt["ws_id"] . " (" . $t_lineup->rating . "%)";
          ?>
              <li class="nav-item d-print-none" role="presentation">
                  <button class="nav-link <?= $t_idx == 0 ? 'active' : '' ?>" id="tab-btn-<?= $t_idx ?>" data-title="<?= htmlspecialchars($tab_title) ?>" data-bs-toggle="tab" data-bs-target="#tab-pane-<?= $t_idx ?>" type="button" role="tab">Optie <?= $t_idx + 1 ?> <span class="badge bg-secondary"><?= round($t_opt['rating'], 2) ?>%</span></button>
              </li>
          <?php endforeach; ?>
      </ul>
      
      <div class="tab-content" id="lineupTabsContent">
      <?php foreach ($top_selected_options as $tab_idx => $t_opt): 
          $lineup = $t_opt['team'];
          $selected = $t_opt;
      ?>
      <div class="tab-pane fade <?= $tab_idx == 0 ? 'show active d-print-block' : 'd-print-none' ?>" id="tab-pane-<?= $tab_idx ?>" role="tabpanel" tabindex="0">
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
                echo "$total_players spelers aanwezig: " . implode(", ",array_keys($lineup->playerindex)); //. implode(", ",$selected["teams"][0]->playernames) ;
                echo "<small> // Schema " . htmlspecialchars($selected["ws_id"]) . " &middot; " . $lineup->rating . "%</small>"; 
                if (isset($selected["run"]) && $selected["run"] > 0) {
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
              if ($game_counter == 4) {
               ?><hr style="margin-top: 30px" class="new-print-page"><?php
              }
              echo "<h5>Wedstrijd $game_counter &middot; " . $game_titles[$wedstrijd][$game_counter]["title"] . " <small>(" . $game_titles[$wedstrijd][$game_counter]["info"] . ")</small></h5>";  
            } else {
              echo "<h5>Wedstrijd $game_counter</h5>";
            }
            echo "</div>";
            foreach ($game_parts as $game_idx){
              $part_score = 0;
              $part_max = 0;
              // Score voor dit partje berekenen:
              foreach($lineup->events[$game_idx]["lineup"] as $_pos => $_shortname){
                $part_score += $lineup->events[$game_idx]["duration"] * $player_scores[$_shortname][$_pos];
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
              $col_width = 4;


              $smcol_width = 4;
            
            
            
              ?>
              <div class="col-4 col-sm-<?php echo $smcol_width ?> col-md-<?php echo $col_width ?> p-2">
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
                            echo "<strong>" . $game["subs"]["in"][$pos];
                          } else {
                            //find player that will come on this position
                            //dpr($game);
                            if (array_key_exists($pos,$game["positions"])){
                              echo $game["positions"][$pos]["player"];
                              echo " <i class='fa-solid fa-bolt' aria-hidden='true'></i> ";
                            }                      
                          }
                          if (array_key_exists($pos,$game["subs"]["in"])){
                            echo "</strong> ";
                            //echo "<i class='fa-solid fa-arrow-right'></i> ";
                          } 
                          echo "<s>" . $game["subs"]["out"][$pos] . "</s>";
                          echo "</li>";
                        }
                        foreach($left as $pos){
                          echo "<li><strong>$pos ";
                          if (array_key_exists($pos,$game["subs"]["in"])){
                            echo $game["subs"]["in"][$pos];
                          } else {
                            //find player that will come on this positions 
                            if (array_key_exists($pos,$game["positions"])){
                              echo $game["positions"][$pos]["player"];
                            }                      
                          }
                          echo "</strong> ";
                          if (array_key_exists($pos,$game["subs"]["out"])){
                            echo $game["subs"]["out"][$pos];
                          }
                          echo "</li>";
                        }
                        echo "</ul>";
                      
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
                          echo "<li>$_player</li>";
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

                    <td align="center" colspan="3" class="lineup-col <?php echo in_array(9,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">9</span><br/><?php echo $game['lineup'][9]; ?></td>
                  </tr>
                  <tr>
                    <td align="center" class="lineup-col <?php echo in_array(11,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">11</span><br/><?php echo $game['lineup'][11]; ?></td>
                    <td align="center" class="lineup-col"></td>
                    <td align="center" class="lineup-col <?php echo in_array(7,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">7</span><br/><?php echo $game['lineup'][7]; ?></td>
                  </tr>
                  <tr>
                    <td align="center" colspan="3" class="lineup-col <?php echo in_array(10,$next_bench_keys) ? "fw-bold" : "";?>">
                      <?php if (array_key_exists(10,$game['lineup'])) { ?>
                      <span class="pos-num">10</span><br/><?php echo $game['lineup'][10]; ?>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr>
                    <td align="center" class="lineup-col <?php echo in_array(5,$next_bench_keys) ? "fw-bold" : "";?>">
                      <?php if (array_key_exists(5,$game['lineup'])) { ?>
                      <span class="pos-num">5</span><br/><?php echo $game['lineup'][5]; ?>
                      <?php } ?></td>
                    <td align="center" class="lineup-col"></td>
                    <td align="center" class="lineup-col <?php echo in_array(2,$next_bench_keys) ? "fw-bold" : "";?>">
                      <?php 
                      if (array_key_exists(2,$game['lineup'])) { ?>
                      <span class="pos-num">2</span><br/><?php echo $game['lineup'][2]; ?>
                      <?php } ?>
                    </td>
                  </tr>
                
                  <tr><td align="center" colspan="3" class="lineup-col <?php echo in_array(4,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">4</span><br/><?php echo $game['lineup'][4]; ?></td></tr>
                  <tr><td align="center" colspan="3" class="lineup-col <?php echo in_array(1,$next_bench_keys) ? "fw-bold" : "";?>"><span class="pos-num">1</span><br/><?php echo $game['lineup'][1]; ?></td></tr>
            
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
                            $pos_wissels[] = "<strong>$speler</strong>: van $vorige_pos naar $pos";
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
              if (array_key_exists("pos",$lineup->playerinfo[$player_on_the_bench])){
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
      
    <?php if ($show_position_stats) {?> 
      
      <div class="row mt-4 timetable new-print-page" style="">
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
              
                $score_for_player = 0;
                
                // Check eerst of de speler wel in de stats zit (voor het geval Franklin nieuw is of eruit lag)
                if (isset($pt_stats[$player]) && isset($pt_stats[$player]["played"]) && isset($pt_stats[$player]["available"]) && $pt_stats[$player]["available"] > 0) {
                    $pt_play_perc = ($pt_stats[$player]["played"] / $pt_stats[$player]["available"]) * 100;
                } else {
                    $pt_play_perc = 0; 
                }           ?>
              <tr>
                <td><?php echo $i+1; ?></td>
                <td><strong><?php echo round($pt_play_perc,1);?>%</strong> <small>(<?php echo round($pt_stats[$player]["played"]/60)  ."/" . round($pt_stats[$player]["available"]/60);?>)</small></td>
                <td><?php echo $player; ?></td>
                <td title="speeltijd deze wedstrijd"><?php echo calctime($lineup->total_playtime[$player]);?></td>
                <?php
                // data vorige wedstrijd ophalen
                
                ?>
                
                <td title="speeltijd <?php echo  $pt_stats[$player]["previous_game_title"];?>">
                  <small><i>
                    <?php 
                            if (isset($pt_stats[$player]) && isset($pt_stats[$player]["previous_game_key"]) && isset($pt_stats[$player]["time_per_game"][$pt_stats[$player]["previous_game_key"]])) {
                                echo calctime($pt_stats[$player]["time_per_game"][$pt_stats[$player]["previous_game_key"]]); 
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
                    <?php if (isset($pt_stats[$player]["positions"][$pos])) { ?>
                    <span title="<?php echo calctime($pt_stats[$player]["positions"][$pos]["time"]); ?>"><?php echo $pt_stats[$player]["positions"][$pos]["percentage"]; ?>%</span>
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
      
      
        <div class="timetable row mt-4 do_not_break <?php echo !$show_position_stats ? 'new-print-page' : ''; ?>">
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

          foreach($lineup->time_in_position as $player=>$playtime) { 
          
            $score_for_player = 0;
            $playertime_to_print[] = "\"" . $player ."\" => " . $lineup->total_playtime[$player];
            $available_positions_for_player = array();
            if (array_key_exists("pos",$lineup->playerinfo[$player])){
              $available_positions_for_player = $lineup->playerinfo[$player]["pos"];
            } else {
              // indien nog geen posities gedefinieerd dan is alles ok.
              $available_positions_for_player = $lineup->positions;
            }
            ?>
            <div class="col-md-<?php echo $timecolwidth;?> col-sm-6 <?php echo $offset_class; ?>">
              <h5 class="d-flex justify-content-between align-items-center mb-3 ps-2">
                <span class="text-primary"><?php echo $player; ?></span>
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
                      <h6 class="my-0">Rating <?php echo $player; ?></h6>
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
     
     
     
       <?php
    
    

        //pr($QTOTALS);
        //pr($QLEVELS);
    
    
        //dpr($g);
      
      ?>
      
          </div> <!-- End tab-pane -->
      <?php endforeach; ?>
      </div> <!-- End tab-content -->
      <?php endif; ?>
      
    </div> <!-- End container -->

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var titleDisplay = document.getElementById("dynamic-page-title");
        var tabs = document.querySelectorAll("#lineupTabs button[data-bs-toggle='tab']");
        tabs.forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                var newTitle = e.target.getAttribute("data-title");
                if (titleDisplay && newTitle) {
                    titleDisplay.innerText = newTitle;
                }
                if (newTitle) {
                    document.title = newTitle;
                }
            });
        });
    });
    </script>
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

  
<?php require_once 'footer.php'; ?>