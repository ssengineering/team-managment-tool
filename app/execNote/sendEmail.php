<?php //sendEmail.php
require('../includes/includeme.php');
require('html2text.php');

if(!isset($_POST['priority']) || !isset($_POST['type'])){
	echo "Invalid Post Data";
	return;
}

$sysId = '';
$passy = '';
if(isset($_POST['sysId']))
{
		$sysId = $_POST['sysId'];
}
if(isset($_SESSION['servNowPass']))
{
	$passy = passDecrypt(urldecode($_SESSION['servNowPass'])); 
}

$ticketEntry = '';
$type = $_POST['type'];
if($type == 'New'){
	newEntry($_POST);
	$ticketEntry = "Sent an Executive Notification regarding this issue: <br/><br/>NEW:".$_POST['desc'];

} else if ($type == 'Update'){
	updateEntry($_POST);
	$ticketEntry = "Sent an Executive Notification regarding this issue: <br/><br/>".$_POST['update'];

} else if ($type == 'Resolve'){
	resolved($_POST);
	$ticketEntry = "Sent an Executive Notification with the resolution: <br/><br/>".$_POST['update'];
	
} else if ($type == 'New/Resolve'){
	newResolved($_POST);
	$ticketEntry = "Sent a New/Resolve Executive Notification with the resolution: <br/><br/>".$_POST['update'];

} else if ($type == "Re-open"){
	reOpen($_POST);
	$ticketEntry = "Re-opening the corresponding Executive Notification for this issue: <br/><br/>".$_POST['update'];
}

// INSERT INTO OLD SUPERVISOR REPORT LOG
	$entry = 'Executive Notification - '.$type.' - '.$_POST['parentTicket'].' - '.$_POST['subject'];
	if ($_POST['update'] != "")
	{
		$entry .= ": <br />".nl2br($_POST['update']);
	}
	try {
		$insertQuery = $db->prepare("INSERT INTO `supReport` (submittedBy, entry, guid) VALUES (:netId, :entry, :guid)");
		$insertQuery->execute(array(':netId' => $netID, ':entry' => $entry, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}

// INSERT INTO NEW SUPERVISOR REPORT LOG TOO
	$entry = 'Executive Notification - '.$type.' - '.$_POST['parentTicket'].' - '.$_POST['subject'];
	if ($_POST['update'] != "")
	{
		$entry .= "\n\n".nl2br($_POST['update']);
	}

// INCLUDE IT AS PART OF THE SUPEVISOR'S REPORT-SO-FAR
	$_SESSION['supReport'][date('Y-m-d G:i:s:u')] = array(date('D. @ G:i:s'), $netID, $entry);


// INCLUDE UPDATES TO THE TICKET ITSELF
try
{
	$soapurl = getenv("SERVICE_NOW_STAGE_URL")."/incident.do?WSDL";
	if( $env==2 ){
		$soapurl = getenv("SERVICE_NOW_URL")."/incident.do?WSDL";
	}
	$incidentClient = new SoapClient($soapurl, array('trace' => 1, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 'login' => $netID, 'password' => $passy));
	$updateResponse = $incidentClient->update(array('sys_id'=>$sysId, 'short_description'=>$_POST['subject'], 'u_work_log'=>"(Internal Comment)".html2text($ticketEntry)));
	if (isset($updateResponse->sys_id))
	{
		echo "<h2> Ticket's work-log has been updated successfully. </h2>";
	}
	else
	{
		echo "<h2> Updating the work-log for the ticket failed. Please attempt to do so manually. </h2>";
	}
}
catch  (Exception $E)
{
	if ($env == 2)
	{
		$errorInfo = '';
	}
	else
	{
		$errorInfo = "ERROR ".print_r($E, true);
	}
	echo "<h2> Unable to connect to service-now to insert your update to the work-log. Please attempt to do so manually. <br><br>$errorInfo</h2>";
}



$emailBody = formEmail($_POST);
$subject = getEmailType($type).$_POST['subject'];
if ($env == 2){
	$recipient = '';
	try {
		$notificationMemberQuery = $db->prepare("SELECT * FROM `notificationMember` WHERE `memberId` IN (SELECT `memberId` FROM `notificationGroupMember` WHERE `groupName` = 'Executive Notification')");
		$notificationMemberQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($execNoteRecipient = $notificationMemberQuery->fetch(PDO::FETCH_ASSOC))
	{
		if ($recipient === '')
		{
			$recipient = $execNoteRecipient['email'];
		}
		else
		{
			$recipient .= ', '.$execNoteRecipient['email'];
		}
	}
}else {
	$recipient = getenv("DEV_EMAIL_ADDRESS");
	$other = '';
	try {
		$notificationMemberQuery = $db->prepare("SELECT * FROM `notificationMember` WHERE `memberId` IN (SELECT `memberId` FROM `notificationGroupMember` WHERE `groupName` = 'Executive Notification')");
		$notificationMemberQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($execNoteRecipient = $notificationMemberQuery->fetch(PDO::FETCH_ASSOC))
	{
		if ($other === '')
		{
			$other = $execNoteRecipient['email'];
		}
		else
		{
			$other .= ', '.$execNoteRecipient['email'];
		}
	}
	$emailBody .= $other;
}

$headers = "From: BYU Operations Center <".getenv("OPS_MANAGERS_EMAIL_ADDRESS").">\r\n";
$headers .= "Return-Path: ".getenv("OPS_MANAGERS_EMAIL_ADDRESS")."\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html;\r\n";
echo $recipient." ".$subject." ".$emailBody."<br />";
if (mail($recipient,$subject,$emailBody, $headers))
{
	echo "<h2><a href='index.php'>Email has Been Sent Return to Exec Note Index</a></h2>";
}
else 
{
	echo "<h2>ERROR SENDING EMAIL, PLEASE SEND AN EMAIL UPDATE MANUALLY.</h2>";
}

function newEntry($post){
	global $netID, $db;
	$time = date('H:i:s', strtotime($post['time']));
	try {
		$insertQuery = $db->prepare("INSERT INTO executiveNotification (subject,ticketNum,incidentCoord,startDate,startTime,status,priority,submitter,description,guid) Values (:subject,:parent,:ic,:day,:time,'0',:priority,:netId,:desc,:guid)");
		$insertQuery->execute(array(':subject' => $post['subject'], ':parent' => $post['parentTicket'], ':ic' => $post['ic'], ':day' => $post['date'], ':time' => $time, ':priority' => $post['priority'], ':netId' => $netID, ':desc' => $post['desc'], ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

function updateEntry($post){
	global $netID, $db;
	$time = date('H:i:s', strtotime($post['time']));
	try {
		$insertQuery = $db->prepare("INSERT INTO executiveNotificationUpdate (execNoteID,updateText,date,time,submitter,type,guid) VALUES (:id,:update,:day,:time,:netId,'Update',:guid)");
		$insertQuery->execute(array(':id' => $post['id'], ':update' => $post['update'], ':day' => $post['date'], ':time' => $time, ':netId' => $netID, ':guid' => newGuid()));

		$updateQuery = $db->prepare("UPDATE executiveNotification SET description=:desc, subject = :subject, status = '0', priority = :priority WHERE ID = :id");
		$updateQuery->execute(array(':desc' => $post['desc'], ':subject' => $post['subject'], ':priority' => $post['priority'], ':id' => $post['id']));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

function resolved($post){
	global $netID, $db;
	$time = date('H:i:s', strtotime($post['time']));
	try {
		$insertQuery = $db->prepare("INSERT INTO executiveNotificationUpdate (execNoteID,updateText,date,time,submitter,type,guid) VALUES (:id,:update,:day,:time,:netId,'Resolve',:guid)");
		$insertQuery->execute(array(':id' => $post['id'], ':update' => $post['update'], ':day' => $post['date'], ':time' => $time, ':netId' => $netID, ':guid' => newGuid()));

		$updateQuery = $db->prepare("UPDATE executiveNotification set description=:desc, subject=:subject, status='1', endDate=:day, endTime=:time, priority=:priority WHERE ID=:id");
		$updateQuery->execute(array(':desc' => $post['desc'], ':subject' => $post['subject'], ':day' => $post['date'], ':time' => $time, ':priority' => $post['priority'], ':id' => $post['id']));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

function newResolved($post){
	global $netID, $db;
	$time = date('H:i:s', strtotime($post['time']));
	try {
		$insertQuery = $db->prepare("INSERT INTO executiveNotification (subject,ticketNum,incidentCoord,startDate,startTime,status,priority,submitter,description, endDate, endTime, guid) Values (:subject,:parent,:ic,:day,:time,'1',:priority,:netId,:desc,:day,:time,:guid)");
		$insertQuery->execute(array(':subject' => $post['subject'], ':parent' => $post['parentTicket'], ':ic' => $post['ic'], ':day' => $post['date'], ':time' => $time, ':priority' => $post['priority'], ':netId' => $netID, ':desc' => $post['desc'], ':day' => $post['date'], ':time' => $time, ':guid' => newGuid()));

		$insert2Query = $db->prepare("INSERT INTO executiveNotificationUpdate (execNoteID,updateText,date,time,submitter,type,guid) VALUES (:id, :update,:day,:time,:netId,'New/Resolve',:guid)");
		$insert2Query->execute(array(':id' => $db->lastInsertId(), ':update' => $post['update'], ':day' => $post['date'], ':time' => $time, ':netId' => $netID, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

function reOpen($post){
	global $netID, $db;
	$time = date('H:i:s', strtotime($post['time']));
	try {
		$insertQuery = $db->prepare("INSERT INTO executiveNotificationUpdate (execNoteID,updateText,date,time,submitter,type,guid) VALUES (:id,:update,:day,:time,:netId,'Re-open',:guid)");
		$insertQuery->execute(array(':id' => $post['id'], ':update' => $post['update'], ':day' => $post['date'], ':time' => $time, ':netId' => $netID, ':guid' => newGuid()));

		$updateQuery = $db->prepare("UPDATE executiveNotification set description=:desc, subject = :subject, status = '0', endDate = NULL, endTime = NULL, priority = :priority WHERE ID = :id");
		$updateQuery->execute(array(':desc' => $post['desc'], ':subject' => $post['subject'], ':priority' => $post['priority'], ':id' => $post['id']));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

function formEmail($postData){
		
	$email= "<b>Notification Time: </b>".$postData['time']."<br />";
	$email.= "<b>Notification Date: </b>".date('m/d/Y',strtotime($postData['date']))."<br /><br />";
	$email.= "<b>Parent Ticket: </b>".$postData['parentTicket']."<br />";
	$email.= "<b>Priority: </b>".$postData['priority']."<br /><br />";
	$email.= "<b>Problem Description: </b>".$postData['desc']."<br /><br />";
	if($postData['update'] != ""){
		$email.= $postData['update']."<br />";
	}
	$email.= "<b>Incident Coordinator: </b>".nameByNetId($postData['ic'])."<br /><br />";
	$email.= "If you require further information please call 801-422-4342 and ask for the Incident Coordinator.";
	
	return $email;
}

function getEmailType($type){
	if($type == "New"){
		return "NEW -- ";
	}else if( $type == "Update"){
		return "UPDATE -- ";
	}else if($type == "Resolve"){
		return "RESOLVED -- ";
	}else if($type == "New/Resolve"){
		return "NEW/RESOLVED -- ";
	}else if($type == "Re-open"){
		return "RE-OPENED -- ";
	}


}

?>



<?php
	require('../includes/includeAtEnd.php');
?>
