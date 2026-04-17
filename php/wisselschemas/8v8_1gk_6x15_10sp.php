<?php

$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);
$building_lineup = 0;
$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);

$ws=array();

$ws[11] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 3,
      9  => 2,
      10  => 1,
      11  => 7
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 3,
      9  => 2,
      10  => 1,
      11  => 4
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 11 => 4
      ),
      "out" => array(
         2 => 5
        , 11 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 1,
      5  => 3,
      7  => 6,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
  
  
  
  8 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),
  9 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),  
  
  
  10 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
    ),
    "duration" => 7.5*60,
    "game_counter" => 6
  ), 
  11 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 6
  ),  
  
  
); 
if (!isset($te_gebruiken_schema)){
 $te_gebruiken_schema = array_rand($ws);
}
if ($te_gebruiken_schema == 0){
  $te_gebruiken_schema = array_rand($ws);  
}



$wisselschema_index[$key] = $te_gebruiken_schema;
$events[$key][$aantal_spelers] = $ws[$wisselschema_index[$key]];

$positions_per_index = array();
foreach($ws[$wisselschema_index[$key]] as $blokjes){
  //dpr($blokjes["lineup"]);
  foreach($blokjes["lineup"] as $p => $i){
    $positions_per_index[$i][$p] = $p;
  }
}
$wisselschema_meta[$key]["positions"] = $positions_per_index;

if ($te_gebruiken_schema == 11){

}
