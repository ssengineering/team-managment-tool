<?php 

/*	Name: saveChanges.php
*	Application: Manager Report
*
*	Description: This php file updates any changes made to manager report entries from the index.php page
*/

	//Include file to include common functions used throughout the site
	require('../includes/includeMeBlank.php');
	
	//Declare variables
	$id = $_POST["id"];
	$comments = $_POST["comments"];
	$checked = $_POST["checked"];
	$today = date("Y-m-d H:i:s");
	
	//If the checked field needs to be updated
	if ($comments != "") {
		try {
			$updateQuery = $db->prepare("UPDATE `managerReports` SET `comments` = :comments, `editDate` = :day WHERE `ID` = :id");
			$success = $updateQuery->execute(array(':comments' => $comments, ':day' => $today, ':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
	} else {
		try {
			$updateQuery = $db->prepare("UPDATE `managerReports` SET `checked` = :checked, `editDate` = :day WHERE `ID` = :id");
			$success = $updateQuery->execute(array(':checked' => $checked, ':day' => $today, ':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}//if-else
	
	//Return result
	if ($success){
		echo json_encode(array('status'=>$success));
	} else{
		echo json_encode(array('status'=>$success, 'error'=>'error in query'));
	}//if-else

	
?>
