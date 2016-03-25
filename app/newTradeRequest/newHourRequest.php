<?php

/*	Name: newHourRequest.php
*	Application: Trade Request
*
*	Description: This page allows employees to post a request that they want more hours.
*/

	//Standard include file for site header
	require('../includes/includeme.php');
	
	//CSS
	echo '<link rel="stylesheet" type="text/css" href="tradeRequest.css" />';
	echo '<h1 align="center">Request for More Hours</h1>';
	
	
	if (isset($_POST['submit']))//If the submit variable is set, insert or update the new request in the DB and show the employee a response that their new trade hour request has been accepted.
	{
		//Check to see if they've already submitted a request for more hours in the area they're using
		try {
			$requestsQuery = $db->prepare("SELECT * FROM scheduleHourRequests WHERE netId LIKE :netId AND area = :area");
			$requestsQuery->execute(array(':netId' => $netID, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
	
		if ($request = $requestsQuery->fetch(PDO::FETCH_ASSOC))
		{
			try {
				$updateQuery = $db->prepare("UPDATE scheduleHourRequests SET notes = :notes, deleted = '0' WHERE netId LIKE :netId AND area = :area");
				$updateQuery->execute(array(':notes' => $_POST['reason'], ':netId' => $netID, ':area' => $area));
			} catch(PDOException $e) {
				exit("error in query");
			}
			//Print page content
			echo "	<div align='center'>
						<h2>Thank you, your request has been submitted!</h2>
						<a href='displayTrades.php'>Return to Trade Requests</a>
					</div>";
		}//if
		else //Insert the new request in the DB and show the employee a response that their new trade hour request has been accepted.
		{
			//Declare variables
			try {
				$insertQuery = $db->prepare("INSERT INTO scheduleHourRequests (netID, notes, area, guid) VALUES (:netId,:notes,:area,:guid)");
				$insertQuery->execute(array(':netId' => $netID, ':notes' => $_POST['reason'], ':area' => $area, ':guid' => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}
			//Print page content
			echo "	<div align='center'>
						<h2>Thank you, your request has been submitted!</h2>
						<a href='displayTrades.php'>Return to Trade Requests</a>
					</div>";
		}//else
		
	}//if
	else //Ask them for a description of why they want more hours
	{
		//Print page content
		echo '	<div align="center">
					<p>Please write a description of why you want more hours.</p>
					<form method="post">
						<textarea id="reason" name="reason" cols="40" rows="3"></textarea>
						<br /><br /><input type="submit" id="submit" name="submit" value="Submit" />
					</form>
					<p>Note: If you currently have a request for hours it will be updated with the new description you enter here.</p>
				</div>';
	}//else
 	
 	//Standard include file for footer
	require('../includes/includeAtEnd.php');
?>
