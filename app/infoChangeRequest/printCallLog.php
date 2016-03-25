<?php //printCallLog.php
//Prints the unusual call log.
require('../includes/includeMeBlank.php');

//Needs to receive information about the start and end date desired from callLog.php. Otherwise set start and end to today and 1 month ago, respectively.
if(isset($_GET['start'])) {
	$start = $_GET['start'];
} else {
	$today = date("Y-m-d");
	$start = strtotime(date("Y-m-d", strtotime($today))."+1 day");
}

if(isset($_GET['end'])) {
	$end = $_GET['end'];
} else {
	$end = date("Y-m-d",strtotime("-1 months"));
}

echo "<table>
	<tr>
	<th>Type</th>
	<th>Poster</th>
	<th>Date</th>
	<th>Original Note</th>	
	</tr>";
?>
<link rel="stylesheet" href="infoChangeCallTable.css" type="text/css">
<?php
//This is the correct query for getting all unusual calls within the start and end dates. The Submit button on DEV just doesn't work yet cause it's not sending information properly or something like that.
try {
	$reportQuery = $db->prepare("SELECT * FROM reportInfoChangeRequest WHERE date >= :start AND date <= :end AND type='Unusual Call' ORDER BY date DESC");
	$reportQuery->execute(array(':start' => $start.' 00:00:00', ':end' => $end.' 23:59:59'));
} catch(PDOException $e) {
	exit("error in query");
}
while($cur = $reportQuery->fetch(PDO::FETCH_ASSOC)){
	echo "<tr><td>";
	echo $cur['type'];
	echo "</td><td>";
	echo nameByNetId($cur['netID']);
	echo "</td><td>";
	echo date("Y-m-d",strtotime($cur['date']));
	echo "</td><td>";
	echo $cur['notes'];
	echo "</td></tr>";
}
echo "</table>";

?>
</link>
