<?php 

/*	Name: submitTrades.php
*	Application: Trade Request
*
*	Description: This file is called from displayTrades.php and from the schedule app.  When a user clicks the submit button. 
*	It assigns takerID's from trade requests.
*/
	
	//Standard include file
	require("../includes/includeMeBlank.php");
	
	//Common php functions used in the Trade Request app
	include("tradesFunctions.php");

	//Declare variables
	$employee = $netID; //$_GET['employee'];
	$trades = explode(",", $_GET['trades']); //This will be an array with each element being a string in the form {netID}_{tradeID}_{hour}
	
	foreach($trades as $cur)
	{
	    if ($cur == "") continue;
	    
	    //This will split the string into an array with each part
		$trade = explode("_",$cur);
		
		try {
	    	//Now we want to insert the bid into the database. The database is set up not to allow duplicate entries so we should have an issue with that.
			$insertQuery = $db->prepare("INSERT INTO `scheduleTradeBids` (tradeID, employee, hour, guid)
					VALUES (:id,:employee,:hour,:guid)
	    			ON DUPLICATE KEY UPDATE deleted = 0");
			$insertQuery->execute(array(':id' => $trade[1], ':employee' => $trade[0], ':hour' => $trade[2], ':guid' => newGuid()));
			//Now we update the scheduleTrades DB to show that there are bids for this trade.
			$updateQuery = $db->prepare("UPDATE `scheduleTrades` SET bids = '1' WHERE ID = :id");
			$updateQuery->execute(array(':id' => $trade[1]));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}//foreach 
	
	echo 1;
?>
