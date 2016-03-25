<?php //summary.php
//this file produces the teaming summary page where it displays statistics about teams and the ability to update teaming for each employee
require('../includes/includeme.php');
require('teamingFunctions.php');
if(!can("read", "28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource*/{
	echo "<h1>You do not have Authorization to view this page, if you feel this is in error contact your supervisor.</h1>";
	require('../includes/includeAtEnd.php');
	return;
}

$startDate = '';
$endDate = '';

	// If no previous dates selected, then default to the current teaming.
	try {
		$teamingQuery = $db->prepare("SELECT * FROM teaming WHERE area = :area");
		$teamingQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$date = $teamingQuery->fetch(PDO::FETCH_ASSOC);
	$startDate = $date['startDate'];
	$endDate = $date['endDate'];
	if(isset($_POST['submit'])){
		try {
			$teaming2Query = $db->prepare("SELECT * FROM teaming WHERE area = :area");
			$teaming2Query->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($cur = $teamingQuery->fetch(PDO::FETCH_ASSOC)) {
			if(isset($_POST['teamed'][$cur['netID']])) {
				try {
					$updateQuery = $db->prepare("UPDATE teaming SET teamed='1' WHERE netID=:netId AND `startDate`=:start AND `endDate`=:end AND `area`=:area");
					$updateQuery->execute(array(':netId' => $cur['netID'], ':start' => $startDate, ':end' => $endDate, ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
			} else {
				try {
					$updateQuery = $db->prepare("UPDATE teaming SET teamed='0' WHERE netID=:netId AND `startDate`=:start AND `endDate`=:end AND `area`=:area");
					$updateQuery->execute(array(':netId' => $cur['netID'], ':start' => $startDate, ':end' => $endDate, ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
			// Set the timely
			if(isset($_POST['timely'][$cur['netID']])){
				try {
					$updateQuery = $db->prepare("UPDATE teaming SET timely='1' WHERE netID=:netId AND `startDate`=:start AND `endDate`=:end AND `area`=:area");
					$updateQuery->execute(array(':netId' => $cur['netID'], ':start' => $startDate, ':end' => $endDate, ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
			} else {
				try {
					$updateQuery = $db->prepare("UPDATE teaming SET timely='0' WHERE netID=:netId AND `startDate`=:start AND `endDate`=:end AND `area`=:area");
					$updateQuery->execute(array(':netId' => $cur['netID'], ':start' => $startDate, ':end' => $endDate, ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
	}

?>
<script language="JavaScript" src="/includes/libs/tcal.js"></script>
<link rel="stylesheet" type="text/css" href="/includes/libs/tcal.css" />

<script type='text/javascript'>

window.onload = function()
{
	$( "input:text.datepicker" ).datepicker({dateFormat:"yy-mm-dd"}); 
	getPeriods();
}
var area = "<?php echo $area; ?>";

function getPeriods()
{
	$.ajax({
		url: 'selectPeriods.php',
		type: 'GET',
		data: {area: area},
		success: function(periods){
			populatePeriodSelect(periods);
		}
	});
}

function populatePeriodSelect(periods)
{
	periods.forEach(function(period)
	{
	$('#prevPeriodSelect').append("<option value='"+period.startDate+","+period.endDate+"'>"+period.dateRangeHumanReadable+"</option>");
	});
}
	
function addNewTeamingPeriod()
{
	$("#teamingPeriod").dialog({
			title: "Add New Period",
			resizable: false,
			width: 350,
			modal: true,
			draggable: true,
			buttons: [{ text: "Submit", click: function() { createNewTeamingPeriod(); } }]
		});
}

function createNewTeamingPeriod()
{
	$("#newPeriodForm").submit();
}

function editPrevTeamingPeriod()
{
	$("#editPreviousPeriod").dialog({
			resizable: false,
			width: 350,
			modal: true,
			draggable: true,
			buttons: [{ text: "Okay", click: function() { selectPreviousTeamingPeriod(); } }]
		});
}

function selectPreviousTeamingPeriod()
{
   $("#editPreviousPeriodForm").submit();
}

function current(){location.reload(true); }
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

</style>
<br />
<h1 class="center">Training Summary </h1><br />
<h3 class="center">Training Period: <?php echo date("F j, Y",strtotime($startDate)).' '.' - '.date("F j, Y",strtotime($endDate));?></h3><br />
<div class='currentTeaming'>
<h2>Training For Period</h2>
<form id='teaming' name='teaming' method='post'>

<?php

printAllCurrentTeaming($area);


if(can("update", "28e60394-f719-4225-85ad-fa542ab6a8df"))//teams resource
{
	echo "<input type='submit' id='submit' name='submit' value='Submit Changes' />";
}
else
{
	echo "<h4>--This page is in read only mode--<br/>--No changes will be saved--</h4>";
}
?>
</form>
</div>


<div class='teamingStats'>
<h2>Training Stats (Trained : Timely)</h2>
<table>
<tr>
	<th>Training Team Leader</th><th>% Last Period</th><th>% This Month</th><th>% This Semester</th><th>% Total</th>
</tr>
<?php printTeamingStats($area); ?>
</table>
</div>
<?php if(can("update", "28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource*/{?>
	<div>
	<h2>Training Management</h2>
	<a href="javascript:void" onClick="addNewTeamingPeriod();">Create New Training Period</a><br /><br />
	<a href="#" onClick="editPrevTeamingPeriod();">Edit Previous Training Period</a><br />
	</div>
<?php } ?>
<!--New teaming period dialog form-->
<div id='teamingPeriod' style='display:none;'>
<form id='newPeriodForm' method='post' action='createNewPeriod.php'>
	<h2>New Training Period</h2>
	<table>
	<tr>
		<th>Start Date</th><th>End Date</th>
	</tr><tr>
		<td><input type='text' name='periodStartDate' id='periodStartDate' class='datepicker' size='10' value="<?php echo date('Y-m-d'); ?>" /></td>
		<td><input type='text' name='periodEndDate' id='periodEndDate' class='datepicker' size='10' PLACEHOLDER='YYYY-MM-DD' value="<?php echo date('Y-m-d',strtotime('+6 days')); ?>" /></td>	
	</tr>
	</table>
	<input type='hidden' name = 'lastEndDateOnTeaming' value="<?php echo $endDate; ?>"/>
</form>
</div>
<!--Edit previous period dialog form-->
<div id='editPreviousPeriod' style='display:none;' >
<h2>Select a training period</h2>

<form id="editPreviousPeriodForm" method="post" action="editPrevPeriods.php">

	<select id='prevPeriodSelect' name='prevPeriodSelect'>
		<option id='DEFAULT'>Please select a period...</option>
	</select>

</form>

</div>
<?php
require('../includes/includeAtEnd.php');
?>
