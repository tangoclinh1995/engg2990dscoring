#!/usr/local/bin/php
<?php
require "util.php";

$GEN_FAMILY_NAME = array("KWONG", "TSAY", "LO", "FAN", "CHAN");
$GEN_MIDDLE_NAME = array("Yik", "Wai", "Chuen", "Ying", "Fung");
$GEN_FIRST_NAME = array("Yin", "Ting", "Ching", "Ming", "Yeung");



function GenerateGameTable() {
	CreateGameDatabaseTableFromFile("game1.score");
	CreateGameDatabaseTableFromFile("game2.score");
	CreateGameDatabaseTableFromFile("game3.score");	
}

function GenerateName() {
	global $GEN_FAMILY_NAME, $GEN_MIDDLE_NAME, $GEN_FIRST_NAME;

	return $GEN_FAMILY_NAME[array_rand($GEN_FAMILY_NAME)] . " " 
			. $GEN_MIDDLE_NAME[array_rand($GEN_MIDDLE_NAME)] . " "
			. $GEN_FIRST_NAME[array_rand($GEN_FIRST_NAME)];
}

function GenerateTestingData($game) {
	$gameStructure[0] = GetGameStructureFromFile("common.score");
	$gameStructure[1] = GetGameStructureFromFile($game . ".score");

	$sqlInsertQuery = "INSERT INTO ". $game . " (team,players,race";

	foreach($gameStructure as $structure)
		foreach($structure[2] as $field) 
			$sqlInsertQuery .= "," . $field[0];

	$sqlInsertQuery .= ",time,total) VALUES ";
	
	$teams = range(1, 15);

	$started = false;
	$cnt = 0;
	$race = 1;

	for ($i = 0; $i < 2; ++$i) {
		shuffle($teams);

		foreach($teams as $team) {
			if ($started) $sqlInsertQuery .= ",";
			$started = true;
			
			$sqlInsertQuery .= "(" . $team . "," .
							 	"\"" . GenerateName() . "; " . GenerateName() . "\"," .
							 	$race;

			$time = rand(0, $gameStructure[1][1]);
			$totalTime = $time;

			foreach($gameStructure as $structure)
				foreach($structure[2] as $field) {
					$randVar = rand(0, min($field[3], 6));
					$totalTime += $randVar * $field[2];

					$sqlInsertQuery .= "," . $randVar;
				}

			$sqlInsertQuery .= "," . $time . "," . $totalTime .")";

			++$cnt;
			if ($cnt % 3 == 0) ++$race;
		}
	}

	DatabaseOpen();
	mysql_query("TRUNCATE " . $game);
	mysql_query($sqlInsertQuery);
	echo $sqlInsertQuery;
}



DatabaseOpen();
//GenerateGameTable();
GenerateTestingData("game1");
GenerateTestingData("game2");
GenerateTestingData("game3");
?>