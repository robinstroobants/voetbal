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
      2  => 5,
      4  => 9,
      5  => 6,
      7  => 10,
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
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 3,
      9  => 2,
      10  => 1,
      11  => 7
    ),
    "bench" => array(
      4,9,10
    ),
    "subs" => array(
      "in" => array(
         4 => 8
        , 7 => 7
        , 11 => 3
      ),
      "out" => array(
         4 => 9
        , 7 => 10
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
      2  => 6,
      4  => 5,
      5  => 3,
      7  => 8,
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
      4  => 5,
      5  => 1,
      7  => 2,
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
        , 5 => 1
        , 7 => 2
      ),
      "out" => array(
        2 => 6
        , 5 => 3 
        , 7 =>  8

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



if (!isset($te_gebruiken_schema)){
 $te_gebruiken_schema = array_rand($ws);
}
if ($te_gebruiken_schema == 0){
  $te_gebruiken_schema = array_rand($ws);  
}

if ($te_gebruiken_schema == 12){
  //zet $show_pt_array = 1 in index en onderaan komt deze info. die kan je dan hier plakken anders moet die dat telkens generen
  $wisselschema_meta[$key]["time"] =  array("min" => array(3,4,7,8),"max" => array(0));
  $wisselschema_times[$key] = array("min" => array(3,4,7,8),"max" => array(0));
} else {
  $wisselschema_meta[$key]["time"] = array("min" => array(3,4,5,7),"max" => array(1,2,6,8,9,10));
  $wisselschema_times[$key] = array("min" => array(3,4,5,7),"max" => array(1,2,6,8,9,10));
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