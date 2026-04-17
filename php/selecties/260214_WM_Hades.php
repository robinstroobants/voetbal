<?php
$shuffle_type = "random";
$shuffle_type = "coach";
$te_gebruiken_schema = 11; 
$doelmannen = "Staf";
$selectie = "Tiebe, Vinn, Miel, Loris, Arda, Jayden, MuratY, Rune, Seppe";
$building_lineup = 0;
$show_pt_array = 0;

$max_runs = 800000;
$no_min_players = "";

$key = str_replace(".php","",basename(__FILE__, '.php'));
$db_date = new DateTime('20' . substr($key,0,2) . '-' .substr($key,2,2) . '-'.substr($key,4,2).'');
$today = new DateTime();
$interval = $db_date->diff($today);
if ($interval->format('%a') < $selection_age_to_include || isset($_GET['wedstrijd'])){
  $format="8v8_1gk_6x15";

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
  
  $game_titles[$key] = array(
    1=> array("title"=>"KVV Zepperen-Brustem", "info"=> "Veld 11 14:30"),
    2=> array("title"=>"RC Hades Hasselt (Wit)", "info"=> "Veld 3B 14:55"),
    3=> array("title"=>"Weerstand Koersel (Wit)", "info"=> "Veld 1A 16:10"),
    4=> array("title"=>"2de Poule W1", "info"=> "???"),
    5=> array("title"=>"2de Poule W2", "info"=> "???"),
    6=> array("title"=>"Finale", "info"=> "???"),
  );

}