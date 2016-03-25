<?php //execNoteFunctions.php contains functions used for the execNote app

//the IC permission is index 113 if this changes on the beta site
//I'm just to lazy to write the query to find the permission id for the ic permission
function getICList($selected){
	global $db;
	try {
		$permissionQuery = $db->prepare("SELECT * FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId` = `permission`.`permissionId` WHERE shortName='ic'");
		$permissionQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	$perm = $permissionQuery->fetch(PDO::FETCH_ASSOC);
	try {
			$employeePermissionQuery = $db->prepare("SELECT * FROM employeePermissions JOIN employee ON `employeePermissions`.`netID` = `employee`.`netID` WHERE employeePermissions.permission = :permission ORDER BY `employee`.`firstName`"); 
			$employeePermissionQuery->execute(array(':permission' => $perm['index']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while( $cur = $employeePermissionQuery->fetch(PDO::FETCH_ASSOC)){
		if($selected == $cur['netID']){
			echo "<option value='".$cur['netID']."' selected>".nameByNetId($cur['netID'])."</option>";
		} else {
			echo "<option value='".$cur['netID']."'>".nameByNetId($cur['netID'])."</option>";
		}
	}

}
?>
