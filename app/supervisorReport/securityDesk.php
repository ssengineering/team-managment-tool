<?php //index.php this is the index for the supervisor report for the SD and COS areas
require('../includes/includeme.php');
// Emails for this report are sent by the sendSecurityEmail.php file which is executed by a cron job. 

//*************************
//add permission check here
if($area!=6){
	echo "<h2>You are not authorized to view this page. Contact your supervisor if you believe this is in error.</h2>";
	require('../includes/includeAtEnd.php');
	return;
}
//***********************

//if the submit button is clicked
if(isset($_POST['submit'])){
	
	//gather the data from the 3 text areas, time and date
	$first = trim($_POST['first']);
        if ($first == '')
        {
                $first = 'None to report.';
        }
        $second = trim($_POST['second']);
        if ($second == '')
        {
                $second = 'None to report.';
        }
        $third = trim($_POST['third']);
        if ($third == '')
        {
                $third = 'None to report.';
        }
	$start = $_POST['start'];
	$end = $_POST['end'];
	$reportDate = $_POST['reportDate'];
	$employeeName = $_POST['employeeName'];
	$employeeEmail = $_POST['employeeEmail'];
	//insert them into the database.
	try {
		$insertQuery = $db->prepare("INSERT INTO supervisorReportSecurityDesk (date,submitter,startTime,endTime,area,securityProblems,shiftProblems,misc,guid) VALUES (:reportDate,:netId,:start,:end,:area,:first,:second,:third,:guid)");
		$success = $insertQuery->execute(array(':reportDate' => $reportDate, ':netId' => $netID, ':start' => $start, ':end' => $end, ':area' => $area, ':first' => $first, ':second' => $second, ':third' => $third, ':guid' => newGuid()));
	} catch(PDOException $e) {
		$success = false;
	}
	
	if($success)
	{
		echo '<script type="text/javascript" >alert("Report submitted successfully");</script>';	
	}
	else
	{
		echo '<script type="text/javascript" >alert("Could not submit report.  Please try again. If error persists contact the website administrator.");</script>';
	}
}
?>

<script language="JavaScript" src="/includes/libs/tcal.js"></script>
<link rel="stylesheet" type="text/css" href="/includes/libs/tcal.css" />
<script type='text/javascript' >
	var submitted = 0;
	
	window.onload = function() {
	
		$('#start').timeEntry({useMouseWheel: true});
		$('#end').timeEntry({useMouseWheel: true});
		
		window.onbeforeunload = confirmLeave;

	}

	function submitReportConfirmation()
	{
		submitted=1;
		
		var res = confirm("Do you really want to submit your report?");
		if(res)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	function confirmLeave() {
		if(!submitted){
			return "Have You Submitted Your Report?";
		}
	}
	
	function newwindow(urlpass) {
		window.open(urlpass,"","status=1,width=600,height=700,scrollbars=1");
	}
	
</script>
<form id='reportData' method='post'>
<input type="hidden" id="employeeName" name="employeeName" value="<?php echo nameByNetId($netID); ?>">
<input type="hidden" id="employeeEmail" name="employeeEmail" value="<?php echo getEmployeeEmailByNetId($netID); ?>">
<div id='headInfo'>
<h1>Security Desk Report Form</h1>
<table>
	<tr>
	<th>NAME: <?php echo nameByNetId($netID); ?></th>
	<th colspan='2'>EMAIL: <?php echo getEmployeeEmailByNetId($netID); ?></th><th>Report Finished?</th>
	</tr><tr>
	<td>DATE: <input type='text' class='tcal' size='10' id='reportDate' name='reportDate' value="<?php echo date('Y-m-d',strtotime("today")); ?>" /></td>
	<td>Start Time: <input type="text" name="start" id='start' maxlength=5 size=8 value="<?php echo date('h:iA'); ?>"/></td>
	<td>End Time: <input type="text" name="end" id='end' maxlength=5 size=8 value="<?php echo date('h:iA'); ?>"/></td>
	<td><input type='submit' name='submit' id='submit' value="Submit Report" onClick="submitReportConfirmation();"/></td>
	</tr>
</table>
</div>
<div id='instructions'>
<br/>

<br/>
1. Include ALL details: TIME, names, situations,etc.<br/>
2. Keep this form open throught your shift and record things as they occur.<br/><br/>

</div>
<br/>

<div id='textFields'>
<table>
	<tr>
	<th>SECURITY PROBLEMS: </th>
	</tr><tr>
	<td><textarea id='first' name='first' cols='100' rows='4'> </textarea></td>
	</tr><tr>
	<th>SHIFT PROBLEMS: </th>
	</tr><tr>
	<td><textarea id='second' name='second' cols='100' rows='4'> </textarea></td>
	</tr><tr>
	<th>MISCELLANEOUS INFORMATION: </th>
	</tr><tr>
	<td><textarea id='third' name='third' cols='100' rows='4'> </textarea></td>
	</tr>
</table>
</div>
<br/>

</form>
<div id='itemEditor' style='display:none;'>
	<h2>Add/Edit Item</h2>
	<table>
	<tr>
		<th>Text</th><td><input type='text' id='itemText' /></td>
	</tr>
	</table>
</div>
<?php
require('../includes/includeAtEnd.php');
?>
