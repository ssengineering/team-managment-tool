<?php 

/*	Name: submitBid.php
*	Application: Trade Request
*
*	Description: This file is called from displayTrades.php when a user checks a trade. 
*	It creates a bid for the given hour on the given trade.
*/
	
	//Standard include file
	require("../includes/includeMeBlank.php");
	
	//Common php functions used in the Trade Request app
	include("tradesFunctions.php");

	if(isset($_GET['id']))
	{
		//Declare variable
		$trade = explode("_", $_GET['id']); //This will be an array with each element being a string in the form {netID}_{tradeID}_{hour}
		
		try {
			//Submit trade bid
			$insertQuery = $db->prepare("INSERT INTO `scheduleTradeBids` (tradeID, employee, hour, guid) 
				VALUES (:id, :employee, :hour, :guid)
				ON DUPLICATE KEY UPDATE deleted = 0");
			$insertQuery->execute(array(':id' => $trade[1], ':employee' => $trade[0], ':hour' => $trade[2], ':guid' => newGuid()));
			//Update trade in scheduleTrades
			$updateQuery = $db->prepare("UPDATE `scheduleTrades` SET bids = '1' WHERE ID = :id"); 
			$updateQuery->execute(array(':id' => $trade[1]));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}//if

	echo 1;
?>
