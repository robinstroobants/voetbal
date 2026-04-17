<?php
$shuffle_type = "random";
//$shuffle_type = "coach";
//$te_gebruiken_schema = 207; 
//$te_gebruiken_schema = 1181; 
$doelmannen = "Franklin";
$selectie = "Rune, Miel, Léno, Thibo, MuratC, Jack, Wannes, Alessio, Seppe";
//$selectie = "Léno, Miel, MuratC, Thibo, Alessio, Seppe, Wannes, Rune";

//9 spelers aanwezig: Franklin, Léno, Miel, MuratC, Thibo, Alessio, Seppe, Wannes, Rune // 1181 · 85.39%

//10 spelers aanwezig: Franklin, Alessio, Miel, MuratC, Léno, Seppe, Jack, Wannes, Thibo, Rune // 1375 · 81.72%
//10 spelers aanwezig: Franklin, Rune, MuratC, Miel, Léno, Jack, Seppe, Wannes, Alessio, Thibo // 9999 · 84.69%



$building_lineup = 0;
$show_pt_array = 0;
$max_runs = 600000;
$no_max_players = "";

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