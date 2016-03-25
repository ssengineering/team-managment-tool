<?php 
require('../../includes/includeMeBlank.php');

$teamId = $_GET['id'];
try {
	$teamsQuery = $db->prepare("SELECT * FROM teams WHERE ID = :teamId");
	$teamsQuery->execute(array(':teamId' => $teamId));
} catch(PDOException $e) {
	exit("error in query");
}
$shiftCheck = $teamsQuery->fetch(PDO::FETCH_ASSOC);
$shift = $shiftCheck['isShift'];
if($shift){
	try {
		$employeeQuery = $db->prepare("SELECT `netID`, CONCAT(`firstName`,' ',`lastName`) AS `name` FROM `employee` WHERE  `supervisor` = '' AND `area` = :area AND active = '1' ORDER BY `name` ASC");
		$employeeQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$data = array();
	while($cur = $employeeQuery->fetch(PDO::FETCH_ASSOC)) {
		$data[] = $cur;
	}
	$data = json_encode($data);
	echo $data;

} else {
	try {
		$employeeQuery = $db->prepare("SELECT `netID`, CONCAT(`firstName`,' ',`lastName`) AS `name` FROM `employee` WHERE `netID` NOT IN (SELECT `netID` FROM `teamMembers` WHERE `teamID` = :teamId) AND `area` = :area AND active = '1' ORDER BY `name` ASC");
		$employeeQuery->execute(array(':teamId' => $teamId, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	$data = array();
	while($cur = $employeeQuery->fetch(PDO::FETCH_ASSOC)) {
		$data[] = $cur;
	}
	$data = json_encode($data);
	echo $data;
}
?>
