<?php
$show_position_stats=0;
$shuffle_type = "random";
$shuffle_type = "coach";
$te_gebruiken_schema = "999";
$doelmannen = "Staf";
$selectie="Senn, Otis, Loris, Isaiah, Tiebe, Seppe, Wannes, Rune, Jack, Tyrone";
$building_lineup =0;

$max_runs = 900000;
$no_min_players = "";

$key = str_replace(".php","",basename(__FILE__, '.php'));
$db_date = new DateTime('20' . substr($key,0,2) . '-' .substr($key,2,2) . '-'.substr($key,4,2).'');
$today = new DateTime();
$interval = $db_date->diff($today);
if ($interval->format('%a') < $selection_age_to_include || isset($_GET['wedstrijd'])){
  $format="8v8_1gk_7x15";
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
    1=> array("title"=>"RC Hades wit", "info"=> "Veld B1 16:10"),
    2=> array("title"=>"Royal Cappellen FC", "info"=> "Veld A2 16:30"),
    3=> array("title"=>"YRKV Mechelen Red", "info"=> "Veld C1 16:50"),
    4=> array("title"=>"Fase 2 Wedstr 1", "info"=> "~18:10"),
    5=> array("title"=>"Fase 2 Wedstr 2", "info"=> "~18:40"),
    6=> array("title"=>"Fase 2 Wedstr 3", "info"=> "~19:30"),
    7=> array("title"=>"Fase 3 Wedstr 1", "info"=> "~20:40"),
  );
}