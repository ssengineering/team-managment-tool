<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');

// Get whiteboard Info (really we just care about the ownerId)
try {
	$whiteboardQuery = $db->prepare("SELECT * FROM `whiteboard` WHERE `messageId` = :id");
	$whiteboardQuery->execute(array(':id' => $_POST['messageId']));
} catch(PDOException $e) {
	exit("error in query");
}
$whiteboardOwner = $whiteboardQuery->fetch(PDO::FETCH_ASSOC);

if ( isset($_POST['messageId']) && ( can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936")/*whiteboard resource*/ || $netID ==  $whiteboardOwner['ownerId'] ) )
{
	$messageId = $_POST['messageId'];
	try {
		$updateQuery = $db->prepare("UPDATE `whiteboardAreas` SET `deleted` = 1, `deletedBy` = :netId, `deletedOn` = NOW() WHERE `whiteboardId` = :messageId");
		$result = $updateQuery->execute(array(':netId' => $netID, ':messageId' => $messageId));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$status = $result ? 'OK' : 'FAIL';
	echo json_encode(array('query'=>$query, 'error'=>"", 'data'=>$status));
}
?>
