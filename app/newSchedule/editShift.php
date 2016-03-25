<?php 
require('../includes/includeMeBlank.php');
//This file is called via Ajax to edit a shift
//Based on what Mika wants to pass in, we could take a JSON or just the normal $_GET[] varibles
$shiftArray = array();
if(isset($_POST['JSON'])){
	$shiftArray = json_decode($_POST['JSON'],true);
} else {
	$shiftArray = json_decode($_GET['JSON'],true);
}

foreach($shiftArray as $shift){

	$id = $shift['ID'];
	$startTime = $shift['startTime'];
	$startDate = $shift['startDate'];
	$endTime = $shift['endTime'];
	$endDate = $shift['endDate'];
	$hourType = $shift['hourType'];
	if (isset($shift['defaultID']) && $shift['defaultID']!= 'null')
		$defaultID = $shift['defaultID'];
	else
		$defaultID = null;
	$hourTotal = computeHourTotal($startDate.' '.$startTime,$endDate.' '.$endTime);
	$trade = '';
	if(isset($shift['trade']))
		$trade = $shift['trade'];
	$posted = '';
	if(isset($shift['posted'])){
		$posted = $shift['posted'];
    }
	else{
   	    $postQuery = $db->prepare("SELECT employeeAreas.postSchedulesByDefault FROM employeeAreas LEFT JOIN scheduleWeekly ON employeeAreas.ID=scheduleWeekly.area WHERE scheduleWeekly.ID=:id");
		$postQuery->execute(array(':id'=> $id));
		$posted = $postQuery->fetch()->postSchedulesByDefault;
	}
	try {
		$updateQuery = $db->prepare("UPDATE `scheduleWeekly` SET startTime = :startTime, startDate = :startDate, endTime = :endTime, endDate = :endDate, hourType = :hourType, hourTotal = :hourTotal, trade = :trade, posted = :posted, defaultID = :defaultID WHERE ID = :id AND `deleted`=0");
		$updateQuery->execute(array(':startTime' => $startTime.':00', ':startDate'  => $startDate, ':endTime' => $endTime.':00', ':endDate' => $endDate, ':hourType' => $hourType, ':hourTotal' => $hourTotal, ':trade' => $trade, ':posted' => $posted, ':defaultID' => $defaultID, ':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
}


function computeHourTotal($startTime,$endTime){
	global $db;
	try {
		$hoursQuery = $db->prepare("SELECT TIME_TO_SEC(TIMEDIFF(:end, :start))/3600 as hours");
		$hoursQuery->execute(array(':end' => $endTime.':00', ':start' => $startTime.':00'));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$data = $hoursQuery->fetch(PDO::FETCH_ASSOC); 
	return $data['hours'];
}

?>
