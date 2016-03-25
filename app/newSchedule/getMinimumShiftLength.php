<?php
	require('../includes/includeMeBlank.php');
	try {
		$areasQuery = $db->prepare("SELECT * FROM `employeeAreas` WHERE `ID` = :area");
		$success = $areasQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		$success = false;
	}	
	$result = $areasQuery->fetch(PDO::FETCH_ASSOC);
	if ($success)
	{
		// The following is not good code and broken FIX IT!
		echo json_encode($result);
	}
?>
