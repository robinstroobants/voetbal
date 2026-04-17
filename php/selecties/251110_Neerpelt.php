<?php
$show_position_stats=0;
$shuffle_type = "random";
$shuffle_type = "coach";
$te_gebruiken_schema = "16";
$doelmannen = "NoahS";
$selectie="Vinn, Miel, Thibo, MuratY, Arda, Scout, NoahW,Alessio, MuratC, Jayden, Léno";
$building_lineup = 0;

$max_runs = 900000;
$no_min_players = "";

$key = str_replace(".php","",basename(__FILE__, '.php'));
$db_date = new DateTime('20' . substr($key,0,2) . '-' .substr($key,2,2) . '-'.substr($key,4,2).'');
$today = new DateTime();
$interval = $db_date->diff($today);
if ($interval->format('%a') < $selection_age_to_include || isset($_GET['wedstrijd'])){
  $format="8v8_1gk_3x20";
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
    1=> array("title"=>"Esperanza 1", "info"=> "Veld KG2 18:00"),
    2=> array("title"=>"Kadijk SK 1", "info"=> "Veld KG2 18:50"),
    3=> array("title"=>"Esperanza 2 H1", "info"=> "Veld C2 20:05"),
    4=> array("title"=>"Esperanza 2 H2", "info"=> "Veld C2 20:05"),
  );
}


