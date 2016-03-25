<?php //updatedReview.php this file will just update the database entry for a netID and a month saying that it was reviewed

require('../includes/includeMeBlank.php');

$month = $_GET['month'];
try {
	$insertQuery = $db->prepare("INSERT INTO reportPerformanceReviewed (netID,area,month,guid) VALUES (:netId,:area,:month,:guid)");
	$insertQuery->execute(array(':netId' => $netID, ':area' => $area, ':month' => $month, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}
?>
