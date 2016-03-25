<?php
require('../../includes/includeMeBlank.php');

$type = $_GET['type'];
$employee = $_GET['employee'];
$teamId = $_GET['team'];

if($type == "add"){
	try {
		$insertQuery = $db->prepare("INSERT INTO teamMembers (netID,teamID,area,guid) VALUES (:employee,:teamId,:area,:guid)");
		$insertQuery->execute(array(':employee' => $employee, ':teamId' => $teamId, ':area' => $area, ':guid' => newGuid()));
		$teamsQuery = $db->prepare("SELECT * FROM teams WHERE ID = :teamId");
		$teamsQuery->execute(array(':teamId' => $teamId));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$teamInfo = $teamsQuery->fetch(PDO::FETCH_ASSOC);
	if($teamInfo['isShift']){
		try {
			$updateQuery = $db->prepare("UPDATE employee SET supervisor = :lead WHERE netID = :employee");
			$updateQuery->execute(array(':lead' => $teamInfo['lead'], ':employee' => $employee));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
} else if ($type == "remove"){
	try {
		$deleteQuery = $db->prepare("DELETE FROM teamMembers WHERE netID = :employee AND teamID = :teamId AND area = :area");
		$deleteQuery->execute(array(':employee' => $employee, ':teamId' => $teamId, ':area' => $area));
		$teamsQuery = $db->prepare("SELECT * FROM teams WHERE ID = :teamId");
		$teamsQuery->execute(array(':teamId' => $teamId));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$teamInfo = $teamsQuery->fetch(PDO::FETCH_ASSOC);
	if($teamInfo['isShift']){
		try {
			$updateQuery = $db->prepare("UPDATE employee SET supervisor = :lead WHERE netID = :employee");
			$updateQuery->execute(array(':lead' => '', ':employee' => $employee));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
?>
