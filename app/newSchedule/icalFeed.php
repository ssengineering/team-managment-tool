<?php
require('../includes/dbconnect.php');
require('../includes/php_header.php');

$netId = $_GET['netId'];
$excluded = array();

// Get area of employee for $netId
try {
	$employeeQuery = $db->prepare("SELECT * FROM `employee` WHERE `netID` = :netId");
	$employeeQuery->execute(array(':netId' => $netId));
} catch(PDOException $e) {
	exit("error in query");
}
$areaResult = $employeeQuery->fetch(PDO::FETCH_ASSOC);
$area = $areaResult['area'];

// Get the current "semester" and the next one and then use the current semester `startDate` as our temporary "period" `startDate` and the next "semester" `endDate` as our "period" `endDate`
// I'm doing this so that we don't end up not showing anything in the feed for future weeks when approaching the end of a "semester"
$periodQueryString = 
<<<SEMESTERS
SELECT min(`startDate`) AS `startDate`, max(`endDate`) AS `endDate`
FROM `scheduleSemesters`
WHERE `area`=:area1
AND
(
   (
      `startDate` <= NOW()
      AND
      `endDate` >= NOW()
   )
   OR
   (
      `endDate` >= (@next := (SELECT DATE_ADD(`endDate`, INTERVAL 1 DAY) AS `next` FROM `scheduleSemesters` WHERE `area`=:area2 AND `startDate` <= NOW() AND `endDate` >= NOW()))
      AND
      `startDate` <= @next
   )
)
SEMESTERS;

//Get the current period start and end dates
$periodQueryParams = array(':area1' => $area, ':area2' => $area);
try {
	$periodQuery = $db->prepare($periodQueryString);
	$periodQuery->execute($periodQueryParams);
} catch(PDOException $e) {
	exit("error in query");
}
$period = $periodQuery->fetch(PDO::FETCH_ASSOC);

$shiftsQueryParams = array(':netId' => $netId, ':start' => $period['startDate'], ':end' => $period['endDate']);
if ( isset($_GET['excluded']) )
{
	$excluded = " AND `scheduleHourTypes`.`ID` NOT IN (:excluded0";
	$excludedTypes = explode(',', $_GET['excluded']);
	$shiftsQueryParams[':excluded0'] = $excludedTypes[0];
	for($i=1; $i < count($excludedTypes); $i++) {
		$excluded .= ", :excluded".$i;
		$shiftsQueryParams[':excluded'.$i] = $excludedTypes[$i];
	}
	$excluded .= ")";
}
else
{
	$excluded = "";
}

// Get weekly shifts for the given period
$shiftsQueryString = 
<<<SHIFTQUERY
SELECT `scheduleWeekly`.*, `scheduleHourTypes`.`name`, `scheduleHourTypes`.`longName`, `scheduleHourTypes`.`color`, `scheduleHourTypes`.`ID` AS `hourTypeId`
FROM `scheduleWeekly`
JOIN `scheduleHourTypes` ON `hourType` = `scheduleHourTypes`.`ID`
LEFT JOIN `employeeAreas` ON `employeeAreas`.`ID`=`scheduleWeekly`.`area`
WHERE `employee`=:netId AND `startDate` >= :start AND 
`endDate` <= :end AND `scheduleWeekly`.`deleted`=0 
AND (`employeeAreas`.`postSchedulesByDefault`=1 OR `scheduleWeekly`.`posted`=1 
OR `scheduleHourTypes`.`selfSchedulable`=1)
{$excluded}
ORDER BY CONCAT(`scheduleWeekly`.`startDate`, ' ', `scheduleWeekly`.`startTime`)
SHIFTQUERY;
try {
	$shiftsQuery = $db->prepare($shiftsQueryString);
	$shiftsQuery->execute($shiftsQueryParams);
} catch(PDOException $e) {
	exit("error in query");
}

$calendarInfo =
<<<CALENDARINFO
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//{$_SERVER['SERVER_NAME']}/workcal//{$netId} v1.0//EN
X-WR-RELCALID:-//{$_SERVER['SERVER_NAME']}/workcal//{$netId} v1.0//EN
X-WR-CALDESC:Work Schedule for BYU OIT
X-WR-CALNAME:OIT Work Schedule

CALENDARINFO;

$now = date('Ymd\THis');

while ($shift = $shiftsQuery->fetch(PDO::FETCH_ASSOC))
{
	$start = new DateTime($shift['startDate'].' '.$shift['startTime']);
	$start->setTimezone(new DateTimeZone('UTC'));
	$startString = $start->format('Ymd\THis');
	$end = new DateTime($shift['endDate'].' '.$shift['endTime']);
	$end->setTimezone(new DateTimeZone('UTC'));
	$endString = $end->format('Ymd\THis');
	   
	   // Maybe change the description so that it works as a link back to the employee's schedule
	   // To make the change above worth the time, we would need to make CAS route back to the original URL entered by the user
		$calendarInfo .= 
<<<CALENDARINFO
BEGIN:VEVENT
UID:{$shift['ID']}@{$_SERVER['SERVER_NAME']}
DTSTAMP:{$now}
DESCRIPTION:https://{$_SERVER['SERVER_NAME']}/newSchedule/?date={$shift['startDate']}&employee={$netId}
DTSTART:{$startString}Z
DTEND:{$endString}Z
SUMMARY:{$shift['longName']}
END:VEVENT

CALENDARINFO;
}

$calendarInfo .= 
<<<CALENDARINFO
END:VCALENDAR
CALENDARINFO;

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=calendar.ics');
//echo $calendarDeleted."\n".$calendarInfo;
echo $calendarInfo;
exit;
?>
