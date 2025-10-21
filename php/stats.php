<?php
require_once("game.php");
require_once("playerscores.php");

$building_lineup = 0; //toont de beschikbare posities als je een wisselschema aan het opstellen bent, verander in selectie
$selection_age_to_include = 15;
$base_year = 2015;

$selecties = array();



// ------------------------------------------------------------
// [2] Selectiebestand bepalen en laden
// ------------------------------------------------------------
// Bepaalt op basis van GET-parameter of laatste bestand welke selectie wordt gebruikt.
// Stap 1: Bepaal het juiste selectiebestand
if (empty($wedstrijd)) {
    $selection_files = glob(__DIR__ . '/selecties/*.php');
    if (!empty($selection_files)) {
        array_multisort(array_map('filemtime', $selection_files), SORT_DESC, $selection_files);
        $latest_file = $selection_files[0];
        $wedstrijd = basename($latest_file, '.php');
    }
}
$selectie_path = __DIR__ . "/selecties/" . $wedstrijd . ".php";


// ------------------------------------------------------------
// [3] Selectiebestand inlezen en format ophalen
// ------------------------------------------------------------
// Laadt het selectiebestand, haalt het wedstrijdformaat en de selectie-array op.
// Stap 2: Laad het selectiebestand en haal $format en $sel op
if (file_exists($selectie_path)) {
    if ($building_lineup == 1) {
        echo "Selectiebestand: " . $selectie_path . "<br/>";
    }

    include $selectie_path;

    if (isset($format) && isset($sel) && is_array($sel)) {
        $aantal = count($sel);

        if ($building_lineup == 1) {
            echo "Format: " . htmlspecialchars($format) . "<br/>";
            echo "Aantal spelers: " . $aantal . "<br/>";
        }


// ------------------------------------------------------------
// [4] Wisselschema zoeken en laden
// ------------------------------------------------------------
// Zoekt het juiste wisselschema-bestand op basis van format en aantal spelers.
        // Stap 3: Zoek en laad het juiste wisselschema-bestand
        $wissel_file = __DIR__ . "/wisselschemas/" . $format . "_" . $aantal . "sp.php";

        if ($building_lineup == 1) {
            echo "Zoekbestand: " . $wissel_file . "<br/>";
        }

        if (file_exists($wissel_file)) {
            if ($building_lineup == 1) {
                echo "Wisselschema geladen: " . basename($wissel_file) . "<br/>";
            }
            include $wissel_file;
        } else {
            if ($building_lineup == 1) {
                echo "⚠️ Geen wisselschema gevonden voor format: " . htmlspecialchars($format) . " met " . $aantal . " spelers.<br/>";
            }
        }
    } else {
        if ($building_lineup == 1) {
            echo "⚠️ Format of selectie-array ontbreekt in het selectiebestand.<br/>";
        }
    }
} else {
    if ($building_lineup == 1) {
        echo "⚠️ Geen geldig selectiebestand gevonden voor wedstrijd: " . htmlspecialchars($wedstrijd) . "<br/>";
    }
}

$QTOTALS = array();
$onlyBestSelection = 1;
$game_titles = array();
$playtime_last_week = array();


$playtime = array();


$format = $game_formats[$wedstrijd];

$timestamp_title = 0;


$all_points = array();
$stats = array(
  "points" => array(
    "worst" => array(),
    "best" => array(),
    "avg" => 0
      
  ),
  "teams" => array(
    "worst" => array(),
    "best" => array()
  ),
);



$squad = $selecties[$wedstrijd];
$total_players = count($squad);


$runs = 0;
$max_score = 0;



if ($shuffle_type == "coach"){
  $max_runs = 1;
}


$teams = array();
$max_points = 0;
$selected = array();
$rating_diffs = array();

$worst_selection = 100000000;
$best_selection = 0;


//TODO Params
$WS_ID = 0;

// ADD META info
foreach($player_scores as $_player => $_scores){
  if (in_array($_player,$squad)){
    foreach($_scores as $_pos=>$_score){
      if ($_score == 0){
        $player_no_go[$_player][] = $_pos;
        //echo "❌ '$_player' mag niet op positie $_pos.\n<br/>";
      }
    }
  }
}

//pr($player_no_go);
//pr($wisselschema_meta["8v8_1gk_4x15"]["positions"]);

$fixed = $squad[0];
$others = array_slice($squad, 1);

$usedHashes = [];
$tries = 0;

$result = null;


// ------------------------------------------------------------
// [5] Opstelling genereren en optimaliseren
// ------------------------------------------------------------
// Probeert via shufflen en scoreberekening de beste opstelling te vinden.
// Houdt rekening met verboden posities, min/max speeltijd, en optimaliseert op score/rating.
while (count($usedHashes) < $max_runs && $tries < $max_runs * 10) {
  $tries++;
  $shuffled = $others;
  if ($max_runs > 1){
    shuffle($shuffled);
  }
   
    $list_of_players = array_merge([$fixed], $shuffled);
    $hash = md5(implode(',', $list_of_players));
    if (!isset($usedHashes[$hash])) {
        $usedHashes[$hash] = true;
        // check lineup voor excludes (bv spelers in $no_max_players in selectie files 

        if (count($no_max[$wedstrijd])){
          $verbodenIndexen = $wisselschema_meta[$format]["time"]["max"];
          $teControlerenNamen = $no_max[$wedstrijd];
          foreach ($teControlerenNamen as $naam) {
              $positie = array_search($naam, $list_of_players); // Zoek de index van de naam

              if ($positie === false) {
                  echo "⚠️ Naam '$naam' niet gevonden in de spelerslijst.\n";
              }

              if (in_array($positie, $verbodenIndexen)) {
                  //echo "❌ '$naam' staat op positie $positie, en dat is een verboden index.\n";
                  continue 2;
              } else {
                  //echo "✅ '$naam' staat op positie $positie, en dat is OK.\n";
              }
          }
          //pr($list_of_players);
          //pr($wisselschema_meta[$format]["time"]["max"],__LINE__);
          //dpr($no_max,__LINE__);  
        }
        
        if (count($no_min[$wedstrijd])){
          $verbodenIndexen = $wisselschema_meta[$format]["time"]["min"];
          $teControlerenNamen = $no_min[$wedstrijd];
          foreach ($teControlerenNamen as $naam) {
              $positie = array_search($naam, $list_of_players); // Zoek de index van de naam

              if ($positie === false) {
                  echo "⚠️ Naam '$naam' niet gevonden in de spelerslijst.\n";
              }

              if (in_array($positie, $verbodenIndexen)) {
                  //echo "❌ '$naam' staat op positie $positie, en dat is een verboden index.\n";
                  continue 2;
              } else {
                  //echo "✅ '$naam' staat op positie $positie, en dat is OK.\n";
              }
          }
          //pr($list_of_players);
          //pr($wisselschema_meta[$format]["time"]["max"],__LINE__);
          //dpr($no_max,__LINE__);  
        }
        
        // check voor verboden posities
        foreach ($list_of_players as $index => $speler) {
            $verbodenPosities = $player_no_go[$speler] ?? [];
            $positiesVoorIndex = $wisselschema_meta[$format]["positions"][$index];
            foreach ($positiesVoorIndex as $positie) {
              if (in_array($positie, $verbodenPosities)) {
                // Speler mag niet op deze index → skip naar volgende speler
                //echo "❌ $speler mag niet op index $index staan (positie $positie is verboden).\n";
                continue 3;
              }
            }
            
        }
        
        $result = new Game($list_of_players,$onlyBestSelection,$format);
        $total_points = $result->score;
        if ($total_points > $max_points){
          $max_points = $total_points;
          $selected["run"] = $tries;
          $selected["ws_id"] = $wisselschema_index[$format];
          $selected["total_points"] = $total_points;
          $selected["rating"] = $result->rating;
          $selected["volgorde"] = implode(',', $list_of_players);
          $selected["team"] = $result;
          $lineup = $result;

        }
    }
}

if (is_null($result)) {
    echo "❌ Geen opstelling gevonden. Verhoog max_runs ($max_runs) in index.php:6";
    die();
}


//dpr($selected,__LINE__);


$page_title = $wedstrijd;
//$page_title .=  "_" . $team_key_idx;
/*
$page_title .=  "-" . count($selected["teams"]) . "ploeg";
if (count($selected["teams"])>1){
  $page_title .= "en";
}*/

if ($shuffle_type != "coach"){
  $page_title .= $total_players . "sp";
  if ($timestamp_title) {
    $page_title .= "_" . strftime("%Y%m%d%H%M%S");
  } else {
    /* if ($team_key_idx != "equal"){
      $page_title .= "_". round($selected["rating_diff"],2);
    } */
 
    $page_title .=  "_" . $selected["total_points"] ;
  }
}

$page_title .=  "_";
$page_title .= substr(md5(implode(", ",$lineup->playernames)),0,8);


if(array_key_exists($format,$wisselschema_index)){
  $page_title .= "_" . $wisselschema_index[$format];
}
$page_title .= " " . $lineup->rating . "%";


$benchlist = array();

//TODO page print breaks
// 6 spelers -> voor time played blokje

//https://fontawesome.com/search


// ------------------------------------------------------------
// [6] HTML-output en presentatie
// ------------------------------------------------------------
// Bouwt de HTML-pagina met:
// - Titel en info over de selectie
// - Per wedstrijddeel een tabel met de opstelling
// - Per speler een overzicht van speelminuten per positie
// - (Optioneel) Geboortedata, speelminuten vorige week, statistieken per kwartaal/jaar
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Robin Stroobants">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap core CSS -->
    <link href="/assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
     
    </style>

    
    <!-- Custom styles for this template -->
    <link href="css/grid.css" rel="stylesheet">
    <link href="css/styles.css?v=1" rel="stylesheet">
  </head>
  <body class="py-4">
    
<main>
  <div class="container">

    <h2><?php echo $page_title; //"Format $lineup->format."; ?></h2>
    <p class="lead" style="margin-bottom:0">
      <?php 
      
      //pr($selected);
      echo "$total_players spelers aanwezig: " . implode(", ",array_keys($lineup->playerindex)); //. implode(", ",$selected["teams"][0]->playernames) ;
      if ($selected["run"]){
        echo "<small> - Run " . $selected["run"] . " van " . $tries . "</small><br/>"; 
      }
      
       ?>
    </p>
   
   
    <?php
    
      if ($building_lineup){
        pr($lineup->playernames,"player index");
      }
        
      
      $newpage = "";
     


      ?>
      <div id="game-1" class="row scheme-with-<?php echo $lineup->available_players; ?>-players ">
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
          if (array_key_exists($wedstrijd,$game_titles)){
            if ($game_counter == 4) {
             ?><hr style="margin-top: 30px" class="new-print-page"><?php
            }
            echo "<h5 style='margin-top:20px'>Wedstrijd $game_counter &middot; " . $game_titles[$wedstrijd][$game_counter]["title"] . " <small>(" . $game_titles[$wedstrijd][$game_counter]["info"] . ")</small></h5>";  
          } else {
           // echo "<h5 style='margin-top:20px'>Wedstrijd $game_counter</h5>";
          }
          
          foreach ($game_parts as $game_idx){
            
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
            $col_width = 12/$nr_of_parts;  
            $smcol_width = 4;
            
            
            
            ?>
            <div class="col-sm-<?php echo $smcol_width ?> col-md-<?php echo $col_width ?> border">
              <table width="100%">
                <tr>  
                  <td align="left" valign="top" rowspan="6">
                    <h4 class="mt-1 mb-5"><small><?php echo $game["start"]; ?></small></h4>
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

                  <td align="center" colspan="3" class="lineup-col <?php echo in_array(9,$next_bench_keys) ? "fw-bold" : "";?>">9<br/><?php echo $game['lineup'][9]; ?></td>
                </tr>
                <tr>
                  <td align="center" class="lineup-col <?php echo in_array(11,$next_bench_keys) ? "fw-bold" : "";?>">11<br/><?php echo $game['lineup'][11]; ?></td>
                  <td align="center" class="lineup-col"></td>
                  <td align="center" class="lineup-col <?php echo in_array(7,$next_bench_keys) ? "fw-bold" : "";?>">7<br/><?php echo $game['lineup'][7]; ?></td>
                </tr>
                <tr>
                  <td align="center" colspan="3" class="lineup-col <?php echo in_array(10,$next_bench_keys) ? "fw-bold" : "";?>">
                    <?php if (array_key_exists(10,$game['lineup'])) { ?>
                    10<br/><?php echo $game['lineup'][10]; ?>
                    <?php } ?>
                  </td>
                </tr>
                <tr>
                  <td align="center" class="lineup-col <?php echo in_array(5,$next_bench_keys) ? "fw-bold" : "";?>">
                    <?php if (array_key_exists(5,$game['lineup'])) { ?>
                    5<br/><?php echo $game['lineup'][5]; ?>
                    <?php } ?></td>
                  <td align="center" class="lineup-col"></td>
                  <td align="center" class="lineup-col <?php echo in_array(2,$next_bench_keys) ? "fw-bold" : "";?>">
                    <?php 
                    if (array_key_exists(2,$game['lineup'])) { ?>
                    2<br/><?php echo $game['lineup'][2]; ?>
                    <?php } ?>
                  </td>
                </tr>
                
                <tr><td align="center" colspan="3" class="lineup-col <?php echo in_array(4,$next_bench_keys) ? "fw-bold" : "";?>">4<br/><?php echo $game['lineup'][4]; ?></td></tr>
                <tr><td align="center" colspan="3" class="lineup-col <?php echo in_array(1,$next_bench_keys) ? "fw-bold" : "";?>">1<br/><?php echo $game['lineup'][1]; ?></td></tr>
            
              </table>
          
            </div>
        
      
        
            <?php
            $total_duration = $total_duration + $game["duration"];
            $previous_game = $game;
            
          }
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
            //echo(count($lineup->events));
            
            echo $lineup->playerindex[$player_on_the_bench];
            echo " &middot; " . $player_on_the_bench;
            echo  " (" . implode(",",$available_positions_for_player) .  ") ";
            echo $lineup->time_since_last_sub[$game_idx][$player_on_the_bench] . " sec -- pt: " . $lineup->time_played_by_index[$game_idx][$player_on_the_bench];
            echo "<br/>";
          }
         
        }
        
      
      
      
        ?>
      
      </div>
      <hr style="margin-top: 30px" class="<?php echo $lineup->available_players > 5 ? "new-print-page":"";?>">
      <div class="timetable row mt-4">
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
            <h4 class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-primary"><?php echo $player; ?></span>
              <span class="badge bg-primary rounded-pill"><?php echo calctime($lineup->total_playtime[$player]);?></span>
            </h4>
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
            
              ?>
              
            </ul>
          </div>
          
          <?php
          $offset_class = ""; //enkel eerste kolom
         } 
         
         
         
         
         

// ------------------------------------------------------------
// [7] Speelminuten/statistieken per speler
// ------------------------------------------------------------
// Toont per speler de speelminuten, geboortedata, en statistieken per kwartaal/jaar.
         require_once("speelminuten.php");
         
         $has_birthdate = array_key_exists("birthdate",$lineup->playerinfo[$lineup->playernames[2]]);
         
         if (array_key_exists($wedstrijd,$playtime_last_week) || $has_birthdate){
           echo "<hr/>";
           if (array_key_exists($wedstrijd,$playtime_last_week)) {
             ?>
             <h4 class="d-flex justify-content-between align-items-center mb-3">
               <span class="text-primary">Speeltijd vorige week</span>
             </h4>
             <?php
           }
           asort($lineup->playernames);
           $cutoff = round(count($lineup->playernames)/2);
           $bdcolwidth = 6;
           $bd_listing = array(
             array_slice($lineup->playernames,0,$cutoff),
             array_slice($lineup->playernames,$cutoff)
           );
           $QLEVELS = array();
           $QTOTALS = array();
           $dispensatie = 0;
           $doorgeschoven = 0;
           foreach($bd_listing as $playernames) {
             ?>
             <div class="col-md-<?php echo $bdcolwidth;?> col-sm-12 player-listing">
               <ul class="list-group mb-3">
                 <?php 
               
                 foreach($playernames as $firstname){ ?>
                   <li class="list-group-item d-flex justify-content-between lh-sm">
                     <div>
                       <h6 class="my-0">
                         <?php 
                         $has_birthdate = array_key_exists("birthdate",$lineup->playerinfo[$firstname]);
                         if ($has_birthdate) {
                           $date = new DateTimeImmutable($lineup->playerinfo[$firstname]["birthdate"]);
                           //echo $lineup->playerinfo[$firstname]["birthdate"] . " -- " . $date->format('n') > "<br/>";
                           $y = $date->format('Y');
                           $q = ceil($date->format('n')/3);
                           
                           
                           
                           if (!array_key_exists($y,$QLEVELS)){
                             $QLEVELS[$y] = array();
                           }
                           if (!array_key_exists($q,$QLEVELS[$y])){
                             $QLEVELS[$y][$q] = array();
                           }
                           if ($y == $base_year){
                             $q_color = "info";
                           } else {
                             if ($y > $base_year){
                               $q_color = "success";
                               $doorgeschoven+=1;
                               $q = 4;
                             } else {
                               $q=1;
                               $dispensatie+=1;
                               $q_color = "danger";
                             }
                           }
 
                           if (!array_key_exists($q,$QTOTALS)){
                             $QTOTALS[$q] = array();
                           }
                           $QLEVELS[$y][$q][] = $firstname;
                           $QTOTALS[$q][] = $firstname;
                           if($q < 3){
                             $QTOTALS[12][] = $firstname;
                           } else {
                             $QTOTALS[34][] = $firstname;  
                           }
                           
                           if (array_key_exists($firstname,$pt_stats)){
                             echo "<span class='badge bg-" . $q_color . " rounded-pill'>";
                             $pt_play_perc = ($pt_stats[$firstname]["played"]/$pt_stats[$firstname]["available"])*100;
                            echo round($pt_play_perc,1) ."%<br/>";
                            echo "<small>" . round($pt_stats[$firstname]["played"]/60)  ."/" . round($pt_stats[$firstname]["available"]/60) . "</small>";
                             echo "</span>";
                             
                           }
                           /*
                           echo "<span class='badge bg-" . $q_color . " rounded-pill'>";
                           echo "Q" . $q;
                           echo "</span>";
                           */
                         }
                         ?>
                         <?php echo $firstname . " " . $lineup->playerinfo[$firstname]["name"]; ?>
                       </h6>
                     </div>
                     <span class="text-muted" style="font-family: monospace">
                       <?php 
                       if ($has_birthdate) { 
                         echo $lineup->playerinfo[$firstname]["birthdate"]; 
                       }
                      
                       if (array_key_exists($wedstrijd,$playtime_last_week)){
                         echo " &middot; ";
                         echo $playtime_last_week[$wedstrijd][$firstname];  
                       }

                        ?>
                     </span>
                   </li>
                 <?php } ?>
               </ul>
             </div>
             <?php
           }
         
         
         
           ?>

        </div>
     <?php
    }
      if (array_key_exists(34,$QTOTALS)){
        $total = count($lineup->playernames);
        $last_half = round(count($QTOTALS[34])/$total*100);
        $first_half = 100 - $last_half;
        ?>
        <div class="col-md-<?php echo $bdcolwidth;?> col-sm-12">
          <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between lh-sm">
              <div><h6 class="my-0"><span class="text-muted" style="font-family: monospace">Eerste jaarhelft</h6></div>
              <span class="text-muted" style="font-family: monospace"><?php echo $first_half; ?>%</span>
            </li>
            <li class="list-group-item d-flex justify-content-between lh-sm">
              <div><h6 class="my-0"><span class="text-muted" style="font-family: monospace">Tweede jaarhelft</h6></div>
                <span class="text-muted" style="font-family: monospace"><?php echo $last_half; ?>%</span>
            </li>
            <?php if ($dispensatie>0) { ?>
              <li class="list-group-item d-flex justify-content-between lh-sm">
                <div><h6 class="my-0"><span class="text-muted" style="font-family: monospace">Dispensatie</h6></div>
                <span class="text-muted" style="font-family: monospace"><?php echo $dispensatie; ?>/<?php echo count($lineup->playernames); ?></span>
              </li>
            <?php } ?>
        
          </ul>
          <?php
      
      }
      //pr($QTOTALS);
      //pr($QLEVELS);
    
    
      //dpr($g);
      
    ?>
    </div>
    
  </main>

<?php if ($show_pt_array) { 
  $selection_used = implode(",",array_keys($lineup->playerindex));
  $time_per_position = array();

  
  foreach($lineup->time_played as $_p=>$_time){
    if ($_time < 3600){
      $time_per_position[$lineup->playerindex[$_p]] = $_time;  
    }
  }
  $min = min($time_per_position);
  $max = max($time_per_position);

  // Zoek indexen van de minimum en maximum waarden
  $min_positions = array_keys($time_per_position, $min);
  $max_positions = array_keys($time_per_position, $max);

  ?>
  <pre class="no-print">
     "<?php echo $wisselschema_index[$format];?>" => array(
      "min" => "<?php echo implode(",",$min_positions); ?>"
      "max" => "<?php echo implode(",",$max_positions); ?>"
    );
    "<?php echo $wedstrijd;?>" => array(
      "selection" => "<?php echo $selection_used; ?>",
      "ws_id" => <?php echo $wisselschema_index[$format]; ?>,
      "rating" => "<?php echo $lineup->rating; ?>%",
      "score" => <?php echo $lineup->score; ?>,
      "duration" => 60*<?php echo $lineup->total_duration ."\n"; ?>
      , "players" => array(
         <?php echo implode(",\n\t",$playertime_to_print) ; /*foreach($playtime as $pos=>$seconds){ echo $pos; echo calctime($seconds) ;))*/
?>))
  </pre>
<?php 

} 
//pr($wisselschema_meta, __LINE__ . ": wisselschema_meta");
?>
  
  </body>
</html>
