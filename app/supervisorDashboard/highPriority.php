<?php

require('../includes/includeMeBlank.php');

function printHiPriTicket($incident)
{
global $passy;
global $env;
global $db;

// CHECK TO SEE IF THE TICKET ALREADY HAS AN EXECUTIVE NOTIFICATION ASSOCIATED WITH IT
		try {
			$notificationQuery = $db->prepare("SELECT `ID`, `startDate`, `startTime`, `status` FROM executiveNotification WHERE `ticketNum` = :number");
			$notificationQuery->execute(array(':number' => $incident->number));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$execCheck = $notificationQuery->fetch(PDO::FETCH_ASSOC);
		if ($incident->priority == '1')
		{
			try {
				$smsQuery = $db->prepare("Select * from `executiveNotificationSMS` where `ticket` = :number");
				$smsQuery->execute(array(':number' => $incident->number));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$smsCheck = $smsQuery->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$smsCheck = 'unsent';
		}

		// IF THE TICKET DOES HAVE AN EXEC NOTE ALREADY
		if (isset($execCheck['ID']))
		{
			// IF THE TICKET HAS AN EXEC NOTE THAT HAS NOT BEEN CLOSED
			if ($execCheck['status'] == '0')
			{
				try {
					$updateQuery = $db->prepare("SELECT date, time FROM `executiveNotificationUpdate` WHERE `execNoteID` = :id ORDER BY date DESC, time DESC LIMIT 1");
					$updateQuery->execute(array(':id' => $execCheck['ID']));
				} catch(PDOException $e) {
					exit("error in query");
				}
				$update = $updateQuery->fetch(PDO::FETCH_ASSOC);
				$currentTime = time();

				$created = strtotime($execCheck['startDate'].' '.$execCheck['startTime']);
				$createdEarliest = strtotime("+72 minutes", $created);
				$createdLatest = strtotime("+81 minutes", $created);

				$updated = strtotime($update['date'].' '.$update['time']);
				$updatedEarliest = strtotime("+72 minutes", $updated);
				$updatedLatest = strtotime("+81 minutes", $updated);

				// IF THE EXEC NOTE WAS LAST UPDATED MORE THAN AN HOUR AGO REMIND SUPERVISOR TO UPDATE EXEC NOTE
				if ($createdLatest > $currentTime && $currentTime > $createdEarliest && (isset($update['time'])))
				{
					echo "<div class='reminderAlert' title='Please Update the Executive Notification for $incident->number - $incident->short_description.'></div>";
				}
				else if ($updatedLatest > $currentTime && $currentTime > $updatedEarliest && (!isset($update['time'])))
				{
					echo "<div class='reminderAlert' title='Please Update the Executive Notification for $incident->number - $incident->short_description.'></div>";
				}

				$href = '../execNote/execNoteForm.php?type=Update&ticketId='.$incident->number;
				$href .= '&sysId='.$incident->sys_id;
				$href .= '&passy='.$passy;
				$title = 'Update or Close';
			}

			// IF THE TICKET HAS AN EXEC NOTE THAT IS ALREADY CLOSED
			else
			{
				$href = '../execNote/execNoteForm.php?type=Re-open&ticketId='.$incident->number;
				$href .= '&sysId='.$incident->sys_id;
				$href .= '&passy='.$passy;
				$title = 'Re-Open';
			}
		}

		// IF THE TICKET DOES NOT HAVE AN EXEC NOTE ALREADY
		else
		{
			$href = '../execNote/execNoteForm.php?ticketId='.$incident->number;
			$href .= '&priority='.$incident->priority;
			$href .= '&sysId='.$incident->sys_id;
			$href .= '&passy='.$passy;
			$title = 'New or New-Resolve';
		}

		echo '<tr>';
		echo '<td>'.$incident->priority.'</td>';
		$linkurl = getenv("SERVICE_NOW_STAGE_URL")."/incident.do?";
		if( $env==2 ){
			$linkurl = getenv("SERVICE_NOW_URL")."/incident.do?";
		}
		if (!can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource */{
			$href = $linkurl.'sys_id='.$incident->sys_id;
			$title= "View ticket";
		}

		echo '<td><a href="javascript:newwindow('."'".$linkurl.'sys_id='.$incident->sys_id."'".')">'.$incident->number.'</a></td>';
		echo '<td style="word-break: break-word;"><a href="javascript:newwindow('."'".$href."'".')" title="'.$title.'">'.$incident->short_description.'</a></td>';
		if ($incident->active)
		{
			echo '<td>Active</td>';
		}
		else
		{
			echo '<td>Closed</td>';
		}
		echo '</tr>';

		/** We need to look at deprecating this code **/

		if ($smsCheck != 'unsent' && !isset($smsCheck['ticket']))
		{
			try {
				$insertQuery = $db->prepare("INSERT INTO `executiveNotificationSMS`(`ticket`, `guid`) VALUES (:incident,:guid)");
				$insertQuery->execute(array(':incident' => $incident->number, ':guid' => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}
			if ($env == 2)
			{
				mail(getenv("HIGH_PRIORITY_TEXT"), 'P1 Alert', $incident->number.": \n".$incident->short_description, 'From: '.getenv("OPS_EMAIL_USERNAME").' <'.getenv("OPS_EMAIL_ADDRESS").'>'."\r\n");
			}
		}
}

// DECRYPT PASSWORD
$passy = $_GET['p'];
$password = passDecrypt($passy);

// ESTABLISH CONNECTION
try
{
	$soapurl = getenv("SERVICE_NOW_STAGE_URL")."/incident.do?WSDL";
	if( $env==2 ){
		$soapurl = getenv("SERVICE_NOW_URL")."/incident.do?WSDL";
	}
	$incidentClient = new SoapClient($soapurl, array('trace' => 1, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 'login' => $netID, 'password' => $password));

// GET HIGH PRIORITY TICKETS FROM THE PAST 12 HOURS
$resultSet = $incidentClient->getRecords(array('__encoded_query'=>'priority<=2^active=true^ORclosed_at>javascript:gs.hoursAgo(12)'));
// IF A HIGH PRIORITY TICKET EXISTS
if (isset($resultSet->getRecordsResult))
{

	// CREATE TABLE AND HEADERS
	echo '<table id="hiPriTable" class="sortable"><tr><th>Pri.</th><th>Ticket #</th><th>Description</th><th>State</th></tr>';
	if (is_array($resultSet->getRecordsResult))
	foreach($resultSet->getRecordsResult as $incident)
	{
		printHiPriTicket($incident);
	}
	else
	{
		printHiPriTicket($resultSet->getRecordsResult);
	}
	echo '</table>';
}

// IF NO HIGH PRIORITY TICKET EXISTS
else
{
	echo '<br /><div style="width: 100%; text-align: center; font-weight: bold; margin-bottom: 8px; font-size: 90%;"><table style="margin-left: auto; margin-right: auto;"><tr><th style="height: 2em; vertical-align: middle;">  There are currently no high priority tickets.  </th></tr></table></div>';
}

}
catch (Exception $E)
{
	echo '<div id="loginDiv" class="infoRequest"><p style="color:red; margin-left:10%;">The password you entered is not correct. Please try again.</p>';
    echo '<form method="post"><input style="margin-left:10%;" name="loginPass" id="login" type="password" /> <input type="submit" value="Authenticate" /></form></div>';
	return False;
}
?>
