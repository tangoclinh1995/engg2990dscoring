#!/usr/local/bin/php
<?php
require "util.php";



function StringToNumberArray($str) {
	$res = array();

	foreach (explode(",", $str) as $value) {
		$intValue = intval($value, 10);		
		if ($intValue > 0)	array_push($res, $intValue);
	}

	return $res;
}



//Get request data from $_POST
$game = isset($_POST["game"]) ? $_POST["game"] : "";

$filterTeam = isset($_POST["team"]) ? StringToNumberArray($_POST["team"]) : array();

$filterRace = isset($_POST["race"]) ? StringToNumberArray($_POST["race"]) : array();

$filterGetMin = isset($_POST["getmin"]) ? (bool)$_POST["getmin"] : false;

$sortColumn = isset($_POST["sortcol"]) ? $_POST["sortcol"] : "";

$sortAscending = isset($_POST["sortasc"]) ? (bool)$_POST["sortasc"] : false;

//Get result from database according to request
DatabaseOpen();
$sqlResult = GetRecordFromDatabase($game, $filterTeam, $filterRace, $filterGetMin, $sortColumn, $sortAscending);

if ($sqlResult === false)
	echo "ERROR";
else {
	//Output in JSON format

	echo "[[";

	$started = false;

	foreach($sqlResult[0] as $header) {
		printf("%s[\"%s\",\"%s\"]", $started ? "," : "", $header[0], $header[1]);
		$started = true;
	}

	echo "],[";

	$len = count($sqlResult[0]);
	$started = false;

	foreach($sqlResult[1] as $record) {
		if ($started) echo ",";
		echo "[";

		$recordStarted = false;

		for ($i = 0; $i < $len; ++$i) {
			if ($recordStarted) echo ",";
			echo "\"";
			
			if (isset($sqlResult[0][$i][2]) && $sqlResult[0][$i][2] == 1)
				echo SecondToDisplayForm($record[$i]);
			else echo $record[$i];

			echo "\"";

			$recordStarted = true;
		}

		echo "]";

		$started = true;
	}

	echo "]]";
}
?>