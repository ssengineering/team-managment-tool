<?php
require('../includes/includeMeBlank.php');

$shiftArray = array();
if(isset($_POST['JSON'])){
	$shiftArray = json_decode($_POST['JSON'],true);
}else{
	$shiftArray = json_decode($_GET['JSON'],true);
}

$defaultStartDate = $shiftArray[0]['startDate'];
$defaultStartTime = $shiftArray[0]['startTime'];
$defaultEndDate = $shiftArray[0]['endDate'];
$defaultEndTime = $shiftArray[0]['endTime'];
$defaultHourType = $shiftArray[0]['hourType'];
$defaultEmployee = $shiftArray[0]['employee'];

$startTime = $shiftArray[1]['startTime'];
$endTime = $shiftArray[1]['endTime'];
$startDate = $shiftArray[1]['startDate'];
$endDate = $shiftArray[1]['endDate'];
$employee = $shiftArray[1]['employee'];
$hourType = $shiftArray[1]['hourType'];

$conflictQueryString = "UPDATE `scheduleWeekly` SET `deleted`=1 WHERE `employee` = :employee AND
		(
			DAYOFWEEK(`startDate`) = :defaultStartDate AND
			DAYOFWEEK(`endDate`) = :defaultEndDate AND
			`startTime` = :defaultStartTime AND
			`endTime` = :defaultEndTime AND
			`hourType` = :type
		) AND
		(
			( CONCAT(`startDate`, ' ', `startTime`) <=  :start AND CONCAT(`endDate`, ' ', `endTime`) > :start1 )
			OR ( CONCAT(`startDate`, ' ', `startTime`) < :end AND CONCAT(`endDate`, ' ', `endTime`) >= :end1 )
			OR ( CONCAT(`startDate`, ' ', `startTime`) > :start2 AND CONCAT(`endDate`, ' ', `endTime`) < :end2 )
		)";
$conflictQueryParams = array(':employee' => $employee, ':defaultStartDate' => $defaultStartDate, ':defaultEndDate' => $defaultEndDate, ':defaultStartTime' => $defaultStartTime, ':defaultEndTime' => $defaultEndTime, ':type' => $defaultHourType, ':start' => $startDate." ".$startTime, ':start1' => $startDate." ".$startTime, ':end' => $endDate." ".$endTime, ':end1' => $endDate." ".$endTime, ':start2' => $startDate." ".$startTime, ':end2' => $endDate." ".$endTime);
try {
	$conflictQuery = $db->prepare($conflictQueryString);
	$conflictQuery->execute($conflictQueryParams);
} catch(PDOException $e) {
	exit("error in query");
}
