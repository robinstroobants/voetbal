<?php
$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);

$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);


$ws=array();
$ws[16] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 4,
      9  => 11,
      10  => 2,
      11  => 3
    ),
    "bench" => array(
      7,10,5,1
    ),
    "duration" => 10*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 10,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 7,
      9  => 11,
      10  => 1,
      11  => 10
    ),
    "bench" => array(
      4,9,3,2
    ),
    "subs" => array(
      "in" => array(
         2 => 5
        , 11 => 10
        , 7 => 7
        , 10 => 1
      ),
      "out" => array(
         2 => 9
        , 11 => 3
        , 7 => 4
        , 10 => 11
      )
    ),
    "duration" => 10*60,
    "game_counter" => 1
  ),  

  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 3,
      5  => 7,
      7  => 1,
      9  => 9,
      10  => 8,
      11  => 11
    ),
    "bench" => array(
      2,5,6,10
    ),
    "duration" => 10*60,
    "game_counter" => 2
  ),  
  3 => array(
    "start" => 10,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 3,
      5  => 7,
      7  => 6,
      9  => 9,
      10  => 2,
      11  => 10
    ),
    "bench" => array(
      4,1,8,11
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 2 => 5
        , 7 => 6
          , 11 => 10
          
      ),
      "out" => array(
         10 => 8
        , 2 => 4
       , 7 => 1
        , 11 => 11
      )
    ),
    "duration" => 10*60,
    "game_counter" => 2
  ),   
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 11,
      7  => 4,
      9  => 10,
      10  => 6,
      11  => 5
    ),
    "bench" => array(
      7,1,3,2
    ),
    "duration" => 5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 1,
      5  => 3,
      7  => 4,
      9  => 10,
      10  => 2,
      11  => 5
    ),
    "bench" => array(
      8,6,11,9
    ),
    "subs" => array(
      "in" => array(
         2 => 7
        , 5 => 3
        , 4 => 1
        , 10 => 2
      ),
      "out" => array(
        2 => 9
        ,10  => 6 
        , 5 => 11
          ,4 => 8

      )
    ),
    "duration" => 5*60,
    "game_counter" => 3
  ),
  
  
  
  6 => array(
    "start" => 10,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 1,
      5  => 8,
      7  => 9,
      9  => 10,
      10  => 11,
      11  => 2
    ),
    "bench" => array(
      7,3,5,4
    ),
    "duration" => 5*60,
    "game_counter" => 4
  ),  
  
  7 => array(
    "start" => 15,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 1,
      5  => 4,
      7  => 5,
      9  => 10,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      9,8,6,11
    ),
    "subs" => array(
      "in" => array(
        7 => 5 
       , 5 => 4
       , 2 => 7
         ,10 => 3
      ),
      "out" => array(
         7 => 9
        , 5 => 8
        , 2 => 6
          ,10 => 11
      ),
    ),
    "duration" => 5*60,
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

$positions_per_index = array();
foreach($ws[$wisselschema_index[$key]] as $blokjes){
  //dpr($blokjes["lineup"]);
  foreach($blokjes["lineup"] as $p => $i){
    $positions_per_index[$i][$p] = $p;
  }
}
$wisselschema_meta[$key]["positions"] = $positions_per_index;