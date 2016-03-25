<?php 
//Info change log
require('../includes/includeme.php');

function sendEmail($comments){
	global $area;
	global $env;
	global $netID;

	$employeeName = nameByNetId($netID);
	$employeeEmail = getEmployeeEmailByNetId($netID);
	
	
	$subject = 'Unusual Call Received';
	$emailBody = <<<STRING
	<b>Unusual Call Details: </b> <br />$comments<br /> <br />
	
	Thank you, <br />
	$employeeName
	
STRING;
	
	if($env == 2)
	{
		if($area == 3){//Service Desk
			$to = getenv("SD_ALIAS");
		}else if($area == 4){//COS
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
	}
	
	$headers = 'From: '.$employeeName.' <'.$employeeEmail.'>' . "\r\n";
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'Return-Path: '.$employeeEmail . "\r\n";

	if(mail($to,$subject,$emailBody,$headers))
	{
		echo '<h2 style="text-align: center;">Email was sent successfully.</h2>';
	}
	else
	{
		echo '<h2 style="text-align: center;">Email failed to be sent.</h2>';
	}
}

if(isset($_POST['submit'])){
	if($_POST['comments']!= '')
	{
		try {
			$insertQuery = $db->prepare("INSERT INTO reportInfoChangeRequest (netID,notes,type,guid) VALUES (:netId,:comments,'Unusual Call',:guid)");
			$insertQuery->execute(array(':netId' => $netID, ':comments' => $_POST['comments'], ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
		sendEmail($_POST['comments']);
	}
	else
	{
		echo "<h2 style='text-align: center;'> Please enter something in the form below.</h2>";
	}
}


?>

<div align='center'>
<h1>Unusual Call Form</h1>
<h2><a href='callLog.php'>View the Log</a>

<script src="../includes/nicEdit.js" type="text/javascript"></script>
<script type="text/javascript">
window.onload=function() {
nice = new nicEditor({fullPanel : true}).panelInstance("comments",{hasPanel : true});
$(".nicEdit-main").css({"text-align":"left"});
$("#info").find(".nicEdit-main:first").focus();
}
</script>
	 
		 
		<h2>Please use this page to submit any information regarding unusual calls. Any request for information changes need to be submitted on the Info Change Request Page.</h2> 
		
	<br/>
		Name: <?php $name=nameByNetId($netID); echo $name?>
			<form id='info' method="post">  
	<br/>	 
			<textarea name='comments' id='comments' cols="55" rows="6"></textarea> <br>
			<input name="submit" type="submit" value="Submit">
			<input type="reset" value="Reset">
		</form>	
		  
	<br/>

</div>

<?php 
require('../includes/includeAtEnd.php'); 
?>
