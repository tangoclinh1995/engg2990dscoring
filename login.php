#!/usr/local/bin/php
<?php 
require "util.php";

$errorAnnoucement = "";

//Login into system when requested
if (isset($_POST["itsc"]) && isset($_POST["pass"]) && isset($_POST["login"])) {
	$mySqlLink = DatabaseOpen();

	$result = mysql_query("SELECT itsc FROM sta WHERE itsc='" . $_POST["itsc"] . "' AND pass='" . $_POST["pass"] . "'");
	$cnt = mysql_num_rows($result);

	//Check whether itsc account and password match or whether database can be connected
	if ($cnt === 0)
		$errorAnnoucement = "Wrong ITSC or Password!";
	elseif ($cnt === false)
		$errorAnnoucement = "Connection error. Please try again.";
	else {	//Successful login
		$loadScoringInterface = true;

		//Start new session
		session_start();

		//Save itsc into SESSION variable
		$_SESSION["itsc"] = $_POST["itsc"];

		//Redirect to scoring page
		header("location: score.php");
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>ENGG 2990D Login</title>

	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" type="text/css" href="css/grids-responsive-min.css">
	<link rel="stylesheet" type="text/css" href="css/pure-min.css">
</head>
<body>
	<div style="margin: 20px auto; width: 35%">
		<form class="pure-form pure-form-stacked" method="post">
			<fieldset>
			    <legend>ENGG 2990D Scoring System</legend>
			    
			    <p style="color: red">
			    	<?php echo $errorAnnoucement ?>
			    </p>
			    
			    <input name="itsc" placeholder="ITSC Account" type="text">
			    <input name="pass" placeholder="Password" type="password">
				<button type="submit" name="login" class="pure-button pure-button-primary">Login</button>
			</fieldset>
		</form>
	</div>
</body>
</html>