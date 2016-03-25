<?php require_once('../includes/includeMeBlank.php');

    $date = $_GET['date']; //This is the current date in yyyy-mm-dd format
    $time = $_GET['time']; //This is the current hour value in 24 hour time
    $values = explode(',',$_GET['values']); //These are the hourTypes in the Database
	$employees_post = explode(',',$_GET['employees']); //This is the list of employees show if they have hours
	if (isset($_GET['unposted']) && $_GET['unposted'] == '1')
	{
		$showUnposted = true;
	}
	else
	{
		$showUnposted = false;
	}
	$displayHours = getDisplayHours($time);

	if (!isSchedulePosted($date)) echo "<h3 align='center' style='color:red'>Schedule For Week Not Yet Posted</h3>";

    $currentDate = date("Y-m-d");
    $currentHour = date("G");
    $currentMinute = date("i");
    $currentTime = $currentHour;
    if ($currentMinute / 60 >= $hourSize) $currentTime += $hourSize;

    $prevDate = date("Y-m-d", strtotime($date." -1 day"));
    $nextDate = date("Y-m-d", strtotime($date." +1 day"));

    // Starting hour values
    $hourTimes = array($time - $hourSize, $time, $time + $hourSize);

    // Dates
    $hourDays = array();
    // Full day schedules
    $hourSchedules = array();

    // This code allows for roll-over hours, either at the beginning or the end of the day.
    if ($hourTimes[0] < 0) {

	    $hourTimes[0] += 24;
	    $hourDays[] = $prevDate;
    }
    else {

	    $hourDays[] = $date;
    }


    $hourDays[] = $date;

    if ($hourTimes[2] >= 24) {

	    $hourTimes[2] -= 24;
	    $hourDays[] = $nextDate;
    }
    else {
	    $hourDays[] = $date;
    }

    echo "<table class='schedule'><tbody>";
        // header stuff
        echo "<tr><th class='hourType'>Shift</th>";

        for ($hour = 0; $hour < count($hourTimes); $hour++) {
            if ($hourTimes[$hour] == $currentTime && $hourDays[$hour] == $currentDate)
                //echo "<th class='schedule'><span style='color:green'>".hourToTime($hourTimes[$hour])." - ".hourToTime($hourTimes[$hour]+$hourSize)."</span></th>";
            	echo "<th class='schedule' style='background:#c0c0c0'>".hourToTime($hourTimes[$hour])." - ".hourToTime($hourTimes[$hour]+$hourSize)."</th>";
            else
                echo "<th class='schedule'>".hourToTime($hourTimes[$hour])." - ".hourToTime($hourTimes[$hour]+$hourSize)."</th>";	
        }

        echo "</tr>";

        // hour types and names
		try {
			$hourTypesQuery = $db->prepare("SELECT ID,value,name,color FROM scheduleHourTypes WHERE area=:area AND `deleted` = 0");
			$hourTypesQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
        while($row = $hourTypesQuery->fetch(PDO::FETCH_ASSOC))
        {
        	if (!in_array($row['ID'], $values)) continue;

            echo "<tr>";

                // hour type cell
	            echo "<td class='hourType' style='background-color:".$row['color']."'>".$row["name"]."</td>";

                // name cell
                for ($hour = 0; $hour < 3; $hour++) {
	                if ($hourTimes[$hour] == $currentTime && $hourDays[$hour] == $currentDate){
		                echo "<td class='schedule' style='background:#f0f0e8'>";
		            }else{
		            	echo "<td class='schedule'>";
					}
					if($displayHours[($hour+1)] == "00:00" && $hour == 0){
						$curShifts = getShifts($displayHours[$hour],$displayHours[($hour+1)],$row['ID'],date("Y-m-d",strtotime($date."-1 day")));
					} else if($displayHours[$hour] == "00:00" && $hour == 2){
						$curShifts = getShifts($displayHours[$hour],$displayHours[($hour+1)],$row['ID'],date("Y-m-d",strtotime($date."+1 day")));
					} else {
						$curShifts = getShifts($displayHours[$hour],$displayHours[($hour+1)],$row['ID'],$date);
					}
					foreach($curShifts as $cur){
						if(in_array($cur['employee'],$employees_post)){
							$name =  nameByNetId($cur['employee']);
							$start = checkStartShift($cur, $displayHours[$hour]);
							$end = checkEndShift($cur, $displayHours[($hour+1)]);
							// Starting and ending shift
		                    if ($start && $end) {
		                        echo "<span style='color:green'>".$name."</span>";
		                    }
		                    // Starting shift
		                    else if ($start) {
		                        echo "<span style='color:green'>".$name."</span>";
		                    }
		                    // Ending shift
		                    else if ($end) {
		                        echo "<span style='color:red'>".$name."</span>";
		                    }
		                    // Normal hour
		                    else{
			                    echo $name;
		                    }
		                    if ($start) {
		                        if (can("access", "033e3c00-4989-4895-a4d5-a059984f7997")/*employeePerformance resource*/) {
		                        	echo " <a href='https://".$_SERVER['SERVER_NAME']."/performance/tardy.php?employee=".$cur['employee']."&startTime=".$hourTimes[$hour]."' >(T)</a>";
		                        	echo " <a href='https://".$_SERVER['SERVER_NAME']."/performance/absence.php?employee=".$cur['employee']."&startTime=".$hourTimes[$hour]."'>(A)</a>";
		                        }
		                        echo " (".date("g:ia",strtotime($cur['startTime']))." - ".date("g:ia",strtotime($cur['endTime'])).")";
		                    }
		                    echo "<br>";
						}
					}

					echo "</td>";

                }
            echo "</tr>";
        }

function getDisplayHours($startHour){
	global $hourSize;
	$hours[] = array();
	$hours[0] = hourToMilitary($startHour - $hourSize);
	if($hours[0] == "-01:00") $hours[0] = "23:00";
	$hours[1] = hourToMilitary($startHour);
	$hours[2] = hourToMilitary($startHour + $hourSize);
	$hours[3] = hourToMilitary($startHour + (2 * $hourSize));
	return $hours;
}

function getShifts($start, $end, $type, $date){
	global $area;
	global $showUnposted;
	global $db;
	$prevDay = date("Y-m-d",strtotime($date." -1 day"));
	$nextDay = date("Y-m-d",strtotime($date." +1 day"));
	// This is the query I wrote to help speed things up and simplify the query logic
	if (!isSchedulePosted($date) && !$showUnposted)
	{
		return array();
	}
	else
	{
		try {
			$scheduleQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE `area` = :area AND `deleted` = 0 AND `hourType` = :type AND `startDate` = :day
        		AND CONCAT(`startDate`, ' ', `startTime`) <= :start AND CONCAT(`endDate`, ' ', `endTime`) > :end");
			$scheduleQuery->execute(array(
				':area'  => $area,
				':type'  => $type,
				':day'   => $date,
				':start' => $date.' '.$start.':00',
				':end'   => $date.' '.$start.':00'));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$shifts = array();
		while($cur = $scheduleQuery->fetch(PDO::FETCH_ASSOC)) {
			$shifts[] = $cur;
		}
		return $shifts;
	}
}

function checkStartShift($data, $time){
	if(substr($data['startTime'],0,5) == $time){
		return 1;
	}
	return 0; 
}

function checkEndShift($data, $time){
	if(substr($data['endTime'],0,5) == $time){
		return 1;
	}
	return 0; 

}
?>
