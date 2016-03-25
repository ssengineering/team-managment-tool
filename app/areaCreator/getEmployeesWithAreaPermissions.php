<?php
require_once("../includes/includeMeBlank.php");

	if (isset($_POST['areaId']) && isset($_POST['permissions']))
	{
		// Create a string of permission Ids to search against for finding affected employees for the current area
		$permissionIds = implode(',', array_map(function($permission) { return is_array($permission)? $permission['permissionId']:$permission->permissionId; }, $_POST['permissions']));

		// Get employees with any of the listed permissions belonging to the current area
		try {
			$employeeQuery = $db->prepare("SELECT * FROM `employee` WHERE `netID` IN (SELECT `netID` FROM `employeePermissions` WHERE `permission` IN (SELECT `index` FROM `permissionArea` WHERE `area` = :area AND `permissionId` IN (:permissions))) ORDER BY `lastName`");
			$success = $employeeQuery->execute(array(':area' => $_POST['areaId'], ':permissions' => $permissionIds));
		} catch(PDOException $e) {
			$success = false;
		}

		if ($success)
		{
			$employees = array();
			while ($employee = $employeeQuery->fetch(PDO::FETCH_ASSOC))
			{
				$employees[] = $employee;
			}
			echo json_encode(array('status'=>"OK", 'query'=>'', 'employees'=>$employees));
		}
		else
		{
			echo json_encode(array('status'=>"FAIL", 'query'=>'', 'error'=>"error in query"));
		}
	}
?>
