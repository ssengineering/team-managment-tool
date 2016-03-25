<?php 

/*	Name: deleteEntry.php
*	Application: Manager Report
*
*	Description: This php file takes the id of an entry and marks it as deleted in the DB.
*/

	//Include file to include common functions used throughout the site
	require('../includes/includeMeBlank.php');
	
	//Declare variables
	$commentID = $_POST["id"];
	
	//Update DB
	try {
		$updateQuery = $db->prepare("UPDATE `managerReports` SET `deleted` = 1 WHERE `ID` = :id");
		$success = $updateQuery->execute(array(':id' => $commentID));
	} catch(PDOException $e) {
		$success = false;
	}
	
	//Return result
	if ($success){
		echo json_encode(array('status'=>$success));
	}else{
		echo json_encode(array('status'=>$success, 'error'=>"error in query"));
	}//if-else
	
?>
