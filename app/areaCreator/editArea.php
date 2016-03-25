<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['id']))
{
	$updateQueryString = "UPDATE `employeeAreas` SET `".$_POST['column']."` = :value WHERE `ID` = :id";
	try {
		$updateQuery = $db->prepare($updateQueryString);
		$success = $updateQuery->execute(array(':value' => $_POST['value'], ':id' => $_POST['id']));
	} catch(PDOException $e) {
		$success = false;
	}	
	if ($success)
	{
		echo json_encode(array("status"=>"OK"));
	}
	else
	{
		echo json_encode(array("status"=>"FAIL", "error"=>"error in query"));
	}
}
?>
