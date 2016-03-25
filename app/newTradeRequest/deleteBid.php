<?php 

/*	Name: deleteBid.php
*	Application: Trade Request
*
*	Description: This file is called from displayTrades.php when a user unchecks a trade. 
*	It deletes their bid for the given hour on the given trade.
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
			//Delete trade bid
			$updateQuery = $db->prepare("UPDATE `scheduleTradeBids` SET `deleted`=1 WHERE tradeID = :id AND employee = :employee AND hour = :hour");
			$updateQuery->execute(array(':id' => $trade[1], ':employee' => $trade[0], ':hour' => $trade[2]));
			
			//Check how many trade bids are left for a trade, if none, update the trade to have bids = 0
			$bidsLeftQuery = $db->prepare("SELECT * FROM `scheduleTradeBids` WHERE tradeID = :id AND `deleted`=0");
			$bidsLeftQuery->execute(array(':id' => $trade[1]));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if(!($row = $bidsLeftQuery->fetch(PDO::FETCH_ASSOC))) {
			try {
				$updateQuery = $db->prepare("UPDATE `scheduleTrades` SET bids = '0' WHERE ID = :id");
				$updateQuery->execute(array(':id' => $trade[1]));
		
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}//if

	echo 1;
?>
