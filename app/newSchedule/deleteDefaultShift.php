<?php
require("../includes/includeMeBlank.php");
//This file deletes shifts from the default shift, then removes them from weekly shift
$shiftArray = array();
if(isset($_POST['JSON'])){
	$shiftArray = json_decode($_POST['JSON'],true);
} else {
	$shiftArray = json_decode($_GET['JSON'],true);
}

foreach ($shiftArray as $shift)
{
	try {
		$updateQuery = $db->prepare("UPDATE `scheduleDefault` SET `deleted`=1 WHERE ID = :id");
		$updateQuery->execute(array(':id' => $shift['ID']));
		$update2Query = $db->prepare("UPDATE `scheduleWeekly` SET `deleted`=1 WHERE defaultID = :id AND CONCAT(`endDate`, ' ', `endTime`) > NOW()");
		$update2Query->execute(array(':id' => $shift['ID']));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>

