<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['id'])) {
	try {
		$deleteQuery = $db->prepare("DELETE FROM `employeeAreas` WHERE `ID` = :id");
		$success = $deleteQuery->execute(array(':id' => $_POST['id']));
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success) {
		echo json_encode(array("status"=>"OK"));
	} else {
		echo json_encode(array("status"=>"FAIL", "error"=>"error in query"));
	}
}
?>
