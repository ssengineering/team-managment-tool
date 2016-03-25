<?php
require ('../includes/includeme.php');
require ('../includes/email.php');

if(can("request", "35599e89-9edc-4e35-acfe-1420fb398920"))/*timeCorrection resource*/{

if (isset($_POST['submit']) && isset($_POST['0']['time'])) 
{	
	$body = "";
	foreach($_POST as $id => $cur){
		$inOut = "";
		$addRemove = "";
	
		if($cur == "Submit"){
		} else{
			$time = $cur['time'];
			$date = $cur['date'];
			$add = $cur['addRemove'];
			$in = $cur['inOut'];
			try {
				$insertQuery = $db->prepare("INSERT INTO kronosEdit (`addRemove`,`inOut`,`time`,`date`,`submitter`,`guid`) VALUES (:add,:in,:time,:day,:netId,:guid)");
				$insertQuery->execute(array(
					':add'   => $add,
					':in'    => $in,
					':time'  => $time,
					':day'   => $date,
					':netId' => $netID,
					':guid'  => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$alert = "Your Edit Request has been submitted!";
			
			if($in == 1){
				$inOut = "In";
			} else {
				$inOut = "Out";
			}
			
			if($add == 1){
				$addRemove = "Add";
			} else {
				$addRemove = "Remove";
			}
			
			$body.=$addRemove." a Clock ".$inOut." at ".$time." on ".$date.".<br />";
		}
	}

	//Prepare email
	$email = (object) array(
		"recipients" => '',
		"subject"    => '',
		"message"    => '',
		"cc"         => null,
		"bcc"        => null 
	);

	// Prepare recipient
	if($env < 2){
		$email->recipients = getenv("DEV_EMAIL_ADDRESS");
	} else {
		$email->recipients = getenv("PAYROLL_EMAIL");
	}

	// Prepare subject
	$email->subject = "Missed Punch Correction";

	// Prepare message body
	$emailBody = "Dear Payroll:<br /><br />";
	$emailBody.= "Would you please make the following time changes on my time card:<br /><br />";
	$emailBody.= $body;
	$emailBody.= "<br />Thanks,<br />".nameByNetID($netID)."<br />NetID: $netID \r\n";
	$emailBody.= "<br />BYUID: ".getEmployeeByuIdByNetId($netID)."<br /> \r\n";
	$emailBody.= "Email: ".getEmployeeEmailByNetId($netID)."<br /> \r\n";
	if($env < 2) {
		$emailBody .= "<br />Area: $area; Env: $env; <br />";
		$emailBody .= "Subject: $email->subject;<br />";
		$emailBody .= "Cc: ".getEmployeeEmailByNetId(getEmployeeManagerByNetId($netID))."\r\n";
		$emailBody .= "<br />Bcc: ".getEmployeeEmailByNetId($netID)."\r\n";
	} else {
		$email->cc  = getEmployeeEmailByNetId(getEmployeeManagerByNetId($netID));
		$email->bcc = getEmployeeEmailByNetId($netID);
	}
	$email->message = $emailBody;

	if (sendEmail($email))
	{
		echo "<div>Request email sent successfully.</div>";
	}
	else 
	{
		echo "<div>ERROR IN SENDING THE EMAIL FOR THE TIME EDIT REQUEST</div>";
	}
}
?>
<script type='text/javascript'>
var num;
window.onload = function() {
	num = 0;
	insertRequest(num);
	$('#time').timeEntry({useMouseWheel: true});
	$("input:button").button();
	$("input:submit").button();
}

function insertRequest(curNum){
	var page = "printForm.php?num="+curNum;
	var divID = curNum
	var cb = function(result){ 
		document.getElementById(num).innerHTML = result; 		 
		$('#time'+num).timeEntry({useMouseWheel: true});
		$('#'+num+"date").datepicker({dateFormat: "yy-mm-dd"});
		num++;
	}
	callPhpPage(page,cb);
}

function deleteRequest(){
	if( num > 0){
		num--;
	}
	document.getElementById(num).innerHTML = "";
}
</script>		

<style type='text/css'>
	#notes{
	font-size:120%;
	}
</style>

<div id="info" align="center">
<h1>Y-Time Edit Request for <?php echo nameByNetID($netID) ?></h1>
<div id='notes' align="center">
	<h3 style='color:red'>*Before submitting a time edit request please try to correct the changes yourself through Y-time.</h3>
	<b>Instructions</b><br/><br/>
	<table>
	<td>
	<b>1.</b> All fields are required in order to submit the edit request(s).<br/>
	<b>2.</b> When the edit request(s) are submitted, an email will be sent to OIT_Payroll@byu.edu.<br/>
	<b>3.</b> You and your supervisor(s) will receive a copy of the email.<br/>
	</td>
	</table>

</div>
<br/>
<br/>
<input type='button' value="+" onclick='insertRequest(num)' />
:-:
<input type='button' value="-" onclick='deleteRequest()' /><br/>

Please allow the next form to load before clicking '+' again.<br/><br/>
<form name="kronosForm" method='post'>
	
	<div id='0'>
	
	</div>
	<br>
<input type="submit" name='submit' value="Submit">
</form> 
</div>

<?php if(isset($alert)){ ?>
<script>alert("<?php echo $alert;?>")</script>
<?php }
} else {
	echo "<h1>You do not have authorization to view this application. Please contact your supervisor if you feel this is in error.</h1>";
}
require ('../includes/includeAtEnd.php');
?>
