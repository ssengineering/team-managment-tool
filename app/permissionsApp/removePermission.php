<?php
	require('../includes/includeMeBlank.php');
	$groupID=$_GET['group'];
	$index=$_GET['index'];


	//Development Purposes:
	//if(true){
	if(checkPermission('permissions')) {
		$permissionsIndex=pullPermissionIndexByNameCurrentArea('permissions');
			$results=revokeGroupPermissionByIndex($groupID,$index);
			if(!$results) {
				echo "There has been an error with removing this permission from this group.";
				echo " Please refresh your page and try again. If you continue to recieve this error, please contact a member of the development team.";
			}
		}
	else {
		echo "You do not have permissions to remove this permission from this group. If you feel this is in error, please contact your supervisor.";
	}
	
?>
