<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');

if (isset($_POST['messageId']) && can("approve", "6db1ee4f-4d80-424d-a062-97dc4cc22936"))
{
	$messageId = $_POST['messageId'];
	try {
		$updateQuery = $db->prepare("UPDATE `whiteboardAreas` SET `approved` = 1, `approvedBy` = :netId, `approvedOn` = NOW() WHERE `whiteboardId` = :messageId");
		$success = $updateQuery->execute(array(':netId' => $netID, ':messageId' => $messageId));
	} catch(PDOException $e) {
		$success = false;
	}
	$status = $success ? 'OK' : 'FAIL';
	echo json_encode(array('query'=>$query, 'error'=>"", 'data'=>$status));
}
?>
