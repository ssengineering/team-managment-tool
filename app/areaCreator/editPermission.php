<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['id'])) {
	$queryString = "UPDATE `permission` SET `".$_POST['column']."` = :value WHERE `permissionId` = :id";
	try {
		$updateQuery = $db->prepare($queryString);
		$success = $updateQuery->execute(array(':value' => $_POST['value'], ':id' => $_POST['id']));
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
