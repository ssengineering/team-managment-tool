<?php
	require('../includes/includeMeBlank.php');
	
	$user=$_GET['netID'];

	$permissions=pullAllPermissionInfoCurrentArea();
	$grantedPermissions=pullUserGrantedPermissionIdsCurrentArea($user);
	$filler="";
	foreach($permissions as $row){
		$filler.="<input type='checkbox' id='".$row['index']."' name='".$row['index']."' value='".$row['index']."' onclick='if(this.checked){grantPermission(this.value);}else{revokePermission(this.value);}' ";
		if(in_array($row['index'],$grantedPermissions))
			$filler.="checked ";
		if ($row['shortName'] == "permissions" || $user == $netID)
			$filler.="disabled ";
		$filler.="/>";
		$filler.="<label class 'title' for='".$row['index']."'> ".$row['longName']."</label> (<a href='javascript:void' onclick=\"showHide('".$row['index']."description')\">?</a>)<br/>";
		$filler.="<div class='description' id='".$row['index']."description' style='display:none;'>".$row['description']."</div><br/>";
	}
	echo $filler;

?>
