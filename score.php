#!/usr/local/bin/php
<?php
require "score_phpcore.php";
?>

<!DOCTYPE html>
<html>
<head>
	<title>ENGG 2990D Scoring System</title>

	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" type="text/css" href="css/grids-responsive-min.css">
	<link rel="stylesheet" type="text/css" href="css/pure-min.css">

	<link rel="stylesheet" type="text/css" href="css/common.css">

	<style type="text/css">
		th { padding: 10px 10px 25px 10px; text-align: left; background: white; }
		td { padding: 5px;}
		table#tableScoring span { margin-right: 10px; }

		.button-secondary { background: rgb(66, 184, 221); color: white; }
		.button-main-timer { font-size: 90%; }
		span#spanMainTimer { font-size: 200%; font-style: bold; }
	</style>
</head>
<body>
	<div class="pure-menu pure-menu-horizontal pure-menu-fixed">
		<ul class="pure-menu-list">
			<li class="pure-menu-item pure-menu-has-children pure-menu-allow-hover">
				<a href="#" class="pure-menu-link">Tasks</a>

				<ul class="pure-menu-children">
					<?php LoadTasksToMenu(); ?>
				</ul>			
			</li>

			<li class="pure-menu-item">
				<a href="report.php" target="_blank" class="pure-menu-link">Report</a>
			</li>			
		</ul>

		<ul class="pure-menu-list" style="float: right">
			<li class="pure-menu-item" style="color: white">
				<a href="#" class="pure-menu-link">
					<?php echo $_SESSION["itsc"]; ?>
				</a>
			</li>

			<li class="pure-menu-item">
				<a href="?logout=1" class="pure-menu-link" style="background: #0078e7">Logout</a>
			</li>
		</ul>
	</div>	

	<div style="margin: 40px auto; width: 80%">
		<form id="formScoring" class="pure-form" method="post" <?php echo isset($_GET["game"]) ? "target='_blank' action='submitscore.php?game=" . $_GET["game"] . "'" : "" ?> >
			<fieldset>
			    <legend>
			    	<?php echo $gameName; ?>
			    </legend>
				    <p style="color: red"></p>

			    <table style="width: 80%; margin-bottom: 20px" id="tableScoring">
			    	<thead><tr>
			    		<th style="width: 20%">
			    			<button type="button" id="btnMainTimerStart" class="pure-button pure-button-primary button-main-timer">START</button>
							<br><br>
							<button type="button" id="btnMainTimerManualSet" class="pure-button" style="font-size: 80%">Manual Set</button>
			    		</th>
			    		<th>
			    			<span id='spanMainTimer'>0:00</span>			    			

							<input type="hidden" class="record" name="time" value="0">
							<input type="hidden" id="mainTimerLimit" value="<?php echo $gameTimeLimit; ?>">			    			
			    		</th>			    				    		
			    	</tr></thead>

			    	<tr>
			    		<td>			    			
			    			Team&nbsp;
			    			<input name="team" type="text" class="record" style="width: 50px">
			    		</td>
			    		<td>			    			
			    			
			    			Race No.&nbsp;
			    			<input name="race" type="text" class="record" style="width: 50px">
			    		</td>
			    	</tr>

			    	<tr>
			    		<td>Players</td>
			    		<td><input name="players" class="record" type="text"></td>
			    	</tr>  	

			    	<?php echo $scoringInterface; ?>
			    </table>

			    <input type="hidden" class="record" name="total" value="0">

			    <button type="submit" class="pure-button pure-button-primary">Submit Score</button>
			    &nbsp;&nbsp;
			    <button type="button" id="btnNewRace" class="pure-button pure-button-primary">New Race</button>
			</fieldset>
		</form>
	</div>
	
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/jquery.floatThead.min.js"></script>
	<script type="text/javascript" src="js/timer.js"></script>
	<script type="text/javascript" src="js/score.js"></script>
</body>
</html>