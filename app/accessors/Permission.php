<?php

namespace TMT\accessor;

class Permission extends MysqlAccessor {

	/*
	 * Checks if the current area has the permission specified by the passed in shortname
	 *
	 * @param $permission string The shortname of the permission to check
	 * @param $area       int    The area id
	 *
	 * @throws Exception if permission doesn't exist
	 *
	 * @return int The id of the permission
	 */
	public function getAreaPermission($permission = null, $area = null) {
		if($permission === null || $area === null)
			throw new \TMT\exception\PermissionException(1);

		try {
			$permissionQuery = $this->pdo->prepare("SELECT `index` FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId` = `permission`.`permissionId` WHERE shortName=:permission AND area=:area");
			$permissionQuery->execute(array(':permission' => $permission, ':area' => $area));
		} catch(\PDOException $e) {
			exit("error in query");
		}

		if($id = $permissionQuery->fetch())
			return $id->index;
		else
			throw new \TMT\exception\PermissionException(2, "Area ".$area." does not have permission '".$permission."'");
	}

	/*
	 * Checks if the current user has the permission specified by the passed in shortname
	 *
	 * @param $permission int    The id of the permission to check
	 * @param $netId      string The user's netId
	 *
	 * @return int The id of the employee's permission
	 */
	public function getUserPermission($permission = null, $netId = null) {
		if($permission === null || $netId === null)
			throw new \TMT\exception\PermissionException(1);

		try {
			$employeeQuery = $this->pdo->prepare("SELECT `index` FROM `employeePermissions` WHERE 
				`netID`=:netID AND `permission`=:permission");
			$employeeQuery->execute(array(':netID' => $netId, ':permission' => $permission));
		} catch(\PDOException $e) {
			exit("error in query");
		}

		if ($id = $employeeQuery->fetch())
			return $id->index;
		else
			throw new \TMT\exception\PermissionException(3, "The user ".$netId." does not have permission ".$permission);
	}

	/**
	 * Retrieves the short name of a permission referenced by
	 *   an areaPermission index
	 *
	 * (i.e. The permissionArea table has index x with permissionId y. This function
	 *    returns the name of permission y)
	 *
	 * @param $id int The index of the area permission
	 *
	 * @return string The short name of the permission
	 */
	public function getShortNameById($id) {
		$stmt = $this->pdo->prepare("SELECT permission.shortName FROM permission LEFT JOIN permissionArea ON permission.permissionId=permissionArea.permissionId WHERE permissionArea.index=:id");
		$stmt->execute(array(':id' => $id));
		if($permission = $stmt->fetch())
			return $permission->shortName;
		else
			throw new \Exception("No permission exists with id ".$id);
	}
	
	/**
	 * Revoke all permissions for an employee
	 *
	 * @param $netID string The employee losing permissions
	 * @param $area  int ID of the area that the employee's permissions should be removed from
	 * TODO: Right now, area is ignored, but we should update this to be able to remove 
	 * 	all permissions from a specified area instead of just all permissions period. 
	 */
	function revokeAll($netID, $area = null){
		if (is_null($area)) {
			$deleteQuery = $this->pdo->prepare("DELETE FROM employeePermissions WHERE netID=:netID");
			$deleteQuery->execute(array(':netID' => $netID));
		} else {

		}
	}

}
?>
