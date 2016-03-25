<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['id'])) {
	try {
		$deleteQuery = $db->prepare("DELETE FROM `permission` WHERE `permissionId` = :id");
		$success = $deleteQuery->execute(array(':id' => $_POST['id']));
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success) {
		echo "OK";
	} else {
		echo "FAIL";
	}
}
?>