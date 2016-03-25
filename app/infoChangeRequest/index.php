<?php 
//Info change log
require('../includes/includeme.php');

$info = '';
$error = '';

function sendEmail($comments, $location){
	global $area;
	global $env;
	global $netID;

	$employeeName = nameByNetId($netID);
	$employeeEmail = getEmployeeEmailByNetId($netID);
	
	
	$subject = 'Info Change Request';
	$emailBody = <<<STRING
	<b>Info Change Request: </b> <br />$comments<br /> <br />
	<b>Location: </b>$location<br /> <br />
	
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
	if($_POST['location'] != ''){
		try {
			$insertQuery = $db->prepare("INSERT INTO reportInfoChangeRequest (netID,notes,type,location,status,comments,guid) VALUES (:netId,:comments,'Info Change Request',:location,'New', '',:guid)");
			$insertQuery->execute(array(':netId' => $netID, ':comments' => $_POST['comments'], ':location' => $_POST['location'], ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
		sendEmail($_POST['comments'],$_POST['location']);
	} else {
		$info = $_POST['comments'];
		$error = "<font color='red'>You must enter a location where you found the incorrect information</font>";
	}
}



?>

<div align='center'>
<h1>Info Change Request</h1>
<h2><a href='infoLog.php'>View the Log</a>
	 
<script src="../includes/nicEdit.js" type="text/javascript"></script>
<script type="text/javascript">
window.onload=function() {
nice = new nicEditor({fullPanel : true}).panelInstance("comments",{hasPanel : true});
nice2 = new nicEditor({fullPanel : true}).panelInstance("location",{hasPanel : true});
$(".nicEdit-main").css({"text-align":"left"});
$("#info").find(".nicEdit-main:first").focus();
}
</script>
		 
		<h2>Please use this page to submit any information suggestions, changes or updates that need <br> to be made either on the operator web page or in the info screen.</h2> 
		  
	<br/>  
		Where other updates need to go:
		  
	<br/>
		<b>Students: </b>Registration Office; My BYU;<br>
		<b>Faculty: </b><?php echo getenv("INFO_CHANGE_REQUEST_EMAIL"); ?><br>
		<b>Department: </b><?php echo getenv("INFO_CHANGE_REQUEST_EMAIL"); ?><br>
	<br/>
		Name: <?php $name=nameByNetId($netID); echo $name?>
			<form id='info' method="post">  
	<br/>
 
			<h3>Comments:</h3>
			<textarea name='comments' id='comments' cols="55" rows="6"><?php echo $info; ?></textarea> <br/>
			<h3>Where the incorrect info is Located:</h3>
			<?php echo $error; ?><br/>
			<textarea name='location' id='location' cols='55' rows='6'></textarea><br/>			
			<input name="submit" type="submit" value="Submit">
			<input type="reset" value="Reset">
		</form>	
		  
	<br/>

</div>

<?php 
require('../includes/includeAtEnd.php'); 
?>
