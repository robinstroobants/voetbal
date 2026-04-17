  <?php
    $show_pt_array = 0;
    $show_position_stats = 1;
    $show_part_score = 0;
    $debug=0;
    /**
   * ============================================================
   *  Opstellingsgenerator Voetbal - Hoofdscript (index_clean.php)
   * ============================================================
   *
   * Doel:
   * ------
   * Dit script genereert automatisch een optimale opstelling en wisselschema
   * voor een voetbalwedstrijd, op basis van een selectie spelers, hun posities,
   * speelminuten, en een wisselschema. Het houdt rekening met beperkingen
   * (zoals spelers die niet op bepaalde posities mogen staan) en probeert
   * een zo eerlijk mogelijke verdeling van speeltijd en posities te maken.
   *
   * Globale werking:
   * ----------------
   * 1. Laadt hulpscripts en definieert globale instellingen.
   * 2. Bepaalt op basis van een GET-parameter of laatste bestand de juiste selectie.
   * 3. Laadt het selectiebestand en het bijbehorende wisselschema.
   * 4. Genereert via (veelvoudig) shufflen de beste opstelling volgens score/rating.
   * 5. Controleert op verboden posities en posities met min/max speeltijd.
   * 6. Toont het resultaat in een HTML-pagina met opstelling, speelminuten, statistieken.
   *
   * Belangrijke variabelen:
   * -----------------------
   * $wedstrijd           - Naam van de wedstrijd/selectie (bepaalt welk bestand geladen wordt)
   * $format              - Wedstrijdformaat (bijv. '8v8_4x15')
   * $squad               - Array met geselecteerde spelers
   * $wisselschema_meta   - Meta-informatie over het wisselschema (indexen met min/max speeltijd)
   * $player_no_go        - Spelers die niet op bepaalde posities mogen staan
   * $lineup              - Het uiteindelijke Game-object met alle info over de opstelling
   * $max_runs            - Aantal pogingen om een optimale opstelling te vinden
   * $building_lineup     - Debugmodus voor het bouwen van een wisselschema
   *
   * Zie per sectie hieronder verdere uitleg.
   */








  // ------------------------------------------------------------
  // [1] Laad hulpscripts en stel globale instellingen in
  // ------------------------------------------------------------
  require_once("game.php");




  if (!isset($max_runs) || $max_runs === 0 || $max_runs === null) {
      $max_runs = 5000;
  }

  $shuffle_type = "random"; //override in selectie
  $no_max = array(); //spelers die niet op een positie mogen staan die het meeste speelt
  $no_min = array(); // spelers die niet op een positie mogen staan die het minst speelt
  $game_titles = array();
  $building_lineup = 0; //toont de beschikbare posities als je een wisselschema aan het opstellen bent, verander in selectie

  $selection_age_to_include = 15;
  $base_year = 2015;
  $selecties = array();
  $events = array();
  $wisselschema_index = array();

  $wedstrijd = $_GET['wedstrijd'] ?? '';


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

      // VALIDATIE: in 'coach' modus is een specifiek schema absoluut vereist
      if (isset($shuffle_type) && $shuffle_type === "coach") {
          if (!isset($te_gebruiken_schema) || $te_gebruiken_schema === "" || $te_gebruiken_schema === null) {
              echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid red; background: #fff5f5;'>";
              echo "<h2 style='color: #d9534f;'>❌ Configuratie Fout</h2>";
              echo "<p>Bij <strong>shuffle_type = 'coach'</strong> moet de parameter <strong>\$te_gebruiken_schema</strong> expliciet ingevuld zijn in het selectiebestand.</p>";
              echo "</div>";
              die();
          }
      }

      if (isset($format) && isset($sel) && is_array($sel)) {
          $aantal = count($sel);

          if ($building_lineup == 1) {
              echo "Format: " . htmlspecialchars($format) . "<br/>";
              echo "Aantal spelers: " . $aantal . "<br/>";
          }


  require_once("playerscores.php");
  
  
  //echo $format . " -- " . $aantal;
          
  // ------------------------------------------------------------
  // [4] Wisselschema zoeken en laden
  // ------------------------------------------------------------
  // Zoekt het juiste wisselschema-bestand op basis van format en aantal spelers.
          // Stap 3: Zoek en laad het juiste wisselschema-bestand
          $wissel_file = __DIR__ . "/wisselschemas/" . $format . "_" . $aantal . "sp.php";

          //echo $wissel_file;die();

          if ($building_lineup == 1) {
              echo "Zoekbestand: " . $wissel_file . "<br/>";
          }

          if (file_exists($wissel_file)) {
              if ($building_lineup == 1) {
                  echo "Wisselschema geladen: " . basename($wissel_file) . "<br/>";
              }
              include $wissel_file;
              $beschikbare_schemas = array_keys(isset($ws) ? $ws : []);
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


   require_once("speelminuten.php");

   $keys = array_keys($pt_all_games);
   $index = array_search($wedstrijd, $keys);

   // Initieer beide als null
   $vorige_wedstrijd = null;
   $huidige_wedstrijd = null;
   $volgende_wedstrijd = null;
   if ($index === false) {
        // Wedstrijd niet gevonden: enkel vorige wedstrijd is het eerste (nieuwste)
        // CHECK: We controleren eerst of er wel keys zijn (of er historie is)
        if (!empty($keys)) {
            $vorige_wedstrijd_key = $keys[0];
            $vorige_wedstrijd = $pt_all_games[$vorige_wedstrijd_key];
        }
        // Geen volgende wedstrijd
    } else {
        
       // Wedstrijd gevonden
       // Vorige wedstrijd = eerstvolgende in array (oudere)
       if (isset($keys[$index + 1])) {
           $vorige_wedstrijd_key = $keys[$index + 1];
           $vorige_wedstrijd = $pt_all_games[$vorige_wedstrijd_key];
       }

       // Volgende wedstrijd = vorige in array (nieuwere)
       if ($index > 0 && isset($keys[$index - 1])) {
           $volgende_wedstrijd_key = $keys[$index - 1];
           $volgende_wedstrijd = $pt_all_games[$volgende_wedstrijd_key];
       }
   }
   

   if (isset($vorige_wedstrijd_key)){
     // Datumdeel uit key halen
     list($datum_raw, $wedstrijd_naam) = explode('_', $vorige_wedstrijd_key, 2);
     // Datumformaten genereren vanuit YYMMDD
     $jaar = '20' . substr($datum_raw, 0, 2); // '25' → '2025'
     $maand = substr($datum_raw, 2, 2);       // '09'
     $dag = substr($datum_raw, 4, 2);         // '27'
     $datum_full = "$jaar-$maand-$dag";       // '2025-09-27'
     $datum_short = "$dag/$maand";            // '27/09'
     // Uitbreiden van array
     $vorige_wedstrijd['key'] = $vorige_wedstrijd_key;
     $vorige_wedstrijd['date_short'] = $datum_short;
     $vorige_wedstrijd['date_full'] = $datum_full;
   }
  
   //pr($vorige_wedstrijd,$vorige_wedstrijd_key);
   //dpr($volgende_wedstrijd,__LINE__);
 
   /* code om vorige / volgende wedstrijd te tonen */
   $current_url = $_SERVER['REQUEST_URI'];
   $base_url = strtok($current_url, '?'); // Verwijdert querystring
   $query_params = $_GET;

   // Zoek de keys
   $keys = array_keys($pt_all_games);
   $index = array_search($wedstrijd, $keys);

   // Bepaal vorige en volgende keys
   $vorige_key = isset($keys[$index + 1]) ? $keys[$index + 1] : null;
   $volgende_key = isset($keys[$index - 1]) ? $keys[$index - 1] : null;
 
   // Helper om URL te bouwen
   function build_url($base, $params) {
       return $base . '?' . http_build_query($params);
   }
   /* EINDE code om vorige / volgende wedstrijd te tonen */
 



  // ------------------------------------------------------------
  // settings min/max uit selectiebestand halen
  // ------------------------------------------------------------

  $no_max_players = str_replace(" ,", ",", $no_max_players ?? '');
  $no_max_players = str_replace(" ,",",", $no_max_players); 
  $no_min_players = str_replace(", ", ",", $no_min_players ?? '');
  $no_min_players = str_replace(" ,",",", $no_min_players); 

  


  $QTOTALS = array();
  $onlyBestSelection = 1;
  
  $playtime = array();
  $format = $game_formats[$wedstrijd];
  $timestamp_title = 0;

  $all_points = array();
  $squad = $selecties[$wedstrijd];
  $total_players = count($squad);
  
  IF ($debug){
    // DEBUG: Toon wie er beschermd wordt
    echo "<div class='alert alert-info'>";
    echo "<strong>Vorige match ($vorige_wedstrijd_key):</strong><br>";
    pr($vorige_wedstrijd,"vorige wedstrijd");
    echo "</div>";    
    dpr($no_min, "no min");
  
    
  }
  
  if ($vorige_wedstrijd !== null) {
      $speeltijd_vorige_wedstrijd = $vorige_wedstrijd["players"];

      // Filter op spelers die in de huidige selectie zitten
      $speeltijd_vorige_wedstrijd = array_filter(
          $speeltijd_vorige_wedstrijd,
          fn($key) => in_array($key, $squad, true),
          ARRAY_FILTER_USE_KEY
      );

      // --- NO MIN (Mogen niet de minste minuten spelen, gebaseerd op vorige match) ---
      // Triggert enkel als de coach zelf niemand manueel heeft benoemd
      if (empty($no_min_players) && !empty($speeltijd_vorige_wedstrijd)) {
          $min = min($speeltijd_vorige_wedstrijd);
          $namen_met_min = array_keys($speeltijd_vorige_wedstrijd, $min, true);

          // We beschermen maximaal 2 spelers per keer om het blokkeren van kleine schema's te voorkomen
          if (count($namen_met_min) > 2) {
              shuffle($namen_met_min); // Kies er willekeurig 2 uit de (meerdere) pechvogels
              $no_min_players = implode(',', array_slice($namen_met_min, 0, 2));
          } else {
              $no_min_players = implode(',', $namen_met_min);
          }
      }
      
      // --- NO MAX (Mogen niet op de posities met de MEESTE minuten staan, gebaseerd op vorige match) ---
      if (empty($no_max_players) && !empty($speeltijd_vorige_wedstrijd)) {
          // Haal de doelmannen of extra uitschieters (>= 3600 seconden / 60 min) eruit
          $filtered = array_filter($speeltijd_vorige_wedstrijd, fn($v) => $v < 3600);

          if (!empty($filtered)) {
              $max = max($filtered);
              $namen_met_max = array_keys($speeltijd_vorige_wedstrijd, $max, true);
              
              // Als > 2 spelers tegelijk 'straf' krijgen, vervalt de regel omdat er vaak niet genoeg wisselslots zijn
              if (count($namen_met_max) > 2) {
                  $no_max_players = ""; 
              } else {
                  $no_max_players = implode(',', $namen_met_max);
              }
          }
      }
  }

  // Huidige mapping naar arrays voor de wisselschema-checks
  if (strlen($no_max_players) > 0){
      $no_max[$wedstrijd] = explode(",", $no_max_players);
  } else {
      $no_max[$wedstrijd] = array();
  }
  if (strlen($no_min_players) > 0){
      $no_min[$wedstrijd] = explode(",", $no_min_players);
  } else {
      $no_min[$wedstrijd] = array();
  }

  
  IF ($debug){
    // DEBUG: Toon wie er beschermd wordt
    echo "<div class='alert alert-info'>";
    echo "<strong>Vorige match ($vorige_wedstrijd_key):</strong><br>";
    echo "Spelers die nu NIET het minst mogen spelen (no_min): " . implode(', ', $no_min[$wedstrijd]) . "<br>";
    echo "Spelers die nu NIET het meest mogen spelen (no_max): " . implode(', ', $no_max[$wedstrijd]);
    echo "</div>";    
    pr($no_max,"no max");
    dpr($no_min, "no min");
  
    
  }

  
  

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
        // Check of we de "0" score moeten negeren voor de keeper (positie 1)
        $is_gk_position = ($_pos == 1);
        $no_fixed_gks = empty(trim($doelmannen ?? ''));
        
        // Blokkeer alleen als score 0 is, BEHALVE als het de keeperpositie is en er geen vaste keepers zijn
        if ($_score == 0 && !($is_gk_position && $no_fixed_gks)){
          $player_no_go[$_player][] = $_pos;
          //echo "❌ '$_player' mag niet op positie $_pos.\n<br/>";
        }
      }
    }
  }

  //pr($player_no_go);
  //pr($wisselschema_meta["8v8_1gk_4x15"]["positions"]);

  // NIEUWE CODE
  // Haal de lijst met doelmannen op (clean array maken)
  $gk_list = array_map('trim', explode(',', $doelmannen ?? '')); 

  // Tel hoeveel van de geselecteerde spelers ($squad) daadwerkelijk doelman zijn
  $fixed_count = 0;
  foreach ($squad as $speler) {
      if (in_array($speler, $gk_list)) {
          $fixed_count++;
      }
  }

  // Fallback: Als er geen doelmannen gevonden zijn, houden we de oude logica aan (1ste speler vast)
  if ($fixed_count == 0) {
      $fixed_count = 1;
  }

  // We pakken nu dynamisch het aantal keepers van het begin van de array
  $fixed_players = array_slice($squad, 0, $fixed_count);
  $others = array_slice($squad, $fixed_count);
  

  $usedHashes = [];
  $tries = 0;

  $result = null;

  // ------------------------------------------------------------
  // [5] Opstelling genereren en optimaliseren
  // ------------------------------------------------------------
  // Probeert via shufflen en scoreberekening de beste opstelling te vinden.
  // Houdt rekening met verboden posities, min/max speeltijd, en optimaliseert op score/rating.
  // Initialiseer tellers
  // ------------------------------------------------------------
  // [5] Opstelling genereren en optimaliseren
  // ------------------------------------------------------------
  $fail_stats = [
      'no_max' => [],
      'no_min' => [],
      'forbidden' => []
  ];

  if ($shuffle_type == "coach"){
      $list_of_players = $squad;
      $result = new Game($list_of_players,$onlyBestSelection,$format);
      $total_points = $result->score;
      $max_points = $total_points;
      $selected["run"] = $tries;
      $selected["ws_id"] = $wisselschema_index[$format] ?? 0;
      $selected["total_points"] = $total_points;
      $selected["rating"] = $result->rating;
      $selected["volgorde"] = implode(',', $list_of_players);
      $selected["team"] = $result;
      $lineup = $result;
  } else {
      // --- BEGIN BACKTRACKING OPTIMIZATION ---
      $top_lineups = []; // Voor het vasthouden van de absolute top over ALLE schemas
      $gecombineerde_fail_stats = ['no_max' => [], 'no_min' => [], 'forbidden' => []];
      $beschikbare_schemas = !empty($beschikbare_schemas) ? $beschikbare_schemas : [0]; 

      // Itereer nu OPEENVOLGEND OVER ALLE SCHEMAS 
      foreach ($beschikbare_schemas as $schema_id) {
          $te_gebruiken_schema = $schema_id;
          if (file_exists($wissel_file)) {
              include $wissel_file; // LAAD SPECIFIEKE META-REGELS (min/max posities) VOOR DIT SCHEMA IN
          }

          // DYNAMISCH CALCULEREN: Sommige oudere schemas (zoals '9999') vergeten door te geven wélke positie indexen de minste/meeste minuten hebben.
          // Hier berekenen we het wiskundig perfect in een fractie van een milliseconde voor álles!
          $schema_events = $events[$format][count($squad)] ?? [];
          $pos_playtimes = [];
          foreach ($schema_events as $game_data) {
              if (isset($game_data["lineup"]) && isset($game_data["duration"])) {
                  foreach ($game_data["lineup"] as $pos => $player_idx) {
                      if (!isset($pos_playtimes[$player_idx])) $pos_playtimes[$player_idx] = 0;
                      $pos_playtimes[$player_idx] += (int)$game_data["duration"];
                  }
              }
          }
          unset($pos_playtimes[0]); // Sluit The Unstoppable (Doelmannen) uit van deze vloek
          if (!empty($pos_playtimes)) {
              $min_time = min($pos_playtimes);
              $max_time = max($pos_playtimes);
              if ($min_time !== $max_time) {
                  $wisselschema_meta[$format]['time']['min'] = array_keys($pos_playtimes, $min_time);
                  $wisselschema_meta[$format]['time']['max'] = array_keys($pos_playtimes, $max_time);
              } else {
                  $wisselschema_meta[$format]['time']['min'] = [];
                  $wisselschema_meta[$format]['time']['max'] = [];
              }
          }

          $verbodenIndexenMax = $wisselschema_meta[$format]['time']['max'] ?? [];
          $verbodenIndexenMin = $wisselschema_meta[$format]['time']['min'] ?? [];
          $noMaxList = $no_max[$wedstrijd] ?? [];
          $noMinList = $no_min[$wedstrijd] ?? [];
          $total_required = count($fixed_players) + count($others);
          
          $valid_lineups = [];
          // Maximaal te behalen unieke oplossingen per matrix
          $solutions_limit = min(5000, max(500, (int)($max_runs / 5))); 
          $max_nodes_limit = $max_runs * 200; // Hard killswitch voor de CPU

          $fail_stats = ['no_max' => [], 'no_min' => [], 'forbidden' => []];
          $tries = 0;

          // 1. Check de vaste spelers (Doelmannen) op geldigheid
          $fixed_valid = true;
          foreach ($fixed_players as $index => $speler) {
              if (in_array($speler, $noMaxList) && in_array($index, $verbodenIndexenMax)) {
                  $fail_stats['no_max'][$speler] = ($fail_stats['no_max'][$speler] ?? 0) + 1;
                  $fixed_valid = false;
              }
              if (in_array($speler, $noMinList) && in_array($index, $verbodenIndexenMin)) {
                  $fail_stats['no_min'][$speler] = ($fail_stats['no_min'][$speler] ?? 0) + 1;
                  $fixed_valid = false;
              }
              $verbodenPosities = $player_no_go[$speler] ?? [];
              $positiesVoorIndex = $wisselschema_meta[$format]["positions"][$index] ?? [];
              foreach ($positiesVoorIndex as $positie) {
                  if (in_array($positie, $verbodenPosities)) {
                      $fail_stats['forbidden'][$speler] = ($fail_stats['forbidden'][$speler] ?? 0) + 1;
                      $fixed_valid = false;
                  }
              }
          }

          if ($fixed_valid) {
              // 2. Recursive Backtracking Logica (DFS Pruning)
              $backtrack = function($current_index, $current_permutation, $remaining_players) use (
                  &$backtrack, &$valid_lineups, &$tries, $solutions_limit, $max_nodes_limit, $total_required,
                  $verbodenIndexenMax, $verbodenIndexenMin, $noMaxList, $noMinList, 
                  $player_no_go, $wisselschema_meta, $format, &$fail_stats
              ) {
                  // Safety checks
                  if (count($valid_lineups) >= $solutions_limit) return;
                  if ($tries > $max_nodes_limit) return;
                  
                  // Base case
                  if ($current_index === $total_required) {
                      $valid_lineups[] = $current_permutation;
                      return;
                  }

                  // Shuffle remaining keys om diverse paden te ontdekken (geen lineaire iteratie!)
                  $keys = array_keys($remaining_players);
                  shuffle($keys);
                  
                  foreach ($keys as $array_key) {
                      $speler = $remaining_players[$array_key];
                      $tries++;
                      
                      // Check constraints on this specific depth before diving
                      if (in_array($speler, $noMaxList) && in_array($current_index, $verbodenIndexenMax)) {
                          $fail_stats['no_max'][$speler] = ($fail_stats['no_max'][$speler] ?? 0) + 1;
                          continue;
                      }
                      
                      if (in_array($speler, $noMinList) && in_array($current_index, $verbodenIndexenMin)) {
                          $fail_stats['no_min'][$speler] = ($fail_stats['no_min'][$speler] ?? 0) + 1;
                          continue;
                      }
                      
                      $verbodenPosities = $player_no_go[$speler] ?? [];
                      $positiesVoorIndex = $wisselschema_meta[$format]["positions"][$current_index] ?? [];
                      $pos_invalid = false;
                      foreach ($positiesVoorIndex as $positie) {
                          if (in_array($positie, $verbodenPosities)) {
                              $pos_invalid = true;
                              $fail_stats['forbidden'][$speler] = ($fail_stats['forbidden'][$speler] ?? 0) + 1;
                              break;
                          }
                      }
                      if ($pos_invalid) continue;

                      // Branch is valid! Go deeper.
                      $next_permutation = $current_permutation;
                      $next_permutation[] = $speler;
                      
                      $next_remaining = $remaining_players;
                      unset($next_remaining[$array_key]);
                      
                      $backtrack($current_index + 1, $next_permutation, $next_remaining);
                      
                      if (count($valid_lineups) >= $solutions_limit) return;
                      if ($tries > $max_nodes_limit) return;
                  }
              };

              $backtrack(count($fixed_players), $fixed_players, $others);
          }
          
          // Merge stats to super list
          foreach (['no_max', 'no_min', 'forbidden'] as $fType) {
              foreach($fail_stats[$fType] as $p => $count) {
                  $gecombineerde_fail_stats[$fType][$p] = ($gecombineerde_fail_stats[$fType][$p] ?? 0) + $count;
              }
          }
          
          // 3. Bereken the best score uit de 100% geldige verzameling van DIT schema
          foreach ($valid_lineups as $list_of_players) {
              $game_result = new Game($list_of_players, $onlyBestSelection, $format);
              $total_points = $game_result->score;
              
              // Geheugenoptimalisatie: we devalueren game objects direct tenzij ze kans maken op de Top 5
              $min_top_score = empty($top_lineups) ? -1 : min(array_column($top_lineups, 'total_points'));
              
              $ws_id_current = $wisselschema_index[$format] ?? $schema_id;

              if (count($top_lineups) < 5 || $total_points > $min_top_score) {
                  $top_lineups[] = [
                      "run" => $tries, // Total nodes evaluated for this matrix
                      "ws_id" => $ws_id_current,
                      "total_points" => $total_points,
                      "rating" => $game_result->rating,
                      "volgorde" => implode(',', $list_of_players),
                      "team" => $game_result
                  ];
                  
                  // Sorteer onze master lijst van hoog naar laag
                  usort($top_lineups, function($a, $b) {
                      return $b['total_points'] <=> $a['total_points'];
                  });
                  
                  // Diversiteitsfilter: Maximaal 2 representaties van exact hetzelfde wisselschema
                  $filtered_lineups = [];
                  $schema_counts = [];
                  foreach ($top_lineups as $item) {
                      $w_id = $item['ws_id'];
                      if (!isset($schema_counts[$w_id])) {
                          $schema_counts[$w_id] = 0;
                      }
                      if ($schema_counts[$w_id] < 2) {
                          $filtered_lineups[] = $item;
                          $schema_counts[$w_id]++;
                      }
                  }
                  
                  $top_lineups = $filtered_lineups;
                  
                  // Knip direct af op 5 na de diversiteitsfilter om geheugen te besparen
                  $top_lineups = array_slice($top_lineups, 0, 5);
              }
          }
      } // -- END FOREACH SCHEMA LOOP

      if (!empty($top_lineups)) {
          $top_selected_options = $top_lineups; 
          
          $selected = $top_lineups[0]; // Active selection = Number 1
          $result = $top_lineups[0]["team"];
          $lineup = $top_lineups[0]["team"];
          $max_points = $top_lineups[0]["total_points"];
          
          // Om te voorkomen dat failstats corrupt gaat in de UI als we geen errors logden:
          $fail_stats = $gecombineerde_fail_stats;
      } else {
          $result = null; // Forces fail_stats analysis output!
          $fail_stats = $gecombineerde_fail_stats;
      }
      // --- END BACKTRACKING OPTIMIZATION ---
  }

  // Nu de uitgebreide debug output als er niets gevonden is
  if (is_null($result)) {
      echo "<div style='font-family: Arial, sans-serif; padding: 20px; border: 2px solid red; background: #fff5f5; margin-bottom: 20px;'>";
      echo "<h2 style='color: #d9534f;'>❌ Geen geldige opstelling gevonden</h2>";
      echo "<p><strong>Schema:</strong> <code>" . basename($wissel_file) . "</code> | <strong>Pogingen:</strong> $tries</p>";

      echo "<h3>🕵️ Bottleneck Analyse</h3>";
      echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%; background: white; text-align: left;'>";
      echo "<tr style='background: #eee;'><th>Speler</th><th>Verboden Positie</th><th>Te weinig minuten (No-Min)</th><th>Te veel minuten (No-Max)</th><th>Totaal Afgekeurd</th></tr>";

      $all_names = array_unique(array_merge(array_keys($fail_stats['forbidden']), array_keys($fail_stats['no_min']), array_keys($fail_stats['no_max'])));
    
      // Sorteren op meeste fouten
      usort($all_names, function($a, $b) use ($fail_stats) {
          $totalA = ($fail_stats['forbidden'][$a] ?? 0) + ($fail_stats['no_min'][$a] ?? 0) + ($fail_stats['no_max'][$a] ?? 0);
          $totalB = ($fail_stats['forbidden'][$b] ?? 0) + ($fail_stats['no_min'][$b] ?? 0) + ($fail_stats['no_max'][$b] ?? 0);
          return $totalB <=> $totalA;
      });

      // Zoek rond regel 343 in index.php naar de 'Bottleneck Analyse' tabel
      foreach ($all_names as $naam) {
          $f = $fail_stats['forbidden'][$naam] ?? 0;
          $mi = $fail_stats['no_min'][$naam] ?? 0;
          $ma = $fail_stats['no_max'][$naam] ?? 0;
          $total = $f + $mi + $ma;

          // NIEUW: Haal de specifieke posities op die op '0' staan voor deze speler
          $verbodenLijst = isset($player_no_go[$naam]) ? implode(', ', $player_no_go[$naam]) : '-';

          echo "<tr>";
          echo "<td><strong>$naam</strong></td>";
          // We voegen de lijst met nummers toe onder de teller van verboden posities
          echo "<td>$f <br><small style='color:red;'>Geblokkeerde posities: $verbodenLijst</small></td>";
          echo "<td>$mi</td>";
          echo "<td>$ma</td>";
          echo "<td><strong>$total</strong></td>";
          echo "</tr>";
      }

      /*foreach ($all_names as $naam) {
          $f = $fail_stats['forbidden'][$naam] ?? 0;
          $mi = $fail_stats['no_min'][$naam] ?? 0;
          $ma = $fail_stats['no_max'][$naam] ?? 0;
          $total = $f + $mi + $ma;
          echo "<tr><td><strong>$naam</strong></td><td>$f</td><td>$mi</td><td>$ma</td><td><strong>$total</strong></td></tr>";
      }*/
      echo "</table></div>";
      die();
  }
  
  
  if (is_null($result)) {
        echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid red; background: #fff5f5;'>";
        echo "<h2 style='color: #d9534f;'>❌ Geen geldige opstelling gevonden</h2>";
        echo "<p><strong>Geprobeerd bestand:</strong> <code>" . basename($wissel_file) . "</code></p>";
        echo "<p><strong>Aantal pogingen:</strong> $max_runs</p>";
        echo "<hr>";
    
        echo "<h3>Mogelijke oorzaken:</h3>";
        echo "<ul>";
            // Check 1: Te veel beperkingen op posities
            echo "<li><strong>Positiebeperkingen:</strong> Controleer of spelers scores van 0 hebben op posities die ze móeten invullen.</li>";
        
            // Check 2: No-Min / No-Max conflicten
            if (!empty($no_min[$wedstrijd]) || !empty($no_max[$wedstrijd])) {
                echo "<li><strong>Min/Max Conflict:</strong> Spelers die niet de minste minuten mogen maken: <code>" . implode(', ', $no_min[$wedstrijd]) . "</code>. <br>";
                echo "Spelers die niet de meeste minuten mogen maken: <code>" . implode(', ', $no_max[$wedstrijd]) . "</code>.</li>";
            }
        echo "</ul>";

        echo "<h3>Speler Checklist (Verboden posities):</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Speler</th><th>Mag NIET op deze posities</th></tr>";
        foreach ($squad as $speler) {
            $verboden = $player_no_go[$speler] ?? [];
            echo "<tr><td>$speler</td><td>" . (empty($verboden) ? "<span style='color:green;'>Geen beperkingen</span>" : "<span style='color:red;'>" . implode(', ', $verboden) . "</span>") . "</td></tr>";
        }
        echo "</table>";
    
        echo "</div>";
        die();
  } else {
    //Voeg toe aan pt_stats    
    if (!in_array($wedstrijd,array_keys($pt_all_games))){
      $pt_all_games[$wedstrijd] = array(
        "duration" => $lineup->total_duration * 60,
        "players" => $lineup->time_played,
        "playtime" => $lineup->time_in_position
          
      );
    }
    
    $huidige_wedstrijd = $pt_all_games[$wedstrijd];
    $pt_stats = build_playtime_stats($pt_all_games, $player_scores);
    
    // Datumdeel uit key halen
    list($datum_raw, $wedstrijd_naam) = explode('_', $wedstrijd, 2);
    // Datumformaten genereren vanuit YYMMDD
    $jaar = '20' . substr($datum_raw, 0, 2); // '25' → '2025'
    $maand = substr($datum_raw, 2, 2);       // '09'
    $dag = substr($datum_raw, 4, 2);         // '27'
    $datum_full = "$jaar-$maand-$dag";       // '2025-09-27'
    $datum_short = "$dag/$maand";            // '27/09'
    // Uitbreiden van array
    $huidige_wedstrijd['key'] = $wedstrijd;
    $huidige_wedstrijd['date_short'] = $datum_short;
    $huidige_wedstrijd['date_full'] = $datum_full;
    
    
    //dpr($result,__LINE__);
  }

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


  if (isset($selected["ws_id"])) {
    $page_title .= "_" . $selected["ws_id"];
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
