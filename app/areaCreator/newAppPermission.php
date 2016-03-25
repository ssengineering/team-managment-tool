<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['appId']) && isset($_POST['permissionId']))
{
	try {
		$insertQuery = $db->prepare("INSERT INTO `appPermission`(`appId`, `permissionId`, `guid`) VALUES (:app, :permission, :guid)");
		$success = $insertQuery->execute(array(':app' => $_POST['appId'], ':permission' => $_POST['permissionId'], ':guid' => newGuid()));
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success)
	{
		$statusArray = array('status'=>"OK", 'appPermissionId'=>$db->lastInsertId());
		echo json_encode($statusArray);

		//give all areas with the app the new permission
		try {
			$areasQuery = $db->prepare("SELECT * FROM `link` WHERE `appId`=:app");
			$areasQuery->execute(array(':app' => $_POST['appId']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row = $areasQuery->fetch(PDO::FETCH_ASSOC)){
			try {
				$areasWithPermissionQuery = $db->prepare("SELECT COUNT(index) FROM `permissionArea` WHERE `area`=:area AND `permissionId`=:permission");
				$areasWithPermissionQuery->execute(array(':area' => $row['area'], ':permission' => $_POST['permissionId']));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$result = $areasWithPermissionQuery->fetch(PDO::FETCH_NUM);
			if($result[0] == 0){
				try {
					$insertPermissionAreaQuery = $db->prepare("INSERT INTO `permissionArea` (`area`, `permissionId`, `guid`) VALUES (:area,:permission,:guid)");
					$insertPermissionAreaQuery->execute(array(':area' => $row['area'], ':permission' => $_POST['permissionId'], ':guid' => newGuid()));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
		
		//give the person who created the association the permissions
		try {
			$permissionQuery = $db->prepare("SELECT * FROM `permissionArea` WHERE `permissionId`=:permission");
			$permissionQuery->execute(array(':permission' => $_POST['permissionId']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			$permissionIndex = $row['index'];
			try {
				$permissionCheckQuery = $db->prepare("SELECT COUNT(`index`) FROM `employeePermissions` WHERE `netID`=:netID AND `permission`=:permission");
				$permissionCheckQuery->execute(array(':netID' => $netID, ':permission' => $permissionIndex));
			} catch(PDOException $e) {
					exit("error in query");
			}
			$result = $permissionCheckQuery->fetch(PDO::FETCH_NUM);
			if($result[0] == 0){
				try {
					$insertEmployeePermissionQuery = $db->prepare("INSERT INTO `employeePermissions` (`netID`,`permission`,`guid`) VALUES (:netID,:permission,:guid)");
					$insertEmployeePermissionQuery->execute(array(':netID' => $netID, ':permission' => $permissionIndex, ':guid' => newGuid()));
				} catch(PDOException $e) {
					exit("error in query");
				}
			} 
		}
	}
	else
	{
		echo json_encode(array('status'=>"FAIL", 'error'=>"error in query"));
	}
}
?>
