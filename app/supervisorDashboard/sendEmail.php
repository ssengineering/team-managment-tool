<?php
	//needs to send an email containing:  Reports, Tasks, ExecNotes?, absenses, tardies, etc.., 
	//How long between emails?  4, 6, 8, 10, 12

	//THIS IS TO MAKE SURE THAT WHEN THE EMAIL IS SENT BY THE CRON JOB IT RUNS FROM THE CORRECT LOCATION
	chdir(dirname(__FILE__));

	require('../includes/dbconnect.php');

	$grp = 'Supervisor Report';

	$mess = null;

	$subj = null;

	if (isset($_GET['grp']))
	{
		$grp = $_GET['grp'];
		$grp = explode('~', $grp);
	}//if

	if (isset($_GET['mess']))
	{
		$mess = $_GET['mess'];
	}//if

	if (isset($_GET['subj']))
	{
		$subj = $_GET['subj'];
	}//if

	function SendEmail($notificationGroups, $message=null, $subject=null)
	{
		global $db;
		try {
			$reportQuery = $db->prepare("SELECT * FROM `supReport` WHERE `timeSubmitted` >= :day ORDER BY `timeSubmitted`");
			$reportQuery->execute(array(':day' => date('Y-m-d H:i:s', strtotime('-13 hour'))));
			$unscheduledRFCQuery = $db->prepare("SELECT * FROM `unscheduledRFC` WHERE `startDate` <= :day AND `endDate` >= :day1 ORDER BY `startDate` ASC");
			$unscheduledRFCQuery->execute(array(':day' => date('Y-m-d'), ':day1' => date('Y-m-d')));
		} catch(PDOException $e) {
			exit("error in query");
		}

		$emailTo = '';
		if (is_array($notificationGroups))
		{
			foreach ($notificationGroups as $group)
			{
				try {
					$groupQuery = $db->prepare("SELECT * FROM `notificationGroupMember` WHERE `groupName` = :group");
					$groupQuery->execute(array(':group' => $group));
				} catch(PDOException $e) {
					exit("error in query");
				}
				while ($whom = $groupQuery->fetch(PDO::FETCH_ASSOC)) {
					try {
						$memberQuery = $db->prepare("Select * from `notificationMember` where `memberId` = :id");
						$memberQuery->execute(array(':id' => $whom['memberId']));
					} catch(PDOException $e) {
						exit("error in query");
					}
					$memberInfo = $memberQuery->fetch(PDO::FETCH_ASSOC);
					$emailTo .= $memberInfo['email'].',';
				}//while
			}//foreach
		}//if
		else 
		{
			try {
				$recipientQuery = $db->prepare("SELECT * FROM `notificationGroupMember` WHERE `groupName` = :group");
				$recipientQuery->execute(array(':group' => $notificationGroups));
			} catch(PDOException $e) {
				exit("error in query");
			}
			while ($whom = $recipientQuery->fetch(PDO::FETCH_ASSOC)) {
				try {
					$memberQuery = $db->prepare("SELECT * FROM `notificationMember` WHERE `memberId` = :id");
					$memberQuery->execute(array(':id' => $whom['memberId']));
				} catch(PDOException $e) {
					exit("error in query");
				}
				$memberInfo = $memberQuery->fetch(PDO::FETCH_ASSOC);
				$emailTo .= $memberInfo['email'].',';
			}//while
		}//else
	
		if ($subject == null)
		{
		  	$subject = 'Network Ops Supervisor Report - '.date('G:i D. M. jS, Y');
		}//if

		if ($message == null)
		{	
			$message = "<br /><br /><h3>Supervisor Log</h3>";
			if($first = $reportQuery->fetch(PDO::FETCH_ASSOC)) {

			  	$message .= "<table style='border: 1px solid #BBBBBB; border-collapse: collapse; border-spacing: 0; margin: 0 0 9px; padding: 5px;'> \r\n";
				$message .= "<tr><th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>Time</th>\r\n";
				$message .= "<th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>Log Entry</th>\r\n";
				$message .= "<th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>Supervisor</th></tr>\r\n";
		
				$message .= "<tr>\r\n";
				try {
					$supervisorQuery = $db->prepare("SELECT `firstName`, `lastName` FROM employee WHERE `netID` = :netId");
					$supervisorQuery->execute(array(':netId' => $first['submittedBy']));
				} catch(PDOException $e) {
					exit("error in query");
				}
				$supName = $supervisorQuery->fetch(PDO::FETCH_ASSOC);
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>".$first['timeSubmitted']."</td>\r\n";
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>".$first['entry']."</td>\r\n";
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>".trim($supName['firstName'].' '.$supName['lastName'])."</td>\r\n";
				$message .= "</tr>\r\n";

				while ($entry = $reportQuery->fetch(PDO::FETCH_ASSOC))
				{
					$message .= "<tr>\r\n";
					try {
						$supervisorQuery = $db->prepare("SELECT `firstName`, `lastName` FROM employee WHERE `netID` = :netId");
						$supervisorQuery->execute(array(':netId' => $first['submittedBy']));
					} catch(PDOException $e) {
						exit("error in query");
					}
					$supName = $supervisorQuery->fetch(PDO::FETCH_ASSOC);
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>".$entry['timeSubmitted']."</td>\r\n";
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>".$entry['entry']."</td>\r\n";
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>".trim($supName['firstName'].' '.$supName['lastName'])."</td>\r\n";
					$message .= "</tr>\r\n";
				}//while
		
				$message .= "</table>";
			
			} else {
				$message .= "There were no log entries since the last report.";
			}//else


			$message .= "<br /><br /><h3>Unscheduled RFCs</h3>";
			if($first = $unscheduledRFCQuery->fetch(PDO::FETCH_ASSOC)) {
				$message .= "<table style='border: 1px solid #BBBBBB; border-collapse: collapse; border-spacing: 0; margin: 0 0 9px; padding: 5px;'> \r\n";
				$message .= "<tr><th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>RFC</th>\r\n";
				$message .= "<th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>Engineer</th>\r\n";
				$message .= "<th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>Start Time</th>\r\n";
				$message .= "<th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>End Time</th>\r\n";
				$message .= "<th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>Description</th>\r\n";
				$message .= "<th style='background: none repeat scroll 0 0 #E0E0E0; border-left: 1px solid #C9C9C9; border-right: 1px solid #C9C9C9; border: 1px solid #BBBBBB; padding: 5px;'>Impact</th></tr>\r\n";
		
				$message .= "<tr>\r\n";
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $first['ticketNumber'] . "</td>\r\n";
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $first['engineerName'] . "</td>\r\n";
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $first['startDate'] . " at " . $first['startTime'] . "</td>\r\n";
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $first['endDate'] . " at " . $first['endTime'] . "</td>\r\n";
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $first['description'] . "</td>\r\n";
				$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $first['impact'] . "</td>\r\n";
				$message .= "</tr>\r\n";

				while ($RFC = $unscheduledRFCQuery->fetch(PDO::FETCH_ASSOC)) {
					$message .= "<tr>\r\n";
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $RFC['ticketNumber'] . "</td>\r\n";
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $RFC['engineerName'] . "</td>\r\n";
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $RFC['startDate'] . " at " . $RFC['startTime'] . "</td>\r\n";
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $RFC['endDate'] . " at " . $RFC['endTime'] . "</td>\r\n";
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $RFC['description'] . "</td>\r\n";
					$message .= "<td style='border: 1px solid #BBBBBB; vertical-align: top; padding: 5px;'>" . $RFC['impact'] . "</td>\r\n";
					$message .= "</tr>\r\n";
				}//while
				$message .= "</table>";
			} else {
				$message .= "There were no unscheduled RFCs since the last report.";
			}//if
		}//if
	
	  	$headers = "From: ".getenv("OPERATIONS_CENTER_EMAIL")."\r\n";
	  	$headers .= "Return-Path: ".getenv("OPS_MANAGERS_EMAIL_ADDRESS")."\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html;\r\n";
	
		if ($emailTo != '')
		{
		  	if (mail( $emailTo, $subject, wordwrap($message, 70), $headers )) 
			{
				echo("\n Email Successfully Sent. \n $emailTo \n $subject \n $message \n $headers");
		  	} 
			else 
			{
			  	echo ("Message Delivery Failed.");
		  	}
		} 
		else 
		{
			echo 'No message sent because there was no one to send an email to.';
		}
	}//SendEmail

		SendEmail($grp, $mess, $subj);

?>

