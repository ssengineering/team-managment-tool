<?php //markRead.php
//This just updates the DB that a user has viewed a whiteboard messege
require('../includes/includeMeBlank.php');

$id = $_GET['id'];

try {
	$insertQuery = $db->prepare("INSERT INTO whiteboardMandatoryLog (netID,msgID,guid) VALUES (:netId,:id,:guid)");
	$insertQuery->execute(array(':netId' => $netID, ':id' => $id, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}
?>
