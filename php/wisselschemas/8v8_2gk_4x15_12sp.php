<?php

$key = str_replace(".php","",basename(__FILE__, '.php'));
//echo $key;
$ws=array();

$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 3,
      4  => 7,
      5  => 11,
      7  => 2,
      9  => 6,
      10  => 4,
      11  => 8
    ),
    "bench" => array(
      5,9,10,0
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 1,
      2  => 3,
      4  => 7,
      5  => 11,
      7  => 9,
      9  => 6,
      10  => 5,
      11  => 10
    ),
    "bench" => array(
      4,8,2,0
    ),
    "subs" => array(
      "in" => array(
         10 => 5
        , 7 => 9
        , 11 => 10
      ),
      "out" => array(
         10 => 4
        , 7 => 2
        , 11 => 8
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 4,
      5  => 10,
      7  => 8,
      9  => 9,
      10  => 5,
      11  => 2
    ),
    "bench" => array(
      3,7,11,1
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 4,
      5  => 10,
      7  => 8,
      9  => 7,
      10  => 11,
      11  => 2
    ),
    "bench" => array(
      1,5,9,6
    ),
    "subs" => array(
      "in" => array(
        2 => 3
        , 10 => 11
        , 9 => 7
      ),
      "out" => array(
         2 => 6
        , 9 => 9
       , 10 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 3,
      4  => 7,
      5  => 5,
      7  => 6,
      9  => 2,
      10  => 9,
      11  => 11
    ),
    "bench" => array(
      10,8,4,0
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
 
  
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 8,
      4  => 4,
      5  => 5,
      7  => 6,
      9  => 10,
      10  => 9,
      11  => 11
    ),
    "bench" => array(
      2,3,7,0
    ),
    "subs" => array(
      "in" => array(
        2 => 8
        , 4 => 4 
        , 9 => 10
      ),
      "out" => array(
        2 => 3
        , 4 => 7 
        , 9 =>  2

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),  
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 4,
      5  => 10,
      7  => 8,
      9  => 9,
      10  => 7,
      11  => 2
    ),
    "bench" => array(
      6,11,5,1
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 4,
      5  => 11,
      7  => 8,
      9  => 6,
      10  => 7,
      11  => 2
    ),
    "bench" => array(
      9,11,5,1
    ),
    "subs" => array(
      "in" => array(
        2 => 5
        , 5 => 11 
        , 9 => 6
      ),
      "out" => array(
         2 => 3
        , 5 => 10
        , 9 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
  
  
  
  
  8 => array(
    "start" => 0,    
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 7,
      5  => 10,
      7  => 11,
      9  => 3,
      10  => 5,
      11  => 6
    ),
    "bench" => array(
      9,4,8,1
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),
  
  9 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 8,
      4  => 9,
      5  => 10,
      7  => 4,
      9  => 3,
      10  => 5,
      11  => 6
    ),
    "bench" => array(
      9,4,8,0
    ),
    "subs" => array(
      "in" => array(
        1 => 1
        , 2 => 8
        , 4 => 9
        , 7 => 4
      ),
      "out" => array(
        1 => 0
        , 2 => 2
        , 4 => 7
        , 7 => 11
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
      2  => 5,
      4  => 6,
      5  => 10,
      7  => 8,
      9  => 2,
      10  => 9,
      11  => 11
    ),
    "bench" => array(
      3,7,4,1
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 4,
      5  => 10,
      7  => 8,
      9  => 2,
      10  => 7,
      11  => 3
    ),
    "bench" => array(
      1,11,9,6
    ),
    "subs" => array(
      "in" => array(
         4 => 4
        , 10 => 7
        , 11 => 3
      ),
      "out" => array(
         4 => 5
        , 10 => 9
        , 11 => 11
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  


  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 7,
      4  => 4,
      5  => 3,
      7  => 6,
      9  => 9,
      10  => 11,
      11  => 2
    ),
    "bench" => array(
      0,10,5,8
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),

  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 7,
      4  => 4,
      5  => 3,
      7  => 8,
      9  => 5,
      10  => 11,
      11  => 10
    ),
    "bench" => array(
      0,2,9,6
    ),
    "subs" => array(
      "in" => array(
        7 => 8
        , 9 => 5
        , 11 => 10
      ),
      "out" => array(
         7 => 6
        , 9 => 9
       , 11 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
 
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 9,
      5  => 5,
      7  => 10,
      9  => 6,
      10  => 7,
      11  => 2
    ),
    "bench" => array(
      1,4,11,3
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 9,
      5  => 4,
      7  => 11,
      9  => 6,
      10  => 7,
      11  => 3
    ),
    "bench" => array(
      2,5,10,1
    ),
    "subs" => array(
      "in" => array(
        5 => 4
        , 7 => 11 
        , 11 => 3
      ),
      "out" => array(
        5 => 5
        , 7 => 10 
        , 11 =>  2

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),  
  
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 3,
      4  => 11,
      5  => 10,
      7  => 5,
      9  => 2,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      0, 8, 7, 6
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
 
  
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 3,
      4  => 6,
      5  => 10,
      7  => 5,
      9  => 2,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      0, 9, 4,11 
    ),
    "subs" => array(
      "in" => array(
        4 => 6
        , 10 => 7 
        , 11 => 8
      ),
      "out" => array(
         4 => 3
        , 10 => 10
        , 11 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
  
  
  
  
  8 => array(
    "start" => 0,    
    "lineup" => array(
      1  => 1,
      2  => 4,
      4  => 6,
      5  => 11,
      7  => 9,
      9  => 3,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      0,2,5,10
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),

  9 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 6,
      5  => 11,
      7  => 9,
      9  => 2,
      10  => 5,
      11  => 10
    ),
    "bench" => array(
      1,3,7,8 
    ),
    "subs" => array(
      "in" => array(
        1 => 1
        , 9 => 3
        , 10 => 7
        , 11 => 8
      ),
      "out" => array(
        1 => 0
        , 9 => 2
        , 10 => 5
        , 11 => 10
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
      2  => 5,
      4  => 6,
      5  => 10,
      7  => 8,
      9  => 2,
      10  => 9,
      11  => 11
    ),
    "bench" => array(
      3,7,4,1
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 4,
      5  => 10,
      7  => 8,
      9  => 2,
      10  => 7,
      11  => 3
    ),
    "bench" => array(
      1,11,9,6
    ),
    "subs" => array(
      "in" => array(
         4 => 4
        , 10 => 7
        , 11 => 3
      ),
      "out" => array(
         4 => 5
        , 10 => 9
        , 11 => 11
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 7,
      4  => 4,
      5  => 3,
      7  => 6,
      9  => 9,
      10  => 11,
      11  => 2
    ),
    "bench" => array(
      0,10,5,8
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 7,
      4  => 4,
      5  => 3,
      7  => 8,
      9  => 5,
      10  => 11,
      11  => 10
    ),
    "bench" => array(
      0,2,9,6
    ),
    "subs" => array(
      "in" => array(
        7 => 8
        , 9 => 5
        , 11 => 10
      ),
      "out" => array(
         7 => 6
        , 9 => 9
       , 11 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 7,
      5  => 5,
      7  => 10,
      9  => 6,
      10  => 9,
      11  => 2
    ),
    "bench" => array(
      1,4,11,3
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 7,
      5  => 4,
      7  => 11,
      9  => 6,
      10  => 9,
      11  => 3
    ),
    "bench" => array(
      2,5,10,1
    ),
    "subs" => array(
      "in" => array(
        5 => 4
        , 7 => 11 
        , 11 => 3
      ),
      "out" => array(
        5 => 5
        , 7 => 10 
        , 11 =>  2

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),    
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 3,
      4  => 11,
      5  => 10,
      7  => 5,
      9  => 2,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      0, 8, 7, 6
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 3,
      4  => 6,
      5  => 10,
      7  => 5,
      9  => 2,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      0, 9, 4,11 
    ),
    "subs" => array(
      "in" => array(
        4 => 6
        , 10 => 7 
        , 11 => 8
      ),
      "out" => array(
         4 => 3
        , 10 => 10
        , 11 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
  8 => array(
    "start" => 0,    
    "lineup" => array(
      1  => 1,
      2  => 4,
      4  => 6,
      5  => 11,
      7  => 9,
      9  => 3,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      0,2,5,10
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),
  9 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 6,
      5  => 11,
      7  => 9,
      9  => 2,
      10  => 5,
      11  => 10
    ),
    "bench" => array(
      1,3,7,8 
    ),
    "subs" => array(
      "in" => array(
        1 => 1
        , 9 => 3
        , 10 => 7
        , 11 => 8
      ),
      "out" => array(
        1 => 0
        , 9 => 2
        , 10 => 5
        , 11 => 10
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),
  
); 


$wisselschema_index["8v8_2gk_5x15"] = array_rand($ws);
$events["8v8_2gk_5x15"][12] = $ws[$wisselschema_index["8v8_2gk_5x15"] ];


?>