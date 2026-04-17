<?php
$shuffle_type = "random";
$shuffle_type = "coach";
$te_gebruiken_schema = 12; 
$doelmannen = "";
$selectie = "Vinn, Thibo,Miel, Tiebe, MuratY, Arda, Wannes, Jayden, Rune";

//$building_lineup =1;
$show_pt_array = 0;

$max_runs = 800000;
$no_min_players = "";

$key = str_replace(".php","",basename(__FILE__, '.php'));
$db_date = new DateTime('20' . substr($key,0,2) . '-' .substr($key,2,2) . '-'.substr($key,4,2).'');
$today = new DateTime();
$interval = $db_date->diff($today);
if ($interval->format('%a') < $selection_age_to_include || isset($_GET['wedstrijd'])){

  $format="8v8_0gk_4x15";
  $selectie = str_replace([" ,", ", "], ",", $selectie);
  $doelmannen = str_replace([" ,", ", "], ",", $doelmannen);
  
  // Filter lege waarden uit de arrays
  $player_sel = array_filter(explode(",", $selectie));
  $sel_gk = array_filter(explode(",", $doelmannen));
  
  $aantal = count($sel_gk);

  // 2. Vervang het getal voor 'gk' door het nieuwe aantal
  // '/\d+gk/' is een patroon: 
  // \d+ betekent "één of meer cijfers"
  // gk  betekent letterlijk "gk"
  $format = preg_replace('/\d+gk/', $aantal . 'gk', $format);
  
  // Voeg samen en herindexeer (0, 1, 2...)
  $sel = array_values(array_merge($sel_gk, $player_sel));

    
  $game_formats[$key] = $format;
  $selecties[$key] = $sel;


}