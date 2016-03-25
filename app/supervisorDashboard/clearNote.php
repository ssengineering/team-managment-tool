<?php

require("../includes/includeMeBlank.php");

$noteId = $_GET['noteId'];
$closingComment = $_GET['closingComment'];
$timeStamp = date('Y-m-d G:i');
try {
	$deleteQuery = $db->prepare("UPDATE `supNotes` SET `cleared` = 1, `clearedBy` = :netId, `timeCleared` = :timeStamp, `closingComment` = :comments WHERE noteId = :noteId");
	$deleteQuery->execute(array(':netId' => $netID, ':timeStamp' => $timeStamp, ':comments' => $closingComment, ':noteId' => $noteId));
} catch(PDOException $e) {
	exit("error in query");
}

?>
