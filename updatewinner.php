#!/usr/local/bin/php
<?php 
require "util.php";



$WINNER_DEDUCTION = 5;



function UpdateWinner($game) {
	global $link;

	echo "UPDATE " . $game . "<br>";

	$mySqlResult = mysql_query("SELECT race, MIN(time) AS racemin FROM " . $game . " GROUP BY race");
	if ($mySqlResult === false) return false;

	while ($row = mysql_fetch_assoc($mySqlResult))
		$raceMinTime[$row["race"]] = $row["racemin"];

	$mySqlResult = mysql_query("SELECT id, race, time, win, total FROM " . $game);
	if ($mySqlResult === false) return false;

	if (mysql_query("BEGIN") === false)
		return false;

	if (mysql_query("SET autocommit=0") === false)
		return false;	

	$success = true;

	while ($row = mysql_fetch_assoc($mySqlResult)) {
		$sqlQuery = "";

		if ($raceMinTime[$row["race"]] == $row["time"]) {
			if ($row["win"] == 0)
				$sqlQuery = sprintf("UPDATE %s SET win=1,total=%d WHERE id=%d", $game, $row["total"] - $WINNER_DEDUCTION, $row["id"]);
		} else {
			if ($row["win"] == 1)
				$sqlQuery = sprintf("UPDATE %s SET win=0,total=%d WHERE id=%d", $game, $row["total"] + $WINNER_DEDUCTION, $row["id"]);
		}

		if ($sqlQuery != "") {
			echo $sqlQuery . "<br>";

			if (mysql_query($sqlQuery) === false) {
				$success = false;
				break;
			}
		}
	}

	if ($success) {		
		mysql_query("COMMIT");		

		echo "DONE!<br><br>";
	}
	else {
		mysql_query("ROLLBACK");

		echo "ERROR!<br><br>";
	}
}



if (isset($_POST["itsc"]) && isset($_POST["pass"]) && isset($_POST["update"])) {
	$link = DatabaseOpen();

	$result = mysql_query("SELECT itsc FROM sta WHERE itsc='" . $_POST["itsc"] . "' AND pass='" . $_POST["pass"] . "'");
	$cnt = mysql_num_rows($result);

	//Check whether itsc account and password match or whether database can be connected
	if ($cnt === 0)
		echo "Wrong ITSC or Password!<br>";
	elseif ($cnt === false)
		echo "Connection error. Please try again.<br>";
	else {	//Successful login
		UpdateWinner("game1");
		UpdateWinner("game2");
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>ENGG 2990D Winner-Updater</title>
</head>
<body>
	<form method="post">
		ITSC: <input type="text" name="itsc"><br><br>
		Pass: <input type="password" name="pass"><br><br>
		<button type="submit" name="update">Update Winner!</button>
	</form>
</body>
</html>