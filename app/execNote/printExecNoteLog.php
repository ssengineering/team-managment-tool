<?php
require('../includes/includeMeBlank.php');

// SET SEARCH CRITERIA
$ticket = '';
$fromDate = '';
$toDate = '';
$ic = '';
$description = '';
if (isset($_GET['searchTicket']))
{
	$ticket = $_GET['searchTicket'];
}
if (isset($_GET['startDate']))
{
	$fromDate = $_GET['startDate'];
}
if (isset($_GET['endDate']))
{
	$toDate = $_GET['endDate'];
}
if (isset($_GET['ic']))
{
	$ic = $_GET['ic'];
	if ($ic == '[ Any ]') $ic = '';
}
if (isset($_GET['description']))
{
	$description = $_GET['description'];
}

// QUERY 'executiveNotification' AND 'executiveNotificationUpdate' FOR ALL EXEC NOTES AND UPDATES WITHIN THE DESIRED TIME SPAN, ETC.
try {
	$notificationQuery = $db->prepare("SELECT `ID`, `subject`, `ticketNum`, `startDate`, `startTime`, `endDate`, `endTime`, `priority`, `executiveNotification`.`submitter` AS execNoteSubmitter, `description`, `updateID`, `updateText`, `date`, `time`, `history`.`submitter` AS historySubmitter, `type` FROM `executiveNotification` LEFT JOIN `executiveNotificationUpdate` AS history ON `ID` = `execNoteID` WHERE `ticketNum` LIKE :ticket AND (`executiveNotification`.`submitter` LIKE :ic OR `history`.`submitter` LIKE :ic1 OR `incidentCoord` LIKE :ic2) AND `startDate` >= :from AND `startDate` <= :to AND (`description` LIKE :description OR `subject` LIKE :description1) ORDER BY `ID`");
	$notificationQuery->execute(array(':ticket' => '%'.$ticket.'%', ':ic' => '%'.$ic.'%', ':ic1' => '%'.$ic.'%', ':ic2' => '%'.$ic.'%', ':from' => $fromDate, ':to' => $toDate, ':description' => '%'.$description.'%', ':description1' => '%'.$description.'%'));
} catch(PDOException $e) {
	exit("error in query");
}
$lastId = '';

$resultArray = $notificationQuery->fetchAll(PDO::FETCH_ASSOC);
// CREATE THE "<th>" DIVS FOR EXEC NOTES
echo "<div id='execNoteTh' class='execNoteContainer'><div class='execNote'><div class='priority divTh'>Priority</div><div class='ticket divTh'>Ticket</div><div class='subject divTh'>Subject</div><div class='start divTh'>Start</div><div class='end divTh'>End</div></div></div>";

echo "<div class='clearMe'></div>";

//while ($execNote = $notificationQuery->fetch(PDO::FETCH_ASSOC))
for($i = 0; $i < count($resultArray); $i++)
{
	$execNote = $resultArray[$i];
// SET GENERAL EXEC NOTE INFO TO BE PRINTED
	$id = $execNote['ID'];
	if ($id == '') $id = '&nbsp;';
	$priority = $execNote['priority'];
	if ($priority == '') $priority = '&nbsp;';
	$ticket = $execNote['ticketNum'];
	if ($ticket == '') $ticket = '&nbsp;';
	$subject = $execNote['subject'];
	if ($subject == '') $subject = '&nbsp;';
	$start = $execNote['startDate'] . '&nbsp;' . $execNote['startTime'];
	$end = $execNote['endDate'] . '&nbsp;' . $execNote['endTime'];
	$execNoteSubmitter = nameByNetId($execNote['execNoteSubmitter']);
	if ($execNoteSubmitter == '') $execNoteSubmitter = '&nbsp;';
	
// IF THIS IS A NEW SET OF EXEC NOTE UPDATES PRINT THE EXEC NOTE DIV WITH PRIORITY ETC.
	if ($execNote['ID'] != $lastId)
	{
		echo "<div id='$id' class='execNoteContainer'><div id='execNote$id' class='execNote' onclick='viewExecNote(this.parentNode.id)'><div class='priority divTd'>▹ P$priority</div><div class='ticket divTd'>$ticket</div><div class='subject divTd'>$subject</div><div class='start divTd'>$start</div><div class='end divTd'>$end</div></div>";
		echo "<div class='execNoteHistory history$id' style='display: none;'><div style='border: 1px solid white;' class='priority divTd'>&nbsp;</div><div class='type divTh'>Type</div><div class='timestamp divTh'>Timestamp</div><div class='text divTh'>Text</div><div class='submitter divTh'>Submitter</div></div><div class='clearMe'></div>";
		
// NOW PRINT THE EXEC NOTE DETAILS FROM WHEN IT WAS FIRST OPENED AS IF THEY WERE AN UPDATE MADE TO THE P1
		$description = $execNote['description'];
		if ($description == '') $description = '&nbsp;';
		echo "<div class='execNoteHistory history${execNote['ID']}' style='display: none;'>";
		echo "<div style='border: 1px solid white;' class='priority divTd'>&nbsp;</div><div class='type divTd'>New</div><div class='timestamp divTd'>$start</div><div class='text divTd'>DESCRIPTION: $description</div><div class='submitter divTd'>$execNoteSubmitter</div>";
		echo '</div>';
	}

// CHECK IF AN UPDATE FOR THE EXECUTIVE NOTIFICATION EXISTS IF SO PRINT THE UPDATE INFO
	echo "<div class='execNoteHistory history${execNote['ID']}' style='display: none;'>";
	if ($execNote['updateID'] != '')
	{
// SET EXEC NOTE UPDATE INFO
		$type = $execNote['type'];
		if ($type == '') $type = '&nbsp;';
		$text = $execNote['updateText'];
		if ($text == '') $text = '&nbsp;';
		$submitter = nameByNetId($execNote['historySubmitter']);
		if ($submitter == '') $submitter = '&nbsp;';
		$timestamp = $execNote['date'] . '&nbsp;' . $execNote['time'];

		echo "<div style='border: 1px solid white;' class='priority divTd'>&nbsp;</div><div class='type divTd'>$type</div><div class='timestamp divTd'>$timestamp</div><div class='text divTd'>$text</div><div class='submitter divTd'>$submitter</div>";
	}
	else
	{
		echo "<div style='border: 1px solid white;' class='priority divTd'>&nbsp;</div><div style='width: 90.8%; text-align: center;' class='divTd'>No updates were made to this Executive Notification.</div><div class='clearMe'></div>";
	}
	echo '</div>';

// CHECK FOR NEXT ROW'S ID TO SEE IF THE ECAPSULATING DIV FOR ALL EXEC NOTE UPDATES OUGHT TO BE CLOSED
	if(array_key_exists($i+1, $resultArray)) {
		$nextRow = $resultArray[$i+1];
		if ($execNote['ID'] != $nextRow['ID'])
		{
			echo "<div class='execNoteHistory history${execNote['ID']}' style='display: none;'><div style='border: 1px solid white;' class='priority divTd'>&nbsp;</div></div><div class='clearMe'></div>";
			echo "</div>";
		}
		echo '<div class="clearMe"></div>';
	}
// SET THE CURRENT ROW ID AS THE LAST ROW ID TO SEE IF THE GENERAL EXEC NOTE INFO NEEDS TO BE PRINTED
	$lastId = $execNote['ID'];
}

?>
