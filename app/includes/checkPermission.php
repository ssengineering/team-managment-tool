<?php

require_once("authorization.php");

//Checks if the current user has the permission specified by the passed in shortname
function checkPermission($permission)
{
	//Makes $netID and $area to reference the global versions, not local (internal to the function) versions.
	global $netID, $area, $db;


	//Gets the ID of the permission needed by area and shortName.
	try {
		$permissionQuery = $db->prepare("SELECT `index` FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId` = `permission`.`permissionId` WHERE shortName=:permission AND area=:area");
		$permissionQuery->execute(array(':permission' => $permission, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	//If a permission with the given shortName and area is not found, returns false as that permission does not exist.
	if (!($permission = $permissionQuery->fetch()))
		return false;

	//Calls the employeePermissions table to see if the given netID has an entry for the given permission index.
	try {
		$employeeQuery = $db->prepare("SELECT * FROM employeePermissions WHERE netID=:netID AND permission=:permission");
		$employeeQuery->execute(array(':netID' => $netID, ':permission' => $permission->index));
	} catch(PDOException $e) {
		exit("error in query");
	}
	//If the employee has the requested permission a row will be found. Return true. If no rows are found, return false.
	if ($employeeQuery->fetch())
		return true;
	else
		return false;

}

//This is identical to the above function except that It takes in a permission ID and not a short name
function checkPermissionByPermissionID($permission)
{
	//Makes $netID and $area to reference the global versions, not local (internal to the function) versions.
	global $netID, $area, $db;
	//Calls the employeePermissions table to see if the given netID has an entry for the given permission index.
	try {
		$permissionQuery = $db->prepare("SELECT * FROM employeePermissions WHERE netID=:netID AND permission=:permission");
		$permissionQuery->execute(array(':netID' => $netID, ':permission' => $permission));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	//If the employee has the requested permission a row will be found. Return true. If no rows are found, return false.
	if ($permissionQuery->fetch())
		return true;
	else
		return false;

}

function checkPermissionRedirect($permission)
{
	if (checkPermission($permission) === false) {
		echo '<h1 style="text-align:center;margin-top:20%;">You do not have permission to see this page</h1><p style="text-align:center;"> If you should have access to this page, please talk to your manager</p>';
		require ('includeAtEnd.php');
		exit();
	}
	
}

/**
 * Calls out to the new permission system to check
 *   if a user has permission to perform this action
 *   on the specified resource
 * @param $verb         string The action being performed
 * @param $resourceGuid string The guid of the resource being accessed
 * @param $netId        string (optional) The netId of the user to check for permission
 *   Defaults to current user's netId
 *
 * @return bool True if the user has permission, false otherwise
 *   or if an error occurred.
 */
function can($verb, $resourceGuid, $netId = null) {
	global $netID, $areaGuid;
	if($netId == null)
		$netId = $netID;
	
	$domain = getEnv('PERMISSIONS_URL');

	$url = $domain."/permission".
		"?employeeGuid=".$netId.
		"&areaGuid=".$areaGuid.
		"&verb=".$verb.
		"&resource=".$resourceGuid;

	$response = sendAuthenticatedRequest("GET", $url);
	// Return response (true OR false) if request was successful, return false otherwise
	if($response["status"] == "OK") {
		return filter_var($response["data"], FILTER_VALIDATE_BOOLEAN);
	} else {
		return false;
	}
}

/**
 * Calls out to the new permission system to check
 *   if a user is an admin in the current area
 *
 * @param $netId string (optional) The netId to check for admin rights
 *   Defaults to the current user
 *
 * @return bool True/False depending on whether or not user is an admin
 */
function isAdmin($netId = null) {
	global $netID, $areaGuid;
	if($netId == null)
		$netId = $netID;
	
	$domain = getEnv('PERMISSIONS_URL');

	$url = $domain."/admin/".$netId."/".$areaGuid;

	$response = sendAuthenticatedRequest("GET", $url);
	// Return response (true OR false) if request was successful, return false otherwise
	if($response["status"] == "OK") {
		return filter_var($response["data"], FILTER_VALIDATE_BOOLEAN);
	} else {
		return false;
	}
}

/**
 * Calls out to the new permission system to check
 *   if a user is current superuser
 *
 * @param $netId string (optional) The netId to check for admin rights
 *   Defaults to the current user
 *
 * @return bool true if the user is currently superuser, false otherwise
 */
function isSuperuser($netId = null) {
	global $netID;
	if($netId == null)
		$netId = $netID;
	
	$domain = getEnv('PERMISSIONS_URL');

	$url = $domain."/superuser/is/".$netId;

	$response = sendAuthenticatedRequest("GET", $url);
	// Return response (true OR false) if request was successful, return false otherwise
	if($response["status"] == "OK") {
		return filter_var($response["data"], FILTER_VALIDATE_BOOLEAN);
	} else {
		return false;
	}
}

/**
 * Calls out to the new permission system to check
 *   if a user can be a superuser
 *
 * @param $netId string (optional) The netId to check for admin rights
 *   Defaults to the current user
 *
 * @return bool true if the user can be superuser, false otherwise
 */
function canBeSuperuser($netId = null) {
	global $netID;
	if($netId == null)
		$netId = $netID;
	
	$domain = getEnv('PERMISSIONS_URL');

	$url = $domain."/superuser/can/".$netId;

	$response = sendAuthenticatedRequest("GET", $url);
	// Return response (true OR false) if request was successful, return false otherwise
	if($response["status"] == "OK") {
		return filter_var($response["data"], FILTER_VALIDATE_BOOLEAN);
	} else {
		return false;
	}
}
?>
