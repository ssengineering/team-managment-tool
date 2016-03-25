<?php 

/*	Name: submitFinal.php
*	Application: Weekly Report
*
*	Description: This php file takes the highlights/accomplishments, challenges, and 
*	interactions managers review on the finalReport.php page. 
*
*	If they were checked they are sent in an email report to the director, the DB is
*	also updated with whether or not they were sent to the director or not.
*	
*	If they were edited, whether they were sent in the report to the director or not,
*	the DB is updated with the edits, and a date of when the edit occurred is saved.
*/

	//Include file to include common functions used throughout the site
	require('../includes/includeMeBlank.php');


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Update the DB

	//Declare variables
	$emailText = $_POST['emailText'];
	$commentsArray = json_decode($_POST['commentsArray'], true);
	$today = date("Y-m-d H:i:s");
	$query = "";
	$pattern = "/ERROR.*/";

	$transactionSuccess = true;
	
	//Start the transaction, if any fail we'll rollback, send an error message and not send the email.  Otherwise we'll commit the transaction and send the email.
	$db->beginTransaction();

	//Run update query for each entry
	foreach ($commentsArray as $commentObject)
	{
		if($commentObject['editedText'] == null)//If the comment wasn't edited use this query
		{
			try {
				$updateQuery = $db->prepare("UPDATE managerReports SET submitted=:checked WHERE ID=:id");
				$transactionSuccess = $updateQuery->execute(array(':checked' => $commentObject['checked'], ':id' => $commentObject['id']));
			} catch(PDOException $e) {
				$transactionSuccess = false;
			}
		}//if
		
		else //If the comment was edited use this query
		{
			try {
				$updateQuery = $db->prepare("UPDATE managerReports SET submitted=:checked, editDate=:day, comments=:text WHERE ID=:id");
				$transactionSuccess = $updateQuery->execute(array(':checked' => $commentObject['checked'], ':day' => $today, ':text' => $commentObject['editedText'], ':id' => $commentObject['id']));
			} catch(PDOException $e) {
				$transactionSuccess = false;
			}
		}//else
	}//foreach
	
	//If the transaction was successful commit it and then go on to do the email.  
	if($transactionSuccess){
		$db->commit();

		//send email
		$to = $_POST['to'];
		$cc = $_POST['cc'];
		$subject = getAreaName(). " Manager Weekly Report";
		$emailBody = $_POST['emailText'];
		$from = 'From:'.getEmployeeNameByNetId($netID).' <'.getEmployeeEmailByNetId($netID).">\r\n";
		$from .= "Cc: ".$cc."\r\n";
		$from .= "Return-Path: ".getEmployeeEmailByNetId($netID)."\r\n";
		$from .= "MIME-Version: 1.0\r\n";
		$from .= "Content-Type: text/html;\r\n";
	
		//Check if email was successful
		if (mail($to,$subject,wordwrap($emailBody, 70),$from)){
			echo json_encode(array('status'=>true));
			
		} else{
			echo json_encode(array('status'=>false, 'error'=>'There was an error submitting the email to the email server.'));
		}//if-else

	} else{//Otherwise rollback the transaction, set the status and error message and don't send the email.
		$db->rollBack();
		echo json_encode(array('status'=>$transactionSuccess, 'error'=>"error in query"));
		return;
	}
?>

