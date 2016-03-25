<?php
require_once('../includes/includeMeBlank.php');
require_once('calculateDuration.php');
//This takes in information and creates a new default shift.
//if there are any conflicting shifts those are put into an array and returned to the caller.

if(isset($_POST['JSON'])){
	$shiftArray = json_decode($_POST['JSON'],true);
}else{
	$shiftArray = json_decode($_GET['JSON'],true);
}

$conflictArray = array();
foreach($shiftArray as $shift)
{
	$defaultId = $shift['ID'];
	$startTime = $shift['startTime'];
	$endTime = $shift['endTime'];
	$startDate = $shift['startDate'];
	$endDate = $shift['endDate'];
	$period = $shift['period'];
	$employee = $shift['employee'];
	$hourType = $shift['hourType'];
	
	// This line translates the start date back into our websites standard of Saturday=0, Sunday=1, . . . Friday=6
	// Just for determining duration, the $startDate is used for all other instances
	if ($startDate != '7')
	{
	    $pretendStartDate = $startDate;
	}
	else
	{
	    $pretendStartDate = '0';
	}
	if ($endDate == '7' && $startDate == '7')
	{
	    $pretendEndDate = '0';
	}
	else
	{
	    $pretendEndDate = $endDate;
	}
	$hourTotal = calculateDuration('2011-01-1'.$pretendStartDate.' '.$startTime.':00','2011-01-1'.$pretendEndDate.' '.$endTime.':00');
	try {
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE ID=:period");
		$semestersQuery->execute(array(':period' => $period));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$semester = $semestersQuery->fetch(PDO::FETCH_ASSOC);
	$periodStart = date('Y-m-d H:i:s',strtotime("today"));
	// This seems a bit strange but we add the day because the entire day is considered part of the current period so the truth is the period ends at the end of the end date (i.e. the start of the next day)
   // Also I didn't just add 86400000 milliseconds (i.e. the duration of the average day in milliseconds) because due to daylight savings some days are longer and some days are shorter.
   $periodEnd = Date('Y-m-d', strtotime('+1 day', strtotime($semester['endDate']))).' 00:00:00';

	// Check to see if the period's start date is a future date, if so use that as the period start
	// If not then use today's date as the start of the period for conflict checks.
	if ( strtotime($semester['startDate']) > strtotime($periodStart) )
	{
		$periodStart = $semester['startDate'].' 00:00:00';
	}

	// If today's date is greater than the end of the period don't look for conflicts.
	if ( strtotime($periodStart) < strtotime($periodEnd) )
	{
		// Check for any possibly conflicting shifts in the weekly schedule and delete them
		$conflictQueryString = 
<<<CONFLICTSEARCH
SELECT weekly.*
FROM `scheduleWeekly` AS `weekly`
WHERE CONCAT( weekly.`startDate` , ' ', weekly.`startTime` ) >= :periodStart
AND CONCAT( weekly.`endDate` , ' ', weekly.`endTime` ) <= :periodEnd
AND `employee` = :employee
AND (
(
CONCAT( '2011-01-1', DAYOFWEEK( weekly.`startDate` ) , ' ', weekly.`startTime` ) <= :time0 
AND CONCAT( '2011-01-1', DAYOFWEEK( weekly.`endDate` ) , ' ', weekly.`endTime` ) > :time1
)
OR (
CONCAT( '2011-01-1', DAYOFWEEK( weekly.`startDate` ) , ' ', weekly.`startTime` ) < :time2
AND CONCAT( '2011-01-1', DAYOFWEEK(weekly.`endDate`) , ' ', weekly.`endTime` ) >= :time3
)
OR (
CONCAT( '2011-01-1', DAYOFWEEK(weekly.`startDate`) , ' ', weekly.`startTime` ) > :time4
AND CONCAT( '2011-01-1', DAYOFWEEK(weekly.`endDate`) , ' ', weekly.`endTime` ) < :time5
)
)
AND `deleted`=0
AND weekly.`defaultID` IS NULL
CONFLICTSEARCH;
$conflictQueryParams = array(':periodStart' => $periodStart, ':periodEnd' => $periodEnd, ':employee' => $employee, ':time0' => '2011-01-1'.$startDate.' '.$startTime, ':time1' => '2011-01-1'.$startDate.' '.$startTime, ':time2' => '2011-01-1'.$endDate.' '.$endTime, ':time3' => '2011-01-1'.$endDate.' '.$endTime, ':time4' => '2011-01-1'.$startDate.' '.$startTime, ':time5' => '2011-01-1'.$endDate.' '.$endTime);
		try {
			$conflictQuery = $db->prepare($conflictQueryString);
			$conflictQuery->execute($conflictQueryParams);
		} catch(PDOException $e) {
			exit("error in query");
		}
		while ($conflictingShift = $conflictQuery->fetch(PDO::FETCH_ASSOC))
		{
			if($conflictingShift['endTime'] != $startTime.":00"){
				$conflictArray[] = $conflictingShift;
				try {
					$updateQuery = $db->prepare("UPDATE `scheduleWeekly` SET `deleted`=1 WHERE ID = :id");
					$updateQuery->execute(array(':id' => $conflictingShift['ID']));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
	}

	// Insert default schedule and all weekly instances as a transaction
	$failure = false;
	$db->beginTransaction();

	// We delete any old instances of the default shift
	try {
		$updateQuery = $db->prepare("UPDATE `scheduleWeekly` SET `deleted`=1 WHERE `defaultID`=:defaultId AND CONCAT(`endDate`, ' ', `endTime`) > NOW()");
		$updateQuery->execute(array(':defaultId' => $defaultId));
		$defaultUpdateQuery = $db->prepare("UPDATE `scheduleDefault` SET employee = :employee, hourType = :hourType, period = :period, startTime = :startTime, startDate = :startDate, endTime = :endTime, endDate = :endDate, hourTotal = :hourTotal, area = :area WHERE ID = :id");
		$defaultUpdateQuery->execute(array(':employee' => $employee, ':hourType' => $hourType, ':period' => $period, ':startTime' => $startTime, ':startDate' => $startDate, ':endTime' => $endTime, ':endDate' => $endDate, ':hourTotal' => $hourTotal, ':area' => $area, ':id' => $defaultId));
	} catch(PDOException $e) {
		$failure = true;
		$db->rollBack();
	}

	// Insert instances of default shift into the weekly schedule
	$insertQueryString = "INSERT INTO `scheduleWeekly` (`employee`, `startTime`, `startDate`, `endTime`, `endDate`, `hourType`, `hourTotal`, `defaultID`, `area`, `guid`) VALUES";

	// Determine start date of first instance (I am comparing the day of the week of the default
	// shift to the day of the week of the period start and incrementing until we find the first
	// valid real date value for an instance of the default shift)
	// p.p.s the "date('w', strtotime($periodStart))+1" has a one added at the end to convert
	// between the way php handles day of week values (i.e. 0=Sunday ... 6=Saturday) and mysql
	$instanceStart = $periodStart;
	while ( date('w', strtotime($instanceStart))+1 != $startDate )
	{
		// Yeah, I probably should have just been working with a Unix timestamp... meh...
		$instanceStart = date('Y-m-d H:i:00', strtotime('+1 day', strtotime($instanceStart)));
	}

	// Get difference between startDate and endDate
	$startEndDifference = $endDate - (date('w', strtotime($instanceStart))+1);

	// I know. I am asking myself the same question. Why do I still refuse to just make a unix
	// timestamp variable? (Shaking my head in both judgement and disappointment) Some people
	// just like the pain.
	$instanceEnd = date('Y-m-d H:i:00', strtotime('+'.$startEndDifference.' day', strtotime($instanceStart)));

	$i = 0;
	// Create values for insertion of all instances of the default shift spanning the period
	while ( strtotime($instanceStart) < strtotime($periodEnd) )
	{
		// If the start of the shift begins before the period ends, then end the shift at the end
		// of the period -- this is building in functionality for allowing cross-day shifts.
		if ( strtotime($instanceEnd) > strtotime($periodEnd) )
		{
			$instanceEnd = $periodEnd;
			$endTime = $periodEnd;
		}

		// If this is our first entry do not add a coma in front of our values, otherwise add it
		if ( strtotime($instanceStart) >= strtotime('+7 days', strtotime($periodStart)) )
			$insertQueryString .= ', ';
		$insertQueryString .= " (:employee".$i.",:startTime".$i.",:start".$i.",:endTime".$i.",:end".$i.",:type".$i.",:total".$i.",:default".$i.",:area".$i.",:guid".$i.") ";
		$insertQueryParams[':employee'.$i]  = $employee;
		$insertQueryParams[':startTime'.$i] = $startTime;
		$insertQueryParams[':start'.$i]     = $instanceStart;
		$insertQueryParams[':endTime'.$i]   = $endTime;
		$insertQueryParams[':end'.$i]       = $instanceEnd;
		$insertQueryParams[':type'.$i]      = $hourType;
		$insertQueryParams[':total'.$i]     = $hourTotal;
		$insertQueryParams[':default'.$i]   = $defaultId;
		$insertQueryParams[':area'.$i]      = $area;
		$insertQueryParams[':guid'.$i]      = newGuid();
		
		$instanceStart = date('Y-m-d H:i:00', strtotime('+7 days', strtotime($instanceStart)));
		$instanceEnd = date('Y-m-d H:i:00', strtotime('+7 days', strtotime($instanceEnd)));
		$i++;
	}
	try {
	$insertQuery = $db->prepare($insertQueryString);
	$success = $insertQuery->execute($insertQueryParams);
	} catch(PDOException $e) {

		$failure = true;		
		$db->rollBack();
	}

	if(!$failure){	$db->commit(); }

}


//END of propgation code.
if (count($conflictArray))
{
	echo json_encode($conflictArray);
}
else
{
	echo 'VALID';
}
?>
