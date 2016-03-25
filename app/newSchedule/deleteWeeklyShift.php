<?php
require("../includes/includeMeBlank.php");
//This file deletes shifts from the weekly shift
	$shiftArray = array();
if(isset($_POST['JSON'])){
	$shiftArray = json_decode($_POST['JSON'],true);
} else {
	$shiftArray = json_decode($_GET['JSON'],true);
}

foreach ($shiftArray as $shift)
{
	try {
		$weeklyQuery = $db->prepare("UPDATE `scheduleWeekly` SET `deleted`=1 WHERE ID = :id");
		$weeklyQuery->execute(array(':id' => $shift['ID']));
		$tradesQuery = $db->prepare("UPDATE `scheduleTrades` SET `deleted`=1 WHERE shiftId = :id");
		$tradesQuery->execute(array(':id' => $shift['ID']));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>

