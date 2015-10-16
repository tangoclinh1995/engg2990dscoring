#!/usr/local/bin/php
<?php 
require "report_phpcore.php";
?>

<!DOCTYPE html>
<html>
<head>
	<title>ENGG 2990D Report</title>

	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" type="text/css" href="css/grids-responsive-min.css">
	<link rel="stylesheet" type="text/css" href="css/pure-min.css">

	<link rel="stylesheet" type="text/css" href="css/common.css">

	<style type="text/css">
		table#tableReport th { cursor: pointer; }
	</style>
</head>
<body>
	<div class="pure-menu pure-menu-horizontal pure-menu-fixed">
		<a class="pure-menu-heading" style="color: white">REPORT</a>

		<ul class="pure-menu-list">
			<li class="pure-menu-item pure-menu-has-children pure-menu-allow-hover">
				<a href="#" class="pure-menu-link">Tasks</a>

				<ul class="pure-menu-children">
					<?php LoadTasksToMenu(); ?>
				</ul>			
			</li>
		</ul>

		<ul class="pure-menu-list" style="float: right">
			<li class="pure-menu-item">
				<a href="score.php" target="_blank" class="pure-menu-link">Scoring System</a>
			</li>
		</ul>		
	</div>

	<h2 style="margin: 60px auto 10px auto; text-align: center; font-weight: 100; font-size: 200%">Report</h2>
	<h3 style="margin: 0px auto 50px auto; text-align: center">
		<?php echo $gameName; ?>
	</h3>

	<form class="pure-form" style="margin: 0px 0px 20px 10px">
		<?php
			//In the "Round 1 & 2 Summary Report, only allow to filter by Team
			if (isset($_GET["game"]) && $_GET["game"] != $GLOBALS["SPECIAL_REPORT_ALIAS"])
				echo "	<input id='filterTeam' placeholder='Team' type='text'>
						<input id='filterRace' placeholder='Race No.' type='text'>
						&nbsp;<input id='checkMin' type='checkbox'> Min Time&nbsp;&nbsp;";
		?>

		<button type="button" id="btnFilter" class="pure-button pure-button-primary">Refresh</button>
	</form>

	<table class="pure-table pure-table-bordered pure-table-striped" id="tableReport" style="margin: 10px">
		<thead><tr>
			<?php 
				if ($sqlResult !== false)
					LoadTableHeaders($sqlResult[0]);
			?>
		</tr></thead>

		<tbody>
			<?php 
				if ($sqlResult !== false)
					LoadTableRecords($sqlResult);
			?>
		</tbody>
	</table>

	<script type="text/javascript">
		var GAME_DATABASE = "<?php echo isset($_GET["game"]) ? $_GET["game"] : ""; ?>";
	</script>
	
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/jquery.floatThead.min.js"></script>
	<script type="text/javascript" src="js/report.js"></script>
</body>
</html>