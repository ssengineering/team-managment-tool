<?php

require('../includes/includeMeBlank.php');

if(!can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))//supervisorDashboard resource
{
	echo "<h2>You are not authorized to view this page. Contact your supervisor if you believe this is in error.</h2>";
	require('../includes/includeAtEnd.php');
	return;
}

$action = $_GET['type'];

if($action == 'save')
{	
	$date = date('Y-m-d',strtotime("today"));
	$first = $_GET['first'];
	$second = $_GET['second'];
	$third = $_GET['third'];
	$fourth = $_GET['fourth'];
	$startTime = $_GET['startTime'];
	$endTime = $_GET['endTime'];
	$openingList = $_GET['openingList'];
	$closingList = $_GET['closingList'];
	$checklistNumber = $_GET['checklistNumber'];
	$checklistItem = $_GET['checklistItem'];

	try {
		$insertQuery = $db->prepare("INSERT INTO `supervisorReportDraft`(`date`, `submitter`, `startTime`, `endTime`, `area`, `outages`, `problems`, `misc`, `supTasks`, `guid`) VALUES (:day,:netId,:start,:end,:area,:first,:second,:third,:fourth,:guid)");
		$insertQuery->execute(array(':day' => $date, ':netId' => $netID, ':start' => $startTime, ':end' => $endTime, ':area' => $area, ':first' => $first, ':second' => $second, ':third' => $third, ':fourth' => $fourth, ':guid' => newGuid()));
		$draftQuery = $db->prepare('SELECT `ID` FROM `supervisorReportDraft` ORDER BY `ID` DESC');
		$draftQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}

	$newDraft = $draftQuery->fetch(PDO::FETCH_ASSOC);

	for($i = 0; $i < count($checklistItem); $i++)
	{
		try {
			$insert2Query = $db->prepare("INSERT INTO `supervisorReportDraftTask`(`draftID`, `name`, `guid`) VALUES (:draft,:item,:guid)");
			$insert2Query->execute(array(':draft' => $newDraft['ID'], ':item' => $checklistItem[$i], ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
else if($action == 'delete')
{
	$draftID = $_GET['id'];
	try {
		$deleteQuery = $db->prepare("DELETE FROM `supervisorReportDraft` WHERE `ID` = :id");
		$deleteQuery->execute(array(':id' => $draftID));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
else if($action == 'load')
{
	$draftID = $_GET['id'];
	try {
		$draft2Query = $db->prepare("SELECT * FROM `supervisorReportDraft` WHERE `ID` = :id");
		$draft2Query->execute(array(':id' => $draftID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$row = $draft2Query->fetch(PDO::FETCH_ASSOC);
	
	echo "<form id='reportData' method='post' onsubmit='return confirmSubmission()'>
<input type='hidden' id='employeeName' name='employeeName' value='".nameByNetId($row['submitter'])."'>
<input type='hidden' id='employeeEmail' name='employeeEmail' value='".getEmployeeEmailByNetId($row['submitter'])."'>
<div id='headInfo'>
<h1>Supervisor Report Form</h1>
<table>
	<tr>
	<th>NAME: ".nameByNetId($row['submitter'])."</th>
	<th colspan='2'>EMAIL: ".getEmployeeEmailByNetId($row['submitter'])."</th><th>Load Draft</th><th>Save as Draft</th><th>Report Finished?</th>
	</tr><tr>
	<td>DATE: <input type='text' class='tcal' size='10' id='reportDate' name='reportDate' value='".$row['date']."' /></td>
	<td>Start Time: <input type='text' name='start' id='start' maxlength=5 size=8 value='".$row['startTime']."'/></td>
	<td>End Time: <input type='text' name='end' id='end' maxlength=5 size=8 value='".$row['endTime']."'/></td>
	<td><input type='button' name='load' id='load' value='Load Draft' onClick='loadDraftDialog()'/></td>
	<td><input type='button' name='save' id='save' value='Save Draft' onClick='saveDraft()' /></td>
	<td><input type='submit' name='submit' id='submit' value='Submit Report'/></td>
	</tr>
</table>
</div>
<div id='instructions'>
<br/>
<b>Report Instructions: </b>Click <a href='reportInstructions.php'>here</a> for detailed instructions.
<br/>
<br/>
1. Include ALL details: TIME, names, situations,etc.<br/>
2. Keep this form open throught your shift and record things as they occur.<br/><br/>
Use these buttons to enter in activites as they occur.<br/>
<input type='button' id='absence' name='absence' value='Absence' onclick='newwindow('../performance/absence.php')' />
<input type='button' id='tardy' name='tardy' value='Tardy' onclick='newwindow('../performance/tardy.php')' />
<input type='button' id='reminder' name='reminder' value='Policy Reminder' onclick='newwindow('../performance/policyReminder.php')' />
<input type='button' id='commendable' name='commendable' value='Commendable Performance' onclick='newwindow('../performance/commendablePerformance.php')' />
</div>
<br/>
<div id='openResults'>
</div>
<br/>
<div id='textFields'>
<table>
	<tr>
	<th>PRODUCT/SERVICE OUTAGES EXPERIENCED AND MESSAGE RELAYS RECEIVED:<br/> Include any service outage affecting our customers. (ie: problems with Route Y, servers, IP Phones, AIM, etc.)</th>
	</tr><tr>
	<td><textarea id='first' name='first' cols='100' rows='4'>".$row['outages']."</textarea></td>
	</tr><tr>
	<th>SHIFT PROBLEMS: <br/>Include high queue loads and possible reasons why. (ie: building evacuations, fire alarms, power outages, etc.)</th>
	</tr><tr>
	<td><textarea id='second' name='second' cols='100' rows='4'>".$row['problems']."</textarea></td>
	</tr><tr>
	<th>MISCELLANEOUS INFORMATION: <br/>Include anything you want to bring to our attention. (ie: problems with consoles, applications, or our office computers, alarms, etc.)</th>
	</tr><tr>
	<td><textarea id='third' name='third' cols='100' rows='4'>".$row['misc']."</textarea></td>
	</tr><tr>
	<th>SUPERVISING TASKS:<br/>Include what you did while supervising. (ie: teaming, projects, mentoring, reviewing tickets, etc.)</th>
	</tr><tr>
	<td><textarea id='fourth' name='fourth' cols='100' rows='4'>".$row['supTasks']."</textarea></td>
	</tr>
</table>
</div>
<br/>
<div id='closeResults'>
</div>
<input type='hidden' id='checkList' name='checkList' value=''>
</form>";
}
else if($action == 'checkList')
{
	$list = $_GET['list'];
	$draftID = $_GET['id'];
	
	if($list == 0)
	{
		echo "<table id='openList'>
		<tr>
		<th>Opening Office Checklist<br/><input type='checkbox' id='openingList' name='openingList' /><label for='opening' >Please check if this is an opening shift</label></th><td>";
		if(can("update", "7db1df8d-0a15-46ed-9c83-701393e9596c"))//reportInstructions resource
		{
			echo "<input type='button' value=\"Add Item\" onclick='addItem(0)' />";
		}
		echo "<input type='button' value=\"Reload\" onclick='printList(0,\"openResults\")' /></td></tr>";
	}
	else
	{
		echo "<table id='closeList'>
		<tr>
		<th>Closing Office Checklist<br/><input type='checkbox' id='closingList' name='closingList' /><label for='closing' >Please check if this is a closing shift</label></th><td>";
		if(can("update", "7db1df8d-0a15-46ed-9c83-701393e9596c"))//reportInstructions resource
		{
			echo "<input type='button' value=\"Add Item\" onclick='addItem(1)' />";
		}
		echo"<input type='button' value=\"Reload\" onclick='printList(1,\"closeResults\")' /></td></tr>";
	}
	
	try {
		$tasksQuery = $db->prepare("SELECT * FROM supervisorReportSDTasks WHERE checklist = :list and area = :area ORDER BY `order` ASC");
		$tasksQuery->execute(array(':list' => $list, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	while($cur = $tasksQuery->fetch(PDO::FETCH_ASSOC))
	{
		try {
			$taskDraftQuery = $db->prepare("SELECT * FROM `supervisorReportDraftTask` WHERE `draftID` = :draft");
			$taskDraftQuery->execute(array(':draft' => $draftID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$success = false;
		
		echo "<tr id='row".$cur['ID']."'><td>";
		
		while($draft = $taskDraftQuery->fetch(PDO::FETCH_ASSOC))
		{
			if('task'.$cur['ID'] == $draft['name'])
			{
				echo "<input onclick='fadeRow(\"row".$cur['ID']."\")' type='checkbox' id='task".$cur['ID']."' name='task".$cur['ID']."' class='task' value = '".$cur['text']."' checked>";
				
				$success = true;
			}
		}
		
		if($success == false)
		{
			echo "<input onclick='fadeRow(\"row".$cur['ID']."\")' type='checkbox' id='task".$cur['ID']."' name='task".$cur['ID']."' class='task' value = '".$cur['text']."' >";
		}
		
		echo "<label for='".$cur['ID']."' id='label".$cur['ID']."'> ".$cur['text']."</label></td><td>";
		if(can("update", "7db1df8d-0a15-46ed-9c83-701393e9596c"))//reportInstructions resource
		{
			echo "<input type='button' value='Edit' onclick='editItem(\"".$cur['ID']."\")' /><input type='button' value='Delete' onclick='deleteItem(\"".$cur['ID']."\")' />";
		}
		echo "</td></tr>";
	}
}
?>
