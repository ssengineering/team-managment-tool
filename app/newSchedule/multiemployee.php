<?php
require ('../includes/includeme.php');
if (isset($_GET['employee']))
	$netId=$_GET['employee'];
else
	$netId=$netID;
if (isset($_GET['date']))
	$dateViewed=$_GET['date'];
else
	$dateViewed=date('Y-m-d');
if (isset($_GET['viewMode']))
	$viewMode=$_GET['viewMode'];
else
	$viewMode='trade';
if (isset($_GET['weeklyDefault']))
	$weeklyDefault=$_GET['weeklyDefault'];
else
	$weeklyDefault='weekly';
?>

<link rel="stylesheet" type="text/css" href="./index.css" />
<style>
	#instructions
	{
		text-align: center;
		font-weight: bold;
		font-size: 1.1em;
		width: 75%;
		margin: auto;
	}
	#controls
	{
		position: fixed;
		bottom: 7px;
		left: 7px;
		opacity: .5;
		background-color: lightgrey;
		padding: .25em;
		border: silver 1px solid;
		border-radius: .8em;
	}
	#controls:hover, #controls:active, #controls:focus
	{
		opacity: 1;
		border: darkgray 1px solid;
	}
	#controlsTitle
	{
		margin: .25em;
		font-weight: bold;
		float: left;
	}
	#viewMode
	{
		float: right !important;
		margin: auto 4px .1em 4px !important;
		font-size: .6em !important;
	}
	#weeklyDefault
	{
		float: right !important;
		margin: auto 4px .1em 4px !important;
		font-size: .6em !important;
	}
	.ui-icon-gear
	{
		margin-top: -9px !important;
		margin-left: -9px !important;
	}
	#employeeGear
	{
		float: right;
		margin: auto 4px .1em 4px;
		width: 1.25em;
		height: 1.25em;
	}
	#timePickerDiv
	{
		width: 202px !important;
		margin: .1em 4px auto 4px !important;
	}
	#now
	{
		margin: auto 0px auto 4px !important;
	}
	#period
	{
		width: 80% !important;
	}
	#shiftTypeSelector
	{
		float: left !important;
		margin: .1em 4px auto 4px !important;
		width: 180px;
	}
	#superSubmitDiv
	{
		font-size: 0.75em;
		float: left;
		margin: .1em 4px auto 4px;
		width: 48px;
	}
</style>
<input id="user" type="hidden" value="<?php echo $netID; ?>" />
<h1 id="multiScheduleTitle" style="text-align: center;"> Multi-employee Schedule </h1>

<div id="instructions">
	<p>
		<span style="font-size: 1.25em;">Instructions:</span> In order to use the Multi-employee Schedule just select the employees' schedules you would like.
		Controls for interacting with all selected employees' schedules are in the bottom left corner.
		The button with a gear icon will open the Employee Selector popup in case you close it.
	</p>
</div>

<div id="viewMode">
	<input type="radio" id="trade" name="view" value="0" onClick="changeView(this);" <?php
	if ($viewMode == 'trade')
		echo 'checked';
	?>>
	<label for="trade">Trade</label>
	<input type="radio" id="edit" name="view" value="1" onClick="changeView(this);" <?php
	if ($viewMode == 'edit')
		echo 'checked';
	?>>
	<label for="edit">Edit</label>
</div>

<div id="weeklyDefault">
	<input type="radio" id="weekly" name="default" value="0" onClick="changeMode(this);" <?php
	if ($weeklyDefault == 'weekly')
		echo 'checked';
	?>>
	<label for="weekly">Weekly</label>
	<input type="radio" id="default" name="default" value="1" onClick="changeMode(this);" <?php
	if ($weeklyDefault == 'default')
		echo 'checked';
	?>>
	<label for="default">Default</label>
</div>
<div class="clearMe"></div>
<div id="employeeSelectors" style="display: none; width: auto; height: auto;">
	<?php
	$content="<table style='width: 100%; height: 100%; text-align: center; margin: 0px !important;'>";
	
	// Add the Team Selector
	$content.="<tr><th style='height: 1em; text-align: center;' class='employees'>Teams</th></tr>";
	
	try {
		$teamsQuery = $db->prepare("SELECT * FROM `teams` WHERE `area` = :area ORDER BY `isShift`");
		$teamsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	$content.="<tr><td style='height: 1.5em;'><select id='teams' class='selectable employees' style='width: 100%; height: 100%;' onchange='teamSelect(this)'>";
	$content.="<option>None</option>";
	while ( $team = $teamsQuery->fetch(PDO::FETCH_ASSOC) )
	{
		$content.="<option value='{$team['ID']}'>{$team['name']}</option>";
	}
	$content.="</select></td></tr>";
	
	// Add the employee selector
	$content.="<tr><th style='height: 1em; text-align: center;' class='employees'>Employees</th></tr>";

	// This query now handles cross area employees
	require_once $_SERVER['DOCUMENT_ROOT']."/includes/heimdall.php";
	$employeeQueryString = "CALL getScheduleList(:area, :netId)";
	$employeeQueryParams = array(':area' => $area, ':netId' => $netId);
	try {
		$employeeQuery = $db->prepare($employeeQueryString);
		$employeeQuery->execute($employeeQueryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}

	$content.="<tr><td><select id='employees' multiple='multiple' class='selectable employees' style='width: 100%; height: 100%;' onchange='employeeList(this)'>";
	$content.="<option>All</option>";
	//Declare array to store the employees, so that we can later sort them.
	$employees = array();
	while ($row=$employeeQuery->fetch(PDO::FETCH_ASSOC)){
		$employees[] = $row;
	}
	//The function for comparison used by the PHP method usort (can see documentation online). This will sort
	//all of the the members fo the array alphabetically by the field "firstName".
	function compareFirstName($employeeA, $employeeB){
		return strcmp($employeeA['firstName'], $employeeB['firstName']);
	}
	usort($employees, "compareFirstName");	
	//Uses a foreach loop to cycle through "employees" and store the content as one long string.
	foreach($employees as $employee){
	// Check if area is the default area, if not add a star to the name
	$notDefaulted='';
	if ($employee['area'] != $area){
			$notDefaulted='*';
	}
	//Print the content with the right "notDefaulted" setting
	$content .= "<option value='".$employee['netID']."'>".$employee['firstName']." ".$employee['lastName'].$notDefaulted."</option>";
	
	}
	$content .="</select></td></tr></table>";
	echo $content;
	
?>
</div>
<div class="clearMe"></div>
<div id="iframeContainer" style="width: 100%; height: auto;"></div>
<script src="multiemployee.js"></script>
<script type="text/javascript">window.onload = employeeScheduleOnload;</script>

<?php
require ('../includes/includeAtEnd.php');
?>
