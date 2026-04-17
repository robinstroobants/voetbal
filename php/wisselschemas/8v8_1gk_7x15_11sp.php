<?php
$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);

$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);
/*
    "positions" => array(
      4 => array(
        "from" => 9,
        "player" => 3
      )
    ),
*/
$ws=array();
$ws[9] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 3,
      9  => 2,
      10  => 1,
      11  => 4
    ),
    "bench" => array(
      7,10,5
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 10,
      9  => 2,
      10  => 1,
      11  => 7
    ),
    "bench" => array(
      4,9,3
    ),
    "subs" => array(
      "in" => array(
         2 => 5
        , 7 => 10
        , 11 => 7
      ),
      "out" => array(
         2 => 9
        , 7 => 3
        , 11 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  

  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 3,
      5  => 7,
      7  => 9,
      9  => 10,
      10  => 8,
      11  => 1
    ),
    "bench" => array(
      2,5,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 3,
      5  => 7,
      7  => 6,
      9  => 10,
      10  => 2,
      11  => 1
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 2 => 5
        , 7 => 6
      ),
      "out" => array(
         10 => 8
        , 2 => 4
       , 7 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),   

  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 3,
      5  => 8,
      7  => 10,
      9  => 5,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      1,7,2
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 1,
      5  => 2,
      7  => 10,
      9  => 5,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 2 => 7
        , 5 => 2
      ),
      "out" => array(
        2 => 6
        , 4 => 3 
        , 5 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),     

  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 2,
      10  => 3,
      11  => 7
    ),
    "bench" => array(
      10, 5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 5,
      7  => 9,
      9  => 2,
      10  => 1,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 5
        , 10 => 1
        , 11 => 10
      ),
      "out" => array(
         5 => 4
        , 10 => 3
        , 11 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  

  8 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 5,
      5  => 7,
      7  => 3,
      9  => 10,
      10  => 1,
      11  => 4
    ),
    "bench" => array(
      8,9,2
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),
  9 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 5,
      5  => 7,
      7  => 3,
      9  => 2,
      10  => 8,
      11  => 4
    ),
    "bench" => array(
      1,6,10
    ),
    "subs" => array(
      "in" => array(
         2 => 9
        , 9 => 2
        , 10 => 8
      ),
      "out" => array(
         2 => 6
        , 9 => 10
        , 10 => 1
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),  
  
  10 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 3,
      9  => 2,
      10  => 1,
      11  => 4
    ),
    "bench" => array(
      7,10,5
    ),
    "duration" => 7.5*60,
    "game_counter" => 6
  ),
  11 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 10,
      9  => 2,
      10  => 1,
      11  => 7
    ),
    "bench" => array(
      4,9,3
    ),
    "subs" => array(
      "in" => array(
         2 => 5
        , 7 => 10
        , 11 => 7
      ),
      "out" => array(
         2 => 9
        , 7 => 3
        , 11 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 6
  ),  
  
  12=> array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 3,
      5  => 2,
      7  => 9,
      9  => 4,
      10  => 8,
      11  => 10
    ),
    "bench" => array(
      6, 5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 7
  ), 
  13 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 3,
      5  => 5,
      7  => 9,
      9  => 4,
      10  => 1,
      11  => 10
    ),
    "bench" => array(
      2,8,7
    ),
    "subs" => array(
      "in" => array(
        5 => 5
        , 10 => 1
        , 2 => 6
      ),
      "out" => array(
         5 => 2
        , 10 => 8
        , 2 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 7
  ),  
); 
// 81,53,  81.95%
$ws[999] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,10,5
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 10
    ),
    "bench" => array(
      4,9,3
    ),
    "subs" => array(
      "in" => array(
         2 => 5
        , 11 => 10
        , 7 => 7
      ),
      "out" => array(
         2 => 9
        , 11 => 3
        , 7 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  

  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 3,
      5  => 7,
      7  => 9,
      9  => 10,
      10  => 8,
      11  => 1
    ),
    "bench" => array(
      2,5,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 3,
      5  => 7,
      7  => 6,
      9  => 10,
      10  => 2,
      11  => 1
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 2 => 5
        , 7 => 6
      ),
      "out" => array(
         10 => 8
        , 2 => 4
       , 7 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),   

  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 3,
      5  => 8,
      7  => 10,
      9  => 5,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      1,7,2
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 1,
      5  => 2,
      7  => 10,
      9  => 5,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 2 => 7
        , 5 => 2
      ),
      "out" => array(
        2 => 6
        , 4 => 3 
        , 5 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),     

  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 2,
      10  => 3,
      11  => 7
    ),
    "bench" => array(
      10, 5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 5,
      7  => 9,
      9  => 2,
      10  => 1,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 5
        , 10 => 1
        , 11 => 10
      ),
      "out" => array(
         5 => 4
        , 10 => 3
        , 11 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  

  8 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 5,
      5  => 7,
      7  => 4,
      9  => 10,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      8,9,2
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),
  9 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 5,
      5  => 7,
      7  => 4,
      9  => 2,
      10  => 8,
      11  => 3
    ),
    "bench" => array(
      1,6,10
    ),
    "subs" => array(
      "in" => array(
         2 => 9
        , 9 => 2
        , 10 => 8
      ),
      "out" => array(
         2 => 6
        , 9 => 10
        , 10 => 1
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),  
  
  10 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,10,5
    ),
    "duration" => 7.5*60,
    "game_counter" => 6
  ),
  11 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 10
    ),
    "bench" => array(
      4,9,3
    ),
    "subs" => array(
      "in" => array(
         2 => 5
        , 7 => 10
        , 11 => 7
      ),
      "out" => array(
         2 => 9
        , 7 => 3
        , 11 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 6
  ),  
  
  12=> array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 3,
      5  => 2,
      7  => 9,
      9  => 4,
      10  => 8,
      11  => 10
    ),
    "bench" => array(
      6, 5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 7
  ), 
  13 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 3,
      5  => 5,
      7  => 9,
      9  => 4,
      10  => 1,
      11  => 10
    ),
    "bench" => array(
      2,8,7
    ),
    "subs" => array(
      "in" => array(
        5 => 5
        , 10 => 1
        , 2 => 6
      ),
      "out" => array(
         5 => 2
        , 10 => 8
        , 2 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 7
  ),  
); 




if (!isset($te_gebruiken_schema)){
 $te_gebruiken_schema = array_rand($ws);
}
if ($te_gebruiken_schema == 0){
  $te_gebruiken_schema = array_rand($ws);  
}

if ($te_gebruiken_schema == 12){
} else {
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