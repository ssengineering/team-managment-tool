<?php
require('../../includes/includeMeBlank.php');

$teamId = $_GET['id'];

try {
	$employeeQuery = $db->prepare("SELECT `netID`, (SELECT CONCAT(`firstName`,' ',`lastName`) AS `name` FROM `employee` WHERE `employee`.`netID` = `teamMembers`.`netID`) AS `name`, `isSupervisor` FROM `teamMembers` WHERE `teamID` = :teamId AND `area` = :area ORDER BY `name` ASC");
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
?>
