<?php
$shuffle_type = "random";
//$shuffle_type = "coach";
//$te_gebruiken_schema = 1375; 
$building_lineup = 0;
$doelmannen = "Staf";
$selectie = "Rune, MuratY, Miel, Loris, Seppe, Jack, Vinn, Tiebe, Jayden";

$show_pt_array = 0;
$max_runs = 1800000;
$no_min_players = "";
$no_max_players = "";

$key = str_replace(".php","",basename(__FILE__, '.php'));
$db_date = new DateTime('20' . substr($key,0,2) . '-' .substr($key,2,2) . '-'.substr($key,4,2).'');
$today = new DateTime();
$interval = $db_date->diff($today);
if ($interval->format('%a') < $selection_age_to_include || isset($_GET['wedstrijd'])){

  $format="8v8_0gk_4x15";
  $aantal = count(array_filter(explode(',', $doelmannen)));

  // 2. Vervang het getal voor 'gk' door het nieuwe aantal
  // '/\d+gk/' is een patroon: 
  // \d+ betekent "één of meer cijfers"
  // gk  betekent letterlijk "gk"
  $format = preg_replace('/\d+gk/', $aantal . 'gk', $format);
  
  
  $selectie = str_replace(", ",",", $selectie);
  $selectie = str_replace(" ,",",", $selectie);
  $doelmannen = str_replace(", ",",", $doelmannen);
  $doelmannen = str_replace(" ,",",", $doelmannen);
  $player_sel = explode(",",$selectie);
  if (!empty($doelmannen)){
    $sel = explode(",",$doelmannen);  
    $sel = array_merge($sel,$player_sel); 
    $selecties[$key] = $sel;
  } else {
    $selecties[$key] = $player_sel;
    
  }
  
  $game_formats[$key] = $format;


}