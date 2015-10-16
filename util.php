<?php
require "config.php";



$SPECIAL_REPORT_ALIAS = "game12sum";



function DatabaseOpen() {
      $mySqlLink = mysql_connect($GLOBALS["MYSQL_HOST"], $GLOBALS["MYSQL_USER"], $GLOBALS["MYSQL_PASSWORD"]);
      if ($mySqlLink === false) return false;

      if (mysql_select_db($GLOBALS["MYSQL_DATABASE"]) === false) return false;

      return $mySqlLink;
}


function DatabaseClose($mySqlLink) {
      mysql_close($mySqlLink);
}


function SecondToDisplayForm($sec) {
      $secFrac = $sec - floor($sec);
      $sec = floor($sec);

      return sprintf("%02d:%02d%s", (int)floor($sec / 60), $sec % 60, $secFrac == 0 ? "" : "." . round($secFrac * 10));
}



/*
Result of this function:
      [
            SQL Table Title, Table Header, Time penalty per occurence, Max. no. of Occurence [, Time threshold for penalty]]
      ]
*/
function GetPenaltyInformationFromLine($line) {
      $line .= ",";      
      $prev = 0;
      $cnt = 0;
      $length = strlen($line);

      for ($i = 0; $i < $length; ++$i)
            if ($line[$i] == ',') {
                  $result[$cnt++] = trim(substr($line, $prev, $i - $prev));
                  $prev = $i + 1;
            }

      return $result;
}


/*
Result of this function:
      [
            0 => |Name of the game, can be blank|,
            1 => |Time limit of the game, will be -1 if Not Available|,
            2 => [
                  [SQL Table Title, Table Header, Time penalty per occurence
                                                , Max. no. of Occurence [, Time threshold for penalty]]
            ]
      ]
*/
function GetGameStructureFromFile($filename) {
      $result[0] = "";
      $result[1] = -1;

      //Caution: Warning is TURNED OFF at fopen (@), therefore, Warning message will not be shown in the website
      $file = @fopen($GLOBALS["ROOT_GAME_PATH"] . $filename, "r");
      if ($file === false) return false;

      $i = 0;
      while (($line = fgets($file)) !== false) {
            $line = trim($line);

            //Comment Line, ignore
            if (strlen($line)>=2 && substr($line, 0, 2) == "//") continue;

            if (strlen($line)>=5 && strtoupper(substr($line, 0, 5)) == "NAME=")
                  $result[0] = substr($line, 5);
            elseif (strlen($line)>=5 && strtoupper(substr($line, 0, 5)) == "TIME=")
                  $result[1] = (int)substr($line, 5);
            else
                  $result[2][$i++] = GetPenaltyInformationFromLine($line);
      }

      fclose($file);

      return $result;
}


function CreateGameDatabaseTableFromFile($filename) {
      $sqlCreateTableQuery = "CREATE TABLE [db_name] (
                                    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                    team MEDIUMINT UNSIGNED NOT NULL,                                    
                                    players VARCHAR(100) NOT NULL,
                                    race MEDIUMINT UNSIGNED NOT NULL,
                                    [common_fields][game_fields]
                                    time MEDIUMINT UNSIGNED NOT NULL,
                                    total MEDIUMINT UNSIGNED NOT NULL,
                                    PRIMARY KEY (id),
                                    INDEX (team))";

      $gameFiles = array("common.score", $filename);
      $replacedFields = array("[common_fields]", "[game_fields]");

      for ($i = 0; $i < 2; ++$i) {
            //Get Game Structure
            $game = GetGameStructureFromFile($gameFiles[$i]);
            if ($game === false) return false;

            $length = count($game);
            $sqlReplaceQuery = "";

            foreach ($game[2] as $value)
                  $sqlReplaceQuery .= $value[0] . " MEDIUMINT UNSIGNED NOT NULL ,";

            //Insert  Structure into SQL CREATE TABLE query
            $sqlCreateTableQuery = str_replace($replacedFields[$i], $sqlReplaceQuery, $sqlCreateTableQuery);            
      }

      //Get the Game's Real Name and Dababase Name

      //Game's real name is the first element of the 'game' array
      $gameName = $game[0];      

      //Game's Database name is the filename without extension
      $gameDatabase = basename($filename, ".score");

      //Insert Database Name into SQL CREATE TABLE query
      $sqlCreateTableQuery = str_replace("[db_name]", $gameDatabase, $sqlCreateTableQuery);

      //Insert Game Table into database
      if (mysql_query($sqlCreateTableQuery) === false) return false;

      //Insert Game's Real name & Database name on Game summary table
      if (mysql_query("INSERT INTO gamedb (db,name) VALUES('". $gameDatabase ."','". $gameName ."')") === false);
            return false;
}


/*
Result of this function:
      [
            0 => [
                  [SQL Table Header, Full Table Header, [1 if this column is in Time-format]]
                  ]
            1 => [
                  [Records, which order matches with table headers listed on 0]
                  ]

      ]
*/
function GetRecordFromDatabase($game, $filterTeam = array(), $filterRace = array(), $getMin = false, $sortColumn = "", $sortAscending = true) {
      $result = array(array(), array());

      //Build WHERE condition with filters of Team and Race
      $sqlFilterTeam = count($filterTeam) == 0 ? "" : " team IN (" . implode(",", $filterTeam) . ")";

      $sqlFilterRace = count($filterRace) == 0 ? "" : " race IN (" . implode(",", $filterRace) . ")";

      $sqlWhere = "";

      if ($sqlFilterTeam != "")
            $sqlWhere = " WHERE" . $sqlFilterTeam;

      if ($sqlFilterRace != "") {
            if ($sqlWhere == "")
                  $sqlWhere = " WHERE" . $sqlFilterRace;
            else
                  $sqlWhere .= " AND" . $sqlFilterRace;
      }

      //Build ORDER BY condition
      $sqlSort = "";

      if ($sortColumn != "")
            $sqlSort = " ORDER BY " . $sortColumn . ($sortAscending ? " ASC" : " DESC");


      //If the requested data is the summary of Task 1 & 2, we need a special process
      if ($game == $GLOBALS["SPECIAL_REPORT_ALIAS"]) {
            //This query is the union of 2 tables: Game 1 & 2
            $sqlQuery = "SELECT game1.team AS team, MIN(game1.total) AS game1min, MIN(game2.total) AS game2min,
                              (MIN(game1.total) + MIN(game2.total)) AS total
                        FROM game1
                        INNER JOIN dummy ON dummy.num < 2
                        LEFT JOIN game2 ON (dummy.num = 0 AND game1.team = game2.team)
                        GROUP BY team"
                        . $sqlSort;

            $result[0] = array(     array("team", "Team"),
                                    array("game1min", "Round 1 Min", 1),
                                    array("game2min", "Round 2 Min", 1),
                                    array("total", "TOTAL", 1)   );
      
      //Normal process for normal tasks
      } else {
            if ($getMin) {
                  $sqlQuery = "SELECT team, MIN(total) AS min FROM " . $game
                                    . $sqlWhere
                                    . " GROUP BY team"
                                    . $sqlSort;
                  
                  $result[0] = array(     array("team", "Team"),
                                          array("min", "Min Time", 1)  );
            } else {
                  $sqlQuery = "SELECT race, team, total, time, players";

                  $result[0] = array(     array("race", "Race No."),
                                          array("team", "Team"),
                                          array("total", "TOTAL", 1),
                                          array("time", "Racing Time", 1),
                                          array("players", "Players"),  );

                  foreach(array("common.score", $game . ".score") as $file) {
                        $structure = GetGameStructureFromFile($file);
                        
                        if ($structure === false)
                              return false;
                        
                        foreach($structure[2] as $field) {
                              //Add SQL Table Header into query
                              $sqlQuery .= "," . $field[0];

                              //Add header information into $result
                              array_push($result[0], array($field[0], $field[1]));;
                        }

                        
                  }

                  $sqlQuery .= " FROM " . $game
                              . $sqlWhere
                              . $sqlSort;
            }
      }

      $mySqlResult = mysql_query($sqlQuery);

      if ($mySqlResult === false)
            return false;

      while ($row = mysql_fetch_array($mySqlResult, MYSQL_NUM)) {
            array_push($result[1], $row);
      }

      return $result;
}
?>