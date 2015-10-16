<?php 
require "util.php";



//This function directly prints out the HTML Task Menu
function LoadTasksToMenu() {
	$TASK_MENU_TEMPLATE = "<li class='pure-menu-item'><a href='?game=%s' class='pure-menu-link'>%s</a></li>";

	$mySqlLink = DatabaseOpen();

	$mySqlResult = mysql_query("SELECT * FROM gamedb");
	if ($mySqlResult === false) return ;

	while ($row = mysql_fetch_assoc($mySqlResult))
		printf($TASK_MENU_TEMPLATE, $row["db"], $row["name"]);

	//Special task, summary of Game 1 & 2
	printf($TASK_MENU_TEMPLATE, "game12sum", "Round 1 & 2: Summary");
}



function LoadTableHeaders($headers) {
	foreach($headers as $value)
		printf("<th title='Click to sort the records based on %s' id='%s'>%s <span class='spanSort'></span></th>",
				$value[1], $value[0], $value[1]);
}


function LoadTableRecords($sqlResult) {
	$len = count($sqlResult[0]);

	foreach($sqlResult[1] as $row) {
		echo "<tr>";

		for ($i = 0; $i < $len; ++$i) {
			echo "<td>";
			
			//If this column is a time value, print in time format MM:SS
			if (isset($sqlResult[0][$i][2]) && $sqlResult[0][$i][2] == 1)
				echo SecondToDisplayForm($row[$i]);
			else echo $row[$i];

			echo "</td>";
		}

		echo "</tr>";
	}
}



$gameName = "";
$sqlResult = false;

//Load the game report, if required
if (isset($_GET["game"])) {
	$structure = GetGameStructureFromFile($_GET["game"] . ".score");

	if ($structure === false && $_GET["game"] != $GLOBALS["SPECIAL_REPORT_ALIAS"])
		unset($_GET["game"]);
	else {
		$gameName = ($_GET["game"] == $GLOBALS["SPECIAL_REPORT_ALIAS"] ? "Round 1 & 2 Summary " : $structure[0]);

		DatabaseOpen();
		$sqlResult = GetRecordFromDatabase($_GET["game"]);
	}
}
?>