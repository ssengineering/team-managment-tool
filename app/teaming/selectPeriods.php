<?php

header("Content-type: application/json");
require('../includes/includeMeBlank.php');

$area = $_GET["area"];

	try {
		$periodDatesQuery = $db->prepare("SELECT DISTINCT `startDate` , `endDate` FROM `teamingLog` WHERE `area`=:area ORDER BY `startDate` DESC LIMIT 20");
		$periodDatesQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$periods = array();
	while($period = $periodDatesQuery->fetch(PDO::FETCH_ASSOC))
	{
		$period['dateRangeHumanReadable'] = date("F j, Y",strtotime($period['startDate'])).' '.' - '.date("F j, Y",strtotime($period['endDate']));
		$periods[] = $period;
	}

	echo json_encode($periods);
?>
