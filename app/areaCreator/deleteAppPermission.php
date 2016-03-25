<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['id'])) {
	//find areas that have the permission
	$permissionId = 0;
	$areas = array();
	try {
		$permissionQuery = $db->prepare("SELECT * FROM `appPermission` WHERE appPermissionId=:id");
		$permissionQuery->execute(array(':id' => $_POST['id']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($appPermissionRow = $permissionQuery->fetch(PDO::FETCH_ASSOC)) {
		$permissionId = $appPermissionRow['permissionId'];
		try {
			$areaQuery = $db->prepare("SELECT area FROM `permissionArea` WHERE `permissionId`=:permission");
			$areaQuery->execute(array(':permission' => $permissionId));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row = $areaQuery->fetch()) {
			array_push($areas, $row->area);
		}
		//loop through each area that has the permission
		for($i=0;$i<sizeof($areas);$i++) {
			$removePermission = true;
			//get all the apps the area uses
			try {
				$appQuery = $db->prepare("SELECT appId FROM `link` WHERE `area`=:area");
				$appQuery->execute(array(':area' => $areas[$i]));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$appCount = 0;
			//for each app the area uses check if the app requires the same permission
			while($row = $appQuery->fetch(PDO::FETCH_ASSOC)){
				try {
					$appCountQuery = $db->prepare("SELECT COUNT(appPermissionId) FROM `appPermission` WHERE `appId`=:app AND `permissionId`=:permission");
					$appCountQuery->execute(array(':app' => $row['appId'], ':permission' => $permissionId));
				} catch(PDOException $e) {
					exit("error in query");
				}
				$appCountResult = $appCountQuery->fetch(PDO::FETCH_NUM);
				$appCount += $appCountResult[0];
				//if the area has more than one app that uses the permission do not remove the permission from that area
				if($appCount > 1){
					$removePermission = false;
					break;
				}
			}
			if($removePermission) {
				//find all the employees that have the permission that no longer need it and delete them
				try {
					$permissionAreaQuery = $db->prepare("SELECT * FROM `permissionArea` WHERE `area`=:area AND `permissionId`=:permission");
					$permissionAreaQuery->execute(array(':area' => $areas[$i], ':permission' => $permissionId));
				} catch(PDOException $e) {
					exit("error in query");
				}
				while($row = $permissionAreaQuery->fetch(PDO::FETCH_ASSOC)){
					try {
						$deleteEmployeePermissionQuery = $db->prepare("DELETE FROM `employeePermissions` WHERE `permission`=:permission");
						$deleteEmployeePermissionQuery->execute(array(':permission' => $row['index']));
					} catch(PDOException $e) {
						exit("error in query");
					}
				}
				//delete the permission
				try {
					$deletePermissionQuery = $db->prepare("DELETE FROM `permissionArea` WHERE `area`=:area AND `permissionId`=:permission");
					$deletePermissionQuery->execute(array(':area' => $areas[$i], ':permission' => $permissionId));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}

		//remove association
		try {
			$appPermissionDeleteQuery = $db->prepare("DELETE FROM `appPermission` WHERE `appPermissionId` = :id");
			$success = $appPermissionDeleteQuery->execute(array(':id' => $_POST['id']));
		} catch(PDOException $e) {
			$success = false;
		}
		if ($success) {
			echo "OK";
		} else {
			echo "FAIL";
		}
	} else {
		echo "FAIL";
	}	
}
?>