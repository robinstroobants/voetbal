  <?php
    $show_pt_array = 0;
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
  require_once("playerscores.php");




  if (!isset($max_runs) || $max_runs === 0 || $max_runs === null) {
      $max_runs = 5000;
  }

  $shuffle_type = "random"; //override in selectie
  $no_max = array(); //spelers die niet op een positie mogen staan die het meeste speelt
  $no_min = array(); // spelers die niet op een positie mogen staan die het minst speelt

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


   require_once("speelminuten.php");

   $keys = array_keys($pt_all_games);
   $index = array_search($wedstrijd, $keys);

   // Initieer beide als null
   $vorige_wedstrijd = null;
   $huidige_wedstrijd = null;
   $volgende_wedstrijd = null;

   if ($index === false) {
       // Wedstrijd niet gevonden: enkel vorige wedstrijd is het eerste (nieuwste)
       $vorige_wedstrijd_key = $keys[0];
       $vorige_wedstrijd = $pt_all_games[$vorige_wedstrijd_key];
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
  $game_titles = array();
  $playtime = array();
  $format = $game_formats[$wedstrijd];
  $timestamp_title = 0;
  $all_points = array();
  $squad = $selecties[$wedstrijd];
  $total_players = count($squad);
  
  if ($vorige_wedstrijd !== null) {
      $speeltijd_vorige_wedstrijd = $vorige_wedstrijd["players"];

      // Filter op spelers die in de huidige selectie zitten
      $speeltijd_vorige_wedstrijd = array_filter(
          $speeltijd_vorige_wedstrijd,
          fn($key) => in_array($key, $squad, true),
          ARRAY_FILTER_USE_KEY
      );

      // --- MIN namen (op basis van vorige wedstrijd) ---
      if (!empty($speeltijd_vorige_wedstrijd)) {
          $min = min($speeltijd_vorige_wedstrijd);
          $namen_met_min = array_keys($speeltijd_vorige_wedstrijd, $min, true);
 
          // 1. Splits de string in een array en verwijder spaties van elk element
          $namen_uit_string = array_map('trim', explode(',', $no_min_players));

          // (Extra check om lege elementen te verwijderen, bv. als de string leeg is)
          $namen_uit_string = array_filter($namen_uit_string);

          // 2. Voeg de arrays samen. De namen uit de string komen nu vooraan.
          $samengevoegd = array_merge($namen_uit_string, $namen_met_min);

          // 3. Verwijder duplicaten. array_unique behoudt het eerste element dat het tegenkomt.
          // Omdat 'Staf', 'Senn', 'Jack' en 'Thibo' nu vooraan staan, worden die behouden.
          $resultaat = array_unique($samengevoegd);

          // 4. (Optioneel) Als je een schone, hergeïndexeerde array wilt (met keys 0, 1, 2...), gebruik dan array_values().
          $namen_met_min = array_values($resultaat);

          // Toon het eindresultaat
          // print_r($resultaat);
 
          //dpr($namen_met_min,"namen_met_min");
          if (count($namen_met_min) > 5) {
              // >4: kies willekeurig 3 i.p.v. leeg te maken
              shuffle($namen_met_min);
              $no_min_players = implode(',', array_slice($namen_met_min, 0, 3));
          } else {
              // ≤4: neem ze allemaal
              $no_min_players = implode(',', $namen_met_min);
          }
      }
      
      
      // --- MAX namen (hoogste waarde < 3600 in vorige wedstrijd) ---
      if (empty($no_max_players) && !empty($speeltijd_vorige_wedstrijd)) {
          $filtered = array_filter($speeltijd_vorige_wedstrijd, fn($v) => $v < 3600);

          if (!empty($filtered)) {
              $max = max($filtered);
              $namen_met_max = array_keys($speeltijd_vorige_wedstrijd, $max, true);
          } else {
              $namen_met_max = [];
          }

          if (count($namen_met_max) > 3) {
              $no_max_players = "";
          } else {
              $no_max_players = implode(',', $namen_met_max);
          }
      }
  }

  /**
   * AANVULLEN van $no_min_players:
   * - Enkel als $no_min_players NIET leeg is.
   * - Vul aan met spelers met de minste TOTALE minuten over ALLE wedstrijden ($pt_all_games),
   *   beperkt tot de huidige selectie ($squad), tot je 6 spelers hebt.
   * - Als er al >= 6 in $no_min_players zaten, laat zoals het is (geen extra toevoegingen).
   * - Sorteer het eindresultaat op oplopende totale minuten.
   */
  if (!empty($no_min_players)) {
      // Startset uit string -> array, enkel spelers in $squad behouden
      $noMinSet = array_values(array_filter(array_map('trim', explode(',', $no_min_players))));
      $noMinSet = array_values(array_intersect($noMinSet, $squad));
      $noMinSet = array_unique($noMinSet);

      // Alleen aanvullen als er minder dan 4 zijn
      // en aanvullen met spelers die vorige week het minste speelden
      // en vervolgens die het minste speelde in totaal
      $need = 5 - count($noMinSet);
      if ($need > 0) {
          // Totale speeltijd over ALLE wedstrijden, gefilterd op $squad
          $totaal_per_speler = array_fill_keys($squad, 0);
          foreach ($pt_all_games as $g) {
              $playersTimes = $g['players'] ?? [];
              foreach ($playersTimes as $p => $secs) {
                  if (isset($totaal_per_speler[$p])) {
                      $totaal_per_speler[$p] += (int)$secs;
                  }
              }
          }
          // Sorteer oplopend op totaal (minst gespeelde eerst)
          asort($totaal_per_speler, SORT_NUMERIC);
          //pr($totaal_per_speler,"totaal_per_speler");
          // Voeg spelers toe met de minste minuten die nog niet in $noMinSet zitten
          foreach ($totaal_per_speler as $p => $secs) {
              if (in_array($p, $noMinSet, true) || in_array($p,explode(",",$doelmannen))) continue;
              $noMinSet[] = $p;
              $need--;
              if ($need <= 0) break;
          }

          // Eindlijst sorteren op oplopende totale minuten (zoals gevraagd)
          usort($noMinSet, function($a, $b) use ($totaal_per_speler) {
              return ($totaal_per_speler[$a] ?? PHP_INT_MAX) <=> ($totaal_per_speler[$b] ?? PHP_INT_MAX);
          });

          // Zet terug naar CSV-string voor downstream code
          $no_min_players = implode(',', $noMinSet);
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
  
  //pr($no_max,"no max");
  //dpr($no_min, "no min");
  
  
  

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
  
  if ($shuffle_type == "coach"){
    $list_of_players = $squad;
    $result = new Game($list_of_players,$onlyBestSelection,$format);
    $total_points = $result->score;
    $max_points = $total_points;
    $selected["run"] = $tries;
    $selected["ws_id"] = $wisselschema_index[$format];
    $selected["total_points"] = $total_points;
    $selected["rating"] = $result->rating;
    $selected["volgorde"] = implode(',', $list_of_players);
    $selected["team"] = $result;
    $lineup = $result;
  } else {
    while (count($usedHashes) < $max_runs && $tries < $max_runs * 10) {
      $tries++;
      $shuffled = $others;
      if ($max_runs > 1){
        shuffle($shuffled);
      }
  
        $list_of_players = array_merge([$fixed], $shuffled);
      
        $player_listing = implode(',', $list_of_players);

        $hash = md5(implode(',', $list_of_players));
        if (!isset($usedHashes[$hash])) {
            $usedHashes[$hash] = true;
        
            // check lineup voor excludes (bv spelers in $no_max_players in selectie files 
            if (isset($wisselschema_meta) && is_array($wisselschema_meta)) {
              if (isset($wisselschema_meta[$format]) && is_array($wisselschema_meta[$format])) {
                if (isset($wisselschema_meta[$format]['time']) && is_array($wisselschema_meta[$format]['time'])) {
                  if (isset($wisselschema_meta[$format]['time']['max']) && is_array($wisselschema_meta[$format]['time']['max'])) {
                    if (isset($no_max[$wedstrijd]) && is_array($no_max[$wedstrijd]) && count($no_max[$wedstrijd])) {
                      $verbodenIndexen = $wisselschema_meta[$format]["time"]["max"];
                      $teControlerenNamen = $no_max[$wedstrijd];

                      foreach ($teControlerenNamen as $naam) {
                        $positie = array_search($naam, $list_of_players); // Zoek de index van de naam
                        if ($positie === false) {
                          echo __LINE__ . ": ⚠️ Naam '$naam' niet gevonden in de spelerslijst.\n";
                          dpr($list_of_players,"⚠️ Naam '$naam' niet gevonden in de spelerslijst.\n");
                        }
                        if (in_array($positie, $verbodenIndexen)) {
                          //echo "❌ '$naam' staat op positie $positie, en dat is een verboden index.\n";
                          continue 2;
                        } else {
                          //echo "✅ '$naam' staat op positie $positie, en dat is OK.\n";
                        }
                      }
                    }
                  } else {
                    //echo "❌ Geen meta informatie voor max posities voor format '$format'<br/>\n";
                  }
                  if (isset($no_min[$wedstrijd]) && is_array($no_min[$wedstrijd]) && count($no_min[$wedstrijd])) {
                    $verbodenIndexen = $wisselschema_meta[$format]["time"]["min"];
                    $teControlerenNamen = $no_min[$wedstrijd];
                    foreach ($teControlerenNamen as $naam) {
                      $positie = array_search($naam, $list_of_players); // Zoek de index van de naam
                      if ($positie === false) {
                        echo __LINE__ . ": ⚠️ Naam '$naam' niet gevonden in de spelerslijst.\n";
                        dpr($no_min,"⚠️ Naam '$naam' niet gevonden in de spelerslijst (no_min).\n");
                    
                      }
                      if (in_array($positie, $verbodenIndexen)) {
                        //echo "❌ '$naam' staat op positie $positie, en dat is een verboden index.\n";
                        continue 2;
                      } else {
                        //echo "✅ '$naam' staat op positie $positie, en dat is OK.\n";
                      }
                    }
                  } else {
                    //echo "❌ Geen meta informatie voor min posities voor format '$format'<br/>\n";
                  }
                } else {
                  //echo "❌ Geen meta informatie voor time posities voor format '$format'<br/>\n";die();
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
            
              } else {
                //echo "❌ Geen meta informatie voor format '$format'<br/>\n";die();
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
    
  }
  
  
  if (is_null($result)) {
      echo "❌ Geen opstelling gevonden. Verhoog max_runs ($max_runs) in index.php:6";
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
      <link href="css/styles.css?v=2" rel="stylesheet">
    </head>
    <body class="py-4">
    
  <main>
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light border rounded">
        
          <?php if ($vorige_key): ?>
              <?php $query_params['wedstrijd'] = $vorige_key; ?>
              <a href="<?= build_url($base_url, $query_params); ?>" class="btn btn-wedstrijd d-print-none"><i class="fa-solid fa-arrow-left"></i>&nbsp;<?= htmlspecialchars($vorige_key) ?></a>
          <?php else: ?>
            
          <?php endif; ?>

          <h5 class="mb-0 w-100 text-center"><?= $page_title ?></h5>

          <?php if ($volgende_key): ?>
              <?php $query_params['wedstrijd'] = $volgende_key; ?>
              <a href="<?= build_url($base_url, $query_params) ?>" class="btn btn-wedstrijd d-print-none align-middle"><i class="fa-solid fa-arrow-right align-middle me-1"></i>&nbsp;<?= htmlspecialchars($volgende_key) ?></a>
          <?php else: ?>
          <?php endif; ?>
      </div>
      <!--h2><?php echo $page_title; //"Format $lineup->format."; ?></h2-->
      <p class="lead" style="margin-bottom:0">
        <?php 
      
        //pr($selected);
        echo "$total_players spelers aanwezig: " . implode(", ",array_keys($lineup->playerindex)); //. implode(", ",$selected["teams"][0]->playernames) ;
        if ($selected["run"] && $tries > 1) {
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
      
        
      <div class="row mt-4 timetable" style="">
        <div class="col">
          <table id="player-overview" class="table table-bordered table-sm table-hover text-center align-middle">
            <tr>
              <thead>
                <th scope="col">#</th>
                <th scope="col">speeltijd</th>
                <th scope="col">speler</th>
                <th scope="col"><i class="fa-solid fa-clock"></i><br/><small style="font-size:8px"><?php echo $huidige_wedstrijd["date_short"]?></small></th>
                <th scope="col"><i class="fa-solid fa-clock"></i> - 1<br/><small style="font-size:8px"><?php 
                  if (isset($vorige_wedstrijd["date_short"])) {
                      echo $vorige_wedstrijd["date_short"];
                  }
                  ?></small></th>
                <?php foreach ($lineup->positions as $pos): ?>
                  <th scope="col"><?= $pos ?></th>
                <?php endforeach; ?>
              </thead>
            </tr>
            <?php             
            foreach($lineup->playernames as $i=> $player){ 
                $score_for_player = 0;
                if (isset($pt_stats[$player]["played"]) && isset($pt_stats[$player]["available"]) && $pt_stats[$player]["available"] > 0) {
                    $pt_play_perc = ($pt_stats[$player]["played"] / $pt_stats[$player]["available"]) * 100;
                } else {
                    $pt_play_perc = 0; // of een andere fallback waarde
                }              ?>
              <tr>
                <td><?php echo $i+1; ?></td>
                <td><strong><?php echo round($pt_play_perc,1);?>%</strong> <small>(<?php echo round($pt_stats[$player]["played"]/60)  ."/" . round($pt_stats[$player]["available"]/60);?>)</small></td>
                <td><?php echo $player; ?></td>
                <td title="speeltijd deze wedstrijd"><?php echo calctime($lineup->total_playtime[$player]);?></td>
                <td title="speeltijd vorige wedstrijd"> 
                  <?php if ($vorige_wedstrijd !== null && isset($vorige_wedstrijd["players"][$player])): ?>
                    <small><i><?php echo calctime($vorige_wedstrijd["players"][$player]); ?></i></small>
                  <?php else: ?>
                    /
                  <?php endif; ?>
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
      
      
        <div class="timetable row mt-4 do_not_break">
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
        , "players" => array(<?php echo implode(",\n\t",$playertime_to_print) ; /*foreach($playtime as $pos=>$seconds){ echo $pos; echo calctime($seconds) ;))*/?>)
        , "playtime" => array(
          <?php foreach($lineup->time_in_position as $_p=>$_positions){ ?>
          "<?php echo $_p; ?>" => array(
            <?php 
            $cnt = 0;
            foreach($_positions as $_pos=>$_time){ 
              if (is_numeric($_pos)){
                if ($cnt > 0) {
                  echo ", ";
                }
                echo $_pos . "=>" . $_time;
                $cnt++;
              }
            } ?>
          ),
          <?php } ?>
        )
          
          
          )
    </pre>
  <?php 

  } 
  //pr($wisselschema_meta, __LINE__ . ": wisselschema_meta");
  ?>
  
    </body>
  </html>
  