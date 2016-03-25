<?php
/*
 * Name: reportCardLoadData.php
 * Application: Silent Monitor
 * Site: psp.byu.edu
 * Author: Joshua Terrasas
 *
 * Description: This page is used to load the all the silent monitors for the
 * given user.
 */

require_once('../includes/includeMeBlank.php');

$result = array();

try {
	$silentMonitorQuery = $db->prepare("SELECT * FROM `silentMonitor` WHERE `netID` = :netId AND `submitDate` >= :start AND `submitDate` <= :end AND `completed` = 1 AND `deleted` = 0 ORDER BY `submitDate` ASC");
	$silentMonitorQuery->execute(array(':netId' => $netID, ':start' => $_GET['startDate'], ':end' => $_GET['endDate']));
} catch(PDOException $e) {
	exit("error in query");
}

while($silentMonitor = $silentMonitorQuery->fetch(PDO::FETCH_ASSOC))
{
	$calls = array();

	try {
		$callsQuery = $db->prepare("SELECT * FROM `silentMonitorCalls` WHERE `smid` = :index AND `deleted` = 0");
		$callsQuery->execute(array(':index' => $silentMonitor['index']));
	} catch(PDOException $e) {
		exit("error in query");
	}

	while($call = $callsQuery->fetch(PDO::FETCH_ASSOC))
	{
		$criteria = array();
		try {
			$criteriaQuery = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` INNER JOIN `silentMonitorCriteriaInfo` ON silentMonitorCallCriteria.criteriaIndex = silentMonitorCriteriaInfo.index WHERE silentMonitorCallCriteria.smid = :index AND silentMonitorCallCriteria.callNum = :call AND silentMonitorCriteriaInfo.area = :area");
			$criteriaQuery->execute(array(':index' => $silentMonitor['index'], ':call' => $call['callNum'], ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}

		while($criterion = $criteriaQuery->fetch(PDO::FETCH_ASSOC))
		{
			$criteria[] = $criterion;
		}

		$call['criteria'] = $criteria;
		
		$calls[] = $call;
	}

	$silentMonitor['calls'] = $calls;

	$result[] = $silentMonitor;
}

echo json_encode($result);
?>
