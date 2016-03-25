<?php //index.php this is the index for the supervisor report for the SD and COS areas
require('../includes/includeme.php');

//*************************
//add permission check here
if(!can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource*/{
	echo "<h2>You are not authorized to view this page. Contact your supervisor if you believe this is in error.</h2>";
	require('../includes/includeAtEnd.php');
	return;
}
//***********************

$success = 0;
//if the submit button is clicked
if(isset($_POST['submit'])){
	//gather the data from the 4 text areas, time and date
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
	$fourth = trim($_POST['fourth']);
	if ($fourth == '')
    {
        $fourth = 'None to report.';
    }
	$checkList = $_POST['checkList'];
	$start = $_POST['start'];
	$end = $_POST['end'];
	$reportDate = $_POST['reportDate'];
	$employeeName = $_POST['employeeName'];
	$employeeEmail = $_POST['employeeEmail'];
	//insert them into the database.
	try {
		$insertQuery = $db->prepare("INSERT INTO supervisorReportSD (date,submitter,startTime,endTime,area,outages,problems,misc,supTasks,guid) VALUES (:reportDate,:netId,:start,:end,:area,:first,:second,:third,:fourth,:guid)");
		$insertQuery->execute(array(
			':reportDate' => $reportDate,
			':netId'      => $netID,
			':start'      => $start,
			':end'        => $end,
			':area'       => $area,
			':first'      => $first,
			':second'     => $second,
			':third'      => $third,
			':fourth'     => $fourth,
			':guid'       => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	if(isset($_POST['openingList'])){
		echo "<br/>Opening List<br/>";
		try {
			$tasksQuery = $db->prepare("SELECT `ID`,`text` FROM supervisorReportSDTasks WHERE area= :area AND checklist = '0'");
			$tasksQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($cur = $tasksQuery->fetch(PDO::FETCH_ASSOC)) {
			if(!isset($_POST['task' . $cur['ID']])){
				echo "<br/>Not Complete: ".$cur['text']."<br/>";
			}
		}
	}
	if(isset($_POST['closingList'])){
		echo "<br/>Closing List<br/>";
		try {
			$tasksQuery = $db->prepare("SELECT `ID`,`text` FROM supervisorReportSDTasks WHERE area= :area AND checklist = '1'");
			$tasksQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($cur = $tasksQuery->fetch(PDO::FETCH_ASSOC)) {
			if(!isset($_POST['task' . $cur['ID']])){
				echo "<br/>Not Complete: ".$cur['text']."<br/>";
			}
		}
	}
	sendEmail($employeeName,$employeeEmail,$reportDate,$start,$end,$first,$second,$third,$fourth,$checkList);
}
?>

<?php

function sendEmail($employeeName,$employeeEmail,$reportDate,$start,$end,$first,$second,$third,$fourth,$checkList){
	global $area;
	global $env;
	
	$subject = 'Supervisor Report';
	$emailBody = '
<div id="headInfo">
<table>
<tr>
	<th style="text-align: left;">NAME:</th><td>'.$employeeName.'</td>
</tr>
<tr>
	<th style="text-align: left;">EMAIL:</th><td>'.$employeeEmail.'</td>
</tr>
<tr>
	<th style="text-align: left;">DATE:</th><td>'.$reportDate.'</td>
</tr>
<tr>
	<th style="text-align: left;">Start Time:</th><td>'.$start.'</td>
</tr>
<tr>
	<th style="text-align: left;">End Time:</th><td>'.$end.'</td>
</tr>
</table>
</div>
<br/>
<br/>
<table>
'.$checkList.'
</table>
<table>
	<tr>
		<th style="text-align: left;">PRODUCT/SERVICE OUTAGES EXPERIENCED AND MESSAGE RELAYS RECEIVED:</th>
	</tr>
	<tr>
		<td>'.$first.'</td>
	</tr>
	<tr>
		<th style="text-align: left;">SHIFT PROBLEMS:</th>
	</tr>
	<tr>
		<td>'.$second.'</td>
	</tr>
	<tr>
		<th style="text-align: left;">MISCELLANEOUS INFORMATION:</th>
	</tr>
	<tr>
		<td>'.$third.'</td>
	</tr>
	<tr>
		<th style="text-align: left;">SUPERVISING TASKS:</th>
	</tr>
	<tr>
		<td>'.$fourth.'</td>
	</tr>
</table>
</div>';
	
	if($env == 2)
	{
		if($area == 3){//Service Desk
			$to = getenv("SD_ALIAS");
			$subject = 'SD Supervisor Report - '.date('G:i D. M. jS, Y');
		}else if($area == 4){//COS
			$subject = 'COS Supervisor Report - '.date('G:i D. M. jS, Y');
			$to = getenv("COS_ALIAS");
		}
		else if ($area == 6)
		{
			// Security Desk
			$to = getenv("SECURITY_DESK_EMAILS");
		}
	}
	else
	{
		$to = getenv("DEV_EMAIL_ADDRESS");
		if($area == 3){//Service Desk
			$subject = 'SD Supervisor Report - '.date('G:i D. M. jS, Y');
		}else if($area == 4){//COS
			$subject = 'COS Supervisor Report - '.date('G:i D. M. jS, Y');
		}
		
	}
	
	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$employeeName.' <'.$employeeEmail.'>' . "\r\n";
	$headers .= 'Return-Path: '.$employeeEmail . "\r\n";

	if(mail($to,$subject,$emailBody,$headers))
	{
		global $success;		
		$success = 1;	
	}
	else
	{
		echo '<h1>Email failed to be sent.</h1>';
	}
}

?>
<script language="JavaScript" src="/includes/libs/tcal.js"></script>
<link rel="stylesheet" type="text/css" href="/includes/libs/tcal.css" />
<div id="redirect"></div>
<script type='text/javascript' >
	 var submitted = 0;
	 var success ="<?php echo $success; ?>";

	 if (success=="1")
	 {
		submitted = 1;
		window.location = "submissionConfirmation.php";
		
	 }
	window.onload = function() {
	
		printList(0,"openResults"); 
		printList(1,"closeResults");
		$('#start').timeEntry({useMouseWheel: true});
		$('#end').timeEntry({useMouseWheel: true});
		$( "input:text.datepicker" ).datepicker({dateFormat:"yy-mm-dd"});
		$('#loadDraftForm').dialog
		({
			title: 'Load Draft',
			width: 500,
			autoOpen: false,
			modal: true,
			buttons:
			{
				Load: function()
				{
					var draftID = $('[type="radio"]:checked').val();
					
					var page = 'draft.php?type=load&id=' + draftID; 
					var cb = function(result){ $('#reportData').replaceWith(result) };
					var confirmDelete = confirm("Are you sure you want to load this? The current form will be lost.");
					if(confirmDelete == true)
					{
						callPhpPage(page,cb);
					}
					
					printList(0,"openResults"); 
					printList(1,"closeResults");
					
					var page = 'draft.php?type=checkList&list=0&id=' + draftID; 
					var cb = function(result){ $('#openResults').replaceWith(result) };
					callPhpPage(page,cb);
					
					var page = 'draft.php?type=checkList&list=1&id=' + draftID; 
					var cb = function(result){ $('#closeResults').replaceWith(result) };
					callPhpPage(page,cb);
					
					$(this).dialog( "close" );
				},
				
				Cancel: function()
				{
					$(this).dialog( "close" );
				}
			}
		});
		
		window.onbeforeunload = confirmLeave;

	}
	
	function listUnchecked()
	{
		submitted=1;
		
		var unchecked = "";
		
		if($('#openingList').prop('checked'))
		{
		    unchecked += "<tr><th style='text-align: left;'>Opening Office Missed Tasks:</th></tr>";
		    $('#openList').children('tbody').children('tr').children('td').children('[type=checkbox]:not(:checked)').each(function(index) {unchecked += "<tr><td>" + $(this).val() + "</td></tr>" });
		}
		
		if($('#closingList').prop('checked'))
		{
		    unchecked += "<br /><tr><th style='text-align: left;'>Closing Office Missed Tasks:</th></tr>";
		    $('#closeList').children('tbody').children('tr').children('td').children('[type=checkbox]:not(:checked)').each(function(index) {unchecked += "<tr><td>" + $(this).val() + "</td></tr>" });
		}
		
		unchecked += "<br/>";
		$("#checkList").val(unchecked);
	}
	
	function confirmLeave() {
		if(!submitted){
			return "Have You Submitted Your Report?";
		}
		
	}
	
	function confirmSubmission()
	{
		var r = confirm("Are you sure you want to submit your report?");
		if(r == true){
			listUnchecked();
			return true;
		}
		else
		{
			return false; 
		}
	}
	
	function newwindow(urlpass) {
		window.open(urlpass,"","status=1,width=600,height=700,scrollbars=1");
	}

	function printList(list,div){
		var page = "checklistAjax/printList.php?type="+list;
		
		var cb = function(result){ document.getElementById(div).innerHTML = result; };
		callPhpPage(page,cb);
		
	}

	function addItem(type){
		document.getElementById("itemText").value = "";
		$( "#itemEditor" ).dialog({
			resizable: false,
			width: 350,
			modal: true,
			draggable: true,
			buttons: [{ text: "Add Item", click: function() { add(type); } }]
		});
	}

	function add(type){
		$("#itemEditor").dialog("close");
		var name = document.getElementById("itemText").value;
		var page = "checklistAjax/addItem.php?text="+name+"&list="+type;
		if(type == 0){		
			var cb = function(result){ 
					$('#openList').append(result);
					};
		} else {
			var cb = function(result){ 
					$('#closeList').append(result);
					};
		}

		callPhpPage(page,cb);
	}
	
	function editItem(id){
		var text = document.getElementById("label"+id).innerHTML;
		document.getElementById("itemText").value = text;
		$( "#itemEditor" ).dialog({
			resizable: false,
			width: 350,
			modal: true,
			draggable: true,
			buttons: [{ text: "Edit Item", click: function() { edit(id); } }]
		});
		
	}
	
	function edit(id){
		$("#itemEditor").dialog("close");
		var name = document.getElementById("itemText").value;
		var page = "checklistAjax/editItem.php?text="+name+"&id="+id;
				
		var cb = function(result){ 
				document.getElementById("label"+id).innerHTML = " "+name;
				};

		callPhpPage(page,cb);
	}
	
	function deleteItem(id){
		var page = "checklistAjax/deleteItem.php?id="+id;
		
		var cb = function(result){ $('#row'+id).fadeOut('slow') };
		var r = confirm("Are you sure you want to delete this?");
		if(r == true){
			callPhpPage(page,cb);
		}
	}
	
	function fadeRow(row){
		$('#'+row).fadeOut('slow');
	}
	
	function saveDraft()
	{
		var firstTextBox = $('#first').val();
		var secondTextBox = $('#second').val();
		var thirdTextBox = $('#third').val();
		var fourthTextBox = $('#fourth').val();
		var openingList = $('#openingList').is(':checked');
		var closingList = $('#closingList').is(':checked');
		var startTime = $('#start').val();
		var endTime = $('#end').val();
		var checklistItem = new Array();
		var checklistNumber = $('.task:checked').length;
		
		var page = 'draft.php?type=save&first=' + firstTextBox + '&second=' + secondTextBox + '&third=' + thirdTextBox + '&fourth=' + fourthTextBox + '&openingList=' + openingList + '&closingList=' + closingList + '&startTime=' + startTime + '&endTime=' + endTime + '&checklistNumber=' + checklistNumber;
		
		$('.task:checked').each(function (i) {page += '&checklistItem[' + i + ']=' + $(this).attr('id')});
		
		var cb = function(result){  };
		
		callPhpPage(page, cb);
		
		alert("Draft saved. You will need to reload the page before you can access the draft.");
	}
	
	function loadDraftDialog()
	{
		$('#loadDraftForm').dialog('open');
	}
	
	function deleteDraft(draftID)
	{
		var page = 'draft.php?type=delete&id=' + draftID; 
		var cb = function(result){ $('#draft' + draftID).fadeOut('slow') };
		var confirmDelete = confirm("Are you sure you want to delete this?");
		if(confirmDelete == true)
		{
			callPhpPage(page,cb);
		}
	}
</script>
<form id='reportData' method='post' onsubmit="return confirmSubmission()">
<input type="hidden" id="employeeName" name="employeeName" value="<?php echo nameByNetId($netID); ?>">
<input type="hidden" id="employeeEmail" name="employeeEmail" value="<?php echo getEmployeeEmailByNetId($netID); ?>">
<div id='headInfo'>
<h1>Supervisor Report Form</h1>
<table>
	<tr>
	<th>NAME: <?php echo nameByNetId($netID); ?></th>
	<th colspan='2'>EMAIL: <?php echo getEmployeeEmailByNetId($netID); ?></th><th>Load Draft</th><th>Save as Draft</th><th>Report Finished?</th>
	</tr><tr>
	<td>DATE: <input type='text' class='datepicker' size='10' id='reportDate' name='reportDate' value="<?php echo date('Y-m-d',strtotime("today")); ?>" /></td>
	<td>Start Time: <input type="text" name="start" id='start' maxlength=5 size=8 value="<?php echo date('h:iA'); ?>"/></td>
	<td>End Time: <input type="text" name="end" id='end' maxlength=5 size=8 value="<?php echo date('h:iA'); ?>"/></td>
	<td><input type='button' name='load' id='load' value='Load Draft' onClick='loadDraftDialog()'/></td>
	<td><input type='button' name='save' id='save' value='Save Draft' onClick='saveDraft()' /></td>
	<td><input type='submit' name='submit' id='submit' value="Submit Report"/></td>
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
<input type='button' id="absence" name="absence" value='Absence' onclick="newwindow('../performance/absence.php')" />
<input type='button' id="tardy" name="tardy" value='Tardy' onclick="newwindow('../performance/tardy.php')" />
<input type='button' id="reminder" name="reminder" value='Policy Reminder' onclick="newwindow('../performance/policyReminder.php')" />
<input type='button' id="commendable" name="commendable" value='Commendable Performance' onclick="newwindow('../performance/commendablePerformance.php')" />
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
	<td><textarea id='first' name='first' cols='100' rows='4'> </textarea></td>
	</tr><tr>
	<th>SHIFT PROBLEMS: <br/>Include high queue loads and possible reasons why. (ie: building evacuations, fire alarms, power outages, etc.)</th>
	</tr><tr>
	<td><textarea id='second' name='second' cols='100' rows='4'> </textarea></td>
	</tr><tr>
	<th>MISCELLANEOUS INFORMATION: <br/>Include anything you want to bring to our attention. (ie: problems with consoles, applications, or our office computers, alarms, etc.)</th>
	</tr><tr>
	<td><textarea id='third' name='third' cols='100' rows='4'> </textarea></td>
	</tr><tr>
	<th>SUPERVISING TASKS:<br/>Include what you did while supervising. (ie: teaming, projects, mentoring, reviewing tickets, etc.)</th>
	</tr><tr>
	<td><textarea id='fourth' name='fourth' cols='100' rows='4'> </textarea></td>
	</tr>
</table>
</div>
<br/>
<div id='closeResults'>
</div>
<input type="hidden" id="checkList" name="checkList" value="">
</form>
<div id='itemEditor' style='display:none;'>
	<h2>Add/Edit Item</h2>
	<table>
	<tr>
		<th>Text</th><td><input type='text' id='itemText' /></td>
	</tr>
	</table>
</div>
<div id='loadDraftForm'>
	<?php
	try {
		$draftQuery = $db->prepare("SELECT * FROM `supervisorReportDraft` WHERE `area` = :area");
		$draftQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	while($row = $draftQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<div id='draft".$row['ID']."'>";
		echo "<input type='radio' name='draftSelect' value='".$row['ID']."' />";
		echo "<label for='draftSelect'>Report by ".nameByNetID($row['submitter'])." started on ".$row['date']."</label>";
		echo "<input type='button' name='deleteDraft".$row['ID']."' id='deleteDraft".$row['ID']."' value='Delete' onClick='deleteDraft(".$row['ID'].")'><br />";
		echo "</div>";
	}
	?>
</div>
<?php
require('../includes/includeAtEnd.php');
?>
