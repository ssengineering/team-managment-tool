<?php
require_once("../includes/includeMeBlank.php");

function transactionFailed($queryString, $error)
{
	$db->rollBack();
	echo json_encode(array('status'=>"FAIL", 'query'=>$queryString, 'error'=>$error));
}

if (isset($_POST['areaId']) && isset($_POST['appId']))
{
	// Get necessary permissions
	ob_start();
	include_once('getUnnecessaryPermissions.php');
	$response = ob_get_contents();
	ob_end_clean();
	$response = json_decode($response);
	$permissions = $response->permissions;
	
	// Start the transaction
	$db->beginTransaction();
	
	// If there are any permissions that are no longer going to be used remove them
	if (count($permissions))
	{
		// Create string of permission IDs for use in queries
		$permissionIds = array_map(function($permission) { return $permission->permissionId; }, $permissions);
		$idString = implode(',', $permissionIds);
		
		// Get any employees that will be affected by the removal of given permissions
		$_POST['permissions'] = $permissions;
		ob_start();
		include_once('getEmployeesWithAreaPermissions.php');
		$employeesResponse = ob_get_contents();
		ob_end_clean();
		$employeesResponse = json_decode($employeesResponse);
		$employees = $employeesResponse->employees;
		
		if (count($employees))
		{
			// Create string of Net IDs for use in query
			$netIds = array_map(function($employee) { return is_array($employee)? $employee['netID']: $employee->netID; }, $employees);
			$netIdString = "'".implode("','", $netIds)."'";
			
			// Delete all employeePermission entries from the database
			try {
				$deleteQuery = $db->prepare("DELETE FROM `employeePermissions` WHERE `netID` IN (:netID) AND `permission` IN 
											(SELECT `index` FROM `permissionArea` WHERE `area` IN (:area) AND `permissionId` IN (:id))");
				$success = $deleteQuery->execute(array(':netID' => $netIdString, ':area' => $_POST['areaId'], ':id' => $idString));
			} catch(PDOException $e) {
				$success = false;
			}
			if (!$success)
			{
				transactionFailed('', "error in query");
			}
		}
		
		// Now delete permissions from the area
		try {
			$permissionAreaDeleteQuery = $db->prepare("DELETE FROM `permissionArea` WHERE `area` IN (:area) AND `permissionId` IN (:ids)");
			$success = $permissionAreaDeleteQuery->execute(array(':area' => $_POST['areaId'], ':ids' => $idString));
		} catch(PDOException $e) {
			$success = false;
		}
		if (!$success)
		{
			transactionFailed('', "error in query");
		}
	}
	
	// Now remove all links to the app for the area
	try {
		$linkDeleteQuery = $db->prepare("DELETE FROM `link` WHERE `area` IN (:area) AND `appId` = :app");
		$success = $linkDeleteQuery->execute(array(':area' => $_POST['areaId'], ':app' => $_POST['appId']));
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success)
	{
		echo json_encode(array('status'=>"OK", 'query'=>''));
		$db->commit();
	}
	else
	{
		transactionFailed('', "error in query");
	}
}
?>
