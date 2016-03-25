<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['id']))
{
	try {
		$deleteQuery = $db->prepare("DELETE FROM `app` WHERE `appId` = :id");
		$success = $deleteQuery->execute(array(':id' => $_POST['id']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($success)
	{
		echo "OK";
	}
	else
	{
		echo "FAIL";
	}
}
?>