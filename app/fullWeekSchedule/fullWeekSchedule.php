<?php
/*
 *	Name: fullWeekSchedule.php
 *	Application: Full Week Schedule
 *	Site: ops.byu.edu
 *	Author: Joshua Terrasas
 *
 *	Description: This is the file that is loaded
 *	into the index.php file, in the modeContent
 *	div. This file builds the calendar based on
 *	the settings selected in the index.php file.
 */

require ('../includes/includeMeBlank.php');
try {
	$timeQuery = $db->prepare("SELECT `startDay`, `endDay`, `startTime`, `endTime`, `hourSize` FROM `employeeAreas` WHERE `ID` = :area");
	$timeQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("1error in query");
}
$row = $timeQuery->fetch(PDO::FETCH_ASSOC); 

$areaStartDay = (float)$row['startDay'];
$areaEndDay = (float)$row['endDay'];
$areaStartTime = (float)$row['startTime'];
$areaEndTime = (float)$row['endTime'];
$areaHourSize = $row['hourSize'];
$areaTimeInterval = new DateInterval('PT' . ($areaHourSize  *  60) . 'M');

define("HOURS", "24");
define("DAYS", (string) $areaEndDay-$areaStartDay+1);
define("BUSINESS_START_DAY", "1");
define("BUSINESS_END_DAY", "6");
define("BUSINESS_START_TIME", "8");
define("BUSINESS_END_TIME", "17");
define("DEFAULT_MODE", "0");
define("WEEKLY_MODE", "1");

$schedule = array();
$employeeList = "";
$employees = explode(',', $_GET['employees']);
$prevNext = $_GET["prevNext"];
$periodDate = new DateTime($_GET['periodDate']);
//net ids and string parameter
$hourTypeList = "";
$hourTypes = explode(',', $_GET['hourTypes']);
//managers, supervisors, analyst hours...
$date = new DateTime($_GET['date']);

if (isset($_GET['mode']))
{
	$mode = $_GET['mode'];
}
else
{
	$mode = WEEKLY_MODE;
}

$date->modify('+' . $areaStartDay . ' day');

if ($mode == WEEKLY_MODE)
{
	$startDate = $date->format('Y-m-d');
	$date->modify('+' . (DAYS) . ' days');
	$endDate = $date->format('Y-m-d');
	$date->modify('-' . (DAYS) . ' days');
}
$timeQueryParams = array();
for ($i = 0; $i < count($employees); $i ++)
{
	if ($i == (count($employees)  -  1))
	{
		$employeeList .= ':e'.$i;
		$timeQueryParams[':e'.$i] = $employees[$i];
	}
	else
	{
		$employeeList .= ':e'.$i.',';
		$timeQueryParams[':e'.$i] = $employees[$i];
	}
}

for ($i = 0; $i < count($hourTypes); $i ++)
{
	if ($i == (count($hourTypes)  -  1))
	{
		$hourTypeList .= ':t'.$i;
		$timeQueryParams[':t'.$i] = $hourTypes[$i];
	}
	else
	{
		$hourTypeList .= ':t'.$i.',';
		$timeQueryParams[':t'.$i] = $hourTypes[$i];
	}
}

if ($mode == WEEKLY_MODE)
{
	$timeQueryParams[':start'] = $startDate;
	$timeQueryParams[':end']   = $endDate;
	$timeQueryParams[':area']  = $area;
	$timeQueryString = "SELECT employee, name, startDate, endDate, startTime, endTime, hourTotal FROM `scheduleWeekly` LEFT JOIN `scheduleHourTypes` ON scheduleHourTypes.ID = scheduleWeekly.hourType WHERE `employee` IN (".$employeeList.") AND `hourType` IN (".$hourTypeList.") AND (`startDate` >= :start AND `startDate` < :end) AND scheduleWeekly.area = :area AND scheduleWeekly.deleted != '1' ORDER BY scheduleHourTypes.name ASC";
}
else 
{
	//Default Mode query
	$timeQueryParams[':start'] = $periodDate->format('Y-m-d');
	$timeQueryParams[':end']   = $periodDate->format('Y-m-d');
	$timeQueryParams[':area']  = $area;
	$timeQueryString = "SELECT scheduleDefault.employee, scheduleHourTypes.name, scheduleDefault.startDate, scheduleDefault.endDate, scheduleDefault.startTime, scheduleDefault.endTime, scheduleDefault.hourTotal, scheduleSemesters.name AS period, scheduleSemesters.ID AS periodID FROM `scheduleDefault` LEFT JOIN `scheduleHourTypes` ON scheduleHourTypes.ID = scheduleDefault.hourType RIGHT JOIN `scheduleSemesters` ON scheduleDefault.period = scheduleSemesters.ID WHERE ( `employee` IN (".$employeeList.") OR `employee` IS NULL) AND (`hourType` IN (".$hourTypeList.") OR `hourType` IS NULL) AND (scheduleSemesters.startDate <= :start AND scheduleSemesters.endDate >= :end) AND scheduleSemesters.area = :area AND (scheduleDefault.deleted != '1' OR scheduleDefault.deleted IS NULL) ORDER BY scheduleHourTypes.name ASC";
}

try {
	$timeQuery = $db->prepare($timeQueryString);
	$timeQuery->execute($timeQueryParams);
} catch(PDOException $e) {
	exit("2error in query");
}

$row = $timeQuery->fetch();//test for results
//if no results are obtained after running either query above (ie. no employees scheduled during period), run the query bellow to retrieve the period for the date selected
if(($row == false) || (!isSchedulePosted($date->format('Y-m-d')) && !can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85"))) //schedule resource
{
	try {
		$timeQuery = $db->prepare("SELECT name AS period, ID AS periodID FROM scheduleSemesters WHERE scheduleSemesters.startDate <= :start AND scheduleSemesters.endDate >= :end AND `area`= :area");
		$timeQuery->execute(array(':start' => $periodDate->format('Y-m-d'), ':end' => $periodDate->format('Y-m-d'), ':area' => $area));
	} catch(PDOException $e) {
		exit("3error in query");
	}
} else {
	try {//reset the query after testing for results
		$timeQuery->execute($timeQueryParams);
	} catch(PDOException $e) {
		exit("4error in query");
	}
}

while ($row = $timeQuery->fetch(PDO::FETCH_ASSOC))
{
	$schedule[] = $row;
}
echo '<div id="calendar">
	<table>
		<th></th>';

for ($i = $areaStartDay; $i <= $areaEndDay; $i ++)
{
	if ($mode == WEEKLY_MODE)
	{
		echo '<th id="day' . $i . '">' . $date->format('l d') . '</th>';
	}
	else
	{
		echo '<th id="day' . $i . '">' . $date->format('l') . '</th>';
	}

	$date->modify('+1 day');
}

echo '<th></th>';

$date->modify('-' . DAYS . ' day');

//changes the date to match the start and end time for the area
$date->modify('+' . (string)((int) $areaStartTime ). ' hour'); 
$date->modify('+' . (string)(((float) $areaStartTime - floor ((float) $areaStartTime ))*60). ' minutes');

for ($i = $areaStartTime; $i <= $areaEndTime; $i += $areaHourSize)
{
	echo '<tr id="hour' . $i . '">';

	echo '<td id="hour' . $i . 'LabelLeft">' . hourToTime($i) . '</td>';

	for ($j = $areaStartDay; $j <= $areaEndDay; $j ++)
	{
		$count = 0;

		if (($i >= BUSINESS_START_TIME  &&  $i <= BUSINESS_END_TIME)  &&  ($j >= BUSINESS_START_DAY  &&  $j <= BUSINESS_END_DAY))
		{
			echo '<td id="' . $date->format('Y-m-d') . '"><span  class="businessHours" employeeTitle="';
		}
		else
		{
			echo '<td id="' . $date->format('Y-m-d') . '"><span class="hours" employeeTitle="';
		}

		for ($k = 0; $k < count($schedule); $k ++)
		{
			if(isset($schedule[$k]['startDate']) && isset($schedule[$k]['endDate']))
			{
				if ($mode == WEEKLY_MODE)
				{
					if(($schedule[$k]['startDate'].' '.$schedule[$k]['startTime']) <= $date->format('Y-m-d H:i:s') && ($schedule[$k]['endDate'].' '.$schedule[$k]['endTime']) > $date->format('Y-m-d H:i:s'))
					{
						$count++;

						echo nameByNetId($schedule[$k]['employee']) . ' - ' . $schedule[$k]['name'] . "\n";
					}
				}
				else
				{
					//THIS IF BLOCK IS TO ACCOUNT FOR THE DIFFERENCE IN TREATING DAYS OF THE WEEK USING 1-7 RATHER THAN 0-6 AND STARTING ON SATURDAY(0-6) RATHEN THAN SUNDAY(1-7) IN THE DEFAULT MODE, THE START DATE $J IS ASSIGNED TO 0-6 RANGE WHERE 0 IS SATURDAY WHILE THE $SCHEDULE DATES HAVE A 1-7 RANGE WHERE 1 IS SUNDAY AND 7 IS SATURDAY. SO SATURDAY IS 6 IN ONE AND 7 ON THE OTHER, TO FIX THIS WHEN $J IS 0 (SATURDAY) AND $schedule[$k]['startDate'] AND $schedule[$k]['endDate'] ARE 7 THEN CODE BELOW RUNS TO ACCOUNT FOR SATURDAY, THE INEQUALITY <= AND >= IS TO ACCOUNT FOR SHIFTS THAT GO FOR MORE THAT ONE DAY.

					if($schedule[$k]['startDate'] == 7)
					{
						$schedule[$k]['startDate'] = 0;
					}
						
					if($schedule[$k]['endDate'] == 7)
					{
						$schedule[$k]['endDate'] = 0;
					}
						
					if($schedule[$k]['startDate'] > $schedule[$k]['endDate'])
					{
						$schedule[$k]['endDate'] += 7;
					}

					if(($schedule[$k]['startDate'].' '.$schedule[$k]['startTime']) <= $j.' '.$date->format('H:i:s') && ($schedule[$k]['endDate'].' '.$schedule[$k]['endTime']) > $j.' '.$date->format('H:i:s'))
					{
						$count++;

						echo nameByNetId($schedule[$k]['employee']) . ' - ' . $schedule[$k]['name'] . "\n";
					}
				}
			}
		}

		if ($count == 0)
		{
			echo '">' . $count . '</span></td>';
		}
		else
		{
			echo '">' . $count . '</span></td>';
		}

		$date->modify('+1 day');
	}

	echo '<td id="hour' . $i . 'LabelRight">' . hourToTime($i) . '</td>';

	$date->modify('-' . DAYS . ' day');

	echo '</tr>';

	$date->add($areaTimeInterval);
}

echo '	</table>';

if ($mode == DEFAULT_MODE)
{

	echo '<input id="period" type="hidden" value="' . $schedule[0]['period'] . '">';
}

echo '	<br />
	
	<h2>Total Hours</h2>';

for ($i = 0; $i < count($employees); $i ++)
{
	$totalHours = 0;

	for ($j = 0; $j < count($schedule); $j ++)
	{

		if (isset($schedule[$j]['employee']) && $schedule[$j]['employee'] == $employees[$i])
		{
			$totalHours += $schedule[$j]['hourTotal'];
		}
	}

	if ($totalHours != 0)
	{
		if ($mode == WEEKLY_MODE)
		{
			echo '| <span class="employeeLink" onClick="window.open(\'https://' . $_SERVER['SERVER_NAME'] . '/newSchedule/index.php?employee=' . $employees[$i] . '&date=' . $startDate . '\')">' . nameByNetId($employees[$i]) . ' - ' . $totalHours . ' </span> |';
		}
		else
		{
			echo '| <span class="employeeLink" onClick="window.open(\'https://' . $_SERVER['SERVER_NAME'] . '/newSchedule/index.php?employee=' . $employees[$i] . '&date=' . $schedule[0]['periodID'] . '&weeklyDefault=default\')">' . nameByNetId($employees[$i]) . ' - ' . $totalHours . ' </span> |';
		}
	}
}

echo '</div>';
?>
