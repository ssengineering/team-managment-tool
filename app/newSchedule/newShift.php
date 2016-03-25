<?php
require_once('../includes/includeMeBlank.php');
//This file takes in information and creates a new shift in the Weekly table.

if (!function_exists('computeHourTotal'))
{
	function computeHourTotal($startTime,$endTime)
	{
		global $db;
		try {
			$timeQuery = $db->prepare("SELECT TIME_TO_SEC(TIMEDIFF(:end, :start))/3600 as hours");
			$timeQuery->execute(array(':end' => $endTime.":00", ':start' => $startTime.":00"));
		} catch(PDOException $e) {
			exit("1error in query");
		}
		$data = $timeQuery->fetch(PDO::FETCH_ASSOC);
		return $data['hours'];
	}
}

$shiftArray = array();
if(isset($_POST['JSON'])){
	$shiftArray = json_decode($_POST['JSON'],true);
}else{
	$shiftArray = json_decode($_GET['JSON'],true);
}

$failedInserts = array();
$failedCheck = 0;
foreach($shiftArray as $shift){

	$startTime = $shift['startTime'];
	$endTime = $shift['endTime'];
	$startDate = $shift['startDate'];
	$endDate = $shift['endDate'];
	$employee = $shift['employee'];
	$hourType = $shift['hourType'];
	if (isset($shift['area']) && $shift['area'] != '' && $shift['area'] != 'null')
	{
		if (isset($area))
		{
			$oldArea = $area;
		}
		$area = $shift['area'];
	}

	if (isset($shift['defaultID']) && $shift['defaultID'] != '' && $shift['defaultID']!= 'null')
		$defaultID = $shift['defaultID'];
	else
		$defaultID = NULL;
	$hourTotal = computeHourTotal($startDate.' '.$startTime,$endDate.' '.$endTime);
	try {
		$scheduleQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE `employee` = :employee AND `deleted`=0 AND 
		(
			( CONCAT(`startDate`, ' ', `startTime`) <=  :time0 AND CONCAT(`endDate`, ' ', `endTime`) > :time1 )
			OR ( CONCAT(`startDate`, ' ', `startTime`) < :time2 AND CONCAT(`endDate`, ' ', `endTime`) >= :time3 )
			OR ( CONCAT(`startDate`, ' ', `startTime`) > :time4 AND CONCAT(`endDate`, ' ', `endTime`) < :time5 )
		)");
		$scheduleQuery->execute(array(':employee' => $employee, ':time0' => $startDate.' '.$startTime.":00", ':time1' => $startDate.' '.$startTime.":00", ':time2' => $endDate.' '.$endTime.":00", ':time3' => $endDate.' '.$endTime.":00", ':time4' => $startDate.' '.$startTime.":00", ':time5' => $endDate.' '.$endTime.":00"));
	} catch(PDOException $e) {
		exit("2error in query");
	}
	if($firstShift = $scheduleQuery->fetch(PDO::FETCH_ASSOC)) {
		$failedCheck = 1;
		$failedInserts[] = $firstShift;
		while($conflictingShift = $scheduleQuery->fetch(PDO::FETCH_ASSOC))
		{
			$failedInserts[] = $conflictingShift;
		}
	}else{
		try {
			$defaultQuery = $db->prepare("SELECT `postSchedulesByDefault` FROM `employeeAreas` WHERE `ID` = :area");
			$defaultQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("3error in query");
		}
	    $postByDefault = $defaultQuery->fetch(PDO::FETCH_ASSOC);
	    $postByDefault = (int) $postByDefault['postSchedulesByDefault'];
	    if (!$postByDefault)
	    {
			try {
				$postQuery = $db->prepare("SELECT `post` FROM `schedulePosting` WHERE `area` = :area AND DATE_ADD(`weekStart`, INTERVAL 1 WEEK) > :start AND :start1 >= `weekStart`");
				$postQuery->execute(array(':area' => $area, ':start' => $startDate, ':start1' => $startDate));
			} catch(PDOException $e) {
				exit("4error in query");
			}
	        $isPosted = $postQuery->fetch(PDO::FETCH_ASSOC);
	        $isPosted = (int) $isPosted['post'];
	        if ($isPosted)
	        {
	            $posted = 1;
	        }
	        else
	        {
	            $posted = 0;
	        }
	    }
	    else
	    {
	        $posted = 1;
	    }
		try {
			$insertQuery = $db->prepare("INSERT INTO `scheduleWeekly` (employee,hourType,startTime,startDate,endTime,endDate,hourTotal,area,defaultID,posted,guid) VALUES(:employee,:type,:start,:startDate,:end,:endDate,:total,:area,:default,:posted,:guid)");
			$insertQuery->execute(array(':employee' => $employee, ':type' => $hourType, ':start' => $startTime.':00', ':startDate' => $startDate, ':end' => $endTime.':00', ':endDate' => $endDate, ':total' => $hourTotal, ':area' => $area, ':default' => $defaultID, ':posted' => $posted, ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("5error in query");
		}
	}
	
	if (isset($oldArea))
	{
		$area = $oldArea;
	}
}
if($failedCheck){
	echo json_encode($failedInserts);
}else {
	echo "VALID";
}
?>
