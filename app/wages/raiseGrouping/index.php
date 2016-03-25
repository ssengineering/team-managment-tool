<?php
/*
This file will display a table of raises that have been approved and submitted by whoever is viewing the page
The purpose is to review all of the raises before submitting them to payroll
It will have a submit button so that the user can then submit the raises to payroll to be applied to the employee's wages.
*/

require ('../../includes/includeme.php');
if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af"))/*wages resource*/{

//This function formats the body of the email that will be send to payroll
function formatEmailBody($supressed){
	global $netID, $db;
	$body = '';
	$count = 0;
	echo "<br/>";
	//This queries for the list of employees you have raises pending for, with each net ID appearing once.
	try {
		$employeeQuery = $db->prepare("SELECT DISTINCT netID FROM employeeRaiseLog WHERE submitter = :netId AND isSubmitted = '0' ORDER BY netID ASC");
		$employeeQuery->execute(array(':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	//This cycles through each net ID producing the section of the email for that net ID
	While($cur = $employeeQuery->fetch(PDO::FETCH_ASSOC)) {
		$count = 0;
		$toBeAdded = '';
		$toBeAdded.="<b>".nameByNetId($cur['netID'])." - BYU ID: ".getEmployeeByuIdByNetId($cur['netID'])." - Net ID: ".$cur['netID']."</b><br/>";
		$toBeAdded.="<table><tr><th>Reason</th><th>Raise</th><th>Date Effective</th></tr>";
		//Queries for ALL pending raises for the current net ID that was submitted by you
		try {
			$logQuery = $db->prepare("SELECT * FROM employeeRaiseLog WHERE submitter = :submitter AND isSubmitted = '0' AND netID = :employee ORDER BY date ASC");
			$logQuery->execute(array(':submitter' => $netID, ':employee' => $cur['netID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		//Adds each pending raise to the html table in the email.
		while($raise = $logQuery->fetch(PDO::FETCH_ASSOC)) {
			if(!in_array($raise['index'],$supressed)){
				$toBeAdded.="<tr>";
				$toBeAdded.="<td>".$raise['comments']."</td>";
				$toBeAdded.="<td><b>".$raise['raise']."</b></td>";	
				$toBeAdded.="<td>".date('Y-m-d',strtotime($raise['date']))."</td>";
				$toBeAdded.="</tr>";
				$count++;
			}
			//This updates the raise in the database to no longer be pending.
			try {
				$updateQuery = $db->prepare("UPDATE employeeRaiseLog SET isSubmitted = '1' WHERE `index` = :index");
				$updateQuery->execute(array(':index' => $raise['index']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
		$toBeAdded.="<tr><th>New wage</th><th>".getEmployeeWageByNetId($cur['netID'])."</th>";
		$toBeAdded.="</table><br/><br/>";
		if($count > 0){
			$body.=$toBeAdded;
		}
	}
		
	return $body;
}

if(isset($_POST['submit'])){
	//send and e-mail to payroll from the current user for all users in the table.
	$body = "";
	$count = 0;
	$supressedIndex = array();
	//Query for all pending raises that you submitted
	try {
		$raiseLogQuery = $db->prepare("SELECT * FROM employeeRaiseLog WHERE submitter = :netId AND isSubmitted='0' ORDER BY date ASC");
		$raiseLogQuery->execute(array(':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	//For each raise do the following: 
	while($curRaise = $raiseLogQuery->fetch(PDO::FETCH_ASSOC)) {
		try {
			$wageQuery = $db->prepare("SELECT wage FROM employeeWages WHERE netID=:netId");
			$wageQuery->execute(array(':netId' => $curRaise['netID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $wageQuery->fetch(PDO::FETCH_ASSOC);
		$newWage = $result['wage'] + $curRaise['raise'];
		//1.Update that employee's wage.
		try {
			$updateQuery = $db->prepare("UPDATE employeeWages SET wage=:wage WHERE netID=:netId");
			$updateQuery->execute(array(':wage' => $newWage, ':netId' => $curRaise['netID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		//2. If the supresse e-mail checkbox for that raise was checked then: 
		if(isset($_POST['email'][$curRaise['index']])){
			$supressedIndex[] = $curRaise['index'];
			//2.a. Set that rase to no longer pending.
			try {
				$updateQuery = $db->prepare("UPDATE employeeRaiseLog SET isSubmitted = '1' WHERE `index` = :index");
				$updateQuery->execute(array(':index' => $curRaise['index']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		} else {
			//2.b. Otherwise increment the count so an e-mail will get sent
			$count++;
		}
	}
	
	if($count > 0){ //If count is greater than 0 then there is info to be emailed to paroll
		//Call formatEmailBody and pass it the index of supressed Indexes
		$body = formatEmailBody($supressedIndex);
		//Send Email. The rest is self explanitory, see the variable names.
		if($env < 2){
			$to = getenv("DEV_EMAIL_ADDRESS"); //Dev site
		} else {
			$to = getenv("PAYROLL_EMAIL"); //Prod site
		}
		$subject = "Wage Increase for Students";
		$emailBody = "<html><body>Dear Payroll Official:";
		$emailBody.= "\nWould you please make the following wage changes for some of the following employees :<br/><br/>";
		$emailBody.= $body;
		$emailBody.= "<br/>Thanks,<br/>".nameByNetID($netID);
		$emailBody.="</body></html>";
		$from = 'From: '.getenv("NO_REPLY_ADDRESS")."\r\n";
		$from .= "CC: ".getEmployeeEmailByNetId($netID)."\r\n"; 
		$from .= "MIME-Version: 1.0\r\n";
		$from .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		//send the Email
		mail($to,$subject,$emailBody,$from);
		
	}
	$_POST = '';
	
}


?>
<script type='text/javascript'>
	window.onload = printRaises;
	
	//This opens a new window for editing
	function newwindow(urlpass) {
		window.open(urlpass,"Edit Raise","status=1,width=1000,height=500,scrollbars=1");
	}
		
	//This prints out the pending raises
	function printRaises(){
		var page = 'ajax/printRaise.php';
			
		var cb = function(result){ document.getElementById("results").innerHTML = result; };

		callPhpPage(page,cb);
	}
	
	//This is the AJAX call for deleting a raise.
	function deleteRaise(id){
		var page = 'ajax/deleteRaise.php?id='+id;
		
		var cb = function(result){  printRaises(); };
		
		callPhpPage(page,cb);
	}

	
</script>
<h2 align='center'>
	Raises Waiting to be submitted by you, <?php echo nameByNetId($netID); ?>
</h2>
<h3 align='center'>
	This is a list of all raises that you have requested but have not yet been submitted to Payroll. <br/>Please review the following table to make sure there are no errors. When finished you can click the submit button to send an e-mail to payroll to request the raises listed in the table.
</h3>
<div>
<INPUT TYPE="button" VALUE="Reload Page" onClick="history.go(0)">
<form name="curRaiseForm" method="post" id='curRaiseForm'>
	<div id='results'>
	
	</div>
	<input type="submit" value="Submit Raises" name='submit' />
</form>
</div>

<?php
}else {
    echo "<h1>You are not Authorized to View this page</h1>";
}
include('../../includes/includeAtEnd.php');

?>
