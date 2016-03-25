<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['shortName']))
{
	try {
		$insertQuery = $db->prepare("INSERT INTO `permission`(`shortName`, `longName`, `description`, `guid`) VALUES (:short,:long,:description,:guid)");
		$success = $insertQuery->execute(array(':short' => $_POST['shortName'], ':long' => $_POST['longName'], ':description' => $_POST['description'], ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($success)
	{
		echo json_encode(array('status'=>"OK", 'permissionId'=>$db->lastInsertId()));
	}
	else
	{
		echo json_encode(array('status'=>"FAIL", 'error'=>"error in query"));
	}
}
?>
