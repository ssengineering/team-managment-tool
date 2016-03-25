<?php
require_once("../includes/includeMeBlank.php");

	if (isset($_POST['appId']) && isset($_POST['areaId']))
	{
		// Get any permissions that the app uses that are not permissions used by any of the other apps in the given area
		try {
			$permissionQuery = $db->prepare("SELECT * FROM `permission` WHERE `permissionId` IN (SELECT `permissionId` FROM `appPermission` WHERE `appId` = :app) 
											 AND `permissionId` NOT IN (SELECT `permissionId` FROM `appPermission` WHERE `appId` IN (SELECT `appId` FROM `link` 
											 WHERE `area` IN (:area)  AND `appId` != :app1)) ORDER BY `shortName`");
			$success = $permissionQuery->execute(array(':app' => $_POST['appId'], ':area' => $_POST['areaId'], ':app1' => $_POST['appId']));
		} catch(PDOException $e) {
			$success = false;
		}
		if ($success)
		{
			$permissions = array();
			while ($permission = $permissionQuery->fetch(PDO::FETCH_ASSOC))
			{
				$permissions[] = $permission;
			}
			echo json_encode(array('status'=>"OK", 'query'=>'', 'permissions'=>$permissions));
		}
		else
		{
			echo json_encode(array('status'=>"FAIL", 'query'=>'', 'error'=>"error in query"));
		}
	}
?>