<?php

$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);

$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);
$ws=array();

$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 6,
      5  => 10,
      7  => 1,
      9  => 5,
      10  => 3,
      11  => 7
    ),
    "bench" => array(
      4,8,9
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 6,
      5  => 10,
      7  => 8,
      9  => 5,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      3,7,1
    ),
    "subs" => array(
      "in" => array(
         10 => 4
        , 7 => 8
        , 11 => 9
      ),
      "out" => array(
         10 => 3
        , 7 => 1
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
      4  => 4,
      5  => 10,
      7  => 7,
      9  => 8,
      10  => 4,
      11  => 1
    ),
    "bench" => array(
      2,6,10
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),

  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 3,
      5  => 9,
      7  => 7,
      9  => 6,
      10  => 10,
      11  => 1
    ),
    "bench" => array(
      4,8,5
    ),
    "subs" => array(
      "in" => array(
        2 => 2
        , 10 => 10
        , 9 => 6
      ),
      "out" => array(
         2 => 5
        , 9 => 8
       , 10 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),


  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 6,
      5  => 4,
      7  => 5,
      9  => 1,
      10  => 8,
      11  => 10
    ),
    "bench" => array(
      9,7,3
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),


  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 3,
      5  => 4,
      7  => 5,
      9  => 9,
      10  => 8,
      11  => 10
    ),
    "bench" => array(
      1,2,6
    ),
    "subs" => array(
      "in" => array(
        2 => 7
        , 4 => 3 
        , 9 => 9
      ),
      "out" => array(
        2 => 2
        , 4 => 6 
        , 9 =>  1

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),  

  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 3,
      5  => 9,
      7  => 7,
      9  => 8,
      10  => 6,
      11  => 1
    ),
    "bench" => array(
      5,10,4
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 3,
      5  => 10,
      7  => 7,
      9  => 5,
      10  => 6,
      11  => 1
    ),
    "bench" => array(
      8,10,4
    ),
    "subs" => array(
      "in" => array(
        2 => 4
        , 5 => 10 
        , 9 => 5
      ),
      "out" => array(
         2 => 2
        , 5 => 9
        , 9 => 8
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),




  8 => array(
    "start" => 0,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 6,
      5  => 9,
      7  => 10,
      9  => 2,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,3,7
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),

  9 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 8,
      5  => 9,
      7  => 3,
      9  => 2,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,3,7
    ),
    "subs" => array(
      "in" => array(
        2 => 7
        , 4 => 8
        , 7 => 3
      ),
      "out" => array(
        2 => 1
        , 4 => 6
        , 7 => 10
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),  
);


$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 6,
      5  => 4,
      7  => 1,
      9  => 8,
      10  => 7,
      11  => 9
    ),
    "bench" => array(
      3,10,5
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 3,
      5  => 5,
      7  => 1,
      9  => 8,
      10  => 7,
      11  => 9
    ),
    "bench" => array(
      6,9,4
    ),
    "subs" => array(
      "in" => array(
        4  => 3
        , 9  => 10
        , 11  => 5
      ),
      "out" => array(
        4  => 6
        ,9   => 8
        ,11  => 9
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
      4  => 4,
      5  => 10,
      7  => 7,
      9  => 8,
      10  => 4,
      11  => 1
    ),
    "bench" => array(
      2,6,10
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 3,
      5  => 9,
      7  => 7,
      9  => 6,
      10  => 10,
      11  => 1
    ),
    "bench" => array(
      4,8,5
    ),
    "subs" => array(
      "in" => array(
        2 => 2
        , 10 => 10
        , 9 => 6
      ),
      "out" => array(
         2 => 5
        , 9 => 8
       , 10 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),

  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 6,
      5  => 4,
      7  => 5,
      9  => 1,
      10  => 8,
      11  => 10
    ),
    "bench" => array(
      9,7,3
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),


  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 3,
      5  => 4,
      7  => 5,
      9  => 9,
      10  => 8,
      11  => 10
    ),
    "bench" => array(
      1,2,6
    ),
    "subs" => array(
      "in" => array(
        2 => 7
        , 4 => 3 
        , 9 => 9
      ),
      "out" => array(
        2 => 2
        , 4 => 6 
        , 9 =>  1

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),  

  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 3,
      5  => 9,
      7  => 7,
      9  => 8,
      10  => 6,
      11  => 1
    ),
    "bench" => array(
      5,10,4
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 3,
      5  => 10,
      7  => 7,
      9  => 5,
      10  => 6,
      11  => 1
    ),
    "bench" => array(
      8,10,4
    ),
    "subs" => array(
      "in" => array(
        2 => 4
        , 5 => 10 
        , 9 => 5
      ),
      "out" => array(
         2 => 2
        , 5 => 9
        , 9 => 8
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),




  8 => array(
    "start" => 0,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 6,
      5  => 9,
      7  => 10,
      9  => 2,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,3,7
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),

  9 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 8,
      5  => 9,
      7  => 3,
      9  => 2,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,3,7
    ),
    "subs" => array(
      "in" => array(
        2 => 7
        , 4 => 8
        , 7 => 3
      ),
      "out" => array(
        2 => 1
        , 4 => 6
        , 7 => 10
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),  
);



$wisselschema_index[$key] = array_rand($ws);
$events[$key][$aantal_spelers] = $ws[$wisselschema_index[$key]];
