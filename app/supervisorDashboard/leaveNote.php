<?php
require("../includes/includeMeBlank.php");

$note = $_GET['note'];
try {
	$insertQuery = $db->prepare("INSERT INTO `supNotes` (submittedBy, note, cleared, closingComment, guid) VALUES (:netId, :note, 0, '', :guid)");
	$insertQuery->execute(array(':netId' => $netID, ':note' => $note, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}
?>
