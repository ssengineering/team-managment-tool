<?php //printSummaryAdmin.php
//This file contains all the functions necessary to print out the summary admin table

require('../includes/includeMeBlank.php');
$month = $_GET['month'];
$year = $_GET['year'];
$sortBy = "employee";
if(isset($_GET['sortBy'])){
	$sortBy = $_GET['sortBy'];
}
$startMon = date("Y-m-01",strtotime($year.'-'.$month));
$endMon = date("Y-m-01",strtotime($startMon."+1month"));


//Compiles the html table summary from the array for the given month with the sortBy Criteria
function compileTableFromArray($month,$sortBy){
	global $area, $db;
	echo "<table class='sortable' id='summary' name='summary' >";
	echo "<tr>
					<th>Employee</th>
					<th><span style='cursor:pointer'>Missed Punches</span></th>					
					<th><span style='cursor:pointer'>Absences</span></th>
					<th><span style='cursor:pointer'>Tardies</span></th>
					<th><span style='cursor:pointer'>Commendable Performances</span></th>
					<th><span style='cursor:pointer'>Policy Reminders</span></th>";
				if($area == 4){ //Only show for campus operators, to show for all areas, remove if statement.
					echo "<th><span style='cursor:pointer'>Quizzes</span></th>";
					echo "<th><span style='cursor:pointer'>Comments</span></th>";
				}
				if($area == 2){
					echo "<th><span style='cursor:pointer'>Security Violations</span></th>";
				}
	echo	 "<th><span style='cursor:pointer'>Meeting Requested</span></th>
					<th><span style='cursor:pointer'>Performance Reviewed</span></th>
				</tr>";
	try {
		$employeeQuery = $db->prepare("SELECT * FROM employee WHERE area = :area AND active = '1' ORDER BY firstName ASC");
		$employeeQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$performanceArray = array();
	while($curNetID = $employeeQuery->fetch(PDO::FETCH_ASSOC)) {
		$performanceArray[$curNetID['netID']] =	compileArrayRow($curNetID['netID']);
	}
	$col = array();
	
	
	//Sort array by the given Field	
	if($sortBy != "employee"){
		foreach($performanceArray as $key => $val){
			$col[$key] = $val[$sortBy];
		}
		array_multisort($col, SORT_DESC, $performanceArray);
	}
	
	foreach($performanceArray as $key => $val){
		echo "<tr><td><a href='summaryIndiv.php?employee=".$key."' >".nameByNetID($key)."</a></td>";
		foreach($val as $k => $v){
			echo "<td>".$v."</td>";
		}		
		echo "</tr>";
	}
	echo "</table>";
	
}


//This will compile all the information for one employee
function compileArrayRow($netID){
	global $area;
	$array = array();
	$array['punches'] = getNumMissedPunchesForNetID($netID);
	$array['absences'] = getNumAbsencesForNetID($netID);
	$array['tardies'] = getNumTardiesForNetID($netID);
	$array['commendables'] = getNumCommendablePerformancesForNetID($netID);
	$array['policy'] = getNumPolicyRemindersForNetID($netID);
if($area == 4){	//only show for campus operators, to show for all areas remove if statement
	$array['quizzes'] = "Placeholder";
	$array['comments']= getNumCommentsForNetID($netID);
}
if($area == 2){
	$array['security'] = getNumSecurityViolationsForNetID($netID);
}
	$array['meeting'] = getNumMeetingRequest($netID);
	$array['reviewed'] = getNumPerformanceReviewed($netID);
	return $array;
}


function getNumPerformanceReviewed($netID){
	global $area;
	global $month;
	global $db;
	$thismonth = date('F',strtotime($month));
	$year = date('Y-01-01',strtotime("today"));
	try {
		$reviewedQuery = $db->prepare("SELECT * FROM reportPerformanceReviewed WHERE netID=:netId AND month=:month AND area=:area AND date > :year");
		$reviewedQuery->execute(array(':netId' => $netID, ':month' => $thismonth, ':area' => $area, ':year' => $year));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($result = $reviewedQuery->fetch()) {
		return "Yes";
	}else {
		return "No";
	}
}

function getNumSecurityViolationsForNetID($netID){
	global $area;
	global $startMon;
	global $endMon;
	global $db;
	try {
		$violationQuery = $db->prepare("SELECT COUNT(ID) FROM reportSecurityViolation WHERE employee = :netId AND date >= :start AND date < :end");
		$violationQuery->execute(array(':netId' => $netID, ':start' => $startMon, ':end' => $endMon));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $violationQuery->fetch(PDO::FETCH_NUM);
	return $result[0];
}

function getNumMeetingRequest($netID){
	global $startMon;
	global $endMon;
	global $db;
	try {
		$commentsQuery = $db->prepare("SELECT * FROM reportComments WHERE netID = :netID AND date >= :start AND date < :end");
		$commentsQuery->execute(array(':netID' => $netID, ':start' => $startMon, ':end' => $endMon));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$total = 0;
	while($curItem = $commentsQuery->fetch(PDO::FETCH_ASSOC)) {
		if($curItem['meetingRequest'] == 1)
			$total++;
	}
	if($total > 0) {
		return $total;
	} else {
		return "No";
	}
}

//gets the total number of missed punches for a $netID
function getNumMissedPunchesForNetID($netID){
	global $startMon;
	global $endMon;
	global $db;
	try {
		$missedPunchesQuery = $db->prepare("SELECT COUNT(`index`) FROM kronosEdit WHERE submitter = :netId AND date >= :start AND date < :end");
		$missedPunchesQuery->execute(array(':netId' => $netID, ':start' => $startMon, ':end' => $endMon));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results = $missedPunchesQuery->fetch(PDO::FETCH_NUM);
	return $results[0];
}

//gets the total number of absences for a particular $netID
function getNumAbsencesForNetID($netID){
	global $startMon;
	global $endMon;		
	global $db;
	try {
		$absenceQuery = $db->prepare("SELECT COUNT(ID) FROM reportAbsence WHERE employee = :netId AND date >= :start AND date < :end");
		$absenceQuery->execute(array(':netId' => $netID, ':start' => $startMon, ':end' => $endMon));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results = $absenceQuery->fetch(PDO::FETCH_NUM);
	return $results[0];
}

//gets the total number of tardies for a particular $netID
function getNumTardiesForNetID($netID){
	global $startMon;
	global $endMon;		
	global $db;
	try {
		$tardyQuery = $db->prepare("SELECT COUNT(ID) FROM reportTardy WHERE employee = :netId AND date >= :start AND date < :end");
		$tardyQuery->execute(array(':netId' => $netID, ':start' => $startMon, ':end' => $endMon));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results = $tardyQuery->fetch(PDO::FETCH_NUM);
	return $results[0];
}

//gets the total number of Commendable Performances for a $netID
function getNumCommendablePerformancesForNetID($netID){
	global $startMon;
	global $endMon;		
	global $db;
	try {
		$commendableQuery = $db->prepare("SELECT COUNT(ID) FROM reportCommendable WHERE employee = :netId AND date >= :start AND date < :end");
		$commendableQuery->execute(array(':netId' => $netID, ':start' => $startMon, ':end' => $endMon));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results = $commendableQuery->fetch(PDO::FETCH_NUM);
	return $results[0];
}

//gets the total number of policy reminders for a $netID
function getNumPolicyRemindersForNetID($netID){
	global $startMon;
	global $endMon;		
	global $db;
	try {
		$policyQuery = $db->prepare("SELECT COUNT(ID) FROM reportPolicyReminder WHERE employee = :netId AND date >= :start AND date < :end");
		$policyQuery->execute(array(':netId' => $netID, ':start' => $startMon, ':end' => $endMon));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results = $policyQuery->fetch(PDO::FETCH_NUM);
	return $results[0];
}

function getNumCommentsForNetID($netID){
	global $startMon;
	global $endMon;		
	global $db;
	try {
		$commentsQuery = $db->prepare("SELECT COUNT(id) FROM reportComments WHERE netID = :netId AND date >= :start AND date < :end");
		$commentsQuery->execute(array(':netId' => $netID, ':start' => $startMon, ':end' => $endMon));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results = $commentsQuery->fetch(PDO::FETCH_NUM);
	return $results[0];
}

compileTableFromArray($month,$sortBy);

?>
