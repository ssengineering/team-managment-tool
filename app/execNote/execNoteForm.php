<?php //execNote.php this is the new version of the Executive Notification tool.
require("../includes/includeMeSimple.php");
require('execNoteFunctions.php');
// TODO: Eventually, this whole page should be re-written to use APIs, but for now,
// I am just requiring the classes that I need.
require_once($_SERVER['DOCUMENT_ROOT']."/accessors/Accessor.php");
require_once($_SERVER['DOCUMENT_ROOT']."/accessors/MysqlAccessor.php");
require_once($_SERVER['DOCUMENT_ROOT']."/accessors/MimAccessor.php");
require_once($_SERVER['DOCUMENT_ROOT']."/models/Model.php");
require_once($_SERVER['DOCUMENT_ROOT']."/models/Mim.php");

function icSelect(){
	echo "<option value='' disabled selected>Select IC</option>";
	$mimAcc = new \TMT\accessor\MimAccessor();
	$mims = $mimAcc->getAll();
	foreach($mims as $mim) {
		echo "<option value='$mim->netID'>$mim->firstName $mim->lastName</option>";		
	}
}
$type = 'New';
if(isset($_GET['type'])){
	$type = $_GET['type'];
}
$loadID = '';
$subject = '';
$parentTicket = '';
$description = '';
$priority = '';
$update = '';
$ic = $netID;
$time = date('g:i A');
$date = date('Y-m-d');
if(isset($_GET['id'])){
	$loadID = $_GET['id'];
	try {
		$notificationQuery = $db->prepare("SELECT * FROM executiveNotification WHERE ID = :id");
		$notificationQuery->execute(array(':id' => $loadID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$cur = $notificationQuery->fetch(PDO::FETCH_ASSOC); 
		
	$subject = $cur['subject'];
	$parentTicket = $cur['ticketNum'];
	$description = $cur['description'];
	$priority = $cur['priority'];
	//$ic = $cur['incidentCoord'];
}

if(isset($_GET['ticketId'])){
	$loadTicket = $_GET['ticketId'];
	try {
		$notificationQuery = $db->prepare("SELECT * FROM executiveNotification WHERE ticketNum = :ticket");
		$notificationQuery->execute(array(':ticket' => $loadTicket));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$cur = $notificationQuery->fetch(PDO::FETCH_ASSOC);
		
	$subject = $cur['subject'];
	$loadID = $cur['ID'];
	$parentTicket = $loadTicket;
	$description = $cur['description'];
	$priority = $cur['priority'];
	if ($cur['incidentCoord'] != "")
	{
		$ic = $cur['incidentCoord'];
	}
}

if(isset($_GET['passy'])){
	$passy = $_GET['passy'];
}
if(isset($_GET['sysId'])){
	$sysId = $_GET['sysId'];
}
if(isset($_GET['priority'])){
	$priority = $_GET['priority'];
}
if(isset($_GET['subject'])){
	$subject = $_GET['subject'];
}
if(isset($_GET['description'])){
	$description = $_GET['description'];
}

if(isset($_GET['update'])){
	$update = $_GET['update'];
}


?>
<link rel="stylesheet" type="text/css" href="../includes/genericPageStyle.css" />
<script src="../includes/nicEdit.js" type="text/javascript"></script>
<script type="text/javascript">
window.resizeTo(1050, 650);
function togglePreview() {
	var incidentCoordinator = document.getElementById("ic").options[document.getElementById("ic").selectedIndex].value;
	if(incidentCoordinator == ""){
		alert("Please select an incident coordinator");
		return;
	}
	var theEmail = '';
	var type = document.getElementById("type").value;
	var subject = document.getElementById("subject").value;
	var time = document.getElementById("time").value;	
	var date = document.getElementById("date").value;	
	var parentTicket = document.getElementById("parentTicket").value;	
	var desc = document.getElementById("desc").value;
	var i = document.getElementById("ic").selectedIndex;
	var ic = document.getElementById("ic").options[i].text;
	var priority = document.getElementById("priority").value;
	var update = $("#inputTable").find(".nicEdit-main").html();
	if (update == null)
	{
		update = document.getElementById("update").innerHTML;
	}

	theEmail+= "Subject: " + getEmailType(type) + "Executive Notification: ";
	theEmail+= subject;
	theEmail+= "\nNotification Time: "+time;
	theEmail+= "\nNotification Date: "+date;
	theEmail+= "\nParent Ticket: "+parentTicket;
	theEmail+= "\n\nPriority: "+priority;
	theEmail+= "\n\nProblem Description: " + desc;
	theEmail+= "\n\n" + update;
	theEmail+= "\n\nIncident Coordinator: "+ ic;
	theEmail+= "\n\nIf you require further information please call 801-422-4342 and ask for the Incident Coordinator.";

	document.getElementById("previewText").value = theEmail;	

	$( "#preview" ).dialog({
		resizable: false,
		width: 600,
		modal: true,
		draggable: true,
		buttons: [{ text: "Send Email", click: function() { sendEmail(); } }]
	});
			
}

function sendEmail(){
	var check = $("#inputTable").find(".nicEdit-main").html();
	if (check == null)
	{
		check = document.getElementById("update").innerHTML;
	}
	document.getElementById("update").value = check;
	$("#preview").dialog("close");
	document.forms['execNote'].submit();
}

function getEmailType(type){
	if(type == "New"){
		return "NEW -- ";
	}else if( type == "Update"){
		return "UPDATE -- ";
	}else if(type == "Resolve"){
		return "RESOLVED -- ";
	}else if(type == "New/Resolve"){
		return "NEW/RESOLVED -- ";
	}else if(type == "Re-open"){
		return "RE-OPENED -- ";
	}


}
var nice = '';
function updateType(type)
	{
	switch(type)
	{
		case "Resolve":
		$("#updateLabel").html("Resolution");
		if (nice != '') nice.removeInstance("update");
		$("#update").val("<b>RESOLUTION:&nbsp;</b>");
		$("#updateRow").show();
		nice = '';
		nice = new nicEditor({fullPanel : true}).panelInstance("update",{hasPanel : true});
		$("#inputTable").find(".nicEdit-main").focus();
		break;

		case "New":
		$("#update").html("");
		$("#updateRow").hide();
		if (nice != '') nice.removeInstance("update");
		nice = '';
		break;

		case "New/Resolve":
		$("#updateLabel").html("Resolution");
		if (nice != '') nice.removeInstance("update");
		$("#update").val("<b>RESOLUTION:&nbsp;</b>");
		$("#updateRow").show();
		nice = '';
		nice = new nicEditor({fullPanel : true}).panelInstance("update",{hasPanel : true});
		$("#inputTable").find(".nicEdit-main").focus();
		break;

		case "Update":
		$("#updateLabel").html("Update");
		if (nice != '') nice.removeInstance("update");
		$("#update").val("<b>UPDATE:&nbsp;</b>");
		$("#updateRow").show();
		nice = '';
		nice = new nicEditor({fullPanel : true}).panelInstance("update",{hasPanel : true});
		$("#inputTable").find(".nicEdit-main").focus();
		break;

		case "Re-open":
		$("#updateLabel").html("Re-open");
		if (nice != '') nice.removeInstance("update");
		$("#update").val("<b>RE-OPEN:&nbsp;</b>");
		$("#updateRow").show();
		nice = '';
		nice = new nicEditor({fullPanel : true}).panelInstance("update",{hasPanel : true});
		$("#inputTable").find(".nicEdit-main").focus();
		break;
	}
	}

window.onload = function ()
{
	updateType("<?php echo $type; ?>");
}

</script>

<style>
td,th{
border:none;
}
</style>

<div align='center'><a href='index.php'>Executive Notification Home</a>
<h2><?php echo $type; ?> Executive Notification</h2>
</div>
<div style="text-align: right; margin-right: auto; margin-left: auto; width: 80%;">
<form method='post' name='execNote' id='execNote' action='sendEmail.php'>
<input type='hidden' id='sysId' name='sysId' value="<?php echo $sysId ?>" />
<input type='hidden' id='passy' name='passy' value="<?php echo $passy ?>" />
<?php if($loadID != ''){ ?>
<input type='hidden' id='id' name='id' value="<?php echo $loadID; ?>" />
<?php } ?>
<table style='margin-right: auto; margin-left: auto;'>
<tr>
	<th>Action:</th>
	<td style='text-align: center;'><select id='type' name='type' onchange="updateType(this.value)">
			<?php if($type == 'New' || $type == 'New/Resolve') { ?>
			<option value='New'>New</option>
			<option value='New/Resolve'>New/Resolve</option>
			<?php } ?>
			<?php if($type == 'Update' || $type == 'Resolve') { ?>
			<option value='Update'>Update</option>
			<?php if($type == 'Resolve'){ ?>
				<option value='Resolve' selected>Resolve</option>
			<?php } else { ?>
				<option value='Resolve'>Resolve</option>
			<?php } ?>
			<?php } ?>
			<?php if($type == 'Re-open') { ?>
			<option value='Re-open'>Re-open</option>
			<?php } ?>
	</select></td>
</tr>
</table>
<table id="inputTable" style='margin-right: auto; margin-left: auto;'>
	<tr>
	<th>Subject Line:</th> 
	<td><input type='text' id='subject' name='subject' size='82' value="<?php echo $subject; ?>" /></td>
</tr><tr>
	<th>Time:</th> 
	<td><input type='text' id='time' name='time' value="<?php echo $time ?>" size='6' /></td>
</tr><tr>
	<th>Date:</th>
	<td><input type='text' id='date' name='date' value="<?php echo $date ?>" size='8' />
	<?php calendar('execNote', 'date') ?></td>
</tr><tr>
	<th>Parent Ticket:</th>
	<td><input type='text' id='parentTicket' name='parentTicket' value="<?php echo $parentTicket; ?>" /></td>
</tr><tr>
	<th>Priority:</th> 
	<td><select name='priority' id='priority'>
		<option value='1' <?php if($priority == 1) echo "selected"; ?>>Priority 1</option>
		<option value='2' <?php if($priority == 2) echo "selected"; ?>>Priority 2</option>
	</select></td>
</tr><tr>
	<th>Incident Coordinator:</th>
	<td><select name='ic' id='ic'>
		<?php icSelect($ic); ?>
	</select></td>
</tr><tr>
	<th>Description:</th>
	<td><textarea name='desc' id='desc' cols='80' rows='5'>
<?php echo $description; ?></textarea></td>
</tr><tr id="updateRow">
		<th id="updateLabel">Update</th>
		<td><textarea name='update' id='update' cols='80' rows='5'>UPDATE: <?php echo $update; ?></textarea></td>
</tr>
</table>
<input type='button' name='preivewbutton' id='previewbutton' value="Preview Email" onClick="togglePreview();" style="" />
</div>
</form>
<div style='display:none;' id='preview' title="Email Preview" >
<textarea id='previewText' cols=60 rows=15 readonly="readonly">
</textarea>
</div>
<?php require("../includes/includeMeSimpleAtEnd.php"); ?>
