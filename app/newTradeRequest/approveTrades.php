<?php

/*	Name: approveTrades.php
*	Application: Trade Request
*
*	Description: 
*/

//Standard include file for site header
require('../includes/includeMeBlank.php');

//Common php functions used in the Trade Request app
require('tradesFunctions.php');

//This file will be called from displayAvailableTrades.php in order to approve trades that were pending. 

/*
Things this file needs to do
1.Take in a list of trade ID's that have been approved. Find the trade bids for that trade, and approve them.
2.Cycle through them and create new shifts for the employees that took them
3.remove the giver of the trades hours so they are no longer responsible for them
4.email the appropriate people
*/

if(isset($_GET['trades']))
{
	global $area;
	$approvedTrades = explode(",",$_GET['trades']);
	foreach($approvedTrades as $curID) //For each trade
	{  
		try {
			$tradesQuery = $db->prepare("SELECT * FROM `scheduleTrades` WHERE ID = :id AND `deleted`=0");
			$tradesQuery->execute(array(':id' => $curID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$curTrade = $tradesQuery->fetch(PDO::FETCH_ASSOC);
		
		//Create taker shifts
		$newShifts = createShiftsFromBids($curTrade); //Create shifts from the bids	

		foreach($newShifts as $shift) //for each new shift 
		{
			$conflict = true;
			
			while($conflict == true)
			{
				// Check if taker is scheduled for non-work type hours
				try {
					$conflictQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE `employee` = :employee AND `deleted` = '0' AND
						`hourType` IN (SELECT `ID` FROM `scheduleHourTypes` WHERE `nonwork` = '1') AND
						((CONCAT(`startDate`, ' ', `startTime`) <=  :start AND CONCAT(`endDate`, ' ', `endTime`) > :start1) OR
						(CONCAT(`startDate`, ' ', `startTime`) < :end AND CONCAT(`endDate`, ' ', `endTime`) >= :end1) OR
						(CONCAT(`startDate`, ' ', `startTime`) < :start2 AND CONCAT(`endDate`, ' ', `endTime`) > :end2))");
					$conflictQuery->execute(array(
						':employee'  => $shift['employee'],
						':start'     => $shift['startDate']." ".$shift['startTime'],
						':start1'    => $shift['startDate']." ".$shift['startTime'],
						':end'       => $shift['endDate']." ".$shift['endTime'],
						':end1'      => $shift['endDate']." ".$shift['endTime'],
						':start2'    => $shift['startDate']." ".$shift['startTime'],
						':end2'      => $shift['endDate']." ".$shift['endTime'],
						));
				} catch(PDOException $e) {
					exit("error in query");
				}
				
				if($conflictShift = $conflictQuery->fetch(PDO::FETCH_ASSOC))
				{	
					// This logic handles the editing of the non-work hours
					// Check if the trade start time is between the original, non-work shift
					if(strcmp($shift['startDate']." ".$shift['startTime'], $conflictShift['startDate']." ".$conflictShift['startTime']) > 0 && strcmp($shift['startDate']." ".$shift['startTime'], $conflictShift['endDate']." ".$conflictShift['endTime']) < 0)
					{
						// Check if the trade end time is between the original, non-work shift
						if(strcmp($shift['endDate']." ".$shift['endTime'], $conflictShift['startDate']." ".$conflictShift['startTime']) > 0 && strcmp($shift['endDate']." ".$shift['endTime'], $conflictShift['endDate']." ".$conflictShift['endTime']) < 0)
						{
							// Shift array for split original shift
							// Set new original start time to the trade end time
							// Set new original end time to the original end time
							$splitShift = array();
							$splitShift['employee'] = $conflictShift['employee'];
							$splitShift['area'] = $conflictShift['area'];
							$splitShift['hourType'] = $conflictShift['hourType'];
							$splitShift['startDate'] = $shift['endDate'];
							$splitShift['startTime'] = $shift['endTime'];
							$splitShift['endDate'] = $conflictShift['endDate'];
							$splitShift['endTime'] = $conflictShift['endTime'];

							// Calculate hourTotal by dividing the difference between two Unix time spamps by the number of seconds in an hour
							$splitShift['hourTotal'] = ((strtotime($splitShift['startDate']." ".$splitShift['startTime']) - strtotime($splitShift['endDate']." ".$splitShift['endTime'])) / 3600);

							// If the new shift is of 0 length, then destroy it because it does not need to be added (ie 10am - 10am);
							if ($splitShift['hourTotal'] == 0)
							{
								unset($splitShift);
							}

							// Set the original shift end time to the trade start time
							$conflictShift['endTime'] = $shift['startTime'];
							$conflictShift['endDate'] = $shift['startDate'];
							$conflictShift['hourTotal'] = ((strtotime($conflictShift['startDate']." ".$conflictShift['startTime']) - strtotime($conflictShift['endDate']." ".$conflictShift['endTime'])) / 3600);
						}
						else
						{
							// Set original end time to trade start time
							$conflictShift['endTime'] = $shift['startTime'];
							$conflictShift['endDate'] = $shift['startDate'];
							$conflictShift['hourTotal'] = ((strtotime($conflictShift['startDate']." ".$conflictShift['startTime']) - strtotime($conflictShift['endDate']." ".$conflictShift['endTime'])) / 3600);
						}
					}
					else
					{
						// Set original start time to trade end time
						$conflictShift['startTime'] = $shift['endTime'];	
						$conflictShift['startDate'] = $shift['endDate'];
						$conflictShift['hourTotal'] = ((strtotime($conflictShift['startDate']." ".$conflictShift['startTime']) - strtotime($conflictShift['endDate']." ".$conflictShift['endTime'])) / 3600);
					}

					$conflictShiftStart = strtotime($conflictShift['startDate']." ".$conflictShift['startTime']);
					$conflictShiftEnd = strtotime($conflictShift['endDate']." ".$conflictShift['endTime']);
					$shiftStart = strtotime($shift['startDate']." ".$shift['startTime']);
					$shiftEnd = strtotime($shift['endDate']." ".$shift['endTime']);

					// If the traded shift is the exact same start and end times as the conflicting shift
					if( $conflictShiftStart == $shiftStart && $conflictShiftEnd == $shiftEnd )
					{
						// Delete the conflicting shift
						try {
							$updateQuery = $db->prepare("UPDATE `scheduleWeekly` SET `deleted` = 0 WHERE `ID` = :id");
							$updateQuery->execute(array(':id' => $conflictShift['ID']));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
					else
					{
						// Edit original shift and insert any split shifts
						try {
							$update2Query = $db->prepare("UPDATE `scheduleWeekly` SET `startDate` = :startDate, `startTime` = :startTime, `endDate` = :endDate, `endTime` = :endTime  WHERE `ID` = :id");
							$update2Query->execute(array(
								':startDate' => $conflictShift['startDate'],
								':startTime' => $conflictShift['startTime'],
								':endDate'   => $conflictShift['endDate'],
								':endTime'   => $conflictShift['endTime'],
								':id'        => $conflictShift['ID']));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}

					if(isset($splitShift))
					{
						insertNewShifts($splitShift);
						unset($splitShift);
					}
				}
				else
				{
					$conflict = false;
				}
			}
			
			unset($conflict);

			insertNewShifts($shift); 		//Insert that shift into the weeklySchedule
		}
		
		$newGiverShifts = moveShiftFromGiver($curTrade); //Get the new shifts for the giver of the trade
		
		//Create new Trades
		$newTrades = createNewTradeEntries($curTrade); //Get the new trades we need to insert and associate
		
		//Delete old Giver Shift
		try {
			$update3Query = $db->prepare("UPDATE `scheduleWeekly` SET `deleted`=1 WHERE ID = :id");
			$update3Query->execute(array(':id' => $curTrade['shiftId']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		
		//Create new Giver Shifts
		if($newGiverShifts != null)
		{
			$createdShifts = array();
			foreach($newGiverShifts as $shift) //for each new shift 
			{		
				$createdShifts[] = insertNewShifts($shift);//Insert that shift into the weeklySchedule
				
			}//foreach

			
			if($newTrades != null)
			{			
				foreach($newTrades as $trade) //for each new shift 
				{		
					insertNewTrades($trade);//Insert that shift into the weeklySchedule
					
				}//foreach
				
			}//if

			//For each newly created shift, find trades that still exist that correspond to these new shifts and assign them accordingly
			for($i=0;$i<count($createdShifts);$i++)
			{
				associateTradesToShifts($createdShifts[$i]);
			}//for

		}//if
		
		//Approve trade
		try {
			$update4Query = $db->prepare("UPDATE `scheduleTrades` SET approvedBy = :netId, approvedOn = CURDATE(), bids = '2' WHERE ID = :id");
			$update4Query->execute(array(':netId' => $netID, ':id' => $curID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		
		// Notify the person whose hours were taken
		$persons = getReceivers($curTrade['postedBy'], $areaGuid, "d13b5f2c-cc5c-11e5-bdda-0242ac110003");
		$tradeMessage = "Your trade request for ".date("D, M jS, Y",strtotime($curTrade['startDate']))." from ".date("g:ia",strtotime($curTrade['startTime']))." to ".date("g:ia",strtotime($curTrade['endTime']))." has been taken. Please see your updated schedule";
		//Call notify function using the object $persons created above as the third argument.
		notify("d13b5f2c-cc5c-11e5-bdda-0242ac110003", $tradeMessage, $persons);

		// Notify the person who took the hours
		try {
			$giverStmt = $db->prepare("SELECT employee FROM scheduleTradeBids WHERE tradeID=:id");
			$giverStmt->execute(array(":id" => $curID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($giver = $giverStmt->fetch()) {
			//Notify and getReceivers functions called a second time for the receiver of the trade.
			$persons = getReceivers($giver->employee, $areaGuid, "b81177ff-cc5c-11e5-bdda-0242ac110003");
			$tradeMessage = "Your trade request bid for ".date("D, M jS, Y",strtotime($curTrade['startDate']))." from ".date("g:ia",strtotime($curTrade['startTime']))." to ".date("g:ia",strtotime($curTrade['endTime']))." has been accepted. Please see your updated schedule";
 
			//Call notify function using the object $persons created above as the third argument.
			notify("b81177ff-cc5c-11e5-bdda-0242ac110003", $tradeMessage, $persons);
		}
	}
	echo "1";
}


//This function should take in an employee, tradeId, and startHour and return a shift to be added
//To the employee's schedule.
//$data = the trade entry from `scheduleTrades`

//TODO make some sort of logic for determining the start and end dates of the new shifts based on the dates for the shift that's being traded away
function createShiftsFromBids($data)
{
	global $hourSize;
	$time = date('H:i:s',strtotime($data['startTime'])); //The start of the trade
	$newShifts = array(); //This will hold all of the new shift we need to create	
	$curShift = array();
	$curShift['employee'] = "";
	$curShift['hourType'] = $data['hourType']; //set hour type
	$curShift['area'] = $data['area']; //set area
	$curShift['startDate'] = $data['startDate']; //TODO set startDate
	$curShift['hourTotal'] = 0; //start hour total at 0
	//Columns needed to create a new shift: Employee|startTime|startDate|endTime|endDate|hourType|hourTotal|area|trade
	while($time != $data['endTime']) //This should loop through all of the hours in the trade.
	{
		$curBid = getBids($time,$data['ID']);
		//Base Cases
		if($time == $data['startTime'] && $curBid != 0) //Start of Trade
		{
			$curShift['startTime'] = $data['startTime']; //set start time
			$curShift['employee']  =  $curBid['employee']; //set employee
		}	
		else if($curBid == 0 || $curShift['employee'] != $curBid['employee'])//Different Bidder //End of Trade //No Bid for hour 
		{
			//Finish data for current new shift
			$curShift['endTime'] = $time;
			if ($data['endTime'] == '00:00:00' && $time != '00:00:00')
			{
				$curShift['endDate'] = $data['startDate'];
			}
			else
			{
				$curShift['endDate'] = $data['endDate']; //TODO
			}
			
			if($curShift['employee'] != ""){
				$newShifts[] = $curShift; //Insert it into the new shift array as long as we're not in a null shift.
			}

			//reset curShift
			$curShift['startDate'] = $data['startDate']; //TODO
			$curShift['startTime'] = $time;
			$curShift['hourTotal'] = 0;
			$curShift['employee'] = "";			
			if($curBid != 0){
				$curShift['employee'] = $curBid['employee'];
			}
		}
				
		$curShift['hourTotal'] = $curShift['hourTotal'] + $hourSize; //Add an hour total
		

		//update Hour for next pass through
		$timestamp = strtotime($time) + 60*(60 * $hourSize); 
		$time = date('H:i:s', $timestamp);
		$curShift['endTime'] = $time; //this updates the time to the next hour
		
		if($time == $data['endTime'] && $curBid != 0){ //This checks if we're at the end of the trade and inserts the trade into the array
			$curShift['endTime'] = $time;
			$curShift['endDate'] = $data['endDate']; //TODO
			$newShifts[] = $curShift; //Insert it into the new shift array
		}
	}

	return $newShifts;
}//createShiftsFromBids

//This inserts the new shifts into the Database and returns the new shift id
function insertNewShifts($shift)
{
	global $db;
	$startTime = $shift['startTime'];
	$endTime   = $shift['endTime'];
	$startDate = $shift['startDate'];
	$endDate   = $shift['endDate'];
	$employee  = $shift['employee'];
	$hourType  = $shift['hourType'];
	$hourTotal = $shift['hourTotal'];
	$area      = $shift['area'];

	try {
		$insertQuery = $db->prepare("INSERT INTO `scheduleWeekly` (employee,hourType,startTime,startDate,endTime,endDate,hourTotal,area,guid)
			VALUES(:employee,:type,:startTime,:startDate,:endTime,:endDate,:total,:area,:guid)");
		$insertQuery->execute(array(
			':employee'  => $employee,
			':type'      => $hourType,
			':startTime' => $startTime,
			':startDate' => $startDate,
			':endTime'   => $endTime,
			':endDate'   => $endDate,
			':total'     => $hourTotal,
			':area'      => $area,
			':guid'      => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
	//return the ID of the shift that was just inserted
	return $db->lastInsertId();
}//insertNewShifts


//Insert new trades into the database
function insertNewTrades($trade)
{
	global $db;
	$employee   = $trade['postedBy'];
	$postedDate = $trade['postedDate'];
	$shiftId    = $trade['shiftId'];
	$startTime  = $trade['startTime'];
	$endTime    = $trade['endTime'];
	$startDate  = $trade['startDate'];
	$endDate    = $trade['endDate'];	
	$hourType   = $trade['hourType'];
	$notes      = $trade['notes'];
	$area       = $trade['area'];

	try {
		$insertQuery = $db->prepare("INSERT INTO `scheduleTrades` (postedBy,postedDate,shiftId,startTime,startDate,endTime,endDate,hourType,notes,area,guid)
			VALUES(:employee,:posted,:shiftId,:startTime,:startDate,:endTime,:endDate,:type,:notes,:area,:guid)");
		$insertQuery->execute(array(
			':employee'  => $employee,
			':posted'    => $postedDate,
			':shiftId'   => $shiftId,
			':startTime' => $startTime,
			':startDate' => $startDate,
			':endTime'   => $endTime,
			':endDate'   => $endDate,
			':type'      => $hourType,
			':notes'     => $notes,
			':area'      => $area,
			':guid'      => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}//insertNewTrades


//This returns the bid from DB
function getBids($hour,$tradeID)
{
	global $db;
	try {
		$tradeBidsQuery = $db->prepare("SELECT * FROM `scheduleTradeBids` WHERE tradeID = :tradeId AND hour = :hour AND `deleted`=0 ORDER BY hour");
		$tradeBidsQuery->execute(array(':tradeId' => $tradeID, ':hour' => $hour));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	if($bid = $tradeBidsQuery->fetch(PDO::FETCH_ASSOC)) {
		return $bid;
	} else {
		return 0;
	}
}//getBids


//This function will do the following:
/*
1. If the entire shift is traded, mark as traded, remove default ID
2. If the trade only represents a portion of shift, resize the shift accordingly or break the shift into 2 parts (before trade and after trade)
*/
function moveShiftFromGiver($trade)
{
	global $db;
	try {
		$weeklyQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE ID = :id AND `deleted`=0");
		$weeklyQuery->execute(array(':id' => $trade['shiftId']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$shift = $weeklyQuery->fetch(PDO::FETCH_ASSOC);
	
	if(checkFullTrade($shift,$trade))//If the entire shift is traded
	{
		try {
			$updateQuery = $db->prepare("UPDATE `scheduleWeekly` SET trade = 'traded', defaultID = NULL WHERE ID = :id");
			$updateQuery->execute(array(':id' => $shift['ID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}//if
	else//Else the shift is only partially taken
	{
		//We need to cycle from the beginning and find the first occurence of a bid, then set the shift end time to that.
		global $hourSize;
		$time = date('H:i:s',strtotime($shift['startTime'])); //The start of the trade
		$newShifts = array(); //This will hold all of the new shifts we need to create	
		$curShift = array();
		$curShift['employee']  =  $shift['employee']; //set employee
		$curShift['hourType'] = $shift['hourType']; //set hour type
		$curShift['area'] = $shift['area']; //set area
		$curShift['startDate'] = $shift['startDate']; //TODO set startDate
		$curShift['endDate'] = $shift['endDate']; //TODO set endDate
		$curShift['hourTotal'] = 0; //start hour total at 0
		$curShift['startTime'] = "new"; //Initialize startTime to 0
		$curShift['endTime'] = "new";   //Initialize endTime to 0
		
		//Columns needed to create a new shift: Employee|startTime|startDate|endTime|endDate|hourType|hourTotal|area|trade
		while($time != $shift['endTime']) //This should loop through all of the hours in the trade.
		{
			$curBid = bidForHour($time,$trade['ID']);
			if(!$curBid) //If there isn't a bid for this hour
			{
				if($curShift['startTime'] == "new") //As long as we haven't started a shift update the startTime
				{		
					$curShift['startTime'] = $time; 
				}//if
				
				$curShift['hourTotal'] = $curShift['hourTotal'] + $hourSize; //Add an hour total
			}//if
			else //There IS a bid for this hour
			{
				if($curShift['startTime'] != "new") //There is a shift started
				{
					$curShift['endTime'] = $time;
					if ($shift['endTime'] == '00:00:00' && $curShift['endTime'] != '00:00:00')
					{
						$curShift['endDate'] = $shift['startDate'];
					}
					else
					{
						$curShift['endDate'] = $shift['endDate'];
					}
					$newShifts[] = $curShift; //Add Shift to array
					
					//Reset start time and hour total
					$curShift['startTime'] = "new";
					$curShift['hourTotal'] = 0;
				}//if
			}//else		

			//Update Hour for next pass through
			$timestamp = strtotime($time) + 60*(60 * $hourSize); 
			$time = date('H:i:s', $timestamp);
			$curShift['endTime'] = $time; //this updates the time to the next hour
			
			if($time == $shift['endTime'] && !$curBid)//This checks if we're at the end of the trade and inserts the trade into the array
			{
				$curShift['endTime'] = $time;
				if ($shift['endTime'] == '00:00:00' && $curShift['endTime'] != '00:00:00')
				{
					$curShift['endDate'] = $shift['startDate'];
				}
				else
				{
					$curShift['endDate'] = $shift['endDate'];
				}
				$newShifts[] = $curShift; //Insert it into the new shift array
			}//if

		}//while

		return $newShifts;
		
	}//else

}//moveShiftFromGiver



//This function checks if a trade emcompases an entire shift and that entire trade is taken
function checkFullTrade($shift,$trade)
{
	global $hourSize, $db;
	$totalHours = computeHourTotal($shift['startTime'],$shift['endTime']);
	try {
		$bidsQuery = $db->prepare("SELECT COUNT(`ID`) FROM `scheduleTradeBids` WHERE tradeID = :id AND `deleted`=0");
		$bidsQuery->execute(array(':id' => $trade['ID']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $bidsQuery->fetch(PDO::FETCH_NUM);
	$numBids = $result[0];
	if($numBids >= ($totalHours/$hourSize)) {
		return 1;
	}
	return 0;
}//checkFullTrade

//This function is designed to make sure whatever is left of the trade is still available to be taken appropriately.

/*BROKEN
* Something in here is not creating the new trades correctly.  if there isn't a bid it just creates an exact copy of the 
* old trade and puts it in the scheduleTrades table in the DB.
*/
function createNewTradeEntries($trade)
{
	global $hourSize;
	$time = date('H:i:s',strtotime($trade['startTime'])); //The start of the trade
	$newTrades = array(); //This will hold all of the new shift we need to create	
	$curShift = array();
	$curShift['postedBy'] = $trade['postedBy']; 	//set posted by
	$curShift['postedDate'] = $trade['postedDate']; //set posted Date
	$curShift['hourType'] = $trade['hourType']; 	//set hour type
	$curShift['area'] = $trade['area']; 			//set area
	$curShift['shiftId'] = $trade['shiftId']; 		//set Shift ID
	$curShift['startDate'] = $trade['startDate']; 	//TODO set startDate
	$curShift['endDate'] = $trade['endDate']; //TODO
	$curShift['notes'] = $trade['notes']; 			//set notes
	$curShift['startTime'] = "new";
	$curShift['endTime'] = "new";
	
	//Columns needed to create a new trade: postedBy|startTime|startDate|endTime|endDate|hourType|area|notes|shiftID |Posted Date
	while($time != $trade['endTime']) //This should loop through all of the hours in the trade.
	{
		$curBid = bidForHour($time,$trade['ID']); //1 if bid, 0 if no bid

		// Get date of day after shift start date
		$dayAfterStartDate = date('Y-m-d', strtotime($curShift['startDate'] . ' + 1 days'));

		//Base Cases
		if(!$curBid) //if there's no bid
		{
			if($curShift['startTime'] == "new")//and we haven't started a new trade
			{ 
				$curShift['startTime'] = $time; //set start time
			}//if
		}//if
		else 
		{
			if($curShift['startTime'] != "new") //There is a shift started
			{
				// Test to handle trades that end on midnight correctly. This fixes T283.
				if($curShift['endDate'] == $dayAfterStartDate && $trade['endTime'] == '00:00:00' && $time != '00:00:00')
				{
					$curShift['endDate'] = $curShift['startDate'];
				}
				$curShift['endTime'] = $time;
				$newTrades[] = $curShift; //Add Shift to array
				
				//Reset start time and hour total
				$curShift['startTime'] = "new";
			}//if
		}//else

		//Update Hour for next pass through
		$timestamp = strtotime($time) + 60 * (60 * $hourSize); 
		$time = date('H:i:s', $timestamp);

		if($time == $trade['endTime'] && !$curBid)//This line was in the moveShiftFromGiver function
		{
			$curShift['endTime'] = $time;
			//If trade ends at midnight set the start date to the next day
			if($curShift['endTime'] == "00:00:00" && $curShift['endDate'] == $curShift['startDate']){
				$curShift['endDate'] = $dayAfterStartDate;
			}
			$newTrades[] = $curShift; //Insert it into the new shift array
		}//if
	}//while

	return $newTrades;

}//createNewTradeEntries


//Takes all trades that are associated with a newly created shift and assigns them the correct shiftId
function associateTradesToShifts($shiftID){
	global $db;
	//get new shift information
	try {
		$scheduleQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE ID=:id");
		$scheduleQuery->execute(array(':id' => $shiftID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$shift = $scheduleQuery->fetch(PDO::FETCH_ASSOC);

	$queryString = "SELECT * FROM `scheduleTrades` WHERE postedBy=:postedBy AND startDate>=:startDate AND endDate<=:endDate AND startTime>=:startTime AND endTime<=:endTime AND deleted=0";
	$queryParams = array(
		':postedBy'  => $shift['employee'],
		':startDate' => $shift['startDate'],
		':endDate'   => $shift['endDate'],
		':startTime' => $shift['startTime'],
		':endTime'   => $shift['endTime']);
	//if trade ends at midnight use this query instead
	if($shift['endTime'] == "00:00:00"){
		$queryString = "SELECT * FROM `scheduleTrades` WHERE postedBy=:postedBy AND startDate=:startDate AND endDate<=:endDate AND startTime>=:startTime AND deleted=0";
		unset($queryParams[':endTime']);
	}

	try {
		$tradesOnShiftQuery = $db->prepare($queryString);
		$tradesOnShiftQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}

	//for each trade that corresponds to the new shift, update shiftID
	while($trades = $tradesOnShiftQuery->fetch(PDO::FETCH_ASSOC))
	{
		try {
			$updateQuery = $db->prepare("UPDATE `scheduleTrades` SET shiftId=:id WHERE ID=:tradeId");
			$updateQuery->execute(array(':id' => $shiftID, ':tradeId' => $trades['ID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}


?>
