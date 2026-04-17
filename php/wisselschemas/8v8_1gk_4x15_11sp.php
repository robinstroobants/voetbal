<?php


$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);

$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);
$ws=array();

$ws[12] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 10,
      4  => 9,
      5  => 6,
      7  => 5,
      9  => 2,
      10  => 1,
      11  => 4
    ),
    "bench" => array(
      3,7,8
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 10,
      4  => 9,
      5  => 6,
      7  => 7,
      9  => 3,
      10  => 1,
      11  => 8
    ),
    "bench" => array(
      2,4,5
    ),
    "subs" => array(
      "in" => array(
         7 => 7
        , 9 => 3
        , 11 => 8
      ),
      "out" => array(
         9 => 2
        , 7 => 5
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
      4  => 8,
      5  => 5,
      7  => 7,
      9  => 3,
      10  => 6,
      11  => 2
    ),
    "bench" => array(
      10,9,1
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 8,
      5  => 5,
      7  => 7,
      9  => 3,
      10  => 9,
      11  => 10
    ),
    "bench" => array(
      2,6,4
    ),
    "subs" => array(
      "in" => array(
        10 => 9
        , 2 => 1
        , 11 => 10
      ),
      "out" => array(
         10 => 6
        , 2 => 4
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
      2  => 5,
      4  => 9,
      5  => 4,
      7  => 10,
      9  => 2,
      10  => 1,
      11  => 6
    ),
    "bench" => array(
      3,7,8
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 9,
      5  => 4,
      7  => 3,
      9  => 2,
      10  => 8,
      11  => 7
    ),
    "bench" => array(
      10,1,6
    ),
    "subs" => array(
      "in" => array(
        7 => 3
        , 10 => 8
        , 11 => 7
      ),
      "out" => array(
        7 => 10
        , 10 => 1
        , 11 => 6

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
      4  => 1,
      5  => 4,
      7  => 10,
      9  => 7,
      10  => 8,
      11  => 3
    ),
    "bench" => array(
      5,9,2
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 9,
      5  => 4,
      7  => 10,
      9  => 2,
      10  => 8,
      11  => 3
    ),
    "bench" => array(
      1,7,6
    ),
    "subs" => array(
      "in" => array(
        4 => 9
        , 9 => 2
        , 2 => 5
      ),
      "out" => array(
         4 => 1
        , 9 => 7
        , 2 => 6
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
); 

$ws[216] = array(
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
      2  => 10,
      4  => 1,
      5  => 9,
      7  => 7,
      9  => 4,
      10  => 8,
      11  => 3
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
      2  => 10,
      4  => 1,
      5  => 5,
      7  => 7,
      9  => 6,
      10  => 2,
      11  => 3
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 9 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 9
       , 9 => 4
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
      4  => 6,
      5  => 3,
      7  => 5,
      9  => 10,
      10  => 9,
      11  => 4
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
      7  => 5,
      9  => 10,
      10  => 9,
      11  => 4
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        2 => 7
        , 4 => 1
        , 5 => 2
      ),
      "out" => array(
        2 => 8
        , 5 => 3 
        , 4 =>  6

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
      4  => 6,
      5  => 4,
      7  => 9,
      9  => 3,
      10  => 8,
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
      2  => 2,
      4  => 6,
      5  => 1,
      7  => 9,
      9  => 5,
      10  => 8,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 9 => 5
        , 11 => 10
      ),
      "out" => array(
         5 => 4
        , 9 => 3
        , 11 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 



$ws[424] = array(
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
      2  => 7,
      4  => 1,
      5  => 9,
      7  => 10,
      9  => 4,
      10  => 8,
      11  => 3
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
      2  => 7,
      4  => 1,
      5  => 5,
      7  => 10,
      9  => 6,
      10  => 2,
      11  => 3
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 9 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 9
       , 9 => 4
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
      4  => 8,
      5  => 3,
      7  => 4,
      9  => 10,
      10  => 9,
      11  => 5
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
      2  => 2,
      4  => 1,
      5  => 7,
      7  => 4,
      9  => 10,
      10  => 9,
      11  => 5
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        2 => 2
        , 4 => 1
        , 5 => 7
      ),
      "out" => array(
        2 => 6
        , 5 => 3 
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
      2  => 2,
      4  => 6,
      5  => 4,
      7  => 9,
      9  => 8,
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
      2  => 2,
      4  => 6,
      5  => 1,
      7  => 9,
      9  => 8,
      10  => 5,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 10 => 5
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
); 

$ws[628] = array(
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
        , 7 => 7
        , 11 => 10
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
      2  => 3,
      4  => 1,
      5  => 4,
      7  => 10,
      9  => 9,
      10  => 8,
      11  => 7
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
      2  => 3,
      4  => 1,
      5  => 5,
      7  => 10,
      9  => 6,
      10  => 2,
      11  => 7
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 9 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 4
       , 9 => 9
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
      4  => 8,
      5  => 3,
      7  => 5,
      9  => 10,
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
      7  => 5,
      9  => 10,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        2 => 7
        , 4 => 1
        , 5 => 2
      ),
      "out" => array(
        2 => 6
        , 5 => 3 
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
      2  => 2,
      4  => 6,
      5  => 4,
      7  => 9,
      9  => 8,
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
      2  => 2,
      4  => 6,
      5  => 1,
      7  => 9,
      9  => 8,
      10  => 5,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 10 => 5
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
); 

$ws[830] = array(
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
      2  => 7,
      4  => 1,
      5  => 4,
      7  => 9,
      9  => 10,
      10  => 8,
      11  => 3
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
      2  => 7,
      4  => 1,
      5  => 5,
      7  => 6,
      9  => 10,
      10  => 2,
      11  => 3
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 7 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 4
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
      7  => 5,
      9  => 10,
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
      2  => 1,
      4  => 7,
      5  => 2,
      7  => 5,
      9  => 10,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        4 => 7
        , 2 => 1
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
      2  => 2,
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 6,
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
      2  => 2,
      4  => 8,
      5  => 1,
      7  => 9,
      9  => 6,
      10  => 5,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 10 => 5
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
); 

$ws[1032] = array(
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
      11  => 5
    ),
    "bench" => array(
      2,1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 3,
      5  => 7,
      7  => 6,
      9  => 10,
      10  => 2,
      11  => 5
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 2 => 1
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
      5  => 9,
      7  => 5,
      9  => 10,
      10  => 4,
      11  => 8
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
      5  => 9,
      7  => 5,
      9  => 10,
      10  => 4,
      11  => 2
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 2 => 7
        , 11 => 2
      ),
      "out" => array(
        2 => 6
        , 4 => 3 
        , 11 =>  8

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
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 6,
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
      2  => 2,
      4  => 8,
      5  => 5,
      7  => 9,
      9  => 6,
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
); 

$ws[1234] = array(
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
      2  => 7,
      4  => 1,
      5  => 4,
      7  => 9,
      9  => 10,
      10  => 8,
      11  => 3
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
      2  => 7,
      4  => 1,
      5  => 5,
      7  => 6,
      9  => 10,
      10  => 2,
      11  => 3
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 7 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 4
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
      7  => 5,
      9  => 10,
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
      2  => 1,
      4  => 7,
      5  => 2,
      7  => 5,
      9  => 10,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        4 => 7
        , 2 => 1
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
      2  => 2,
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 6,
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
      2  => 2,
      4  => 8,
      5  => 5,
      7  => 9,
      9  => 6,
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
); 

$ws[1436] = array(
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
      7  => 5,
      9  => 10,
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
      7  => 5,
      9  => 10,
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
      2  => 2,
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 6,
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
      2  => 2,
      4  => 8,
      5  => 5,
      7  => 9,
      9  => 6,
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
); 


$ws[1638] = array(
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
      5  => 9,
      7  => 5,
      9  => 10,
      10  => 4,
      11  => 8
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
      5  => 9,
      7  => 5,
      9  => 10,
      10  => 4,
      11  => 2
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 2 => 7
        , 11 => 2
      ),
      "out" => array(
        2 => 6
        , 4 => 3 
        , 11 =>  8

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
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 6,
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
      2  => 2,
      4  => 8,
      5  => 5,
      7  => 9,
      9  => 6,
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
); 

$ws[1837] = array(
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
      2  => 10,
      4  => 1,
      5  => 9,
      7  => 7,
      9  => 4,
      10  => 8,
      11  => 3
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
      2  => 10,
      4  => 1,
      5  => 5,
      7  => 7,
      9  => 6,
      10  => 2,
      11  => 3
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 9 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 9
       , 9 => 4
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
      4  => 6,
      5  => 3,
      7  => 10,
      9  => 5,
      10  => 9,
      11  => 4
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
      10  => 9,
      11  => 4
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        2 => 7
        , 4 => 1
        , 5 => 2
      ),
      "out" => array(
        2 => 8
        , 5 => 3 
        , 4 =>  6

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
      4  => 6,
      5  => 4,
      7  => 9,
      9  => 3,
      10  => 8,
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
      2  => 2,
      4  => 6,
      5  => 1,
      7  => 9,
      9  => 5,
      10  => 8,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 9 => 5
        , 11 => 10
      ),
      "out" => array(
         5 => 4
        , 9 => 3
        , 11 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[2035] = array(
  0 => array(
    "start" => 0,
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
      7,10,5
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
      5  => 5,
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
         5 => 5
        , 7 => 7
        , 11 => 10
      ),
      "out" => array(
         5 => 9
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
      2  => 3,
      4  => 1,
      5  => 4,
      7  => 10,
      9  => 9,
      10  => 8,
      11  => 7
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
      2  => 3,
      4  => 1,
      5  => 5,
      7  => 10,
      9  => 6,
      10  => 2,
      11  => 7
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 9 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 4
       , 9 => 9
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
      4  => 8,
      5  => 3,
      7  => 5,
      9  => 10,
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
      7  => 5,
      9  => 10,
      10  => 4,
      11  => 9
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        2 => 7
        , 4 => 1
        , 5 => 2
      ),
      "out" => array(
        2 => 6
        , 5 => 3 
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
      2  => 2,
      4  => 6,
      5  => 4,
      7  => 9,
      9  => 8,
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
      2  => 2,
      4  => 6,
      5  => 1,
      7  => 9,
      9  => 8,
      10  => 5,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 10 => 5
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
); 

$ws[2237] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 3,
      9  => 4, // Gewisseld met pos 11
      10 => 1,
      11 => 2  // Gewisseld met pos 9
    ),
    "bench" => array(5, 7, 10),
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
      9  => 7, // Gewisseld met pos 11 (was 2, nu 7)
      10 => 1,
      11 => 2  // Gewisseld met pos 9 (was 7, nu 2)
    ),
    "bench" => array(3, 4, 9),
    "subs" => array(
      "in" => array(2 => 5, 7 => 10, 9 => 7),
      "out" => array(2 => 9, 7 => 3, 9 => 4)
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  

  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 3,
      5  => 7,
      7  => 9,
      9  => 10,
      10 => 4,
      11 => 5
    ),
    "bench" => array(8, 2, 6),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 2,  // Gewisseld met pos 10
      4  => 3,
      5  => 7,
      7  => 6,
      9  => 10,
      10 => 8,  // Gewisseld met pos 2
      11 => 5
    ),
    "bench" => array(4, 8, 9),
    "subs" => array(
      "in" => array(2 => 2, 7 => 6, 10 => 8),
      "out" => array(2 => 1, 7 => 9, 10 => 4)
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
      5  => 9,
      7  => 5,
      9  => 8,  // Eerst 9<->10 (werd 4), daarna 9<->11 (werd 8)
      10 => 10, // Gewisseld met pos 9 (was 4, nu 10)
      11 => 4   // Gewisseld met pos 9 (was 8, nu 4)
    ),
    "bench" => array(1, 2, 7),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 1,
      5  => 9,
      7  => 5,
      9  => 2,  // Eerst 9<->10 (werd 4), daarna 9<->11 (werd 2)
      10 => 10, // Gewisseld met pos 9 (was 4, nu 10)
      11 => 4   // Gewisseld met pos 9 (was 2, nu 4)
    ),
    "bench" => array(3, 6, 8),
    "subs" => array(
      "in" => array(2 => 7, 4 => 1, 9 => 2),
      "out" => array(2 => 6, 4 => 3, 9 => 8)
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9, // Gewisseld met pos 7
      4  => 8,
      5  => 7, // Gewisseld met pos 11
      7  => 2, // Gewisseld met pos 2
      9  => 4,
      10 => 3,
      11 => 6  // Gewisseld met pos 5
    ),
    "bench" => array(1, 5, 10),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9, // Blijft 9 (gewisseld met pos 7)
      4  => 8,
      5  => 10, // Gewisseld met pos 11
      7  => 2, // Blijft 2 (gewisseld met pos 2)
      9  => 5,
      10 => 1,
      11 => 6  // Gewisseld met pos 5
    
    ),
    
    "bench" => array(3, 4, 7),
    
    "subs" => array(
      "in" => array(5 => 10, 10 => 1, 9 => 5),

      "out" => array(5 => 7, 10 => 3, 9 => 4)

    ),

    "duration" => 7.5*60,

    "game_counter" => 4

  ),

);

$ws[2396] = array(
  // BLOK 1: Wissel positie 9 en 11
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 3,
      9  => 4, // Was 2 (key van Rune), nu 4 (key van Miel)
      10 => 1,
      11 => 2  // Was 4 (key van Miel), nu 2 (key van Rune)
    ),
    "bench" => array(7, 10, 5), // Spelers MuratY(5), Arda(6), Jayden(7), Seppe(9), Vinn(10) - wacht, bench bevat keys 7, 10, 5
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  // BLOK 2: Positie 9 en 11 blijven gewisseld t.o.v. origineel
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 10,
      9  => 7, // Aangepast naar 7 om consistent te blijven met de subs hieronder
      10 => 1,
      11 => 2  // Rune(2) blijft staan op 11
    ),
    "bench" => array(4, 9, 3), 
    "subs" => array(
      "in" => array(2 => 5, 7 => 10, 9 => 7),
      "out" => array(2 => 9, 7 => 3, 9 => 4) // Positie 9: 4 gaat eruit, 7 komt erin
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  

  // BLOK 3
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 3,
      5  => 7,
      7  => 9,
      9  => 10,
      10 => 8,
      11 => 5
    ),
    "bench" => array(2, 1, 6),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  // BLOK 4: Wissel positie 10 met 2
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 8, // Was 1, nu 8 (van positie 10)
      4  => 3,
      5  => 7,
      7  => 6,
      9  => 10,
      10 => 1, // Was 2 (in jouw schema stond 2?), nu 1 (van positie 2)
      11 => 5
    ),
    "bench" => array(4, 9, 2), // 2 naar bench (Rune)
    "subs" => array(
      "in" => array(2 => 8, 7 => 6, 10 => 1),
      "out" => array(2 => 4, 7 => 9, 10 => 8)
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),    

  // BLOK 5: Wissel positie 9 met 10
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 3,
      5  => 9,
      7  => 5,
      9  => 4, // Was 10, nu 4 (van positie 10)
      10 => 10, // Was 4, nu 10 (van positie 9)
      11 => 8
    ),
    "bench" => array(1, 7, 2),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  // BLOK 6: Wissel positie 9 met 10
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 1,
      5  => 9,
      7  => 5,
      9  => 4, // Was 10, nu 4
      10 => 10, // Was 4, nu 10
      11 => 2
    ),
    "bench" => array(8, 3, 6),
    "subs" => array(
      "in" => array(2 => 7, 4 => 1, 11 => 2),
      "out" => array(2 => 6, 4 => 3, 11 => 8)
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  // BLOK 7: Wissel 2 met 7 EN 11 met 5
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9, // Was 2, nu 9 (van positie 7)
      4  => 8,
      5  => 7, // Was 4, nu 7 (van positie 11)
      7  => 2, // Was 9, nu 2 (van positie 2)
      9  => 6,
      10 => 3,
      11 => 4  // Was 7, nu 4 (van positie 5)
    ),
    "bench" => array(10, 5, 1),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  // BLOK 8: Wissel 2 met 7 EN 11 met 5
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9, // Blijft 9 (van positie 7)
      4  => 8,
      5  => 10, // Invalbeurt op positie 5 (was 5)
      7  => 2, // Blijft 2 (van positie 2)
      9  => 6,
      10 => 1,
      11 => 5  // Invalbeurt op positie 11 (was 10)
    ),
    "bench" => array(4, 3, 7),
    "subs" => array(
      "in" => array(5 => 10, 10 => 1, 11 => 5),
      "out" => array(5 => 7, 10 => 3, 11 => 4)
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
);





$ws[2560] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 2,
      5  => 6,
      7  => 3,
      9  => 8,
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
      4  => 2,
      5  => 6,
      7  => 10,
      9  => 8,
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
      2  => 7,
      4  => 1,
      5  => 9,
      7  => 4,
      9  => 10,
      10  => 8,
      11  => 3
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
      2  => 7,
      4  => 1,
      5  => 5,
      7  => 6,
      9  => 10,
      10  => 2,
      11  => 3
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 7 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 9
       , 7 => 4
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
      4  => 8,
      5  => 3,
      7  => 4,
      9  => 10,
      10  => 5,
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
      2  => 2,
      4  => 1,
      5  => 7,
      7  => 4,
      9  => 10,
      10  => 5,
      11  => 9
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        2 => 2
        , 4 => 1
        , 5 => 7
      ),
      "out" => array(
        2 => 6
        , 5 => 3 
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
      2  => 2,
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 3,
      10  => 6,
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
      2  => 2,
      4  => 8,
      5  => 1,
      7  => 9,
      9  => 5,
      10  => 6,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 9 => 5
        , 11 => 10
      ),
      "out" => array(
         5 => 4
        , 9 => 3
        , 11 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 


$ws[2765] = array(
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
      2  => 7,
      4  => 1,
      5  => 9,
      7  => 4,
      9  => 10,
      10  => 8,
      11  => 3
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
      2  => 7,
      4  => 1,
      5  => 5,
      7  => 6,
      9  => 10,
      10  => 2,
      11  => 3
    ),
    "bench" => array(
      4,9,8
    ),
    "subs" => array(
      "in" => array(
        10 => 2
        , 5 => 5
        , 7 => 6
      ),
      "out" => array(
         10 => 8
        , 5 => 9
       , 7 => 4
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
      4  => 8,
      5  => 3,
      7  => 4,
      9  => 10,
      10  => 9,
      11  => 5
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
      2  => 2,
      4  => 1,
      5  => 7,
      7  => 4,
      9  => 10,
      10  => 9,
      11  => 5
    ),
    "bench" => array(
      8,3,6
    ),
    "subs" => array(
      "in" => array(
        2 => 2
        , 4 => 1
        , 5 => 7
      ),
      "out" => array(
        2 => 6
        , 5 => 3 
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
      2  => 2,
      4  => 8,
      5  => 4,
      7  => 9,
      9  => 6,
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
      2  => 2,
      4  => 8,
      5  => 1,
      7  => 9,
      9  => 6,
      10  => 5,
      11  => 10
    ),
    "bench" => array(
      4,3,7
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 10 => 5
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
); 


$ws[2970] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 3,
      9  => 4, // Gewisseld met pos 11
      10 => 1,
      11 => 2  // Gewisseld met pos 9
    ),
    "bench" => array(5, 7, 10),
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
      9  => 7, // Gewisseld met pos 11 (was 2, nu 7)
      10 => 1,
      11 => 2  // Gewisseld met pos 9 (was 7, nu 2)
    ),
    "bench" => array(3, 4, 9),
    "subs" => array(
      "in" => array(2 => 5, 7 => 10, 9 => 7),
      "out" => array(2 => 9, 7 => 3, 9 => 4)
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  

  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 3,
      5  => 7,
      7  => 9,
      9  => 10,
      10 => 4,
      11 => 5
    ),
    "bench" => array(8, 2, 6),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 2,  // Gewisseld met pos 10
      4  => 3,
      5  => 7,
      7  => 6,
      9  => 10,
      10 => 8,  // Gewisseld met pos 2
      11 => 5
    ),
    "bench" => array(4, 8, 9),
    "subs" => array(
      "in" => array(2 => 2, 7 => 6, 10 => 8),
      "out" => array(2 => 1, 7 => 9, 10 => 4)
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
      5  => 9,
      7  => 5,
      9  => 8,  // Eerst 9<->10 (werd 4), daarna 9<->11 (werd 8)
      10 => 10, // Gewisseld met pos 9 (was 4, nu 10)
      11 => 4   // Gewisseld met pos 9 (was 8, nu 4)
    ),
    "bench" => array(1, 2, 7),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 1,
      5  => 9,
      7  => 5,
      9  => 2,  // Eerst 9<->10 (werd 4), daarna 9<->11 (werd 2)
      10 => 10, // Gewisseld met pos 9 (was 4, nu 10)
      11 => 4   // Gewisseld met pos 9 (was 2, nu 4)
    ),
    "bench" => array(3, 6, 8),
    "subs" => array(
      "in" => array(2 => 7, 4 => 1, 9 => 2),
      "out" => array(2 => 6, 4 => 3, 9 => 8)
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9, // Gewisseld met pos 7
      4  => 8,
      5  => 7, // Gewisseld met pos 11
      7  => 2, // Gewisseld met pos 2
      9  => 4,
      10 => 3,
      11 => 6  // Gewisseld met pos 5
    ),
    "bench" => array(1, 10, 5),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9, // Blijft 9 (gewisseld met pos 7)
      4  => 8,
      5  => 5, 
      7  => 2, // Blijft 2 (gewisseld met pos 2)
      9  => 10,
      10 => 1,
      11 => 6  // Gewisseld met pos 5
    
    ),
    
    "bench" => array(3, 4, 7),
    
    "subs" => array(
      "in" => array(9 => 10, 10 => 1, 5 => 5),

      "out" => array(5 => 7, 10 => 3, 9 => 4)

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