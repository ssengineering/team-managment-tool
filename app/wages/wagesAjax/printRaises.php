<?php //printRaises.php used to print the raises for a specific employee.
require('../../includes/includeMeBlank.php');
global $area;
$employeeNetId = $_GET['employee']; //employee's Net Id
try {
	$areaQuery = $db->prepare("SELECT area FROM `employee` WHERE netId = :netId");
	$areaQuery->execute(array(':netId' => $employeeNetId));
} catch(PDOException $e) {
	exit("error in query");
}
$employeeArea = $areaQuery->fetch()->area;
if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af")/*wages resource*/ && $area == $employeeArea){ //permission check

//echo table headers 
echo '<table border="1" cellpadding="3" style="margin:auto; border-collapse:collapse;">	
	<tbody>
		<tr>
		<th>Date</th><th>Raise</th><th>New Wage</th><th>Submitter</th><th>Reason</th><th></th>
		</tr>';

		//This query selects all of the raises for the passed in employee with a column that sums the raises up to that point for an accurate total.
		try {
			$raiseQuery = $db->prepare("SELECT `netID`, `raise`, FORMAT((SELECT SUM(ew.raise) FROM `employeeRaiseLog` AS ew WHERE ew.date <= ew_outer.date AND ew.netid = ew_outer.netid), 2)
				AS `newWage`, `submitter`, `date`, `comments`, `isSubmitted` FROM `employeeRaiseLog` AS `ew_outer` WHERE `netID` = :netId ORDER BY `date` DESC");
			$raiseQuery->execute(array(':netId' => $employeeNetId));
		} catch(PDOException $e) {
			exit("error in query");
		}
		
		//Echo table rows
		while($row=$raiseQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo "<tr>";
			echo "<td>".date('Y-m-d',strtotime($row["date"]))." </td>";
			echo "<td>$".$row["raise"]."</td>";
			echo "<td>$".$row["newWage"]."</td>";
			echo "<td>".nameByNetId($row["submitter"])."</td>";
			echo "<td>".$row["comments"]."</td>";
			echo "<td>";
			if($row['isSubmitted'] == 0){ //If the raise is pending we need a link to the pending page
				echo "<a href='raiseGrouping/index.php'>Pending</a>";
			}
			echo "</td></tr>";

		}
		echo "</tbody></table>";
}
?>
