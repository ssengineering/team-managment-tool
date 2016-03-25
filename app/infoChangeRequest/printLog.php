<?php //printLog.php
//Prints the info change request log.
require('../includes/includeMeBlank.php');

$start = date("Y-m-d");
$end = date("Y-m-d",strtotime("-2 years"));

try {
	$query = $db->prepare("SELECT * FROM reportInfoChangeRequest WHERE date > :end AND type='Info Change Request' ORDER BY date DESC");
	$query->execute(array(':end' => $end));
} catch(PDOException $e) {
	exit("error in query");
}

echo "<table>
	<tr>
	<th>Type</th>
	<th>Poster</th>
	<th>Date</th>
	<th>Original Note</th>	
	<th>Location</th>
	<th>Status</th>
	<th>Comments</th>
	</tr>"; ?>
<link rel="stylesheet" href="infoChangeLogTable.css" type="text/css">
<?php
while($cur = $query->fetch(PDO::FETCH_ASSOC)){
	echo "<tr><td>";
	echo $cur['type'];
	echo "</td><td>";
	echo nameByNetId($cur['netID']);
	echo "</td><td>";
	echo date("Y-m-d",strtotime($cur['date']));
	echo "</td><td>";
	echo $cur['notes'];
	echo "</td><td>";
	echo $cur['location'];
	echo "</td><td>";
	echo "<select name='status[".$cur['id']."]' id='status[".$cur['id']."]'>".getOptions($cur['status'])."";
	echo "</td><td>";
	echo "<textarea rows='3' cols='40' name='realComments[".$cur['id']."]' id='realComments[".$cur['id']."]'>".$cur['comments']."</textarea>";
	echo "</td></tr>";
	
}
echo "</table>";

function getOptions($string){
	if ($string == "New")
		return "<option value='New'>New</option><option value='Assigned'>Assigned</option><option value='Resolved'>Resolved</option></select>";
	else if ($string == "Assigned")
		return "<option value='Assigned'>Assigned</option><option value='New'>New</option><option value='Resolved'>Resolved</option></select>";
	else if ($string == "Resolved")
		return "<option value='Resolved'>Resolved</option><option value='New'>New</option><option value='Assigned'>Assigned</option></select>";
	else
		return "<option value='New'>New</option><option value='Assigned'>Assigned</option><option value='Resolved'>Resolved</option></select>";
}
?>
</link>
