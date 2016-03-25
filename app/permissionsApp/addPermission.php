<?php
	require('../includes/includeMeBlank.php');
	$groupID=$_GET['group'];
	$index=$_GET['index'];

	//Development Purposes:
	//if(true){
	if(checkPermission('permissions')){
		$results=grantGroupPermissionByIndex($index,$groupID);
		if(!$results)
			echo "There has been an error with adding this permission. Please refresh your page and try again. If you continue to recieve this error, please contact a member of the development team";
	}else{
		echo "You unauthorized to add this permission. If you feel this is in error, please contact your supervisor.";
	}
	
?>
