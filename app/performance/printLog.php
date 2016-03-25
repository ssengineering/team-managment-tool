<?php
require ('../includes/includeMeBlank.php');

$name = $_GET['employee'];
$startDate = $_GET['start'];
$endDate = $_GET['end'];
$terminated = "";
if(isset($_GET['terminated'])) {
	$terminated = $_GET['terminated'];
}
if (isset($_GET['smID']))//Only used for Silent Monitor App.
{
	$smID = $_GET['smID'];
	//Used if Silent Monitor Log wants to load a specific silent monitor instance.
}
else
{
	$smID = null;
	//Set $smID to null per proper programming practices.
}
$admin = can("access", "033e3c00-4989-4895-a4d5-a059984f7997");//employeePerformance resource
$adminSilentMonitor = can("read", "86755385-4a09-45ce-81b9-049b660210df");//performanceSummary resource

if ($name == "")
{
	try {
		$nameQuery = $db->prepare("SELECT firstName, lastName, netID FROM employee WHERE area=:area AND active='1'  ORDER BY lastName");
		$nameQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($curEmp = $nameQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<h2>" . nameByNetId($curEmp['netID']) . "</h2>";
		$name = $curEmp['netID'];
		if ($_GET['type'] == 'terminated')
		{
			echo getTerminatedLog($name);
		}
		if ($_GET['type'] == "absence")
		{
			echo getAbsenceLog($name, $startDate, $endDate);
		}
		else if ($_GET['type'] == "tardy")
		{
			echo getTardyLog($name, $startDate, $endDate);
		}
		else if ($_GET['type'] == "policy")
		{
			echo getPolicyReminderLog($name, $startDate, $endDate);
		}
		else if ($_GET['type'] == "commendable")
		{
			echo getCommendables($name, $startDate, $endDate);
		}
		else if ($_GET['type'] == "security")
		{
			echo getSecurityViolationsLog($name, $startDate, $endDate);
		}
		else if ($_GET['type'] == "comment")
		{
			echo getCommentLog($name, $startDate, $endDate);
		}
		else if ($_GET['type'] == "admin")
		{
			echo getAdminLog($name, $startDate, $endDate);
		}
		else if ($_GET['type'] == "silentMonitor")
		{
			echo "<h3>Silent Monitor Performance:</h3>";
			getSilentMonitorPerformance($name, $startDate, $endDate, $smID);
		}
	}
}
else
{
	if ($_GET['type'] == 'terminated')
	{
		echo getTerminatedLog($name);
	}
	if ($_GET['type'] == "absence")
	{
		echo getAbsenceLog($name, $startDate, $endDate);
	}
	else if ($_GET['type'] == "tardy")
	{
		echo getTardyLog($name, $startDate, $endDate);
	}
	else if ($_GET['type'] == "policy")
	{
		echo getPolicyReminderLog($name, $startDate, $endDate);
	}
	else if ($_GET['type'] == "commendable")
	{
		echo getCommendables($name, $startDate, $endDate);
	}
	else if ($_GET['type'] == "security")
	{
		echo getSecurityViolationsLog($name, $startDate, $endDate);
	}
	else if ($_GET['type'] == "comment")
	{
		echo getCommentLog($name, $startDate, $endDate);
	}
	else if ($_GET['type'] == "admin")
	{
		echo "<h2>" . nameByNetId($name) . "</h2>";
		echo getAdminLog($name, $startDate, $endDate);
	}
	else if ($_GET['type'] == "silentMonitor")
	{
		echo "<h3>Silent Monitor Performance:</h3>";
		getSilentMonitorPerformance($name, $startDate, $endDate, $smID);
	}
}

function getAdminLog($name, $start, $end)
{
	global $area;
	global $terminated;
	global $smID;
	if (isset($terminated)  &&  $terminated == 'true')
	{
		echo "<h3>Terminated </h3>";
		echo getTerminatedLog($name);
	}
	echo "<h3>Absences: </h3>";
	getAbsenceLog($name, $start, $end);
	echo "<h3>Tardies: </h3>";
	getTardyLog($name, $start, $end);
	echo "<h3>Commendable Performance:</h3>";
	getCommendables($name, $start, $end);
	if ($area == 4)
	{
		echo "<h3>Quizzes: </h3>";
		echo "<h3>Certifications: </h3>";
		echo "<h3>Missed Punches: ";
		getMissedPunchesLog($name, $start, $end);
	}
	if ($area != 2)
	{

		echo "<h3>Silent Monitor Performance:</h3>";
		getSilentMonitorPerformance($name, $start, $end, $smID);

		if ($area != 4)
		{
			echo "<h3>Ticket Performance: <img src='question-mark.jpg' alt='?' title='To edit category percentage please go to the Ticket Review Log application and update tickets.'></h3>";
			getTicketReviewStats($name, $start, $end);
		}
		echo "<h3>Performance Comments</h3>";
		getCommentLog($name, $start, $end);
	}
	echo "<h3>Policy Reminders: </h3>";
	getPolicyReminderLog($name, $start, $end);
	if ($area == 2)
	{
		echo "<h3>Security Violations</h3>";
		getSecurityViolationsLog($name, $start, $end);
	}
}

function getTerminatedLog($name)
{
	global $db;
	try {
		$terminationQuery = $db->prepare("SELECT * FROM `employeeTerminationDetails` LEFT JOIN `employee` ON  `employeeTerminationDetails`.`netID` = `employee`.`netID` WHERE `employeeTerminationDetails`.`netID`=:name");
		$terminationQuery->execute(array(':name' => $name));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$text = "<table><tr><th>Termination Date</th><th>Reason</th><th>Attendance</th><th>Attitude</th><th>Performance</th><th>Terminated By</th></tr>";
	while ($row = $terminationQuery->fetch(PDO::FETCH_ASSOC))
	{
		$text .= "<tr><td>$row[terminationDate]</td><td>$row[reasons]</td><td>$row[attendance]</td><td>$row[attitude]</td><td>$row[performance]</td><td>$row[firstName] $row[lastName]</td></tr>";
	}
	$text .= "</table>";
	return $text;
}

function getMissedPunchesLog($netID, $start, $end)
{
	global $terminated, $db;
	$query = '';
	$queryString = "";
	$queryParams = array();
	if (isset($terminated)  &&  $terminated == 'true')
	{
		$queryString = "SELECT COUNT(`index`) FROM kronosEdit WHERE submitter = :netId";
		$queryParams[':netId'] = $netID;
	}
	else
	{
		$queryString = "SELECT COUNT(`index`) FROM kronosEdit WHERE submitter = :netId AND date >= :start AND date <= :end";
		$queryParams = array(':netId' => $netID, ':start' => $start, ':end' => $end);
	}
	try {
		$missedPunchQuery = $db->prepare($queryString);
		$missedPunchQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $missedPunchQuery->fetch(PDO::FETCH_NUM);
	echo $result[0];
}

function getSilentMonitorPerformance($name, $start, $end, $smID)
{
	global $adminSilentMonitor;
	global $terminated;
	global $db;

	if ($smID != NULL  ||  (isset($terminated)  &&  $terminated == 'true'))
	{
		try {
			$silentMonitorQuery = $db->prepare("SELECT * FROM `silentMonitor` WHERE `index` = :smId AND `deleted` = '0'");
			$silentMonitorQuery->execute(array(':smId' => $smID));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	else
	{
		try {
			$silentMonitorQuery = $db->prepare("SELECT * FROM silentMonitor WHERE netID = :name AND submitDate >= :start AND submitDate <= :end AND deleted = '0'");
			$silentMonitorQuery->execute(array(':name' => $name, ':start' => $start, ':end' => $end));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	//Loops through each silent monitor for the time period associated with the netID.
	while ($cur = $silentMonitorQuery->fetch(PDO::FETCH_ASSOC))
	{
		$ratingAvg = 0;
		$percentAvg = 0;
		$numCalls = 0;
		echo "<table><tr><th>Call #</th><th>Date</th><th>Comment</th><th>Percent</th><th>Rating</th>";
		if ($adminSilentMonitor)
		{
			echo "<th>Edit</th><th>Delete</th></tr>";
		}
		else
		{
			echo "</tr>";
		}
		try {
			$callQuery = $db->prepare("SELECT * FROM silentMonitorCalls WHERE smid = :index AND deleted = '0'");
			$callQuery->execute(array(':index' => $cur['index']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		//loops through each call associated with that monitor.
		while ($curCall = $callQuery->fetch(PDO::FETCH_ASSOC))
		{
			$numCalls += 1;
			echo "<tr><td>" . $curCall['callNum'] . "</td><td>" . $curCall['date'] . "</td><td>" . $curCall['comments'] . "</td>";
			echo "<td>" . $curCall['criteriaAvg'] . "</td><td>" . $curCall['rating'] . "</td>";
			if ($adminSilentMonitor)
			{
				echo "<td><input type='button' class='editButton' value='Edit' onClick='editCallLog(\"silentMonitorCall\"," . $curCall['smid'] . "," . $curCall['callNum'] . ")'/></td>";
				echo "<td><input type='button' class='deleteButton' value='Delete' onClick='deleteCallLog(\"call\"," . $curCall['smid'] . "," . $curCall['callNum'] . ")'/></td></tr>";
			}
			$ratingAvg += $curCall['rating'];
			$percentAvg += $curCall['criteriaAvg'];
		}

		if ($numCalls > 0)
		{
			$ratingAvg = $ratingAvg  /  $numCalls;
			$percentAvg = $percentAvg  /  $numCalls;
		}
		else
		{
			$ratingAvg = 0;
			$percentAvg = 0;
		}

		echo "<tr><td colspan='3'>Overall Comments:<br/> " . $cur['overallComment'] . "</td>";
		echo "<td>Average: " . $percentAvg . "</td><td>Average: " . $ratingAvg . "</td>";
		if ($adminSilentMonitor)
		{
			echo "<td><input type='button' class='editButton' value='Edit' onClick='editLog(" . $cur['index'] . ",\"silentMonitor\")'/></td>";
			echo "<td><input type='button' class='deleteButton' value='Delete' onClick='deleteCallLog(\"monitor\"," . $cur['index'] . ",0)'/></td></tr></table>";
		}
		echo "<a href=" . "\"javascript:newwindow('./printCallLog.php?ID=" . $cur['index'] . "')\">Show Call Summary</a>";
		echo "</br>";
		echo "</br>";
	}
}

function getAbsenceLog($name, $start, $end)
{
	global $admin;
	global $area;
	global $terminated;
	global $db;
	$query = '';
	if (isset($terminated)  &&  $terminated == 'true')
	{
		$queryString = "SELECT * FROM reportAbsence WHERE employee=:name";
		$queryParams = array(':name' => $name);
	}
	else
	{
		$queryString = "SELECT * FROM reportAbsence WHERE employee=:name AND date >= :start AND date <= :end";
		$queryParams = array(':name' => $name, ':start' => $start, ':end' => $end);
	}
	try {
		$absenceQuery = $db->prepare($queryString);
		$absenceQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($first = $absenceQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<table>		
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Shift Start Time</th>
			<th>Shift End Time</th>
			<th>Reason</th>";
		if ($area == 2)
		{
			echo "<th>No Call</th>";
		}
		else
		{
			echo "<th>No Show</th>";
		}
		echo "<th>Submitted By</th>";
		if ($admin)
		{
			echo "<th></th>
				<th></th>";
		}
		echo "</tr>";
		echo "<tr>";
		echo "<td>" . nameByNetId($first['employee']) . "</td>";
		echo "<td>" . $first['date'] . "</td>";
		echo "<td>" . date("g:i A", strtotime($first['shiftStart'])) . "</td>";
		echo "<td>" . date("g:i A", strtotime($first['shiftEnd'])) . "</td>";
		echo "<td>" . $first['reason'] . "</td>";
		echo "<td>" . $first['noCall'] . "</td>";
		echo "<td>" . nameByNetId($first['submitter']) . "</td>";
		if ($admin)
		{
			echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $first['ID'] . "\",\"absence\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $first['ID'] . "\",\"Absence\")' /></td>";
		}
		echo "</tr>";
		while ($current = $absenceQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo "<tr>";
			echo "<td>" . nameByNetId($current['employee']) . "</td>";
			echo "<td>" . $current['date'] . "</td>";
			echo "<td>" . date("g:i A", strtotime($current['shiftStart'])) . "</td>";
			echo "<td>" . date("g:i A", strtotime($current['shiftEnd'])) . "</td>";
			echo "<td>" . $current['reason'] . "</td>";
			echo "<td>" . $current['noCall'] . "</td>";
			echo "<td>" . nameByNetId($current['submitter']) . "</td>";
			if ($admin)
			{
				echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $current['ID'] . "\",\"absence\")' /></td>";
				echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $current['ID'] . "\",\"Absence\")' /></td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	else
	{
		echo "0 Absences during this time period";
	}
}

function getTardyLog($name, $start, $end)
{
	global $admin;
	global $area;
	global $terminated;
	global $db;
	$query = '';
	if (isset($terminated)  &&  $terminated == 'true')
	{
		$queryString = "SELECT * FROM reportTardy WHERE employee=:name";
		$queryParams = array(':name' => $name);
	}
	else
	{
		$queryString = "SELECT * FROM reportTardy WHERE employee=:name AND date >= :start AND date <= :end";
		$queryParams = array(':name' => $name, ':start' => $start, ':end' => $end);
	}
	try {
		$tardyQuery = $db->prepare($queryString);
		$tardyQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($first = $tardyQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<table>		
				<tr>
					<th>Employee</th>
					<th>Date</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Time Arrived</th>
					<th>Mins. Late</th>
					<th>Reason</th>";
		if ($area == 2)
		{
			echo "<th>No Call</th>";
		}
		else
		{
			echo "<th>No Show</th>";
		}
		echo "<th>Submitted By</th>";
		if ($admin)
		{
			echo "<th></th>
				<th></th>";
		}
		echo "</tr>";
		$to_time = strtotime($first['time']);
		$from_time = strtotime($first['start']);
		$minsLate = round(abs($to_time  -  $from_time)  /  60, 2) . " minute(s)";
		echo "<tr>";
		echo "<td>" . nameByNetId($first['employee']) . "</td>";
		echo "<td>" . $first['date'] . "</td>";
		echo "<td>" . date("g:i A", strtotime($first['start'])) . "</td>";
		echo "<td>" . date("g:i A", strtotime($first['end'])) . "</td>";
		echo "<td>" . date("g:i A", strtotime($first['time'])) . "</td>";
		echo "<td>" . $minsLate . "</td>";
		echo "<td>" . $first['reason'] . "</td>";
		echo "<td>" . $first['noCall'] . "</td>";
		echo "<td>" . nameByNetId($first['submitter']) . "</td>";
		if ($admin)
		{
			echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $first['ID'] . "\",\"tardy\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $first['ID'] . "\",\"Tardy\")' /></td>";
		}
		echo "</tr>";
		while ($current = $tardyQuery->fetch(PDO::FETCH_ASSOC))
		{
			$to_time = strtotime($current['time']);
			$from_time = strtotime($current['start']);
			$minsLate = round(abs($to_time  -  $from_time)  /  60, 2) . " minute(s)";
			echo "<tr>";
			echo "<td>" . nameByNetId($current['employee']) . "</td>";
			echo "<td>" . $current['date'] . "</td>";
			echo "<td>" . date("g:i A", strtotime($current['start'])) . "</td>";
			echo "<td>" . date("g:i A", strtotime($current['end'])) . "</td>";
			echo "<td>" . date("g:i A", strtotime($current['time'])) . "</td>";
			echo "<td>" . $minsLate . "</td>";
			echo "<td>" . $current['reason'] . "</td>";
			echo "<td>" . $current['noCall'] . "</td>";
			echo "<td>" . nameByNetId($current['submitter']) . "</td>";
			if ($admin)
			{
				echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $current['ID'] . "\",\"tardy\")' /></td>";
				echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $current['ID'] . "\",\"Tardy\")' /></td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	else
	{
		echo "0 Tardies during this time period";
	}
}

function getPolicyReminderLog($name, $start, $end)
{
	global $admin;
	global $terminated;
	global $db;
	$query = '';
	if (isset($terminated)  &&  $terminated == 'true')
	{
		$queryString = "SELECT * FROM reportPolicyReminder WHERE employee=:name";
		$queryParams = array(':name' => $name);
	}
	else
	{
		$queryString = "SELECT * FROM reportPolicyReminder WHERE employee=:name AND date >= :start AND date <= :end";
		$queryParams = array(':name' => $name, ':start' => $start, ':end' => $end);
	}
	try {
		$policyQuery = $db->prepare($queryString);
		$policyQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($first = $policyQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<table>		
				<tr>
					<th>Employee</th>
					<th>Date</th>
					<th>Submitted By</th>
					<th>Reminder</th>";
		if ($admin)
		{
			echo "<th></th>
				<th></th>";
		}
		echo "</tr>";
		echo "<tr>";
		echo "<td>" . nameByNetId($first['employee']) . "</td>";
		echo "<td>" . $first['date'] . "</td>";
		echo "<td>" . nameByNetId($first['submitter']) . "</td>";
		echo "<td>" . $first['reason'] . "</td>";
		if ($admin)
		{
			echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $first['ID'] . "\",\"policy\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $first['ID'] . "\",\"PolicyReminder\")' /></td>";
		}
		echo "</tr>";
		while ($current = $policyQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo "<tr>";
			echo "<td>" . nameByNetId($current['employee']) . "</td>";
			echo "<td>" . $current['date'] . "</td>";
			echo "<td>" . nameByNetId($current['submitter']) . "</td>";
			echo "<td>" . $current['reason'] . "</td>";
			if ($admin)
			{
				echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $current['ID'] . "\",\"policy\")' /></td>";
				echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $current['ID'] . "\",\"PolicyReminder\")' /></td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	else
	{
		echo "0 Policy Reminders during this time period";
	}
}

function getCommendables($name, $start, $end)
{
	global $admin;
	global $terminated;
	global $db;
	$query = '';
	if (isset($terminated)  &&  $terminated == 'true')
	{
		$queryString = "SELECT * FROM reportCommendable WHERE employee=:name";
		$queryParams = array(':name' => $name);
	}
	else
	{
		$queryString = "SELECT * FROM reportCommendable WHERE employee=:name AND date >= :start AND date <= :end";
		$queryParams = array(':name' => $name, ':start' => $start, ':end' => $end);
	}
	try {
		$commendableQuery = $db->prepare($queryString);
		$commendableQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($first = $commendableQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<table>		
				<tr>
					<th>Employee</th>
					<th>Date</th>
					<th>Submitted By</th>
					<th>Reason</th>
					<th>Public</th>";
		if ($admin)
		{
			echo "<th></th>
					<th></th>";
		}
		echo "</tr>";
		echo "<tr>";
		echo "<td>" . nameByNetId($first['employee']) . "</td>";
		echo "<td>" . $first['date'] . "</td>";
		echo "<td>" . nameByNetId($first['submitter']) . "</td>";
		echo "<td>" . $first['reason'] . "</td>";
		echo "<td>";
		if ($first['public'] == 1)
		{
			echo "Yes";
		}
		else
		{
			echo "No";
		}
		echo "</td>";
		if ($admin)
		{
			echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $first['ID'] . "\",\"commendable\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $first['ID'] . "\",\"Commendable\")' /></td>";
		}
		echo "</tr>";
		while ($current = $commendableQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo "<tr>";
			echo "<td>" . nameByNetId($current['employee']) . "</td>";
			echo "<td>" . $current['date'] . "</td>";
			echo "<td>" . nameByNetId($current['submitter']) . "</td>";
			echo "<td>" . $current['reason'] . "</td>";
			echo "<td>";
			if ($current['public'] == 1)
			{
				echo "Yes";
			}
			else
			{
				echo "No";
			}
			echo "</td>";
			if ($admin)
			{
				echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $current['ID'] . "\",\"commendable\")' /></td>";
				echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $current['ID'] . "\",\"Commendable\")' /></td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	else
	{
		echo "0 Commendable Performances during this time period";
	}
}

function getSecurityViolationsLog($name, $start, $end)
{
	global $admin;
	global $terminated;
	global $db;
	$query = '';
	if (isset($terminated)  &&  $terminated == 'true')
	{
		$queryString = "SELECT * FROM reportSecurityViolation WHERE employee=:name";
		$queryParams = array(':name' => $name);
	}
	else
	{
		$queryString = "SELECT * FROM reportSecurityViolation WHERE employee=:name AND date >= :start AND date <= :end";
		$queryParams = array(':name' => $name, ':start' => $start, ':end' => $end);
	}
	try {
		$violationQuery = $db->prepare($queryString);
		$violationQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($first = $violationQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<table>		
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Violation</th>
			<th>Reason</th>
			<th>Submitted By</th>";
		if ($admin)
		{
			echo "<th></th>
				<th></th>";
		}
		echo "</tr>";
		echo "<tr>";
		echo "<td>" . nameByNetId($first['employee']) . "</td>";
		echo "<td>" . $first['date'] . "</td>";
		echo "<td>" . $first['violation'] . "</td>";
		echo "<td>" . $first['reason'] . "</td>";
		echo "<td>" . nameByNetId($first['submitter']) . "</td>";
		if ($admin)
		{
			echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $first['ID'] . "\",\"security\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $first['ID'] . "\",\"SecurityViolation\")' /></td>";
		}
		echo "</tr>";
		while ($current = $violationQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo "<tr>";
			echo "<td>" . nameByNetId($current['employee']) . "</td>";
			echo "<td>" . $current['date'] . "</td>";
			echo "<td>" . $current['violation'] . "</td>";
			echo "<td>" . $current['reason'] . "</td>";
			echo "<td>" . nameByNetId($current['submitter']) . "</td>";
			if ($admin)
			{
				echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $current['ID'] . "\",\"security\")' /></td>";
				echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $current['ID'] . "\",\"SecurityViolation\")' /></td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	else
	{
		echo "0 Security Violations during this time period";
	}
}

function getCommentLog($name, $start, $end)
{
	global $admin;
	global $terminated;
	global $db;
	$query = '';
	if (isset($terminated)  &&  $terminated == 'true')
	{
		$queryString = "SELECT * FROM reportComments WHERE netID=:name";
		$queryParams = array(':name' => $name);
	}
	else
	{
		$queryString = "SELECT * FROM reportComments WHERE netID=:name AND date >= :start AND date <= :end";
		$queryParams = array(':name' => $name, ':start' => $start, ':end' => $end);
	}
	try {
		$commentsQuery = $db->prepare($queryString);
		$commentsQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($first = $commentsQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<table>		
				<tr>
					<th>Employee</th>
					<th>Date</th>
					<th>Submitted By</th>
					<th>Comment</th>
					<th>Meeting Request</th>";
		if ($admin)
		{
			echo "<th></th>
					<th></th>";
		}
		echo "</tr>";
		echo "<tr>";
		echo "<td>" . nameByNetId($first['netID']) . "</td>";
		echo "<td>" . date("Y-m-d", strtotime($first['date'])) . "</td>";
		echo "<td>" . nameByNetId($first['submitter']) . "</td>";
		echo "<td>" . $first['comments'] . "</td>";
		echo "<td>";
		if ($first['meetingRequest'] == 1)
		{
			echo "Yes";
		}
		else
		{
			echo "No";
		}
		echo "</td>";
		if ($admin)
		{
			echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $first['id'] . "\",\"comment\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $first['id'] . "\",\"Comments\")' /></td>";
		}
		echo "</tr>";
		while ($current = $commentsQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo "<tr>";
			echo "<td>" . nameByNetId($current['netID']) . "</td>";
			echo "<td>" . date("Y-m-d", strtotime($current['date'])) . "</td>";
			echo "<td>" . nameByNetId($current['submitter']) . "</td>";
			echo "<td>" . $current['comments'] . "</td>";
			echo "<td>";
			if ($current['meetingRequest'] == 1)
			{
				echo "Yes";
			}
			else
			{
				echo "No";
			}
			echo "</td>";
			if ($admin)
			{
				echo "<td><input type='button' value='Edit' onclick='editLog(\"" . $current['id'] . "\",\"comment\")' /></td>";
				echo "<td><input type='button' value='Delete' onclick='deleteLog(\"" . $current['id'] . "\",\"Comments\")' /></td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	else
	{
		echo "0 Comments for this period";
	}
}

// Get ticket Review Stats
function getTicketReviewStats($name, $startDate, $endDate)
{
	global $db;
	$avgAllCategories = 0;

	$requestorTally = 0;
	$contactInfoTally = 0;
	$sscTally = 0;
	$ticketSourceTally = 0;
	$priorityTally = 0;
	$kbTally = 0;
	$workOrderTally = 0;
	$templatesTally = 0;
	$troubleTally = 0;
	$closureCodesTally = 0;
	$professionalismTally = 0;

	// WorkOrder divide by:
	try {
		$reviewQuery = $db->prepare("SELECT COUNT(`entryNum`) FROM `ticketReview` WHERE `ticketDate` >= :start AND `ticketDate` <= :end AND `agentID` = :name AND (`workOrderNumber`='Yes' OR `workOrderNumber`='No')");
		$reviewQuery->execute(array(':start' => $startDate, ':end' => $endDate, ':name' => $name));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$reviewQueryResult = $reviewQuery->fetch(PDO::FETCH_NUM);
	$workOrderDivideBy = $reviewQueryResult[0];

	// Templates divide by:
	try {
		$review2Query = $db->prepare("SELECT * FROM `ticketReview` WHERE `ticketDate` >= :start AND `ticketDate` <= :end AND `agentID` = :name AND (`templates`='Yes' OR `templates`='No')");
		$review2Query->execute(array(':start' => $startDate, ':end' => $endDate, ':name' => $name));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$reviewQuery2Result = $review2Query->fetch(PDO::FETCH_NUM);
	$templatesDivideBy = $reviewQuery2Result[0];

	try {
		$reviewsQuery = $db->prepare("SELECT * FROM `ticketReview` WHERE `ticketDate` >= :start AND `ticketDate` <= :end AND `agentID` = :name");
		$reviewsQuery->execute(array(':start' => $startDate, ':end' => $endDate, ':name' => $name));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;

	while ($cur = $reviewsQuery->fetch(PDO::FETCH_ASSOC))
	{
		$divBy++;
		if ($cur['requestor'] == 'Yes')
		{
			$requestorTally ++;
		}
		if ($cur['contactInfo'] == 'Yes')
		{
			$contactInfoTally ++;
		}
		if ($cur['ssc'] == 'Yes')
		{
			$sscTally ++;
		}
		if ($cur['ticketSource'] == 'Yes')
		{
			$ticketSourceTally ++;
		}
		if ($cur['priority'] == 'Yes')
		{
			$priorityTally ++;
		}
		if ($cur['kbOrSource'] == 'Yes')
		{
			$kbTally ++;
		}
		if ($cur['workOrderNumber'] == 'Yes')
		{
			// what about NA
			$workOrderTally ++;
		}
		if ($cur['templates'] == 'Yes')
		{
			$templatesTally ++;
		}
		if ($cur['troubleshooting'] == 'Yes')
		{
			$troubleTally ++;
		}
		if ($cur['closureCodes'] == 'Yes')
		{
			$closureCodesTally ++;
		}
		if ($cur['professionalism'] == 'Yes')
		{
			$professionalismTally ++;
		}
	}

	if ($divBy != 0)
	{
		$requestorTally = round(($requestorTally  /  $divBy)  *  100, 0);
		$contactInfoTally = round(($contactInfoTally  /  $divBy)  *  100, 0);
		$sscTally = round(($sscTally  /  $divBy)  *  100, 0);
		$ticketSourceTally = round(($ticketSourceTally  /  $divBy)  *  100, 0);
		$priorityTally = round(($priorityTally  /  $divBy)  *  100, 0);
		$kbTally = round(($kbTally  /  $divBy)  *  100, 0);
		if ($workOrderDivideBy != 0)
		{
			$workOrderTally = round(($workOrderTally  /  $workOrderDivideBy)  *  100, 0);
		}
		else
		{
			$workOrderTally = 100;
		}
		if ($templatesDivideBy != 0)
		{
			$templatesTally = round(($templatesTally  /  $templatesDivideBy)  *  100, 0);
		}
		else
		{
			$templatesTally = 100;
		}
		$troubleTally = round(($troubleTally  /  $divBy)  *  100, 0);
		$closureCodesTally = round(($closureCodesTally  /  $divBy)  *  100, 0);
		$professionalismTally = round(($professionalismTally  /  $divBy)  *  100, 0);
	}
	else
	{
		$requestorTally = 100;
		$contactInfoTally = 100;
		$sscTally = 100;
		$ticketSourceTally = 100;
		$priorityTally = 100;
		$kbTally = 100;
		$workOrderTally = 100;
		$templatesTally = 100;
		$troubleTally = 100;
		$closureCodesTally = 100;
		$professionalismTally = 100;
	}

	//Average of all the categories:
	$avgAllCategories = $requestorTally  +  $contactInfoTally  +  $sscTally  +  $ticketSourceTally  +  $priorityTally  +  $kbTally  +  $workOrderTally  +  $templatesTally  +  $troubleTally  +  $closureCodesTally  +  $professionalismTally;
	$avgAllCategories = round(($avgAllCategories  /  11), 0);

	echo '<table class="centerText">		
			<tr>
				<th>Category</th>
				<th>Percentage</th>
			</tr>
			<tr>
				<td>Requestor</td>
				<td class="centerText">' . $requestorTally . ' %</td>
			</tr>
			<tr>
				<td>Contact Info</td>
				<td class="centerText">' . $contactInfoTally . ' %</td>
			</tr>
			<tr>
				<td>Service Category</td>
				<td class="centerText">' . $sscTally . ' %</td>
			</tr>
			<tr>
				<td>Ticket Source</td>
				<td class="centerText">' . $ticketSourceTally . ' %</td>
			</tr>
			<tr>
				<td>Priority</td>
				<td class="centerText">' . $priorityTally . ' %</td>
			</tr>
			<tr>
				<td>KB Source</td>
				<td class="centerText">' . $kbTally . ' %</td>
			</tr>
			<tr>
				<td>Work Order</td>
				<td class="centerText">' . $workOrderTally . ' %</td>
			</tr>
			<tr>
				<td>Template</td>
				<td class="centerText">' . $templatesTally . ' %</td>
			</tr>
			<tr>
				<td>Troubleshooting</td>
				<td class="centerText">' . $troubleTally . ' %</td>
			</tr>
			<tr>
				<td>Closure Codes</td>
				<td class="centerText">' . $closureCodesTally . ' %</td>
			</tr>
			<tr>
				<td>Professionalism</td>
				<td class="centerText">' . $professionalismTally . ' %</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Total Avg</td>
				<td class="centerText">' . $avgAllCategories . ' %</td>
			</tr>
		</table>';
}
?>
