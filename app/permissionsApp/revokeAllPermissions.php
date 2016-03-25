<?php //revokes ALL permissions for a user
require('../includes/includeMeBlank.php');
	$grantee=$_GET['netID'];

	//Development Purposes:
	//if(true){
	if(checkPermission('permissions')) {
		revokeAllPermissionsByNetIDInArea($grantee);
	} else {
		echo "You do not have permissions to revoke this permission from ".nameByNetID($grantee)." If you feel this is in error, please contact your supervisor.";
	}
