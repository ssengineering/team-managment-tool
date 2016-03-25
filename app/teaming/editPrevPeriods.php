<?php
require('../includes/includeme.php');
require('teamingFunctions.php');
if(!can("read", "28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource--this only allows the user to see the summary page*/{
	echo "<h1>You do not have Authorization to view this page, if you feel this is in error contact your supervisor.</h1>";
	require('../includes/includeAtEnd.php');
	return;
}

$startDate = '';
$endDate = '';
//Set new start and end dates in case a previous period is chosen.  prevPeriodSelect
if(isset($_POST['prevPeriodSelect'])){

	$splitCombinedDate = explode(",", $_POST['prevPeriodSelect']);
	$startDate = $splitCombinedDate[0];
	$endDate = $splitCombinedDate[1];
	$currentTeamingPeriod = 0;
}

//If a previous date has been selected, update the teamingLog table.  Else the current period is assumed. 
if(isset($_POST['prevStartDate']) || isset($_POST['prevEndDate'])) {
	if(isset($_POST['submit'])){
		try {
			$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE area = :area AND `startDate`=:start AND `endDate`=:end");
			$logQuery->execute(array(':area' => $area, ':start' => $_POST['prevStartDate'], ':end' => $_POST['prevEndDate']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
			if(isset($_POST['teamed'][$cur['netID']])) {
				try {
					$updateQuery = $db->prepare("UPDATE teamingLog SET teamed='1' WHERE netID = :netId AND `startDate`=:start AND `endDate`=:end AND `area` = :area");
					$updateQuery->execute(array(':netId' => $cur['netID'], ':start' => $_POST['prevStartDate'], ':end' => $_POST['prevEndDate'], ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
			} else {
				try {
					$updateQuery = $db->prepare("UPDATE teamingLog SET teamed='0' WHERE netID = :netId AND `startDate`=:start AND `endDate`=:end AND `area` = :area");
					$updateQuery->execute(array(':netId' => $cur['netID'], ':start' => $_POST['prevStartDate'], ':end' => $_POST['prevEndDate'], ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
			// Set the timely
			if(isset($_POST['timely'][$cur['netID']])) {
				try {
					$updateQuery = $db->prepare("UPDATE teamingLog SET timely='1' WHERE netID = :netId AND `startDate`=:start AND `endDate`=:end AND `area` = :area");
					$updateQuery->execute(array(':netId' => $cur['netID'], ':start' => $_POST['prevStartDate'], ':end' => $_POST['prevEndDate'], ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
			} else {
				try {
					$updateQuery = $db->prepare("UPDATE teamingLog SET timely='0' WHERE netID = :netId AND `startDate`=:start AND `endDate`=:end AND `area` = :area");
					$updateQuery->execute(array(':netId' => $cur['netID'], ':start' => $_POST['prevStartDate'], ':end' => $_POST['prevEndDate'], ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
	}

	$startDate = $_POST['prevStartDate'];
	$endDate = $_POST['prevEndDate'];
	$currentTeamingPeriod = 0;
}
?>
<script type='text/javascript'>

function currentTeamingPeriod()
{
 location.replace('summary.php');
}

</script>

<style type="text/css">
.center
{
	margin:auto;
	width:100%;
	text-align:center;
}
.currentTeaming{
	width:40%;
	float:left;

}
.teamingStats{
	width:537px;
	float:right;
	position: relative;
}
.floatRight
{
	float: right;
	position: relative;
	right: 35%;
	
}
</style>
	<br />
	<h1 class="center">Previous Training Period </h1><br />
	<h3 class="center"> <?php echo date("F j, Y",strtotime($startDate)).' '.' - '.date("F j, Y",strtotime($endDate)); ?></h3><br />
	<div class='currentTeaming'>
	<h2>Training For Period</h2>
<?php

	if($startDate == '' ||$endDate == '')
	{
		echo '<h2>Please choose a previous period from the Training Summary Page</h2>';
	}
	else
	{
		
		echo "<form id='teaming' name='teaming' method='post'>";
		printAllPreviousTeaming($startDate,$endDate,$area);
		echo '<input type="hidden" id="prevStartDate" name="prevStartDate" value="'.$startDate.'" />';
		echo '<input type="hidden" name="prevEndDate" value="'.$endDate.'" />';
		echo "<input type='submit' id='submit' name='submit' value='Submit Changes' /></form>";
	}
?>
	</div>
	<div class='teamingStats'>
	<h2>Training Stats (Trained : Timely)</h2>
	<table>
	<tr>
	<th>Team Leader</th><th>% This Period's Semester </th>
	</tr>
	<?php printPreviousTeamingStats($startDate, $endDate, $area); ?>
	</table>
	</div>

	<div class='floatRight'>
	<h2>Training Management</h2>
	<a href="javascript:void" onClick="currentTeamingPeriod();">Go To Current Training Period</a><br /><br />

	</div>
<?php 
require('../includes/includeAtEnd.php');
 ?>
