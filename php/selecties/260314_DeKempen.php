<?php
$shuffle_type = "random";
$shuffle_type = "coach";
$te_gebruiken_schema = 12; 
$building_lineup = 0;
$doelmannen = "Staf";
$selectie = "Tiebe, Miel, Jayden, Arda, Seppe, Vinn, MuratY, Loris, Rune, Jack"; // 216 · 73.91%
//11 spelers aanwezig: Staf, Loris, Tiebe, MuratY, Vinn, Miel, Seppe, Jack, Rune, Arda, Jayden // 2560 · 76.08%
//11 spelers aanwezig: Staf, Loris, Jayden, Tiebe, Miel, Jack, Seppe, MuratY, Rune, Arda, Vinn // 1638 · 75.34%



$show_pt_array = 0;
$max_runs = 800000;
$no_min_players = "";
$no_max_players = "Jack";

$key = str_replace(".php","",basename(__FILE__, '.php'));
$db_date = new DateTime('20' . substr($key,0,2) . '-' .substr($key,2,2) . '-'.substr($key,4,2).'');
$today = new DateTime();
$interval = $db_date->diff($today);
if ($interval->format('%a') < $selection_age_to_include || isset($_GET['wedstrijd'])){

  $format="8v8_1gk_4x15";
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
  $sel = explode(",",$doelmannen);  
  $sel = array_merge($sel,$player_sel);
  $game_formats[$key] = $format;
  $selecties[$key] = $sel;


}