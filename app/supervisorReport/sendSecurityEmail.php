<?php
// This file will be executed by a Cron job
//THIS IS TO MAKE SURE THAT WHEN THE EMAIL IS SENT BY THE CRON JOB IT RUNS FROM THE CORRECT LOCATION
chdir(dirname(__FILE__));
require('../includes/dbconnect.php');
$i = 1; 
$emailBody = '';
$employeeName = '';
$employeeEmail = '';
$env = $_SERVER['argv'];
if($env[1]==2)
{
	$emailGroup = 'Security Desk Report';
}
else
{	// Testing parameters
	$emailGroup = 'OPS Development';
}

$emailSubject ='Security Supervisor Report - '.date('G:i D. M. jS, Y');
try {
	$logEntryQuery = $db->prepare("SELECT * FROM `supervisorReportSecurityDesk` WHERE `emailSent`= 0 ORDER BY `submitter`");
	$logEntryQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}
while ($reportEntry = $logEntryQuery->fetch(PDO::FETCH_ASSOC))
{
	try {
		$employeeQuery = $db->prepare("SELECT * FROM employee WHERE netID=:submitter");
		$employeeQuery->execute(array(':submitter' => $reportEntry['submitter']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $employeeQuery->fetch(PDO::FETCH_ASSOC);
	$employeeName = $result['firstName']." ".$result['lastName'];
    $employeeEmail= $result['email'];
	
	$emailBody .='<p style="font-weight:bold;">Report '.$i.'</p><br />Date: '. $reportEntry['date'].'<br /> Start Time: '.$reportEntry['startTime'].' End Time: '.$reportEntry['endTime'].'<br /> Name: '.$employeeName.'<br /> Email: '.$employeeEmail.'<br /><br /> <div style="font-weight:bold;">SECURITY PROBLEMS: </div>'.$reportEntry['securityProblems'].'<br /><br /> <div style="font-weight:bold;">SHIFT PROBLEMS: </div>'.$reportEntry['shiftProblems'].'<br /><br /> <div style="font-weight:bold;">MISCELLANEOUS INFORMATION:</div>'.$reportEntry['misc'].'<br /><br /><br />';
	$i++;
}
if($emailBody=='')
{
	$emailBody = 'There are no reports for today.';
}
$_GET['mess']=$emailBody;
$_GET['grp']=$emailGroup;
$_GET['subj']=$emailSubject;

// Set report email as sent
try {
	$updateQuery = $db->prepare("UPDATE `supervisorReportSecurityDesk` SET `emailSent`=1 WHERE `emailSent`=0");
	$updateQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}
require('../supervisorDashboard/sendEmail.php');
?>
