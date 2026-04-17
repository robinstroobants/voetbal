<?php
$shuffle_type = "random";
$shuffle_type = "coach";
$te_gebruiken_schema = 12;
$doelmannen = "NoahS";
$selectie = "Thibo, Jayden, MuratC, Miel, Seppe, Léno, Arda,Senn, Rune, Jack";
//$building_lineup = 1;
$show_pt_array = 1;

$max_runs = 800000;
//$no_max_players = "Jayden,Thibo.Léno";
//$no_max_players = "Jayden,Thibo";

$key = str_replace(".php","",basename(__FILE__, '.php'));
$db_date = new DateTime('20' . substr($key,0,2) . '-' .substr($key,2,2) . '-'.substr($key,4,2).'');
$today = new DateTime();
$interval = $db_date->diff($today);
if ($interval->format('%a') < $selection_age_to_include || isset($_GET['wedstrijd'])){
  
  $format="8v8_1gk_4x15";
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