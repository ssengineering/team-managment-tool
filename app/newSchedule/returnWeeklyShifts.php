<?php
require("../includes/includeMeBlank.php");
//This function returns all the shifts for an employee in a given period

$weekStart = $_GET['startDate'];
$employee = $_GET['employee'];
$areaInfo = getAreaInfo($area);
$numberOfSchedulableDays = ($areaInfo['endDay']-$areaInfo['startDay']);
if ( $numberOfSchedulableDays < 0 )
{
   $numberOfSchedulableDays +=7;
}

$numberOfDaysToReturn = $numberOfSchedulableDays;
if ( $numberOfDaysToReturn < 6 )
{
   $numberOfDaysToReturn = 6;
}
$endDate = date("Y-m-d",strtotime($weekStart."+$numberOfDaysToReturn days"));
$date = $weekStart;
$shiftArray = array();


if(!$areaInfo['postSchedulesByDefault']){
	//Check if the schedule has been posted.
	try {
		$postingQuery = $db->prepare("SELECT * FROM schedulePosting WHERE area = :area AND weekStart = :start");
		$postingQuery->execute(array(':area' => $area, ':start' => $weekStart));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($checkArray = $postingQuery->fetch(PDO::FETCH_ASSOC)) {
		if($checkArray['post'] == 0 && !can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/){
			echo json_encode($shiftArray);
			return;
		}	
	}else{
		echo json_encode($shiftArray);
		return;
	}
}



while($date <= $endDate)
{
	try {
		$scheduleQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE employee = :employee AND startDate = :day AND `deleted`=0");
		$scheduleQuery->execute(array(':employee' => $employee, ':day' => $date));
	} catch(PDOException $e) {
		exit("error in query");
	}
    while($shift = $scheduleQuery->fetch(PDO::FETCH_ASSOC))
    {
        $shiftArray[] = $shift;
    }
    
    $date = date("Y-m-d",strtotime($date."+1 day"));
}

echo json_encode($shiftArray);



function getAreaInfo($area){
	global $db;
	try {
		$areaQuery = $db->prepare("SELECT * FROM employeeAreas WHERE ID = :area");
		$areaQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$row = $areaQuery->fetch(PDO::FETCH_ASSOC);
	return $row;
}
?>

