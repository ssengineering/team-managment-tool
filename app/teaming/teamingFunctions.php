<?php //teamingFunctions.php this file holds all of the functions for the teaming app
ini_set('display_errors', '1');

//prints a table of teams
/*
 |Team Name| Team Leader | delete? |

*/
function pullTeamsSelect($area){
	global $db;
	try {
		$teamsQuery = $db->prepare("SELECT * FROM teams WHERE area=:area ORDER BY name");
		$teamsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $teamsQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><td><input type='text' name='".$cur['ID']."name' value=\"".$cur['name']."\" /></td>";
		echo "<td><select name='".$cur['ID']."lead'>";
		teamLeaderSelect($area,$cur['lead']);
		echo "</select></td></tr>";
	}
}

//prints out the teams in a select box
function teamsSelect($area){
	global $db;
	try {
		$teamQuery = $db->prepare("SELECT * FROM teams WHERE area=:area");
		$teamQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $teamQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value='".$cur['ID']."'>".$cur['name']."</option>";
	}
} 

function teamLeaderSelect($area,$selected){
	$url = getEnv('PERMISSIONS_URL');
	// Ask the permission microservice which groups' users can be team leaders
	$groups = sendAuthenticatedRequest("GET", $url."/permission/verbs/28e60394-f719-4225-85ad-fa542ab6a8df/lead");
	$teamLeads = array();
	// loop through groups and get users
	for($i=0; $i < count($groups["data"]); $i++) {
		$users = sendAuthenticatedRequest("GET", $url."/groupMembers/".$groups["data"][$i]["Guid"]);
		for($j=0; $j < count($users["data"]); $j++) {
			if(!in_array($users["data"][$j], $teamLeads)) {
				$teamLeads[] = $users["data"][$j];
			}
		}
	}
	$count = 0;
	for($i=0; $i < count($teamLeads); $i++) {
		if($teamLeads[$i] == $selected) {
			echo "<option value='".$teamLeads[$i]."' selected>".nameByNetId($teamLeads[$i])."</option>";
		} else {
			$count++;
			echo "<option value='".$teamLeads[$i]."'>".nameByNetId($teamLeads[$i])."</option>";
		}
	}
	// If the selected team lead was not pulled add him/her
	if(count($teamLeads) == $count) {
		echo "<option value='".$selected."' selected>".nameByNetId($selected)."</option>";
	}
}

//prints out the table of team leads and members for team.php
function displayTeamsTable($area){
	global $db;
	try {
		$teamsQuery = $db->prepare("SELECT * FROM teams WHERE area=:area ORDER BY name");
		$teamsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($curTeam = $teamsQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><td>".$curTeam['name']."</td><td>".nameByNetId($curTeam['lead'])."</td>";
		try {
			 $membersQuery = $db->prepare("SELECT * FROM `teamMembers` JOIN `employee` ON `teamMembers`.`netID` = `employee`.`netID`  WHERE teamID = :id AND isSupervisor = '0' AND `employee`.`active` = 1 ORDER BY `employee`.`firstName` ");
			$membersQuery->execute(array(':id' => $curTeam['ID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo "<td>";
		while($cur = $membersQuery->fetch(PDO::FETCH_ASSOC)) {
			echo nameByNetId($cur['netID']);
			echo "<br/>";
		}
		echo "</td></tr>";
	}
}


//*****************************************************
//			 HELPER FUNCTIONS
//*****************************************************

function teamLeadByTeamID($id){
	global $db;
	try {
		$teamByIdQuery = $db->prepare("SELECT * FROM teams WHERE ID = :id");
		$teamByIdQuery->execute(array(':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$cur = $teamByIdQuery->fetch(PDO::FETCH_ASSOC);
	return nameByNetId($cur['lead']);
}

function teamLeadIdByTeamId($id){
	global $db;
	try {
		$teamLeadIdQuery = $db->prepare("SELECT * FROM teams WHERE ID = :id");
		$teamLeadIdQuery->execute(array(':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$cur = $teamLeadIdQuery->fetch(PDO::FETCH_ASSOC);
	return $cur['lead'];
}

// Get the start or end date for a teaming period
function getTeamingPeriod($startOrEnd, $empArea)
{
	global $db;
	try {
		$periodQuery = $db->prepare("SELECT * FROM teaming WHERE area=:area LIMIT 1");
		$periodQuery->execute(array(':area' => $empArea));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$cur = $periodQuery->fetch(PDO::FETCH_ASSOC);
	if ($startOrEnd=='start') 
	{	
		return $cur['startDate'];
	}
	else if($startOrEnd=='end')
	{
		return $cur['endDate'];
	}
}


function calculateLastPeriodPercent($netID){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	try {
		$logQuery = $db->prepare("SELECT * FROM `teamingLog` WHERE `endDate`=(SELECT MAX(endDate) FROM teamingLog) AND supervisorID = :netId");
		$logQuery->execute(array(':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100, 1);
	$timleyTally = round(($timelyTally/$divBy)*100, 1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;
}


function calculateCurrentMonthPercent($netID){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	$start = date("Y-m-01",strtotime("today",time()));
	$lastDayMonth = date('t',strtotime('today'));
	$end = date("Y-m-".$lastDayMonth);
	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE startDate >= :start AND endDate <= :end AND supervisorID = :netId");
		$logQuery->execute(array(':start' => $start, ':end' => $end, ':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100, 1);
	$timleyTally = round(($timelyTally/$divBy)*100, 1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;	
}
// current semester
function calculateCurrentPeriodPercent($empArea,$netID){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	//textual representation of the semester name	
	$curPeriod = getSemester(date("Y-m-d"));
	try {
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE semester=:period AND `area`=:area");
		$semestersQuery->execute(array(':period' => $curPeriod, ':area' => $empArea));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$period = $semestersQuery->fetch(PDO::FETCH_ASSOC);
	$start = $period['startDate'];
	$end = $period['endDate'];

	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE startDate >= :start AND endDate <= :end AND supervisorID = :netId");
		$logQuery->execute(array(':start' => $start, ':end' => $end, ':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100, 1);
	$timleyTally = round(($timelyTally/$divBy)*100, 1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;
}

function calculateTotalPercent($netID){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE supervisorID = :netId");
		$logQuery->execute(array(':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally =  round(($teamedTally/$divBy)*100, 1);
	$timleyTally =  round(($timelyTally/$divBy)*100, 1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;	
}


function calculateLastPeriodPercentAllTeams($area){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	try {
		$logQuery = $db->prepare("SELECT * FROM `teamingLog` WHERE `endDate`=(SELECT MAX(endDate) FROM teamingLog) AND `area` = :area");
		$logQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100,1);
	$timleyTally = round(($timelyTally/$divBy)*100,1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;
}


function calculateCurrentMonthPercentAllTeams($area){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	$start = date("Y-m-01",strtotime("today",time()));
	$lastDayMonth = date('t',strtotime('today'));
	$end = date("Y-m-".$lastDayMonth);
	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE startDate >= :start AND endDate <= :end AND `area` = :area");
		$logQuery->execute(array(':start' => $start, ':end' => $end, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100,1);
	$timleyTally = round(($timelyTally/$divBy)*100,1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;	
}

function calculateCurrentPeriodPercentAllTeams($area){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	//textual representation of the semester name	
	$curPeriod = getSemester(date("Y-m-d"));
	try {
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE semester= :period AND `area`=:area");
		$semestersQuery->execute(array(':period' => $curPeriod, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$period = $semestersQuery->fetch(PDO::FETCH_ASSOC);
	$start = $period['startDate'];
	$end = $period['endDate'];
	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE startDate >= :start AND endDate <= :end AND `area` = :area");
		$logQuery->execute(array(':start' => $start, ':end' => $end, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100,1);
	$timleyTally = round(($timelyTally/$divBy)*100,1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;
}

function calculateTotalPercentAllTeams($area){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE `area` = :area");
		$logQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100,1);
	$timleyTally = round(($timelyTally/$divBy)*100,1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;	
}

//*****************************************************
//				SECTION FOR UPDATE TEAMING
//*****************************************************
//This function will print out the team list given the supervisors ID. If the person is NOT a supervisor, they will get a message notifying them of this.
function printTeamForUpdate($netId, $area){
	global $db;
	try {
		$teamsQuery = $db->prepare("SELECT * FROM teams WHERE lead = :netId AND area=:area");
		$teamsQuery->execute(array(':netId' => $netId, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$teamID = $teamsQuery->fetch(PDO::FETCH_ASSOC);
	echo "<table>
			<tr>
			<th>Team Member</th><th>Trained?</td>
			</tr>";

	try {
		$supervisorQuery = $db->prepare("SELECT * FROM teaming WHERE supervisorID = :lead");
		$supervisorQuery->execute(array(':lead' => $teamID['lead']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $supervisorQuery->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr><td>";
			echo nameByNetId($cur['netID']);
			echo "</td><td>";
			if($cur['teamed']){
				echo "<input type='checkbox' name='teamed[".$cur['netID']."]' checked/>";
			} else {
				echo "<input type='checkbox' name='teamed[".$cur['netID']."]' />";
			}
			echo "</td></tr>";

	}
	echo "</table>";
}

// Check if the employee is in the teaming table for the period.
function isEmployeeInTheTeamingTable($empNetID)
{
	global $db, $area;
	try {
		$tableQuery = $db->prepare("SELECT * FROM teaming WHERE netID=:netId AND area=:area");
		$tableQuery->execute(array(':netId' => $empNetID, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($row = $tableQuery->fetch())
	{
		return true;
	}
	else
	{
		return false;
	}
}

//Removes employee from teaming table
function removeEmployeeFromTeaming($employee)
{
	global $db;
	if(isEmployeeInTheTeamingTable($employee))
	{
		try {
			$deleteQuery = $db->prepare("DELETE FROM teaming WHERE netID = :employee");
			$deleteQuery->execute(array(':employee' => $employee));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}

// update/insert employee team lead in the teaming table
function updateEmployeeTeamingInfo($empNetID, $empTeamID, $empArea)
{
	global $db;
	if(isEmployeeInTheTeamingTable($empNetID))
	{
		// Update teaming
		try {
			$updateQuery = $db->prepare("UPDATE teaming SET `supervisorID` = :leadId WHERE netID=:netId");
			$updateQuery->execute(array(':leadId' => teamLeadIdByTeamId($empTeamID), ':netId' => $empNetID));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	else
	{
		//Insert into teaming 
		try {
			$insertQuery = $db->prepare("INSERT INTO teaming (netID,supervisorID,startDate,endDate,teamed,timely,area,guid) VALUES (:netId, :leadId, :start, :end,'0','0',:area,:guid)");
			$insertQuery->execute(array(':netId' => $empNetID, ':leadId' => teamLeadIdByTeamId($empTeamID), ':start' => getTeamingPeriod('start',$empArea), ':end' => getTeamingPeriod('end',$empArea), ':area' => $empArea, ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
//*****************************************************
//				SECTION FOR TEAMING SUMMARY
//*****************************************************
function printAllCurrentTeaming($area){
	global $db;
	echo "<table>
					<tr>
					<th>Team Member</th><th>Team Leader</th><th>Trained?</th><th>Timely</th>
					</tr>";

	try {
		//A query statement to order the table first by supervisor first name, then by employee first name.	
		$individualTeamsQuery = $db->prepare("SELECT teaming.*, emp.firstName AS empFirst, emp.lastName AS empLast, supervisor.firstName, supervisor.lastName FROM teaming JOIN employee AS emp ON emp.netID=teaming.netID JOIN employee AS supervisor ON supervisorID=supervisor.netID WHERE teaming.area=:area ORDER BY supervisor.firstName ASC, empFirst ASC");
		$individualTeamsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $individualTeamsQuery->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr><td>";
			echo $cur["empFirst"]." ".$cur["empLast"];
			echo "</td><td>";
			echo $cur["firstName"]." ".$cur["lastName"];
			echo "</td><td>";
			if($cur['teamed']){
				echo "<input type='checkbox' name='teamed[".$cur['netID']."]' checked/>";
			} else {
				echo "<input type='checkbox' name='teamed[".$cur['netID']."]' />";
			}
			echo "</td><td>";
			if($cur['timely'])
			{
				echo "<input type='checkbox' name='timely[".$cur['netID']."]' checked/>";
			}
			else
			{
				echo "<input type='checkbox' name='timely[".$cur['netID']."]' />";
			}
			echo "</td></tr>";

	}
	echo "</table>";
}

function printTeamingStats($area){
	global $db;
	try {
		$areaQuery = $db->prepare("SELECT * FROM teams WHERE area =:area ORDER BY name");
		$areaQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
			exit("error in query");
	}
	while($cur = $areaQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><td>";
		echo nameByNetId($cur['lead']);
		echo "</td><td>";
		echo calculateLastPeriodPercent($cur['lead']);
		echo "</td><td>";
		echo calculateCurrentMonthPercent($cur['lead']);
		echo "</td><td>";
		echo calculateCurrentPeriodPercent($area,$cur['lead']);
		echo "</td><td>";
		echo calculateTotalPercent($cur['lead']);
		echo "</td></tr>";
	}
	echo "<tr><th>";
	echo "OVERALL TOTALS";
	echo "</th><th>";
	echo calculateLastPeriodPercentAllTeams($area);
	echo "</th><th>";
	echo calculateCurrentMonthPercentAllTeams($area);
	echo "</th><th>";
	echo calculateCurrentPeriodPercentAllTeams($area);
	echo "</th><th>";
	echo calculateTotalPercentAllTeams($area);
	echo "</th>";
}


//This function will clear the current teaming table entries for the given area, and push them to the teamingLog table.
function resetTeamingPeriod($periodStartDate, $periodEndDate, $area){
	global $db;
	try {
		$insertQuery = $db->prepare("INSERT INTO teamingLog (netID, supervisorID, startDate,endDate,teamed,timely,area,guid) SELECT netID, supervisorID, startDate,endDate,teamed,timely,area,:guid FROM `teaming` WHERE area = :area");
		$insertQuery->execute(array(':guid' => newGuid(), ':area' => $area));
		// Get rid of terminated employees from the teaming table.
		$teamingQuery = $db->prepare("SELECT * FROM `teaming` WHERE `area`=:area");
		$teamingQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $teamingQuery->fetch(PDO::FETCH_ASSOC))
	{
		try {
			$activeQuery = $db->prepare("SELECT * FROM `employee` WHERE `active`='1' AND `area`=:area AND `netID`=:netId");
			$activeQuery->execute(array(':area' => $area, ':netId' => $cur['netID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if(!($row = $activeQuery->fetch(PDO::FETCH_ASSOC)))
		{
			try {
				$delete1Query = $db->prepare("DELETE FROM `teaming` WHERE `netID`=:netId AND `area`=:area");
				$delete1Query->execute(array(':netId' => $cur['netID'], ':area' => $area));
				$delete2Query = $db->prepare("DELETE FROM `teamMembers` WHERE `netID`=:netId AND `area`=:area");
				$delete2Query->execute(array(':netId' => $cur['netID'], ':area' => $area));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
	try {
		$updateQuery = $db->prepare("UPDATE teaming SET startDate = :start, endDate = :end, teamed = '0', timely = '0' WHERE area = :area");
		$updateQuery->execute(array(':start' => $periodStartDate, ':end' => $periodEndDate, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

//*****Subsection of Teaming Summary that contains functions for editing previous periods.***************///
function printAllPreviousTeaming($startDate,$endDate,$area)
{
	global $db;
	echo "<table>
			<tr>
			<th>Team Member</th><th>Team Leader</th><th>Trained</th><th>Timely</th>
			</tr>";

	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE area=:area AND `startDate`=:start AND `endDate`=:end ORDER BY supervisorID ASC");
		$logQuery->execute(array(':area' => $area, ':start' => $startDate, ':end' => $endDate));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr><td>";
			echo nameByNetId($cur['netID']);
			echo "</td><td>";
			echo nameByNetId($cur['supervisorID']);
			echo "</td><td>";
			if($cur['teamed']){
				echo "<input type='checkbox' name='teamed[".$cur['netID']."]' checked/>";
			} else {
				echo "<input type='checkbox' name='teamed[".$cur['netID']."]' />";
			}
			echo "</td><td>";
			if($cur['timely'])
			{
				echo "<input type='checkbox' name='timely[".$cur['netID']."]' checked/>";
			}
			else
			{
				echo "<input type='checkbox' name='timely[".$cur['netID']."]' />";
			}
			echo "</td></tr>";

	}
	echo "</table>";
}

function printPreviousTeamingStats($startDate, $endDate, $area)
{
	global $db;
	try {
		$supervisorQuery = $db->prepare("SELECT DISTINCT `supervisorID` FROM teamingLog WHERE area=:area AND `startDate`=:start AND `endDate`=:end ORDER BY supervisorID ASC");
		$supervisorQuery->execute(array(':area' => $area, ':start' => $startDate, ':end' => $endDate));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $supervisorQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><td>";
		echo nameByNetId($cur['supervisorID']);
		echo "</td><td>";
		echo calculatePreviousPeriodPercent($area,$cur['supervisorID'],$startDate);
		echo "</td></tr>";
	}
	echo "<tr><th>";
	echo "OVERALL TOTALS";
	echo "</th><th>";
	echo calculatePreviousPeriodPercentAllTeams($area, $startDate);
	echo "</th></tr>";
}

// Previous semester
function calculatePreviousPeriodPercent($empArea,$netID,$startDate){
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	//textual representation of the semester name	
	$curPeriod = getSemester($startDate);
	try {
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE semester=:period AND `area`=:area");
		$semestersQuery->execute(array(':period' => $curPeriod, ':area' => $empArea));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$period = $semestersQuery->fetch(PDO::FETCH_ASSOC);
	$start = $period['startDate'];
	$end = $period['endDate'];
	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE startDate >= :start AND endDate <= :end AND supervisorID = :netId");
		$logQuery->execute(array(':start' => $start, ':end' => $end, ':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100, 1);
	$timleyTally = round(($timelyTally/$divBy)*100, 1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;
}

function calculatePreviousPeriodPercentAllTeams($area, $startDate)
{
	global $db;
	$timelyTally = 0;
	$teamedTally = 0;
	//textual representation of the semester name	
	$curPeriod = getSemester($startDate);
	try {
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE semester= :period AND `area`=:area");
		$semestersQuery->execute(array(':period' => $curPeriod, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$period = $semestersQuery->fetch(PDO::FETCH_ASSOC);
	$start = $period['startDate'];
	$end = $period['endDate'];
	try {
		$logQuery = $db->prepare("SELECT * FROM teamingLog WHERE startDate >= :start AND endDate <= :end AND `area` = :area");
		$logQuery->execute(array(':start' => $start, ':end' => $end, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$divBy = 0;
	while($cur = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		$divBy++;
		if($cur['teamed'] == 1){
			$teamedTally++;
		}
		if($cur['timely'] == 1){
			$timelyTally++;
		}
	}
	if($divBy == 0){
		return "- : -";
	}
	$teamedTally = round(($teamedTally/$divBy)*100,1);
	$timleyTally = round(($timelyTally/$divBy)*100,1);
	$return = $teamedTally." : ".$timleyTally;
	return $return;
}

//*****************************************************
//				SECTION FOR TEAM ORGANIZER
//*****************************************************

//This will print the team organizer for all active employees
function printTeamOrganizer($area){
	global $db;
	try {
		$employeeQuery = $db->prepare("SELECT * FROM employee WHERE area=:area AND active='1' ORDER BY firstName ASC");
		$employeeQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	$nameCount = 1;
	while($cur = $employeeQuery->fetch(PDO::FETCH_ASSOC))
	{
		try {
			$membersQuery = $db->prepare("SELECT * FROM teamMembers WHERE netID = :netId AND `area` = :area");
			$membersQuery->execute(array(':netId' => $cur['netID'], ':area' => $area));
			$teamCountQuery = $db->prepare("SELECT COUNT(ID) FROM teamMembers WHERE netID = :netId AND `area` = :area");
			$teamCountQuery->execute(array(':netId' => $cur['netID'], ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$countResult = $teamCountQuery->fetch(PDO::FETCH_NUM);
		$numOfTeamsEmployeeBelongsTo = $countResult[0];
		if($numOfTeamsEmployeeBelongsTo > 0)
		{
			while($teamMember = $membersQuery->fetch(PDO::FETCH_ASSOC))
			{	
				$teamID = $teamMember['teamID'];
				echo "<tr><td>";	
				echo nameByNetId($cur['netID']);
				echo "</td><td>";
				echo teamLeadByTeamID($teamID);
				echo "</td><td>";
				if($numOfTeamsEmployeeBelongsTo>1)
				{	//Make the selects name unique if employee belongs to multiple teams. 
					echo teamSelectByTeam($teamID,$nameCount,$cur['netID']);
					echo "</td><td id='futureTeamLeader_".$nameCount."'></td></tr>";
					$nameCount++;
				}
				else
				{
					echo teamSelectByTeam($teamID,$nameCount,$cur['netID']);
					echo "</td><td id='futureTeamLeader_".$nameCount."'></td></tr>";
					$nameCount++;
				}
			}
		} 
		else 
		{
			$teamID = 0;
			echo "<tr><td>";	
			echo nameByNetId($cur['netID']);
			echo "</td><td>";
			echo teamLeadByTeamID($teamID);
			echo "</td><td>";
			echo teamSelectByTeam($teamID,$nameCount,$cur['netID']);
			echo "</td><td id='futureTeamLeader_".$nameCount."'></td></tr>";
			
			$nameCount++;
		}
	}
}

function teamSelectByTeam($id,$counter,$empNetID)
{
	global $area, $db;
	// This is the team ID before any updates are made. 
	$currentTeamID= '';  
	echo "<select name='select_".$counter."' id='select_".$counter."'>";
	
	if($id==0)
	{
		echo "<option value='' selected>Unassigned</option>";
	}
	else
	{
		echo "<option value=''>Unassigned</option>";
	}

	try {
		$teamSelectQuery = $db->prepare("SELECT * FROM teams WHERE area=:area");
		$teamSelectQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $teamSelectQuery->fetch(PDO::FETCH_ASSOC)) {
		if($id  == $cur['ID']){
			echo "<option value='".$cur['ID']."' selected>".$cur['name']."</option>";
			$currentTeamID = $cur['ID'];
		} else {
			echo "<option value='".$cur['ID']."'>".$cur['name']."</option>";
		}
	}
	echo "</select>";
	echo "<input type='hidden' name='currentTeamId_".$counter."' value='".$currentTeamID."'/>";
	echo "<input type='hidden' name='empNetID_".$counter."' value='".$empNetID."'/>";
}

//Check for employee in multiple teams.  If in multiple teams, then make sure we are not updating to a teamID that is already in the database.
function noSameTeamIDToUpdate($empNetID, $teamIDToCheck)
{
	global $db;
	try {
		$memberQuery = $db->prepare("SELECT * FROM `teamMembers` WHERE `netID` =:netId");
		$memberQuery->execute(array(':netId' => $empNetID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row=$memberQuery->fetch(PDO::FETCH_ASSOC))
	{
		if($row['teamID']==$teamIDToCheck)
		{
			return false;
		}
	}
	return true;
}

?>
