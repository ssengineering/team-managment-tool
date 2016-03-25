<?php
require('../../../includes/includeMeBlank.php');


	echo '<table width="100%">
		<tr>
			<th>Suppress Email</th><th>Employee</th><th>New Wage</th><th>Requested Raise</th><th>Comments</th>
			<th>Date Requested</th><th>Edit</th><th>Delete</th>
		</tr>';		
	printCurrentRaiseTable();
	echo "</table>";
	
function printCurrentRaiseTable(){
	global $area;
	global $netID;
	global $db;

	//This query sums up the raises field to give us accurate wage totals.
	try {
		$logQuery = $db->prepare("SELECT `index`, `netID`, `raise`, FORMAT((SELECT SUM(ew.raise) FROM `employeeRaiseLog` AS ew WHERE ew.date <= ew_outer.date AND ew.netid = ew_outer.netid), 2)
			 AS `newWage`, `submitter`, `date`, `comments`, `isSubmitted` FROM `employeeRaiseLog` AS `ew_outer` WHERE `submitter` = :netId AND isSubmitted='0' ORDER BY netID ASC,`date` ASC");
		$logQuery->execute(array(':netId' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}

	while($curRaise = $logQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><td>";
		echo "<input type='checkbox' name='email[".$curRaise['index']."]' id='email[".$curRaise['index']."]' value='1' </td><td>";
		echo nameByNetId($curRaise['netID'])."</td><td>";
		echo $curRaise['newWage']."</td><td>";
		echo $curRaise['raise']."</td><td>";
		echo $curRaise['comments']."</td><td>";
		echo date("Y-m-d",strtotime($curRaise['date']))."</td><td>";
		echo "<input type='button' value='Edit' id='edit".$curRaise['index']."' onclick='javascript:newwindow(\"../editRaise.php?raiseId=".$curRaise['index']."\")' /></td><td>";
		echo "<input type='button' value='Delete' id='delete".$curRaise['index']."' onclick='deleteRaise(".$curRaise['index'].")' />";
		echo "</td></tr>";
	}
}

?>
