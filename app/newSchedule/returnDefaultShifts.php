<?php
require("../includes/includeMeBlank.php");
//This function returns all the shifts for an employee in a given period

$period = $_GET['period'];
$employee = $_GET['employee'];

try {
	$defaultQuery = $db->prepare("SELECT * FROM `scheduleDefault` WHERE employee = :netId AND period = :period AND `deleted`=0");
	$defaultQuery->execute(array(':netId' => $employee, ':period' => $period));
} catch(PDOException $e) {
	exit("error in query");
}
$shiftArray = array();

while($shift = $defaultQuery->fetch(PDO::FETCH_ASSOC))
{
    $shiftArray[] = $shift;
}

echo json_encode($shiftArray);

?>
