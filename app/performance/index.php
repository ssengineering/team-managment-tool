<?php
	require('../includes/includeme.php');

	function security(){
		global $db;
		try {
			$securityViolationQuery = $db->prepare("SELECT * FROM reportSecurityViolation WHERE employee=:netId");
			$securityViolationQuery->execute(array(':netId' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row=$securityViolationQuery->fetch(PDO::FETCH_ASSOC)) {
			echo 'Security Violation submitted on '.$row['date'].'.  Violation: '.$row['violation'].'.  Reason: '.$row['reason'];		
		}
	}
?>
<html>
<head>
<style>
#absence{float:left;width:50%;}
#commendable{float:right;width:50%;}
#policy{float:left;width:50%;}
#security{width:50%;}
</style>
</head>
<body>
<font size='4'>
	<div id='absence'>
	<h2>Absences:</h2> 
	 <?php 		
		try {
			$absencesQuery = $db->prepare("SELECT * FROM reportAbsence WHERE employee=:netId");
			$absencesQuery->execute(array(':netId' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row=$absencesQuery->fetch(PDO::FETCH_ASSOC)) {
			echo 'Absent on '.$row['date'].'.  </br>Shift missed: '.$row['shiftStart'].':00 - '.$row['shiftEnd'].':00.  </br>Reason: '.$row['reason'];
			if($row['noCall']=='yes')
				echo '</br>This was a <font color="red">no-call.</font>';  
			echo '<hr />';
		}
	?>
	</div>

	<div id='commendable'>
	<h2>Commendable Performance:</h2>
	<?php
		try {
			$commendableQuery = $db->prepare("SELECT * FROM reportCommendable WHERE employee=:netId");
			$commendableQuery->execute(array(':netId' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row=$commendableQuery->fetch(PDO::FETCH_ASSOC)) {
			echo 'Commendable Performance submitted on '.$row['date'].'.  </br>Reason: '.$row['reason'].'<hr />';
		}
	?>
	</div>
	<h2>Policy Reminders:</h2>
	<div id='policy'>
	<?php
		try {
			$policyQuery = $db->prepare("SELECT * FROM reportPolicyReminder WHERE employee=:netId");
			$policyQuery->execute(array(':netId' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row=$policyQuery->fetch(PDO::FETCH_ASSOC)) {
			echo 'Policy Reminder submitted on '.$row['date'].'.  </br>Reason: '.$row['reason'].'<hr />';
		}
	?>
	</div>
	<div id='security'>
	<h2>Security Violations:</h2>
	<?php
		try {
			$violationQuery = $db->prepare("SELECT * FROM reportSecurityViolation WHERE employee=:netId");
			$violationQuery->execute(array(':netId' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row=$violationQuery->fetch(PDO::FETCH_ASSOC)) {
			echo 'Security Violation submitted on '.$row['date'].'.  </br>Violation: '.$row['violation'].'.  </br>Reason: '.$row['reason'].'<hr />';		
		}
	?>
	</div>
</font>
<body>
</html>
<?php require('../includes/includeAtEnd.php'); ?>
