<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['appName']))
{
	try {
		$insertQuery = $db->prepare("INSERT INTO `app`(`appName`, `filePath`, `description`, `internal`, `guid`) VALUES (:name, :filePath, :description, :internal, :guid)");
		$success = $insertQuery->execute(array(':name' => $_POST['appName'], ':filePath' => $_POST['filePath'], 
											   ':description' => $_POST['description'], ':internal' => $_POST['internal'], ':guid' => newGuid()));
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success)
	{
		echo json_encode(array('status'=>"OK", 'appId'=>$db->lastInsertId()));
	}
	else
	{
		echo json_encode(array('status'=>"FAIL", 'error'=>"error in query"));
	}
}
?>
