#!/usr/local/bin/php
<?php 
require "util.php";

session_start();



$submissionError = "";

$sqlInsertQueryHeader = "INSERT INTO " . $_GET["game"] . "(";
$sqlInsertQueryValue = ") VALUES(";

$summaryText = "";


//If user hasn't logged in, redirect to Login page
if (!isset($_SESSION["itsc"])) {
	session_destroy();
	header("location: login.php");
}

//Check whether task paramter is provided along with submission
if (!isset($_GET["game"]))
	$submissionError = "Task parameter not provided";

//Check whether common.score file exists
if ($submissionError == "") {
	$gameStructure[0] = GetGameStructureFromFile("common.score");
	if ($gameStructure[0] === false)
		$submissionError == "Common task file does not exist";		
}

//Check whether task exists
if ($submissionError == "") {
	$gameStructure[1] = GetGameStructureFromFile($_GET["game"] . ".score");
	if ($gameStructure[1] === false)
		$submissionError == "Task file does not exist";

	$summaryText .= $gameStructure[1][0] . "<br><br>";
}

//Check whether basic information is provided. Also generate Insert Query
if ($submissionError == "") {
	$beginning = true;

	$BASIC_FIELDS = array(	"team" => "Team",
							"race" => "Race No",
							"players" => "Players",							
							"time" => "Racing Time",
							"total" => "Total time",
						);

	foreach($BASIC_FIELDS as $field => $title) {
		if (!isset($_POST[$field])) {
			$submissionError = "Scoring information missed: " . $title;
			break;
		}

		//Generate SQL Query
		$sqlInsertQueryHeader .= ($beginning ? "" : ",") . $field;
		$sqlInsertQueryValue .= ($beginning ? "" : ",")
								. ($field == "players" ? "\"" : "") . $_POST[$field] . ($field == "players" ? "\"" : "");

		//Create Summary Text. "total" and "time" fields will be put at the end
		if ($field != "total" && $field != "time")
			$summaryText .= $title . ": " . $_POST[$field] . "<br>";

		$beginning = false;
	}
}

//Check whether task-related information is provided
if ($submissionError == "") {
	foreach($gameStructure as $structure) {
		foreach($structure[2] as $field) {
			if (!isset($_POST[$field[0]])) {
				$submissionError = "Scoring information missed: " . $field[0];
				break;
			}

			//Generate SQL Query
			$sqlInsertQueryHeader .= "," . $field[0];
			$sqlInsertQueryValue .= "," . $_POST[$field[0]];

			//Create Summary Text
			$summaryText .= $field[1] . ": " . $_POST[$field[0]] . " (&times; " . $field[2] .	" sec)<br>";
		}

		if ($submissionError != "") break;
	}
}

//Create Summary Text: Adding "total" and "time" fields
if ($submissionError == "") {
	$summaryText .= "Racing time: " . SecondToDisplayForm($_POST["time"]) . "<br>"
					. "-----<br> TOTAL TIME: " . SecondToDisplayForm($_POST["total"]) . "<br><br>";

	$sqlInsertQuery = $sqlInsertQueryHeader . $sqlInsertQueryValue . ")";

	//Insert record into database
	DatabaseOpen();
	if (mysql_query($sqlInsertQuery) === false)
		$submissionError = "Database operation failed";
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>ENGG 2990D Score Submission</title>
</head>
<body>
	SCORE SUBMISSION RECEIPT<br>-----<br><br>

	<?php
		if ($submissionError == "")
			echo $summaryText . "<b>SCORE SUBMISSION SUCCESS!</b><br><br>You can close this page";
		else
			echo "<b>SCORE SUBMISSION FAILED!</b><br>Error: " . $submissionError
					. "<br><br>You can refresh the page to resubmit the record.";
	?>
</body>
</html>