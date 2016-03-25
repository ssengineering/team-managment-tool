<?php //pullPermissionsGroup.php this pulls the list of permissions currently in the group
	require('../includes/includeMeBlank.php');
	
	$group=$_GET['group'];

	$permissions=pullAllPermissionInfoCurrentArea();
	$grantedPermissions=pullGroupPermissionIdsCurrentGroup($group);
	$filler="";
	foreach($permissions as $row){
		$filler.="<input type='checkbox' id='".$row['index']."' name='".$row['index']."' value='".$row['index']."' onclick='if(this.checked){addPermission(this.value);}else{removePermission(this.value);}' ";
		if(in_array($row['index'],$grantedPermissions))
			$filler.="checked ";
		$filler.="/>";
		$filler.="<label class 'title' for='".$row['index']."'> ".$row['longName']."</label> (<a href='javascript:void' onclick=\"showHide('".$row['index']."description')\">?</a>)<br/>";
		$filler.="<div class='description' id='".$row['index']."description' style='display:none;'>".$row['description']."</div><br/>";
	}
	echo $filler;

?>
