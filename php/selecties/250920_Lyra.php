<?php
$shuffle_type = "random";
$te_gebruiken_schema = 0; //219;

$shuffle_type = "coach";
$te_gebruiken_schema = 424; //219;

$doelmannen = "NoahS";
$selectie = "Seppe, Léno, Jack, Jayden, Miel, Arda, Thibo, Rune, Senn, MuratC";
//$no_max_players = "Jack,Thibo,Miel"; //spelers die niet op een positie mogen staan die het meeste speelt
//$no_min_players = "0"; // spelers die niet op een positie mogen staan die het minst speelt


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