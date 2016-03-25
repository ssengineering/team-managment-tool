<?php 

/*	Name: submitEntry.php
*	Application: Manager Report
*
*	Description: This php file takes the manager entry submitted on the index.php page and saves it to the DB.
*/

	//Include file to include common functions used throughout the site
	require('../includes/includeMeBlank.php');
	
	//Declare variables
	global $netID;
	global $area;
	$comment = $_POST["comment"];
	$category = $_POST["category"];
	
	//Update DB
	try {
		$insertQuery = $db->prepare("INSERT INTO `managerReports` (`netID`, `comments`, `category`, `area`, `guid`) VALUES (:netId,:comment,:category,:area,:guid)");
		$success = $insertQuery->execute(array(':netId' => $netID, ':comment' => $comment, ':category' => $category, ':area' => $area, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	//Return result
	if ($success){
		echo json_encode(array('status'=>$success));
	} else{
		echo json_encode(array('status'=>$success, 'error'=>"error in query"));
	}//if-else
	
?>
