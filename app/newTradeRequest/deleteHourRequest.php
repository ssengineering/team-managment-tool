<?php

/*	Name: deleteHourRequest.php
*	Application: Trade Request
*
*	Description: This will mark a request for more hours as deleted in the DB table scheduleHourRequests
*/

	//Standard include file
	require('../includes/includeMeBlank.php');

	$id = $_GET['id'];
	try {
		$updateQuery = $db->prepare("UPDATE scheduleHourRequests SET deleted = 1 WHERE netId = :netId AND area = :area");
		$updateQuery->execute(array(':netId' => $id, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	header('Location: displayTrades.php');
?>
