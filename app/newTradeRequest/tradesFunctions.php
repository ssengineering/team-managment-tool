<?php

/*	
*	Name: tradesFunctions.php
*	Application: Trade Request
*
*	Description: This file contains helper functions for the Trade Request app.
*/

//Populates the trades that are available to take or have not been approved.
function populateTradesPage()
{
	global $db, $netID, $area;
	$startDate = date('Y-m-d');
	
	//Pull all trades from the database that have a startDate >= the $startDate
	try {
		$tradesQuery = $db->prepare("SELECT * FROM `scheduleTrades` WHERE startDate >= :start AND bids < '2' AND `deleted` = 0 AND `area` = :area ORDER BY `startDate`, `startTime` ASC");
		$tradesQuery->execute(array(':start' => $startDate, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	//Build an array of trades
	$tradesArray = array();
	$index = 0;
	while($cur = $tradesQuery->fetch(PDO::FETCH_ASSOC))
	{
		$tradesArray[$index] = $cur;
		$index++;
	}//while
	
	//If there aren't any trades display the following
	if(count($tradesArray) < 1)
	{
		echo "<h1>There are currently no trades available</h1>";
	}//if
	else//Otherwise create tables with the trade information
	{
		//Headers for tradeTable
		echo "	<table class='tradeTable'>
				<tr>
					<th width='17%'>Date</th>
					<th width='20%'>Name</th>
					<th>Notes</th>
				</tr>
			</table>";
			
		//Loop through each trade and print out a table with the trade information
		foreach($tradesArray as $trade)
		{
			//Check to see if the table row should be highlighted because the entire trade is taken
			$highlight = checkHighlight($trade);
			
			if($highlight)//Add styling if the row should be highlighted
			{
				$highlightStyle = "style='background-color:#47BB47'";
			}//if
			else//Otherwise don't add styling
			{
				$highlightStyle = "";
			}//else
		
		
			/*This table displays the date of the trade, name of the employee wanting to trade their hours, 
			*and any notes the employee made.*/
			echo "	<table class='tradeTable'>
						<tr>
							<td width='17%'" .$highlightStyle. ">
								<span style='cursor:pointer'>
									<div id='title' onclick='$(\"#".$trade['ID']."_".$trade['postedBy']."\").slideToggle(\"fast\")'>
										<li class='ui-state-default ui-corner-all'>
											<span class='ui-icon ui-icon-triangle-1-s'></span>
										</li>&nbsp;" .date('D j M Y',strtotime($trade['startDate'])). "
									</div>
								</span>
							</td>
							<td width='20%'" .$highlightStyle. ">" .getEmployeeNameByNetId($trade['postedBy']). "</td>
							<td " .$highlightStyle. ">" .stripslashes($trade['notes']). "</td>
						</tr>
					</table>
				";
		
			//Check to see if the employee has rights to delete the trade.  If they do then include a delete button.
			if($trade['postedBy'] == $netID || can("approve", "8d50e67c-53db-4a56-af2e-502d0d770bef")) //tradeRequest resource
			{
				$deleteTradeButton = "<th><a href='deleteTrade.php?id=".$trade['ID']."&displayTradesPage=yes'>Delete Trade</a></th>";
			}//if
			else//Otherwise don't
			{
				$deleteTradeButton = "";	
			}//else
		
		
			//Inside this div is where the hours will be displayed for other employees to take
			echo "	<div id='".$trade['ID']."_".$trade['postedBy']."' style='display:none; float:left;'>
						<table>
							<tr>
								<th>Hour</th>
								<th>Taken By</th>
								" .$deleteTradeButton. "
								<th>Posted on: ".$trade['postedDate']."</th>
							</tr>
							" .listShiftHours($trade). "
						</table>
					</div>
				";
		
		}//foreach
	}//else
}//populateTradesPage


//This function returns the html to print out the units of the trade that other employees can take.
function listShiftHours($trade)
{
	//Declare variables
	global $area;
	global $netID; //This will need to be changed so we can allow trades to be taken as someone other than the logged in individual
	global $hourSize;
	$time = $trade['startDate'].' '.$trade['startTime'];
	$color = getHourColor($trade['hourType']);
	$html = "";
	$endDateTime = $trade['endDate'].' '.$trade['endTime'];
	while($time < $endDateTime)
	{
		//Declare variables
		$printTime = date('g:i A',strtotime($time));
		
		$html .= "
					<tr>
						<td style='background-color:" .$color. ";' >" .$printTime. "</td>
						<td style='text-align: center'>" .printCheckBox($trade,substr($time, 11, 8),$netID). "</td>
					</tr>
				";
		//Update trade unit for next pass through
		$timestamp = strtotime($time) + 60 * (60 * $hourSize);
		$time = date('Y-m-d H:i:s', $timestamp);
	}//while

	return $html;
}//listShiftHours


//This function will print the check box allowing a user to take the trade hour 
//Or it will print that a user is already scheduled during that time or the name of the person who has already bid on that hour.
//If the user is scheduled for the time of the trade, but for a nonwork type hour, it allows them to take the trade and informs them what they're scheudled for.
//$data = an row from the trades table
//$hour = the specific hour of the trade we are looking at
//$netID = the netID of the person who is taking the trade.
function printCheckBox($data,$hour,$netID)
{
	global $db;
	//Declare variables
	$bidID = $netID."_".$data['ID']."_".$hour;
	$returnMe = '<input type="checkbox" class = "tradeCheckbox" onclick="if(!this.checked){deleteBid(&quot;' .$bidID. '&quot;)}else{submitBid(&quot;' .$bidID. '&quot;)}" id="' .$bidID. '"';
	
	//Check for bids
	try {
		$bidsQuery = $db->prepare("SELECT * FROM `scheduleTradeBids` WHERE tradeID = :id AND hour = :hour AND `deleted`=0");
		$bidsQuery->execute(array(':id' => $data['ID'], ':hour' => $hour));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	if($bid = $bidsQuery->fetch(PDO::FETCH_ASSOC))
	{
		if($bid['employee'] == $netID)
		{
			$returnMe .= " checked >"; 
		}//if
		else
		{
			$returnMe = nameByNetId($bid['employee']);
		}//else
	}//if
	else
	{
		//Check for already being scheduled
		$perm = getHourPermissionById($data['hourType']);
		
		if($netID == $data['postedBy'])
		{
			$returnMe = "";
		}//if	
		else if($perm != "" && !checkPermissionByPermissionID($perm)) 
		{
			$returnMe = "(Unavailable)";
		}//else-if	
		else if($conflict = checkConflictingShift($data,$netID,$hour))
		{
			if($conflict['nonwork'])
			{
				$returnMe .= "> (Scheduled: ".$conflict['longName'].")";
			}
			else
			{
				$returnMe = "(Scheduled: ".$conflict['longName'].")"; 
			}
		}//else-if	
		else 
		{
			$returnMe.=">";
		}//else
	}//else

	return $returnMe;
}//printCheckBox

//This function will run the complex series of queries that will determine if there is a conflicting shift.
// $data = and entry from the trades table representing a shift up for trade.
//$hour = the specific hour of the trade we are looking at
//$netID = the netID of the person who is taking the trade.
function checkConflictingShift($data,$netID,$hour)
{
	global $area, $hourSize, $db;
	$timestamp = strtotime($hour) + (60 * (60 * $hourSize));
	$endTime = date('H:i:s', $timestamp);
	
	if ($endTime == '00:00:00')
	{
	    $endDate =  $data['endDate'];
	}
	else
	{
	    $endDate = $data['startDate'];
	}
	
	$startDate = $data['startDate'];
	$startTime = $hour;

	$conflictQueryString = "SELECT scheduleHourTypes.longName, scheduleHourTypes.nonwork FROM `scheduleWeekly` FORCE INDEX(`startDate`, `endDate`, `employee`)
		LEFT JOIN `scheduleHourTypes` ON scheduleWeekly.hourType = scheduleHourTypes.ID 
		WHERE (`startDate` = :startDate OR `startDate` = :endDate OR `endDate` = :startDate1) AND `employee` = :netId AND scheduleWeekly.area = :area AND scheduleWeekly.deleted = 0 AND 
		(
			( CONCAT(`startDate`, ' ', `startTime`) <=  :start AND CONCAT(`endDate`, ' ', `endTime`) > :start1 )
			OR ( CONCAT(`startDate`, ' ', `startTime`) < :end AND CONCAT(`endDate`, ' ', `endTime`) >= :end1 )
			OR ( CONCAT(`startDate`, ' ', `startTime`) > :start2 AND CONCAT(`endDate`, ' ', `endTime`) < :end2 )
		)";
	try {
		$conflictQuery = $db->prepare($conflictQueryString);
		$conflictQuery->execute(array(
			':startDate'  => $startDate,
			':endDate'    => $endDate,
			':startDate1' => $startDate,
			':netId'      => $netID,
			':area'       => $area,
			':start'      => $startDate." ".$startTime,
			':start1'     => $startDate." ".$startTime,
			':end'        => $endDate." ".$endTime,
			':end1'       => $endDate." ".$endTime,
			':start2'     => $startDate." ".$startTime,
			':end2'       => $endDate." ".$endTime));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	//If there are conflicting shifts this query will have 1 or more rows. Then we delete the current default linked shift.	
	if($shift = $conflictQuery->fetch(PDO::FETCH_ASSOC)) {
		return $shift;
	} else {
		return 0;
	}
}

//Checks to see if a trade should be highlighted
function checkHighlight($trade)
{
	//Declare variables
	global $hourSize, $db;
	$totalHours = computeHourTotal($trade['startTime'],$trade['endTime']);
	
	//Get all tradeBids related to the trade being passed in
	try {
		$bidsQuery = $db->prepare("SELECT COUNT(`ID`) FROM `scheduleTradeBids` WHERE tradeID = :id AND `deleted` = 0");
		$bidsQuery->execute(array(':id' => $trade['ID']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $bidsQuery->fetch(PDO::FETCH_NUM);
	$numBids = $result[0];
	//If there are >= tradeBids than the amount of time for the trade then the trade should be highlighted
	if($numBids >= ($totalHours/$hourSize))
	{
		return 1;
	}//if
	
	return 0;
}//checkHighlight

function computeHourTotal($startTime,$endTime){
	global $db;
	try {
		$totalQuery = $db->prepare("SELECT TIME_TO_SEC(TIMEDIFF(:end, :start))/3600 as hours");
		$totalQuery->execute(array(':end' => $endTime, ':start' => $startTime));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$data = $totalQuery->fetch(PDO::FETCH_ASSOC);
	if($endTime < $startTime){
		return ($data['hours'] + 24);
	}
	return $data['hours'];
}

function getHourColor($type){
	global $area, $db;
	try {
		$colorQuery = $db->prepare("SELECT color FROM scheduleHourTypes WHERE ID = :type AND area = :area");
		$colorQuery->execute(array(':type' => $type, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $colorQuery->fetch(PDO::FETCH_ASSOC);
	return $result['color'];
}

function getTradeNotes($employee, $date) {
	global $area, $db;
	try {
		$notesQuery = $db->prepare("SELECT note FROM scheduleTradeRequestNotes WHERE netID = :employee AND date = :day AND request = '0' AND area = :area");
		$notesQuery->execute(array(':employee' => $employee, ':day' => $date, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $notesQuery->fetch(PDO::FETCH_ASSOC);
	return $result['note'];
}


function populateHourRequests()
{
	//Declare variables
	global $area, $db;
	
	try {
		$requestsQuery = $db->prepare("SELECT postDate, netId, notes FROM scheduleHourRequests WHERE area=:area AND deleted=0 ORDER BY postDate DESC");
		$requestsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	if($first = $requestsQuery->fetch(PDO::FETCH_ASSOC))
	{
		$deleteHeader = "";
		if(can("approve", "8d50e67c-53db-4a56-af2e-502d0d770bef")) //tradeRequest resource
		{
			$deleteHeader = "<th></th>";
		}//if
		
		echo "<table class='tradeTable'>
				<tr>
					<th>Date Posted</th>
					<th>Name</th>
					<th>Phone</th>
					<th>Email</th>
					<th>Note</th>
					" .$deleteHeader. "
				</tr>";
				
		echo "<tr><td>";
		echo date("j M Y",strtotime($first['postDate']));
		echo "</td><td>";
		echo nameByNetId($first['netId']);
		echo "</td><td>";
		echo getEmployeePhoneByNetId($first['netId']);
		echo "</td><td>";
		echo getEmployeeEmailByNetId($first['netId']);
		echo "</td><td>";
		echo stripslashes($first['notes']);
		echo "</td>";
		deleteLink($first['netId']);
		echo "</tr>";

		$requests = array();	
		while($row = $requestsQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo "<tr><td>";
			echo date("j M Y",strtotime($row['postDate']));
			echo "</td><td>";
			echo nameByNetId($row['netId']);
			echo "</td><td>";
			echo getEmployeePhoneByNetId($row['netId']);
			echo "</td><td>";
			echo getEmployeeEmailByNetId($row['netId']);
			echo "</td><td>";
			echo stripslashes($row['notes']);
			echo "</td>";
			deleteLink($row['netId']);
			echo "</tr>";
			
		}//while
		
		echo "</table>";
		
	}//if
	else
	{	
		echo "<p>There are currently no requests for more hours.</p>";
	}//else
}//populateHour


//If the user has the tradeRequest permission print a Delete link
function deleteLink($cur){
	global $netID;
	if(($cur == $netID) || (can("approve", "8d50e67c-53db-4a56-af2e-502d0d770bef")))//tradeRequest resource
	{
		echo "<td><a href='deleteHourRequest.php?id=".$cur."'>Delete</a></td>";
	}//if
	
}//deleteLink


//**************************************
//PENDING TRADES PAGE FUNCTIONS
//*************************************

//This function populates the entire pending trades page for supervisors.
function populatePendingTradesPage($date) 
{
	//Declare variables
	global $netID;
	$tradesList = pullTrades($date);//Get all dates with trades not approvedBy from $date until forever.
	
	
	if($tradesList == 0)//If there are no pending trades do this
	{
		echo "<h2 align='center'>No Trades Pending Approval</h2>";
	}//if
	else //Otherwise do this
	{
		echo "<table>";
			//For each trade print out the trade information
			foreach($tradesList as $curTrade)
			{
				//Check to see if the trade should be highlighted
				$highlight = checkHighlight($curTrade);
				$highlighted = "";
				if($highlight)
				{
					$highlighted = "style='background-color:#47BB47'";
				}//if
				
				//Set the date the trade was posted
				if($curTrade['startDate'] == $curTrade['endDate'])
				{
					$tradeDate = date("D, M jS",strtotime($curTrade['startDate']));
				}//if
				else
				{
					$tradeDate = date("D, M jS",strtotime($curTrade['startDate']))." - ".date("D, M jS",strtotime($curTrade['endDate']));
				}//else	
				
				
				echo "	<tr>
							<td " .$highlighted. ">
								<h3>
									Posted by: <a target=_blank href='../newSchedule/index.php?employee=".$curTrade['postedBy']."' >".getEmployeeNameByNetId($curTrade['postedBy'])."</a><br/>
									Date: $tradeDate
								</h3>
								<table class='tradeTable'>
									<tr>
										<th>Approved:</th>
										<th><input class='approveCheckBox' type='checkbox' id='".$curTrade['ID']."' name='".$curTrade['ID']."' value='1' ></th>
										<th></th>
									</tr>
									<tr>
										<th>Hour</th>
										<th>Taken By (New Hours Total)</th>
										<th></th>
									</tr>
									" .listPendingHours($curTrade). "
								</table>
							</td>
						</tr>			
					";

			}//foreach
			
		echo "</table>";
		
		echo "<input type='button' onclick='submitTrades()' value='Submit' style='float:right;'/>";
		
	}//else
}//populatePendingTrades


//This function returns an array of trades for the current area that start after the given date.
function pullTrades($date){
	global $area, $db;
	try {
		$tradesQuery = $db->prepare("SELECT DISTINCT `scheduleTrades`.*  FROM `scheduleTrades`,`scheduleTradeBids` WHERE `scheduleTrades`.ID = `scheduleTradeBids`.tradeID AND
			 startDate >= :start AND area = :area AND `scheduleTrades`.`deleted`=0  AND `scheduleTradeBids`.`deleted`=0 AND approvedBy IS NULL ORDER BY `startDate`,`startTime`");
		$tradesQuery->execute(array(
			':start' => $date,
			':area'  => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$trades = array();
	while($cur = $tradesQuery->fetch(PDO::FETCH_ASSOC)) {
		$trades[] = $cur;
	}
	if(empty($trades))
		return 0;
	else
		return $trades;
}	

//This function lists out all of the pending hours for a trade.
function listPendingHours($data){
	global $hourSize;	
	global $area;
	global $netID; //This will need to be changed so we can allow trades to be taken as someone other than the logged in individual
	$time = date('H:i:s',strtotime($data['startTime']));	
	$returnMe = "";
	//Get hour color for the trade
	$color = getHourColor($data['hourType']);
	while($time != $data['endTime'])
	{
		$printTime = date('g:i A',strtotime($time));
		$returnMe.= "<tr><td style='background-color:".$color.";' >".$printTime."</td>";
		if(bidforHour($time,$data['ID'])){ //This checks if there is actually a bid for this hour
			$bid = getBid($time,$data['ID']); //If so, get that bid from the DB
			$totalHours = weeklyHourTotal($bid['employee'],$data['startDate']); //Check the total hours of the employee for the given week	
		
			$returnMe.= "<td style='text-align: center; background-color:#FFFFFF'><a target=_blank href='../newSchedule/index.php?employee=".$bid['employee']."' >".nameByNetID($bid['employee'])."</a>";
			$returnMe.= " (".$totalHours."+".computeTotalHoursAddedByTrade($bid['employee'],$data['ID']).")";
			$returnMe.= "</td><td style='background-color:#FFFFFF'>";
			$returnMe.= "<input type='button' id='remove' name='remove' value='Remove Person' onclick='deleteBid(\"".$bid['employee']."_".$bid['tradeID']."_".$bid['hour']."\")'/>";
			$returnMe.= "</td></tr>";
		}else { //This prints a blank row so the person approving can see that the hours are continuous
			$returnMe.= "<td style='text-align: center'>Up for Grabs</td><td style='text-align: center'>-</td></tr>";
		}
		//update Hour for next pass through
		$timestamp = strtotime($time) + 60*(60 * $hourSize);
		$time = date('H:i:s', $timestamp);
	}
	
	return $returnMe;
}

//This returns the bid from DB
function getBid($hour,$tradeID){
	global $db;
	try {
		$bidsQuery = $db->prepare("SELECT * FROM `scheduleTradeBids` WHERE tradeID = :id AND hour = :hour AND `deleted`=0 ORDER BY hour");
		$bidsQuery->execute(array(':id' => $tradeID, ':hour' => $hour));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$bid = $bidsQuery->fetch(PDO::FETCH_ASSOC);
	return $bid;
}

//This checks if there is a bid on the given hour for the given trade.
function bidForHour($hour,$tradeID){
	global $db;
	try {
		$bidsHourQuery = $db->prepare("SELECT * FROM `scheduleTradeBids` WHERE tradeID = :id AND hour = :hour AND `deleted`= 0 ORDER BY hour");
		$bidsHourQuery->execute(array(':id' => $tradeID, ':hour' => $hour));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($bid = $bidsHourQuery->fetch(PDO::FETCH_ASSOC)) {
		return 1;
	}
	return 0;
}

//This function will compute how many hours will be added to an employee's schedule if this trade is approved
function computeTotalHoursAddedByTrade($employee,$tradeID){
	global $hourSize, $db;
	try {
		$hoursAddedQuery = $db->prepare("SELECT COUNT(`ID`) FROM `scheduleTradeBids` WHERE employee = :employee AND tradeID = :id AND `deleted`=0");
		$hoursAddedQuery->execute(array(':employee' => $employee, ':id' => $tradeID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $hoursAddedQuery->fetch(PDO::FETCH_NUM);
	$entries = $result[0];
	return $entries * $hourSize;
}

//This function computes the total hours in shifts an employee has for a given week
function weeklyHourTotal($employee,$date){
	global $db;
	if(date('D',strtotime($date)) == "Sat"){
		$saturday = $date;
	}else{
		$saturday  = date('Y-m-d',strtotime($date." last saturday"));
	}
	$weekEnd = date('Y-m-d',strtotime($saturday." +1 week"));
	try {
		$totalHourQuery = $db->prepare("SELECT SUM(hourTotal) AS total FROM `scheduleWeekly` sw LEFT JOIN `scheduleHourTypes` sht ON sw.`hourType` = sht.`ID`
			 WHERE sw.`employee` = :employee AND sw.`startDate` >= :saturday AND sw.`startDate` < :weekend  AND sw.`deleted`=0 AND sht.`nonwork`=0");
		$totalHourQuery->execute(array(':employee' => $employee, ':saturday' => $saturday, ':weekend' => $weekEnd));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results = $totalHourQuery->fetch(PDO::FETCH_ASSOC);
	return $results['total'];
}
?>
