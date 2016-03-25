<?php
require('../includes/includeMeBlank.php');
//Store all the post variables
$ticketNum = $_POST['ticketNum'];
$engName = $_POST['engName'];
$startTime = $_POST['startTime'];
$startDate = $_POST['startDate'];
$endTime = $_POST['endTime'];
$endDate = $_POST['endDate'];
$description = $_POST['description'];
$impact = $_POST['impact'];


//Insert them into the database
try {
	$insertQuery = $db->prepare("INSERT INTO unscheduledRFC (ticketNumber, engineerName, description, startTime, startDate, endTime, endDate, impact, guid) VALUES (:ticket,:engName,:descr,:start,:startDate,:end,:endDate,:impact,:guid)");
	$results = $insertQuery->execute(array(':ticket' => $ticketNum, ':engName' => $engName, ':descr' => $description, ':start' => $startTime, ':startDate' => $startDate, ':end' => $endTime, ':endDate' => $endDate, ':impact' => $impact, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}
echo $results;

?>
