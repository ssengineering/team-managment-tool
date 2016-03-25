<?php
	##This file contains several functions which are used for various permission based tasks. 

	##TO-DO##
	//Move checkPermissions and checkPermissionsManual to this file
	##Create function that returns permissions for current area (all information)
	##Create functions that returns NetID's of people with a certain right (in current area)
	// ^ in all areas? is this function needed? As some areas will have permissions others do not. 
	##Create function that returns a Permission's Information by index or Area and ShortName
	##Create function that returns a Permission's Index by Area and ShortName
	##Create function that returns a Permission's Area by Index
	##Create function that returns a Permission's Description by Index
	##Create function that returns a Permission's longName by Index
	##Create function that wipes all granted permissions for a netID
	//Create function that creates a default permission set
	//Create function that grants a user permissions based on a default permission set
	##Create function that grants a user a permission. 
	##Create function that revokes a user permission. 
	
	function pullCurrentUserGrantedPermissionIdsCurrentArea(){
		//Uses the global versions of $netID and $area instead of local versions
		global $netID,$area,$db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM employeePermissions WHERE netID=:netID");
			$permissionQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$permissions = array();
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			try {
				$areaQuery = $db->prepare("SELECT area FROM `permissionArea` WHERE `index`=:permission");
				$areaQuery->execute(array(':permission' => $row['permission']));
			} catch(PDOException $e) {
				exit("error in query");
			}			
			$check=$areaQuery->fetch(PDO::FETCH_ASSOC);
			if($check['area'] == $area)
				$permissions[] = $row['permission'];
		}
		return $permissions;
	}

	function pullUserGrantedPermissionIdsCurrentArea($netID){
		//Uses the global version $area instead of local version
		global $area,$db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM employeePermissions WHERE netID=:netID");
			$permissionQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$permissions = array();
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			try {
				$areaQuery = $db->prepare("SELECT area FROM `permissionArea` WHERE `index`=:permission");
				$areaQuery->execute(array(':permission' => $row['permission']));
			} catch(PDOException $e) {
				exit("error in query");
			}			
			$check = $areaQuery->fetch(PDO::FETCH_ASSOC);
			if($check['area'] == $area)
				$permissions[]=$row['permission'];		
		}
		return $permissions;
	}

	function pullCurrentUserGrantedPermissionIdsAllAreas(){
		//Uses the global version of $netID instead of local version
		global $netID,$db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM employeePermissions WHERE netID=:netID");
			$permissionQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$permissions = array();
		while($row = $permissionQuery->fetch()){
			$permissions[]=$row->permission;
		}
		return $permissions;
	}

	function pullUserGrantedPermissionIdsAllAreas($netID){
		global $db;
		try {
			$query = $db->prepare("SELECT permission FROM employeePermissions WHERE netID=:netID");
			$query->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$permissions = array();
		while($row = $query->fetch()){
			$permissions[]=$row->permission;
		}
		return $permissions;
	}
	
	function pullCurrentUserGrantedPermissionNamesCurrentArea(){
		//Uses the global versions of $netID and $area instead of local versions
		global $netID,$area,$db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM employeePermissions WHERE netID=:netID");
			$permissionQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$permissions = array();
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			try {
				$areaQuery = $db->prepare("SELECT area,shortName FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE `index`=:permission");
				$areaQuery->execute(array(':permission' => $row['permission']));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$check = $areaQuery->fetch(PDO::FETCH_ASSOC);
			if($check['area'] == $area)
				$permissions[]=$check['shortName'];		
		}
		return $permissions;
	}
	
	function pullUserGrantedPermissionNamesCurrentArea($netID){
		//Uses the global version $area instead of local version
		global $area,$db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM employeePermissions WHERE netID=:netID");
			$permissionQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$permissions = array();
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			try {
				$areaQuery = $db->prepare("SELECT area,shortName FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE `index`=:permission");
				$areaQuery->execute(array(':permission' => $row['permission']));
			} catch(PDOException $e) {
				exit("error in query");
			}			
			$check = $areaQuery->fetch(PDO::FETCH_ASSOC);
			if($check['area'] == $area)
				$permissions[]=$check['shortName'];
		}
		return $permissions;
	}

	function pullCurrentUserGrantedPermissionNamesAllAreas(){
		//Uses the global version of $netID instead of local version
		global $netID,$db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM employeePermissions WHERE netID=:netID");
			$permissionQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$permissions = array();
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			try {
				$areaQuery = $db->prepare("SELECT area,shortName FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE `index`=:permission");
				$areaQuery->execute(array(':permission' => $row['permission']));
			} catch(PDOException $e) {
				exit("error in query");
			}			
			$check = $areaQuery->fetch(PDO::FETCH_ASSOC);
			$permissions[]=$check['shortName'];		
		}
		return $permissions;
	}

	function pullUserGrantedPermissionNamesAllAreas($netID){
		global $db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM employeePermissions WHERE netID=:netID");
			$permissionQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$permissions = array();
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			try {
				$areaQuery = $db->prepare("SELECT area,shortName FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE `index`=:permission");
				$areaQuery->execute(array(':permission' => $row['permission']));
			} catch(PDOException $e) {
				exit("error in query");
			}			
			$check = $areaQuery->fetch(PDO::FETCH_ASSOC);
			$permissions[] = $check['shortName'];		
		}
		return $permissions;
	}

	function pullAllPermissionInfoCurrentArea(){
		//Uses the global version of $area instead of local version
		global $area,$db;
		try {
			$permissionQuery = $db->prepare("SELECT * FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE area=:area ORDER BY longName ASC");
			$permissionQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$permissions=array();
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			$permissions[]=$row;
		}
		return $permissions;
	}

	function pullAllPermissionInfoAllAreas(){
		global $db;
		try {
			$permissionQuery = $db->prepare("SELECT * FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` ORDER BY longName ASC");
			$permissionQuery->execute();
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$permissions = array();
		while($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)){
			$permissions[] = $row;
		}
		return $permissions;
	}
	
	function pullAllUsersWithPermissionCurrentArea($permission){
		//Uses the global version $area instead of local version
		global $area,$db;
		try {
			$permissionQuery = $db->prepare("SELECT `index` FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId` = `permission`.`permissionId` WHERE shortName=:permission AND area=:area");
			$permissionQuery->execute(array(':permission' => $permission, ':area' => $area));
			$results = $permissionQuery->fetch(PDO::FETCH_ASSOC);
			$userQuery = $db->prepare("SELECT netID FROM employeePermissions WHERE permission=:index");
			$userQuery->execute(array(':index' => $results['index']));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$users = array();
		while($row = $userQuery->fetch())
			$users[] = $row->netID;
		return $users;
	}

	function pullPermissionIndexByNameCurrentArea($permission){
		//Uses the global version $area instead of local version
		global $area,$db;
		try {
			$permissionQuery = $db->prepare("SELECT `index` FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE shortName=:permission AND area=:area");
			$permissionQuery->execute(array(':permission' => $permission, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$results = $permissionQuery->fetch();
		return $results->index;
	}

	function pullAllPermissionInfoByIndex($index){
		global $db;
		try {
			$permissionQuery = $db->prepare("SELECT * FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE `index`=:index");
			$permissionQuery->execute(array(':index' => $index));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $permissionQuery->fetch(PDO::FETCH_ASSOC);
		return $result;
	}

	function pullAllPermissionInfoByNameCurrentArea($permission){
		//Uses the global version $area instead of local version
		global $area,$db;
		$index=pullPermissionIndexByNameCurrentArea($permission);
		try {
			$permissionQuery = $db->prepare("SELECT * FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE `index`=:index");
			$permissionQuery->execute(array(':index' => $index));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $permissionQuery->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
	
	function pullPermissionAreaByIndex($index){
		$info=pullAllPermissionInfoByIndex($index);
		return $info['area'];
	}
	
	function pullPermissionDescriptionByIndex($index){
		$info=pullAllPermissionInfoByIndex($index);
		return $info['description'];
	}
	
	function pullPermissionLongNameByIndex($index){
		$info=pullAllPermissionInfoByIndex($index);
		return $info['longName'];
	}

	function pullPermissionShortNameByIndex($index){
		$info=pullAllPermissionInfoByIndex($index);
		return $info['shortName'];
	}

	function revokeAllPermissionsByNetID($netID){
		global $db;
		try {
			$deleteQuery = $db->prepare("DELETE FROM employeePermissions WHERE netID=:netID");
			$deleteQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}

	function revokeAllPermissionsByNetIDInArea($netID){
		global $area,$db;
		$perms = pullUserGrantedPermissionIdsCurrentArea($netID);
		foreach($perms as $perm){
			try {
				$deleteQuery = $db->prepare("DELETE FROM employeePermissions WHERE netID = :netID AND permission = :permission");
				$deleteQuery->execute(array(':netID' => $netID, ':permission' => $perm));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
	
	function grantUserPermissionByIndex($netID, $index){
		global $db;
		try {
			$insertQuery = $db->prepare("INSERT INTO employeePermissions (netID,permission,guid) VALUES (:netID,:index,:guid) ON DUPLICATE KEY UPDATE netID=:netID2");
			$success = $insertQuery->execute(array(':netID' => $netID, ':index' => $index, ':guid' => newGuid(), ':netID2' => $netID));
		} catch(PDOException $e) {
			$success = false;
		}
		return $success;
	}

	
	function grantUserPermissionByName($netID, $permission){
		$index=pullPermissionIndexByNameCurrentArea($permission);
		return grantUserPermissionByIndex($netID, $index);
	}

	function revokeUserPermissionByIndex($netID, $index){
		global $db;
		try {
			$deleteQuery = $db->prepare("DELETE FROM employeePermissions WHERE netID=:netID AND permission=:index");
			$success = $deleteQuery->execute(array(':netID' => $netID, ':index' => $index));
		} catch(PDOException $e) {
			$success = false;
		}
		return $success;
	}
	
	function revokeUserPermissionByName($netID, $permission){
		$index=pullPermissionIndexByNameCurrentArea($permission);
		return revokeUserPermissionByIndex($netID,$index);
	}

//**********************************
// PERMISSION GROUP FUNCTIONS
//**********************************
function pullGroupPermissionIdsCurrentGroup($group){
	global $db;
	//Uses the global version $area instead of local version
	try {
		$groupQuery = $db->prepare("SELECT * FROM permissionsGroupMembers WHERE groupID=:group");
		$groupQuery->execute(array(':group' => $group));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$permissions = array();
	while($row = $groupQuery->fetch()){
			$permissions[] = $row->permID;		
	}
	return $permissions;
}

function grantGroupPermissionByIndex($index,$groupID){
	global $db;
	try {
		$insertQuery = $db->prepare("INSERT INTO permissionsGroupMembers (permID,groupID,guid) VALUES (:index,:groupID,:guid)");
		$success = $insertQuery->execute(array(':index' => $index, ':groupID' => $groupID, ':guid' => newGuid()));
	} catch(PDOException $e) {
		$success = false;
	}
	return $success;
}

function revokeGroupPermissionByIndex($groupID, $index){
	global $db;
	try {
		$deleteQuery = $db->prepare("DELETE FROM permissionsGroupMembers WHERE groupID=:groupID AND permID=:index");
		$success = $deleteQuery->execute(array(':groupID' => $groupID, ':index' => $index));
	} catch(PDOException $e) {
		$success = false;
	}
	return $success;
}

//Checks if a user has all of the permissions in the specified group
function hasAllGroupPermissions($netID,$groupID){
	global $db;
	//Uses the global version $area instead of local version
	try {
		$permissionQuery = $db->prepare("SELECT * FROM permissionsGroupMembers WHERE groupID=:groupID AND permID NOT IN (SELECT permission as permID FROM employeePermissions WHERE netID=:netID)");
		$permissionQuery->execute(array(':groupID' => $groupID, ':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	if($results = $permissionQuery->fetch()) {
		return false;
	} else {
		return true;
	}
}

?>
