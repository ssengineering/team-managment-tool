<?php
require('../includes/includeMeBlank.php');
//This script should return an array of shifts given an area
//Pending needs, we might have permissions for who can see what types of shifts

try {
	$hourTypesQuery = $db->prepare("SELECT * FROM scheduleHourTypes");
	$hourTypesQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}
$shiftTypes = array();
while($cur = $hourTypesQuery->fetch(PDO::FETCH_ASSOC))
{
	$shiftTypes[] = $cur;
}
echo json_encode($shiftTypes);
?>
