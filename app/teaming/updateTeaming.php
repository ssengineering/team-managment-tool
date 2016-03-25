<?php //updateTeaming.php This allows a team leader to update teaming for his team
require('../includes/includeme.php');
require('teamingFunctions.php');

if(!can("lead", "28e60394-f719-4225-85ad-fa542ab6a8df"))//teams resource
{
	echo "<h2>You are not authorized to view this page. Contact your supervisor if you believe you reached this in error.</h2>";
	require('../includes/includeAtEnd.php');
	return;
}

if(isset($_POST['submit'])){
	try {
		$supervisorQuery = $db->prepare("SELECT * FROM `teaming` JOIN `employee` ON `teaming`.`netID`=`employee`.`netID` WHERE `teaming`.`supervisorID` = :netId ORDER BY `employee`.`firstName`");
		$supervisorQuery->execute(array(':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $supervisorQuery->fetch(PDO::FETCH_ASSOC)) {
		if(isset($_POST['teamed'][$cur['netID']])){
			try {
				$updateQuery = $db->prepare("UPDATE teaming SET teamed='1' , timely='1' WHERE netID = :netId");
				$updateQuery->execute(array(':netId' => $cur['netID']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		} else {
			try {
				$updateQuery = $db->prepare("UPDATE teaming SET teamed='0' , timely='0' WHERE netID = :netId");
				$updateQuery->execute(array(':netId' => $cur['netID']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
}

?>

<style type="text/css">
.center
{
	margin:auto;
	width:100%;
	text-align:center;
}
.floatLeft
{
	float: left;
	position: relative;
	left: 3%;
}
.floatRight
{
	float: right;
	position: relative;
	width:500px;
}
</style> 
<br />
<h1 class="center">Update Training</h1><br />
<h3 class="center">Training Period: <?php echo date("F j, Y",strtotime(getTeamingPeriod('start', $area))).' '.' - '.date("F j, Y",strtotime(getTeamingPeriod('end', $area))); ?></h3>
<br /><br />
<div class='floatLeft'>
	<h2>Update Current Training Team</h2>
	<form name='teaming' id='teaming' method='post' >
	<?php printTeamForUpdate($netID, $area); ?>
	<input type='submit' value="Submit Update" name='submit' id='submit' />
	</form>
</div>
<div class='floatRight'>
<h2>Training Stats for <?php echo nameByNetId($netID); ?> (Trained : Timely)</h2>
<table>
<tr>
	<th>% Last Period</th><th>% This month</th><th>% This Semester</th><th>% Total</th>
</tr><tr>
<?php
echo "<td>";
echo calculateLastPeriodPercent($netID);
echo "</td><td>";
echo calculateCurrentMonthPercent($netID);
echo "</td><td>";
echo calculateCurrentPeriodPercent($area, $netID);
echo "</td><td>";
echo calculateTotalPercent($netID);
echo "</td>";
?>
</tr>
</table>
</div>
<div class='floatRight'>
<h2>Training Stats for all Team Leaders (Trained : Timely) </h2>
<table>
<tr>
	<th>% Last Period</th><th>% This month</th><th>% This Semester</th><th>% Total</th>
</tr><tr>
<?php
echo "<td>";
echo calculateLastPeriodPercentAllTeams($area);
echo "</td><td>";
echo calculateCurrentMonthPercentAllTeams($area);
echo "</td><td>";
echo calculateCurrentPeriodPercentAllTeams($area);
echo "</td><td>";
echo calculateTotalPercentAllTeams($area);
echo "</td>";
?>
</tr>
</table>
</div>

<?php
require('../includes/includeAtEnd.php');
?>
