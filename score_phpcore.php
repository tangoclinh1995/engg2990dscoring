<?php
require "util.php";

session_start();



//This function directly prints out the HTML Task Menu
function LoadTasksToMenu() {
	$TASK_MENU_TEMPLATE = "<li class='pure-menu-item'><a href='?game=%s' class='pure-menu-link'>%s</a></li>";

	$mySqlLink = DatabaseOpen();

	$mySqlResult = mysql_query("SELECT * FROM gamedb");
	if ($mySqlResult === false) return ;

	while ($row = mysql_fetch_assoc($mySqlResult))
		printf($TASK_MENU_TEMPLATE, $row["db"], $row["name"]);
}


//This function return the HTML string of the scoring field OR False on failure
//NOTE: This function also loads the name of the game
function LoadScoringInterface($game) {
	$FIELD_TEMPLATE = 	"<tr>
							<td class='penalty-title'>%s</td>
							<td class='penalty'>								
								<input type='hidden' class='record' name='%s' value='0'>
								<input type='hidden' class='param-weight' value='%d'>
								%s%s%s
							</td>
						</tr>";
	
	$BUTTON_CHANGE_TEMPLATE = "<input type='hidden' class='param-max' value='%d'>
								<button type='button' class='pure-button button-increase'>+</button>
								<button type='button' class='pure-button button-decrease'>-</button>";

	$SPAN_HTML = "<span>0</span>";

	$CHECKBOX_HTML = "<input type='checkbox'>";

	$TIMER_FIELD_TEMPLATE = "<input type='hidden' class='param-timerthreshold' value='%d'>
							<button type='button' class='pure-button button-timer button-secondary'>Start</button>&nbsp;&nbsp;&nbsp;";

	$result = "";

	foreach(array("common.score", $game . ".score") as $file) {
		$structure = GetGameStructureFromFile($file);
		if ($structure === false) return false;

		foreach ($structure[2] as $value)
			$result .= sprintf($FIELD_TEMPLATE,
								$value[1],
								$value[0],
								$value[2],
								$value[3] > 1 ? $SPAN_HTML : "",
								count($value) > 4 ? sprintf($TIMER_FIELD_TEMPLATE, $value[4]) : "",
								$value[3] > 1 ? sprintf($BUTTON_CHANGE_TEMPLATE, $value[3]) : $CHECKBOX_HTML);

		//Load the name of the game into the global variable $gameName
		$GLOBALS["gameName"] = $structure[0];

		//Load the time limit of the game into the globlal variable $gameTimeLimit
		$GLOBALS["gameTimeLimit"] = $structure[1];
	}

	return $result;
}


//If user hasn't logged in OR user intentionally logs out, redirect to Login page
if (!isset($_SESSION["itsc"]) || isset($_GET["logout"])) {
	session_destroy();
	header("location: login.php");
}

//Load the game, if required
if (isset($_GET["game"])) {
	$gameName = "";
	$scoringInterface = LoadScoringInterface($_GET["game"]);
}
?>