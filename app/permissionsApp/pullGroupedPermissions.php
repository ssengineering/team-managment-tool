<?php
	require('../includes/includeMeBlank.php');
		$user=$_GET['netID'];
	
function printGroups($area,$user){
	global $netID, $db;
	//go through all groups in current area
	try {
		$permissionsQuery = $db->prepare("SELECT * FROM permissionsGroups WHERE area=:area");
		$permissionsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $permissionsQuery->fetch(PDO::FETCH_ASSOC)) {
		$filler = "";
		//call has group permissions on them to print checkbox
		$filler.="<input type='checkbox' id='".$cur['ID']."' name='".$cur['ID']."' value='".$cur['ID']."' onclick='if(this.checked){grantGroupPermission(this.value);}else{revokeGroupPermission(this.value);}' ";
		if(hasAllGroupPermissions($user,$cur['ID'])){
			$filler.="checked ";
		}
		if((!hasAllGroupPermissions($netID,$cur['ID']) || $user == $netID) && !checkPermission('development') ){
			$filler.="disabled ";
		}
		$filler.="/>";
		//print group name
		$filler.="<label class 'title' for='".$cur['ID']."'> ".$cur['name']."</label> (<a href='javascript:void' onclick=\"showHide('".$cur['ID']."group')\">See Permissions</a>)<br/>";
		//call pull group permissions to print list of permissions
		$filler.="<div class='description' id='".$cur['ID']."group' style='display:none;'>";
		$filler.= printPermissions($cur['ID']);
		$filler.= "</div><br/>";
		echo $filler;
	}
}

function printPermissions($groupID){
	global $db;
	$info = "";	
	try {
		$groupQuery = $db->prepare("SELECT * FROM permissionsGroupMembers WHERE groupID=:id");
		$groupQuery->execute(array(':id' => $groupID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $groupQuery->fetch(PDO::FETCH_ASSOC)) {
		try {
			$areaQuery = $db->prepare("SELECT * FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId` = `permission`.`permissionId` WHERE `index` = :id");
			$areaQuery->execute(array(':id' => $cur['permID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$row = $areaQuery->fetch(PDO::FETCH_ASSOC);
		$info.="<label class 'title' > ".$row['longName']."</label> (<a href='javascript:void' onclick=\"showHide('".$row['index']."_".$groupID."')\">Info</a>)<br/>";
		$info.="<div class='description' id='".$row['index']."_".$groupID."' style='display:none;'>".$row['description']."</div><br/>";
	}
	return $info;
}


printGroups($area,$user);	
echo "<label class 'title' >All Area Permissions</label> (<a href='javascript:void' onclick=\"showHide('allAreaPermissions')\">See Permissions</a>)<br/>";
echo "<div class='description' id='allAreaPermissions' style='display:none;'>";
$permissions=pullAllPermissionInfoCurrentArea();
$grantedPermissions=pullUserGrantedPermissionIdsCurrentArea($user);
$filler="";
foreach($permissions as $row){
	$filler.="<input type='checkbox' id='".$row['index']."' name='".$row['index']."' value='".$row['index']."' onclick='if(this.checked){grantPermission(this.value);}else{revokePermission(this.value);}' ";
	if(in_array($row['index'],$grantedPermissions))
		$filler.="checked ";
		
		if (checkPermission($row['shortName']) || checkPermission('development')){
			if ($row['shortName'] == "permissions" && $user == $netID){
				$filler.="disabled";
			}
		} else {
			$filler.="disabled ";
		}
	$filler.="/>";
	$filler.="<label class 'title' for='".$row['index']."'> ".$row['longName']."</label> (<a href='javascript:void' onclick=\"showHide('".$row['index']."description')\">Info</a>)<br/>";
	$filler.="<div class='description' id='".$row['index']."description' style='display:none;'>".$row['description']."</div><br/>";
}
echo $filler;
echo "</div>";

?>
