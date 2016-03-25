<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['id']))
{
	try {
		$permissionQuery = $db->prepare("SELECT *, `app`.`description` AS 'appDescription', `permission`.`description` AS 'permissionDescription' FROM `appPermission` 
										 JOIN `app` ON `app`.`appId` = `appPermission`.`appId` JOIN `permission` ON `permission`.`permissionId` = `appPermission`.`permissionId` 
										 WHERE `appPermissionId` = :id");
		$success = $permissionQuery->execute(array(':id' => $_POST['id']));
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success && $appPermission = $permissionQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo json_encode(array('status'=>"OK", 'appPermission'=>$appPermission));
	}
	else
	{
		echo json_encode(array('status'=>"FAIL", 'error'=>"error in query"));
	}
}
?>
