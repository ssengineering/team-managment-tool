<?php
require_once ("../includes/includeMeBlank.php");

function transactionFailed($queryString, $error)
{
	global $db;
	$db->rollBack();
	echo json_encode(array(
		'status' => "FAIL",
		'query' => $queryString,
		'error' => $error
	));
	exit();
}

if (isset($_POST['areaId']) && isset($_POST['appId']))
{
	global $db;
	// Get necessary permissions
	ob_start();
	include_once ('getNecessaryPermissions.php');
	$response = ob_get_contents();
	ob_end_clean();
	$response = json_decode($response);

	// Begin a transaction
	$db->beginTransaction();

	// Get Necessary permissions' IDs
	$ids = array_map(function($permission)
	{
		return is_array($permission) ? $permission['permissionId'] : $permission -> permissionId;
	}, $response -> permissions);

	// If there are any necessary permissions that do not already exist in the area, associate those permissions with the area
	if (count($ids))
	{
		// Generate a query to insert necessary permissions associated with the given area
		$permissionAreaInsertString = "INSERT INTO `permissionArea` (`guid`, `area`, `permissionId`) VALUES ";
		$permissionAreaInsertParams = array();
		// Generate Insert's values string for permissions
		$values = $_POST['areaId'];
		$values = explode(",", $values);
		$paramNum = 0;
		for ($x = 0; $x < count($values); $x++)
		{
			for($y = 0; $y < count($ids); $y++)
			{
				$permissionAreaInsertString .= "(:guid".$paramNum.",:value".$paramNum.",:id".$paramNum."),";
				$permissionAreaInsertParams[':guid'.$paramNum] = newGuid();
				$permissionAreaInsertParams[':value'.$paramNum] = $values[$x];
				$permissionAreaInsertParams[':id'.$paramNum] = $ids[$y];
				$paramNum++;
			}
		}
		$permissionAreaInsertString = substr($permissionAreaInsertString, 0, -1);
		try {
			$permissionAreaInsertQuery = $db->prepare($permissionAreaInsertString);
			$permissionAreaInsertQuery->execute($permissionAreaInsertParams);
		} catch(PDOException $e) {
			transactionFailed($permissionAreaInsertString, "error in query");
		}

		// Grant the current user any necessary permissions for the app being added to the area
		$idString = implode(',', $ids);

		$employeePermissionsInsertString = "";
		$values = $_POST['areaId'];
		$values = explode(",", $values);
		for ($x = 0; $x < count($values); $x++)
		{
			try {
				$employeePermissionInsertQuery = $db->prepare("INSERT INTO `employeePermissions` (`netID`, `permission`, `guid`) 
															   SELECT :netID, `index`, :guid FROM `permissionArea` WHERE `area` = :area AND `permissionId` IN (:ids)");
				$employeePermissionInsertQuery->execute(array(':guid' => newGuid(),':netID' => $netID, ':area' => $values[$x], ':ids' => $idString));
			} catch(PDOException $e) {
				transactionFailed("", "error in query $e");
			}
		}
	}

	// Set default values for insert (i.e. if the link is not visible what values should be used)
	$name = "${_POST['areaId']}_${_POST['appId']}";
	
	$newTab = '0';
	$sortOrder = '0';
	$parent = NULL;
	// Insert new link between the area and the app

	$linkInsertString = "INSERT INTO `link` (`visible`, `area`, `appId`, `name`, `permission`, `newTab`, `sortOrder`, `parent`, `guid`) VALUES ";
	$values = $_POST['areaId'];
	$values = explode(",", $values);
	

	for ($x = 0; $x < count($values); $x++)
	{
		if (intval($_POST['visible']))
		{
			// This is a link as normally defined in HTML, it will be rendered somewhere on the top-floating nav-bar
			$name = explode(",", $_POST['name']);

			// Get area specific permission then use that as the required permission ID
			$permission = explode(",", $_POST['permission']);
			if ($permission[$x] != 'NULL')
			{
				try {
					$permissionQuery = $db->prepare("SELECT `index` FROM `permissionArea` WHERE `area` = :area AND `permissionId` = :permission");
					$permissionQuery->execute(array(':area' => $values[$x], ':permission' => $permission[$x]));
				} catch(PDOException $e) {
					transactionFailed("", "error in query");
				}
				$permission = $permissionQuery->fetch();
				$permission = $permission->index;
			}
			else
			{
				$permission = NULL;
			}

			// Set the remaining options
			$newTab = explode(",", $_POST['newTab']);
			$parent = explode(",", $_POST['parent']);
			$visible = explode(",", $_POST['visible']);
		}

		$linkInsertString .= "(:visible".$x.", :values".$x.", :app".$x.", :name".$x.", :permission".$x.", :newTab".$x.", :sortOrder".$x.", :parent".$x.", :guid".$x."),";
		$linkInsertParams[':visible'.$x]    = $visible[$x];
		$linkInsertParams[':values'.$x]     = $values[$x];
		$linkInsertParams[':app'.$x]        = $_POST['appId'];
		$linkInsertParams[':name'.$x]       = $name[$x];
		$linkInsertParams[':permission'.$x] = $permission;
		$linkInsertParams[':newTab'.$x]     = $newTab[$x];
		$linkInsertParams[':sortOrder'.$x]  = $sortOrder;
		$linkInsertParams[':parent'.$x]     = ($parent[$x] == "" || $parent[$x] == "NULL") ? NULL: $parent[$x];
		$linkInsertParams[':guid'.$x]       = newGuid();
	}
	$linkInsertString = substr($linkInsertString, 0, -1);
	try {
		$linkInsertQuery = $db->prepare($linkInsertString);
		$success = $linkInsertQuery->execute($linkInsertParams);
	} catch(PDOException $e) {
		transactionFailed($linkInsertString, "error in query");
	}
	if ($success)
	{
		echo json_encode(array(
			'status' => "OK",
			'query'  => $linkInsertString,
			'linkId' => $db->lastInsertId()
		));
		$db->commit();
	}
}
?>
