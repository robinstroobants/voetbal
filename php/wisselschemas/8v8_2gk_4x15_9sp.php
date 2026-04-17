<?php
$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);
$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);

$ws=array();

$ws[499] = array(
  // GAME 1
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 0,
      7  => 8,
      9  => 6,
      10  => 7,
      11  => 3
    ),
    "bench" => array(2),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 0,
      7  => 8, 
      9  => 2,
      10  => 7,
      11  => 3
    ),
    "bench" => array(6),
    "subs" => array(
      "in" => array(
           9 => 2
      ),
      "out" => array(
           9 => 6
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  // GAME 2
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 1,
      7  => 2,
      9  => 5,
      10  => 7, 
      11  => 3
    ),
    "bench" => array(4),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 1,
      7  => 2,
      9  => 5, 
      10  => 7,
      11  => 4
    ),
    "bench" => array(3),
    "subs" => array(
      "in" => array(
          11 => 4
      ),
      "out" => array(
           11 => 3
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  // GAME 3
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 8,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 5
    ),
    "bench" => array(0),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 8,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 0
    ),
    "bench" => array(5),
    "subs" => array(
      "in" => array(
          11 => 0
      ),
      "out" => array(
        11 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  // GAME 4
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 5, 
      5  => 1,
      7  => 7,
      9  => 3,
      10  => 4,
      11  => 2
    ),
    "bench" => array(6),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 5,
      5  => 6,
      7  => 7,
      9  => 3, 
      10  => 4,
      11  => 2
    ),
    "bench" => array(1),
    "subs" => array(
      "in" => array(
        5 => 6
      ),
      "out" => array(
         5 => 1
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
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

?>
