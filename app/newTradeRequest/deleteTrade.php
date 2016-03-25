<?php
	
/*	Name: submitTrades.php
*	Application: Trade Request
*
*	Description: This file is called from displayTrades.php when a user clicks the submit button. 
*	It assigns takerID's from trade requests.
*/
	
	//Standard include file
	require("../includes/includeMeBlank.php");
	
	//Declare variables
	$id = '';
	if (isset($_GET['id']))//If the id is set, get it
	{
		$id = $_GET['id'];
	}//if

	//Get the list of trades
	$tradeArray = array();
	if(isset($_POST['JSON']))
	{
		$tradeArray = json_decode($_POST['JSON'],true);
	}//if
	else if(isset($_GET['JSON']))
	{
		$tradeArray = json_decode($_GET['JSON'],true);
	}//else-if
	
	
	if(count($tradeArray, 1))//If the array of trades is > 1 do this
	{
		foreach($tradeArray as $trade)
		{
			//Declare variables
			$id = $trade['ID'];
			
			//Update scheduleTrades and scheduleTradeBids
			try {
				$updateQuery = $db->prepare("UPDATE `scheduleTrades` SET `deleted`=1 WHERE ID = :id");
				$updateQuery->execute(array(':id' => $id));
				$update2Query = $db->prepare("UPDATE `scheduleTradeBids` SET `deleted`=1 WHERE tradeID = :id");
				$update2Query->execute(array(':id' => $id));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}//foreach
		
	}//if
	else if ($id != '')//Otherwise, as long as the $id got set, then do this
	{
		//Update scheduleTrades and scheduleTradeBids
		try {
			$updateQuery = $db->prepare("UPDATE `scheduleTrades` SET `deleted`=1 WHERE ID = :id");
			$updateQuery->execute(array(':id' => $id));
			$update2Query = $db->prepare("UPDATE `scheduleTradeBids` SET `deleted`=1 WHERE tradeID = :id");
			$update2Query->execute(array(':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}//else-if
	
	if(isset($_GET['displayTradesPage']) || isset($_POST['displayTradesPage']))
	{
		header('Location: displayTrades.php');
	}
	else 
	{
		echo 1;
	}
?>
