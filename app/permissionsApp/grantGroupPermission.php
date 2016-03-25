<?php
	require('../includes/includeMeBlank.php');
	$grantee=$_GET['netID'];
	$group=$_GET['index'];
	$grantor=$netID;

	//Development Purposes:
	//if(true){
	if(checkPermission('permissions')){
		$perms = pullGroupPermissionIdsCurrentGroup($group);
		foreach($perms as $cur){
			$results=grantUserPermissionByIndex($grantee,$cur);
			if(!$results)
				echo "There has been an error with granting ".nameByNetID($grantee)." this permission. Please refresh your page and try again. If you continue to recieve this error, please contact a member of the development team";
		}
	}else{
		echo "You do not have permissions to grant ".nameByNetID($grantee)." this permission. If you feel this is in error, please contact your supervisor.";
	}
	
?>
