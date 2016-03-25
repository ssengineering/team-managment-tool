<?php

	require_once('../includes/includeMeBlank.php');
	$date = $_GET['date']; //Current date
	$values = explode(',',$_GET['values']); //These are the hour values that are being shown.
	$employees = explode(',',$_GET['employees']); //Array of all the netId's selected to be shown.
	// Has someone with permissions to schedule asked to view the un-posted schedule
	if (isset($_GET['unposted']) && $_GET['unposted'] == '1')
	{
	    $showUnposted = true;
	}
	else
	{
	    $showUnposted = false;
	}
	if(isset($_GET['print']) && ($_GET['print'] == 1))
	{
		$printMode = true;
	}
	else
	{
		$printMode = false;
	}
	try {
		$hourTypesQuery = $db->prepare("SELECT ID,value,name,color FROM scheduleHourTypes WHERE area=:area AND `deleted` = 0");
		$hourTypesQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$hourTypes = array();

	$startTime = getStartTime();
	$endTime = getEndTime();

	if (!isSchedulePosted($date)) echo "<h3 align='center' style='color:red'>Schedule For Week Not Yet Posted</h3>";

	echo "<table class='schedule' id='sched'><thead><tr><th class='schedule_time'><b>Hours</b></th>";
	while ($row=$hourTypesQuery->fetch(PDO::FETCH_ASSOC)){
		$hourTypes[] = $row['ID'];

		if (!in_array($row['ID'], $values)) continue;

		echo "<th class='schedule' style='background-color:".$row['color']."'> <b> ".$row['name']."</b> </th>";
	}

	echo "</tr></thead><tbody>";
	for($hour=($startTime*1); $hour<=$endTime; $hour+=$hourSize) //This loops through all of the
	{
		echo "<tr class='schedule'><td class='schedule_time'>".hourToTime($hour)."</td>";
		foreach($hourTypes as $curType)
		{
			if (!in_array($curType, $values)) continue;

			echo "<td class='schedule'>";
			$shifts = getShifts(hourToMilitary($hour),hourToMilitary($hour+$hourSize),$curType,$date);
			foreach($shifts as $curShift)
			{
				$netId = $curShift['employee'];
				if (!in_array($netId, $employees)) continue;
				if (checkStartShift($curShift,$hour) && ($printMode == false))
				{
					echo "<span style='color:green'>".nameByNetId($netId)."</span>";
					if (can("access", "033e3c00-4989-4895-a4d5-a059984f7997")) //employeePerformance resource
					{
       		    		echo " <a href='https://".$_SERVER['SERVER_NAME']."/performance/tardy.php?employee=".$netId."&startTime=".$hour."' >(T)</a>";
           				echo " <a href='https://".$_SERVER['SERVER_NAME']."/performance/absence.php?employee=".$netId."&startTime=".$hour."'>(A)</a>";
			        }
  			    	echo " (".date("g:ia",strtotime($curShift['startTime']))." - ".date("g:ia",strtotime($curShift['endTime'])).")";
				}
				else if (checkEndShift($curShift,($hour+$hourSize)) && ($printMode == false))
				{
					echo "<span style='color:red'>".nameByNetId($netId)."</span>";
				}
				else
				{
					echo nameByNetId($netId);
				}
		    	echo "<br />";

			}
			echo "</td>";
		}
		echo "</tr>";
	}
	echo"</tbody></table>";



function getShifts($start, $end, $type, $date){
	global $area, $db, $showUnposted;
	$prevDay = date("Y-m-d",strtotime($date." -1 day"));
	$nextDay = date("Y-m-d",strtotime($date." +1 day"));
	
	// This is the query I wrote to simplify the logic of the query and help speed things up ~Mika
	if (!isSchedulePosted($date) && !$showUnposted)
	{
	    return array();
	}
	else
	{
		try {
			$scheduleQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE `area` = :area AND `deleted` = 0 AND `hourType` = :type AND `startDate` = :day AND CONCAT(`startDate`, ' ', `startTime`) <= :start AND CONCAT(`endDate`, ' ', `endTime`) > :end");
			$scheduleQuery->execute(array(':area' => $area, ':type' => $type, ':day' => $date, ':start' => $date.' '.$start.':00', ':end' => $date.' '.$start.':00'));
		} catch(PDOException $e) {
			exit("error in query");
		}
	    $shifts = array();
	    while($cur = $scheduleQuery->fetch(PDO::FETCH_ASSOC)){
		    $shifts[] = $cur;
	    }
	    return $shifts;
	}
}

function checkStartShift($data, $time){
	$time = hourToMilitary($time);
	if(substr($data['startTime'],0,5) == $time){
		return 1;
	}
	return 0;
}

function checkEndShift($data, $time){
	$time = hourToMilitary($time);
	if(substr($data['endTime'],0,5) == $time){
		return 1;
	}
	return 0;

}

?>
