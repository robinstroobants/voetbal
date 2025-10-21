<?php
require_once("getconn.php");


class Game
{   
    public $score;
    public $rating;
    public $algorithm = 0;
    public $shuffleType = "random";
    public $shuffle_skip = 1; // hoeveel posities moeten genegeerd worden om de lineup te generen (doelmannen)
    public $score_threshold;
    public $onlyBestSelection;

    
    public $position_weight = 9; // if rand(0,10) > position_weight -> random positions instead of on strength
    public $format = '8v8';
    public $playercount = 5;
    public $game_duration = 15;
    public $nr_of_games=4;
    
    // calculated 
    public $time_played = array(); /* time played by each player */
    public $time_benched = array(); /* time played by each player */
    
    
    public $available_players = 0;
    public $total_duration = 0;
    public $minutes_per_player = 0;
    public $benchtime = 0;
    
    public $fullgamers = array();
    public $fullgamers_by_game = array();
    
    
    public $positions = array(1,2,4,5,7,10,11,9);
    public $events = array();
    
    public $players = array();
    public $playernames = array();
    public $playerindex = array();
    public $playerfullnames = array();
    
    /* Helper arrays */
    public $positions_per_player = array();
    public $players_per_position = array();
    public $score_per_position = array();
    public $time_in_position = array(); /* time played by each player in position*/

    public $bench_order = array(); /* last time on bench */
    public $bench_order_history = array(); /* last time on bench */
    public $total_playtime = array(); /* time played by each player */
    public $game_parts = array();
    
    public $goalies = array();
    public $most_played = array();
    public $least_played = array();
    
    private $squad = array();
    public $playerinfo = array(
       "Cédric" => array("name" => "Nestor", "birthdate" => "2016-03-16")
       ,"Miel" => array("name" => "T'Syen", "pos" => array(9,7,11,2,5), "birthdate" => "2015-07-30")
       ,"Jack" => array("name" => "Stroobants", "pos" => array(9,7,11,2,5), "birthdate" => "2015-08-06")
      , "Staf" => array("name" => "Van Genechten", "pos" => array(1), "birthdate" => "2015-05-16")
      , "Thibo" => array("name" => "Fierens", "pos" => array(4,10,2,5,9), "birthdate" => "2015-05-19")
      , "Tiebe" => array("name" => "Leduc", "pos" => array(4,10,2,5,9), "birthdate" => "2015-08-23")
      , "Loris" => array("name" => "Fierens", "pos" => array(4,10,2,5,9), "birthdate" => "2015-10-13")

      , "MuratC" => array("name" => "Cilingir", "pos" => array(9,2,5), "birthdate" => "2015-06-05")
      , "MuratY" => array("name" => "Yagmuroglu", "pos" => array(9,11,7,5), "birthdate" => "2015-04-09")
      , "Arda" => array("name" => "Shakir", "pos" => array(9,11,10,2), "birthdate" => "2015-04-23")
      , "Senn" => array("name" => "Goossens", "pos" => array(10,4), "birthdate" => "2015-11-25")
         , "Léno" => array("name" => "Kerckhofs", "pos" => array(5,11,7), "birthdate" => "2015-07-24")
      , "Jayden" => array("name" => "Theys", "pos" => array(2,5,7,11), "birthdate" => "2015-07-05")
      , "Wannes" => array("name" => "Van Gestel", "pos" => array(4,10,7,11,2,5), "birthdate" => "2015-11-03")
      ,"Scout" => array("name" => "Vannuffelen", "pos" => array(2,5,7), "birthdate" => "2015-08-05")
      , "Rune" => array("name" => "Truyers", "pos" => array(4,10,9,2,5), "birthdate" => "2015-04-22")
      , "Seppe" => array("name" => "Geukens", "pos" => array(4,9,2,5,1), "birthdate" => "2015-04-30")  
      , "Otis" => array("name" => "Laurent", "pos" => array(5,11,9), "birthdate" => "")
        , "Franklin" => array("name" => "Tebe", "pos" => array(1), "birthdate" => "")
          , "NoahS" => array("name" => "Sterckx-Geukens", "pos" => array(9,7,11), "birthdate" => "2015-01-23")
      , "NoahW" => array("name" => "Willems", "pos" => array(2,5,7), "birthdate" => "")
//      , "MilV" => array("name" => "Vanelven", "pos" => array(1,10,4,2))
//      , "OtisV" => array("name" => "Laurent", "pos" => array(2,5,7))
      , "Alessio" => array("name" => "Armento", "pos" => array(4,10,9,11), "birthdate" => "")
//      , "Akay" => array("name" => "Kara", "pos" => array(1))
      , "Tyrone" => array("name" => "Monkau", "pos" => array(5,11,9),"birthdate" => "?")


    );
 
    public array $playerscores = [];
    
    
    // method declaration
    public function __construct($spelers, $onlyBestSelection,$format,$shuffle_type = "random"){
      
      
      //tijdelijk tot het uit de db komt
      global $player_scores;
      $this->setPlayerScores($player_scores);


          
      //pr($spelers,__LINE__);
      $this->playercount = substr($format,0,1);
      if ($this->playercount == 8){
        $this->positions = array(1,2,4,5,7,9,10,11);
      }
      
      $this->onlyBestSelection = $onlyBestSelection;
      $this->shuffleType = $shuffle_type;
      
      $this->initSquad($spelers);

      $format_parts = explode('_',$format);
      $nr_and_duration = array_pop($format_parts);
      $nr_and_duration_parts = explode('x',$nr_and_duration);
      $this->nr_of_games = $nr_and_duration_parts[0];
      $this->game_duration = $nr_and_duration_parts[1];
      $duration = $this->nr_of_games * $this->game_duration;
      $this->total_duration = $duration;
      $this->minutes_per_player = ($duration * $this->playercount)/$this->available_players;
      $this->benchtime = $duration - $this->minutes_per_player;
      
      //$this->setTimePlayed();
      //$this->resetPlayerPositions();
      //$this->resetPositionPlayers();


      $this->events = $this->getEvents($this->available_players,$format);
      
      //games
      
      foreach($this->events as $game_idx=>$event){
        $game_counters[$event["game_counter"]][] = $game_idx;
      
      }
      $this->game_parts = $game_counters;
      
   
      $this->swapPlayers();
      $this->setTimePlayed(count($this->events)-1);
        
        
      switch ($this->playercount){
        case 5: $this->score_threshold = rand(230,260);break;
        case 8: $this->score_threshold = rand(500,570);break;
        default: $this->score_threshold = rand(230,260);break;
        
        
      }
      if ($this->onlyBestSelection){
        $this->score_threshold = 5000000;
      }
      $this->setRunQuality();
    }
    
  
    public function setPlayerScores(array $scores): void {
        $this->playerscores = $scores;
    }

    public function getPlayerScore(string $player, int $wedstrijd): ?int {
        return $this->playerscores[$player][$wedstrijd] ?? null;
    }

    private function getEvents($players,$format = "5sp4x15"){
      global $events;
      //pecho($format);
      //dpr($events,$format);
      $returnVal = $events[$format][$players];
      return $returnVal;
    }
    
    public $time_since_last_sub = array();
    public $time_played_by_index = array();
    
    public function swapPlayers(){
      $game_time_index = 0;
      $players = $this->getPlayerListing();
      $last_bench_stats = array();
      $time_played_by_index = array();
      foreach($players as $player){
       $last_bench_stats[$player] = 60*60;
       $time_played_by_index[$player] = 0;
      }
      
      foreach($this->events as $game_idx => $game){
        foreach($game["lineup"] as $_pos=> $player_number){
          if (is_numeric($player_number)){
            $this->events[$game_idx]["lineup"][$_pos] = $players[$player_number];
            $time_played_by_index[$players[$player_number]] += $game["duration"];
          }
        }
        $this->time_played_by_index[$game_idx] = $time_played_by_index;
        
        
        foreach($game["bench"] as $_pos=> $player_number){
          if (is_numeric($player_number)){
            $this->events[$game_idx]["bench"][$_pos] = $players[$player_number];
            $last_bench_stats[$players[$player_number]] = $game_time_index;
          }
        }
        
        if (array_key_exists("subs",$game)){
          foreach($game["subs"] as $direction => $_players){
            foreach($_players as $_pos => $player_number){
              if (is_numeric($player_number)){
                $this->events[$game_idx]["subs"][$direction][$_pos] = $players[$player_number];
              }
            }
          }
        }
        
        if (array_key_exists("positions",$game)){
          
          foreach($game["positions"] as $_pos => $_swap){
            if (is_numeric($_swap["player"])){
              $this->events[$game_idx]["positions"][$_pos]["player"] = $players[$_swap["player"]];
            }
          }
        }
        //$this->setTimePlayed($game_idx);
        asort($last_bench_stats);
        $this->time_since_last_sub[$game_idx] = $last_bench_stats;
        //$this->time_since_last_sub[$game_idx] = $last_bench_stats;
        $game_time_index += $game["duration"];
      }
      //dpr($this->time_played_by_index);
      //dpr($this->time_since_last_sub);
      
      

    }
    
    
    public function setEvents(){
      
      $events = array();
      switch ($this->available_players) {
          case 6:
            $moments = array(0,10,15,20,30,40,45,50);
          break;
          case 8:
            $moments = array(0,7.5,15,22.5,30,37.5,45,52.5);
            break;
          default:
            decho("voorlopig enkel 8 spelers");
      }
      $game_time = 0;
      $game_counter = 1;
      foreach($moments as $_i=>$moment){
        if (array_key_exists($_i+1,$moments)){
          $duration_in_minutes = $moments[$_i+1]-$moments[$_i];
        } else {
          $duration_in_minutes = ($this->game_duration * $this->nr_of_games) - $moments[count($moments)-1];
        }
        //hecho("Game $game_counter &middot; $_i duurt $duration_in_minutes");
        $events[$_i] = array(
          "lineup" => array(),
          "bench" => array(),
          "duration" => $duration_in_minutes * 60,
          "game_counter" => $game_counter
        );
        $game_time += $duration_in_minutes;
        if (round($game_time) % $this->game_duration == 0){
          $game_counter++;
        }
      }
      
      //dpr($events);
      $this->events = $events;      
    }
    
    
    public function setRunQuality(){
      /* Run Quality */
      $score = 0;
      //gebaseerd op tijd op een bepaald positie
      foreach($this->time_in_position as $_player => $time_played_in_position){
        foreach($time_played_in_position as $_pos=>$time_in_position){
          if (is_numeric($_pos)){
            $score += $this->playerscores[$_player][$_pos] * ($time_in_position / 60);//tijd is berekend in seconden
          }
        }
      }
      //aantal_punten moest real madrid spelen
      $minuten_te_spelen = $this->game_duration * $this->nr_of_games;
      $max_points_possible = $this->playercount * $minuten_te_spelen * 100;
      
      //pecho(" $this->playercount * $minuten_te_spelen * 100 ");
      $this->score = round($score);
      $this->rating = round(($score/$max_points_possible)*100,2);
      //pecho($max_points_possible . " -- " . $score);
      //pecho($this->rating);die();
    }
       
    
    public function setTimePlayed($game_idx = 0){
      //hecho("Calculating time played for game $game_idx");
      $time_played = array();
      $players = $this->getPlayers();
      
      foreach ($players as $player){
        $time_played[$player["name"]] = 0;
        $time_benched[$player["name"]] = 0;
      }
      
      $total_playtime = $this->nr_of_games * $this->game_duration * $this->playercount;
      if ($this->available_players){
        $this->minutes_per_player = ($this->nr_of_games * $this->game_duration * $this->playercount)/$this->available_players;  
      }
      
      
      //initialize total time played for each player
      $time_in_position = array();
      foreach($this->playernames as $idx=> $name){
        $this->playerindex[$name] = $idx;
        foreach($this->positions as $pos){
           $time_in_position[$name][$pos] = 0; 
        }
        $time_in_position[$name]["bench"] = 0;
        $time_in_position[$name]["total"] = 0;
      }
      //echo "Game idx: $game_idx <br/>";
      //pr($this->events);
      foreach ($this->events as $event_idx => $event){
        $duration = $event["duration"];
        //pr($event["lineup"], __LINE__ . " LINEUP");
        foreach ($event["lineup"] as $pos => $name){
          //pr($time_in_position[$name],$name . " " .$pos);
          $time_in_position[$name][$pos] += $duration;
          $time_played[$name] += $duration;
          $time_in_position[$name]["total"] += $duration;
          
        }
        foreach ($event["bench"] as $name){
          $time_in_position[$name]["bench"] += $duration;
          $time_benched[$name] += $duration;
        }
        //$this->events[$game_idx]["benchtime"] = $time_benched;
        //$this->events[$game_idx]["playtime"] = $time_played;
        //dpr($this->events,$game_idx);
        //pr($time_in_position,$event_idx);
      }
      $this->time_in_position = $time_in_position;
      
      
      $this->time_benched = $time_benched;
      $this->time_played = $time_played;
      //pr($time_played,"GameIdx $game_idx");
      $this->time_in_position = $time_in_position;
      
      $this->total_playtime = $time_played;  
    }
    
    private function initSquad($spelers) {
      //pr($spelers,__LINE__);
      
      $player_scores = $this->playerscores;
      $all_players = array();
      foreach ($this->playerscores as $player=>$scores){
        arsort($scores);
        $all_players[] = array(          
          "name" => $player
            , "positions" => array_keys($scores)
        );
      }
      //pr($spelers,__LINE__);
     
      $playernames = array();
      $playerfullnames = array();
      $squad = array();
      
      
      // code herschreven dat shuffle in index gebeurd....
      foreach($spelers as $naam){
        foreach($all_players as $player){
          if($player["name"] == $naam){
            array_push($squad,$player);
            array_push($playernames,$player["name"]);
            array_push($playerfullnames,$player["name"] . " " . $this->playerinfo[$player["name"]]["name"]);
          }
        }
      }
      /*
      
      if ($this->shuffleType == "coach"){
        foreach($spelers as $naam){
          foreach($all_players as $player){
            if($player["name"] == $naam){
              array_push($squad,$player);
              array_push($playernames,$player["name"]);
              array_push($playerfullnames,$player["name"] . " " . $this->playerinfo[$player["name"]]["name"]);
            }
          }
        }
        //print_r($spelers);
        //dpr($playernames,__LINE__ . ":" . __FILE__);
      } else {
       // pr($spelers,__LINE__);
       
        
        foreach($all_players as $player){
          if (in_array($player["name"],$spelers)){
            array_push($squad,$player);
            array_push($playernames,$player["name"]);
            array_push($playerfullnames,$player["name"] . " " . $this->playerinfo[$player["name"]]["name"]);            
          }
        }
        shuffle($squad);
        shuffle($playernames);
      }
      asort($playerfullnames);*/
      //pr($squad,__LINE__);
      //pr($playernames,__LINE__);
      //dpr($all_players,__LINE__);
      
      $this->squad = $squad;
      $this->players = $squad;
      $this->playernames = $playernames;
      $this->playerfullnames = $playerfullnames;
      //dpr($squad);
      $this->available_players = count($squad);
      
      // Zoek namen die niet in beide arrays voorkomen
      $mismatches = array_merge(
          array_diff($spelers, $playernames),
          array_diff($playernames, $spelers)
      );

      if (!empty($mismatches)) {
          echo "Speler gevonden zonder settings ==> :\n";
          var_dump($mismatches);
          
          pr($spelers,"spelers");
          pr($playernames,"playernames");
          exit("Script gestopt vanwege mismatch.");
      }
      /* Check if every squad player has settings ... */
      
    }
    

    
    private function getSquad() {
      
      return $this->squad;
    }
    
    public function getPlayerListing($exclude = null) {
      //public function getPlayerListing($sort = 0,$exclude = null) {
      /*
       * sort:
       * 0 -> random (default)
       * 1 -> time_played asc
       * 2 -> time_played desc
      */
      return $this->playernames;
      
      /*
      $_players = array();
      foreach($this->time_played as $_name=>$time_played){
        if (!is_null($exclude) and is_array($exclude)){
          if (in_array($_name,$exclude)){
            continue(1);
          }
        }
        $_players[] = $_name;
      }
      */
      
      /*
      //hard coded player swap
      // speler naar achter schuiven -> minste minuten
      $players_to_shift = array("Lenn","Ayaz");
      $players_to_return = array();
      $players_to_add = array();
      foreach($_players as $k=>$v){
        if (in_array($v,$players_to_shift)){
          unset($_players[$k]);
          array_push($players_to_add,$v);
        } else {
          array_push($players_to_return,$v);
        }
      }
      $players_to_return = array_merge($players_to_return,$players_to_add);
      //dpr($players_to_return);
      */
      return $players_to_return;  
    }

    public function getPlayers($exclude = null) {
      return $this->players;  
    }
    public function setPlayers($players) {
      $this->players = $players;
    }
    
    public function setScore($score) {
      $this->score = $score;
    }
    
    



    private function parseFormat(string $format): void {
        $this->playercount = (int)substr($format, 0, 1);
        if ($this->playercount == 8) {
            $this->positions = array(1,2,4,5,7,9,10,11);
        }
        $this->format = $format;
    }
    
    

    
}
function getPositionScore($player,$position){
  global $player_positions;
  foreach ($player_positions as $score=>$_pos){
    if ($_pos == $position) {
      return $score + 1;
    }
  }
}

function getLeastPlayed(){
  global $time_played;
  $leastAmount_played = 500;
  foreach($time_played as $player_key=>$minutes){    
    if ($minutes < $leastAmount_played){      
      $leastAmount_played = $minutes;
    }
  }
  return $leastAmount_played;
}

function getGoalkeepers($positions_per_player,$playercount,$aantal=3){
  $fixed_goalkeepers = array();
  $random_goalkeepers = array();
  $goalkeepers = array();
  /* eerst doelmannen invullen - anders komt wisselschema niet uit */
  foreach($positions_per_player as $player_key => $arr){
    foreach($arr as $weight => $_pos) {
      if ($_pos == 1){
        if ($weight == 0) {
          if (count($goalkeepers) < 2) {
            array_push($goalkeepers,$player_key);
          } else {
            $inserts = $playercount - $weight;
            for($i=0;$i<$inserts;$i++){
              $random_goalkeepers[] = $player_key;
            }
          }
        } else {
          // voeg de overige toe met een weging
          $inserts = $playercount - $weight;
          for($i=0;$i<$inserts;$i++){
            $random_goalkeepers[] = $player_key;
          }
        }
      }
    
    }
  }
  shuffle($random_goalkeepers);
  
  while(count($goalkeepers) < $aantal){
    $_key = array_shift($random_goalkeepers);
    if (!in_array($_key,$goalkeepers)){
      $goalkeepers[] = $_key;
    }
  }
    
  return $goalkeepers;
}
function getPositionFromLineup($_game,$player){
  global $game;
  foreach ($_game["lineup"] as $position => $player_key) {
    if ($player == $player_key) {
      return $position;
    }
  }
  pr($game);
  dpr($_game,__LINE__ . " - No position found for $player");
}
function getPositionsFromLineup($lineup){
  global $time_played;
  //pr($lineup,"lineup");
  //pr($time_played,"minutes_played");
  
  $positions_by_minutes = array();
  foreach($time_played as $k=>$v){
    if (!array_key_exists($v,$positions_by_minutes)){
      $positions_by_minutes[$v] = array();
    }
  }
  krsort($positions_by_minutes);
  
  foreach($lineup as $_pos=>$_player){
    if ($_pos > 1){
      $positions_by_minutes[$time_played[$_player]][] = $_pos;  
    }
    $positions[$_player]=$_pos;
  }
  
  $positions = array();
  foreach($positions_by_minutes as $_min=>$_positions){
    shuffle($_positions);
    foreach($_positions as $_position){
      $positions[] = $_position;
    }
  }
  //re-add goalkeeper
  //$positions[] = 1;    
  //pr($positions_by_minutes);
  //dpr($positions);
  return $positions;  
}

function getSubLineup($lineup){
  global $time_played,$players,$active_lineup;
  
  $minutes = array();
  $array_keys = array();
  $array_to_return = array();
  $arrays = array();
  $players_found = 0;
  
  foreach($time_played as $minute){
    if (!in_array($minute,$minutes)){
      array_push($minutes,$minute);
    }
  }
  arsort($minutes);
  
  foreach($minutes as $minute){
    if ($minute){ // geen nut om lege minuten mee te nemen
      foreach($time_played as $player_key => $number_of_minutes_played){
        
        //make sure player is on the pitch
        if (in_array($player_key,array_keys($active_lineup))){ 
          if ($number_of_minutes_played == $minute){
            $arrays[$minute][]= $player_key;
          }
        }
      }
    }
  }
  
    
  foreach($arrays as $min => $players_with_those_minutes){
    shuffle($players_with_those_minutes);
    $array_to_return[$min] = $players_with_those_minutes;
  }
  //pr($time_played);
  //pr($lineup);
  //dpr($array_to_return);
  return $array_to_return;
  
  
}

function getPlayers($returnPayload = "OnlyName",$sortOrder="down"){
  global $time_played, $spelers;
  $aantal = count($spelers);
    
  $array_keys = array();
  $array_to_return = array();
  $arrays = array();
  $players_found = 0;
  $sumtingwong = 0;
  $minutes = array();
  foreach($time_played as $minute){
    if (!in_array($minute,$minutes)){
      array_push($minutes,$minute);
    }
  }
  asort($minutes);

  foreach($minutes as $minute){
    foreach($time_played as $player_key => $number_of_minutes_played){
      if ($number_of_minutes_played == $minute){
        $arrays[$minute][]=$player_key;
      }
    }
  }
  
  if ($sortOrder == "up"){
    $arrays = array_reverse($arrays);
  }
  
  foreach($arrays as $min => $players_with_those_minutes){
    shuffle($players_with_those_minutes);
    //make sure players are shuffled within their minutes
    $arrays[$min] = $players_with_those_minutes;
    foreach($players_with_those_minutes as $p){
      $array_keys[] = $p;  
    }
  }
 
  //pr($arrays,__LINE__." - arrays");
  //dpr($array_keys,__LINE__." - array_keys");
  
  foreach($array_keys as $key){
    foreach($spelers as $player){
      if ($player["name"] == $key){
        //$player["minutes"] = $time_played[$key];
        if ($returnPayload == "OnlyName"){
          $array_to_return[] = $player["name"];
        } else {
          $array_to_return[] = $player;
        }        
      }
    } 
  }
  return $array_to_return;
  
  
}

function findBestPlayerForPosition($subs,$position){
  global $position_players;
  pecho(__LINE__." - op zoek naar positie $position in subs: " . implode(", ",$subs));
  //start from preferred position
  
  foreach($position_players[$position] as $_player){
    if (in_array($_player,$subs)){
      //pecho(__LINE__." Wissel gevonden voor $position in subs: " . $_player);
      //TODO check if first match returns
      
      return $_player;
    }
  }
  dpr($position_players,__LINE__."- geen sub gevonden");
  
}
function findBestSub($subs,$position){
  global $position_players;
  //pecho(__LINE__." - op zoek naar positie $position in subs: " . implode(", ",$subs));
  //start from preferred position
  if (!count($subs)){
    hecho(__LINE__ . " No subs left");
  } 
  foreach($position_players[$position] as $_player){
    if (in_array($_player,$subs)){
      //pecho(__LINE__." Wissel gevonden voor $position in subs: " . $_player);
      //TODO check if first match returns
      
      return $_player;
    }
  }
  dpr($position_players,__LINE__."- geen sub gevonden voor $position");
}

function findBestPositionForPlayer($g,$player_name, $exclude = array()){
  //global $player_positions,$time_in_position;
  //check if threshold hit... ignore if value above
  /*
  if(rand(0,10) > $g->position_weight){
    $_positions = $g->positions_per_player[$player_name];
    shuffle($_positions);
  } else {
    
  }*/
  
  $threshold = 7.5 * 60;
  foreach($g->positions_per_player[$player_name] as $k=>$pos){
    if (is_null($g->time_in_position)){
      dpr($g,"g -> time in position is null");
    }
    if (!array_key_exists($player_name,$g->positions_per_player)){
      dpr($g,"$player_name not in positions_per_player");
    
    }
    if (!array_key_exists($player_name,$g->time_in_position)){
      dpr($g,"$player_name not in time_in_position");
    
    }
    $time = $g->time_in_position[$player_name][$pos];
    if ($time >= $threshold){
      //hecho("Remove position $pos for $player_name because playtime ($time) exceeds threshold ($threshold).");
      $_position_to_shift_back = $g->positions_per_player[$player_name][$k];
      unset($g->positions_per_player[$player_name][$k]);
      $g->positions_per_player[$player_name][] = $_position_to_shift_back;
    }
  }
  $_positions = $g->positions_per_player[$player_name];
  
 
  
  
  foreach($_positions as $pos) {
    if (!in_array($pos,$exclude)){
      return $pos;
    }
  }
}
function getLeastFullGameCounter($g,$player = null){
  $counts = array();
  $skip_games = array();
  
  if ($g->available_players == 6){
    // in geval van 6 spelers, eerst even checken wanneer die op de bank moet want er is maar 1 plek vrij normaalgezien...
    $bench_slots = $g->getOpenBenchSlots($player);
    foreach($bench_slots as $bench_slot){
      array_push($skip_games,$bench_slot["game"]);
    }
  }
  
  // skip if player is allready on the bench in that game...
  foreach($g->events as $game_idx => $event){
    if (in_array($player,$event["bench"])){
      //hecho("Skipping game " . $event["game_counter"] . " for $player because he is allready on the bench for that game");
      array_push($skip_games,$event['game_counter']);
    }
  }
  
  foreach($g->fullgamers_by_game as $game_counter => $players){
    if (!in_array($game_counter,$skip_games)){
      $counts[$game_counter] = count($players);  
    }    
  }
  asort($counts);
  return array_key_first($counts);
}

function removeFromArrByVal($array,$value){
  foreach($array as $_k=>$_v){
    if ($_v == $value) {
      unset($array[$_k]);
      return $array;
    }
  }
  return $array;
}

function shuffle_assoc($list) { 
  if (!is_array($list)) return $list; 

  $keys = array_keys($list); 
  shuffle($keys); 
  $random = array(); 
  foreach ($keys as $key) { 
    $random[$key] = $list[$key]; 
  }
  return $random; 
} 

/* DEBUG */
function pecho($var) {
  echo "<p>" . $var . "</p>";
}
function decho($var) {
  echo "<p>" . $var . "</p>";
  die();
}

function hecho($var) {
  echo "<h3>" . $var . "</h3>";
}
function pr($content, $title = ""){
  if(!empty($title)){
    echo "<h2>" . $title . "</h2>";
  }
  echo "<pre>";
    print_r($content); 
  echo "</pre>";
}
function dpr($content, $title = ""){
  pr($content, $title);
  die();
}
function calctime($aantal_seconden){
	if(!is_numeric($aantal_seconden)){
		return;
	}
	$sec = $aantal_seconden;
	$minuten = 0;
	$uren = 0;
	while($sec>=3600){
		$uren++;
		$sec-=3600;
	}
	while($sec>=60){
		$minuten++;
		$sec-=60;
	}
	if(strlen($sec)==1){
		$sec= '0'.$sec;
	}
	if($uren>0and(strlen($minuten)==1)){
		$minuten = '0'.$minuten;
	}
	if($uren>0){
    if($sec != '00') {
  		$tijd = $uren.':'.$minuten.':'.$sec.' uur';
    } else {
      if ($minuten != '00'){
    		$tijd = $uren.':'.$minuten.' uur';
      } else {
    		$tijd = $uren .' uur';
      }
    }
	}
	elseif($uren==0and($minuten>0)){
    if($sec != '00') {
      $tijd = $minuten.':'.$sec; //.' minuten';
    } else {
      $tijd = $minuten; //.' minuten';
      
    }
	} else {
	  $tijd = '';
	}
	return $tijd;
}


function build_playtime_stats(array $pt_all_games, array $player_scores): array {
    $stats = [];
    $totalDuration = 0;

    // --- STAP 1: Verzamel alle data per speler per wedstrijd ---
    foreach ($pt_all_games as $g) {
        $d = $g['duration'] ?? 0;
        $totalDuration += $d;

        // Loop door de spelers van de huidige wedstrijd
        foreach (($g['players'] ?? []) as $p => $t) {
            // Initialiseer de speler als deze voor het eerst voorkomt
            if (!isset($stats[$p])) {
                $stats[$p] = [
                    'available' => 0,
                    'played' => 0,
                    'positions' => [] // NIEUW: array voor positiedata
                ];
            }
            
            // Tel de beschikbare en gespeelde tijd op (zoals voorheen)
            $stats[$p]['available'] += $d;
            $stats[$p]['played']    += (int)$t;
            
            // NIEUW: Loop door de 'playtime' data van deze speler in deze wedstrijd
            if (isset($g['playtime'][$p])) {
                foreach ($g['playtime'][$p] as $position => $time_in_position) {
                    // Initialiseer de positie voor de speler als nodig
                    if (!isset($stats[$p]['positions'][$position])) {
                        $stats[$p]['positions'][$position] = ['time' => 0];
                    }
                    // Tel de speeltijd voor deze specifieke positie op
                    $stats[$p]['positions'][$position]['time'] += $time_in_position;
                }
            }
        }
    }
    
    // --- STAP 2: Voeg spelers toe die wel gescoord hebben maar niet speelden ---
    foreach (array_keys($player_scores) as $p) {
        if (!isset($stats[$p])) {
            $stats[$p] = [
                'available' => $totalDuration,
                'played' => 0,
                'positions' => [] // Lege array voor posities
            ];
        }
    }

    // --- STAP 3: Bereken de percentages ---
    foreach ($stats as &$s) {
        // Bereken het algemene aanwezigheidspercentage
        $s['percentage'] = $s['available'] ? round(100 * $s['played'] / $s['available'], 2) : 0;
        
        // NIEUW: Bereken het percentage per positie
        // Dit percentage is gebaseerd op de *totale speeltijd* van de speler
        if ($s['played'] > 0) { // Voorkom delen door nul
            foreach ($s['positions'] as $pos_key => &$pos_data) { // Gebruik reference '&' om direct aan te passen
                $pos_data['percentage'] = round(100 * $pos_data['time'] / $s['played'], 2);
            }
            unset($pos_data); // Belangrijk: verbreek de referentie na de loop
        }
    }
    unset($s); // Belangrijk: verbreek de referentie na de loop

    // --- STAP 4: Sorteer de resultaten ---
    uasort($stats, fn($a, $b) => $b['played'] <=> $a['played']);
    //pr($stats);
    return $stats;
}

/*
function build_playtime_stats(array $pt_all_games, array $player_scores): array {
    $stats = [];
    $totalDuration = 0;
    foreach ($pt_all_games as $g) {
        $d = $g['duration'] ?? 0;
        $totalDuration += $d;
        foreach (($g['players'] ?? []) as $p => $t) {
            if (!isset($stats[$p])) $stats[$p] = ['available'=>0,'played'=>0];
            $stats[$p]['available'] += $d;
            $stats[$p]['played']    += (int)$t;
        }
    }
    
    foreach (array_keys($player_scores) as $p) {
        if (!isset($stats[$p])) $stats[$p] = ['available'=>$totalDuration,'played'=>0];
    }
    foreach ($stats as &$s) {
        $s['percentage'] = $s['available'] ? round(100*$s['played']/$s['available'],2) : 0;
    }
    unset($s);
    uasort($stats, fn($a,$b)=>$b['played']<=>$a['played']);
    return $stats;
}
 */
?>