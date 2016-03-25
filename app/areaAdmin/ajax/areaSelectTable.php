<?php 
require('../../includes/includeMeBlank.php');
$table = "";

$emps =  explode(',',$_GET['employees']);

foreach($emps as $cur){
	$table.= "<tr><td>";
	$table.= nameByNetId($cur);
	$table.= "</td>";
	$table.= PrintEmployeeAreaPermissions($cur);
	$table.= "<td>";
	$table.= PrintDefaultDropDown($cur);
	$table.= "</td></tr>";
}

echo $table;

//this helper function pulls the employees area permissions and prints them for the table
function PrintEmployeeAreaPermissions($emp){
	global $db;
	$returnVal = "";
	$areas = getAreas();
	$permAreas = array();
	try {
		$areaQuery = $db->prepare("SELECT area FROM employeeAreaPermissions WHERE netID = :employee");
		$areaQuery->execute(array(':employee' => $emp));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $areaQuery->fetch()){
		$permAreas[] = $cur->area;
	}
		
	foreach($areas as $curArea){
		$returnVal.= "<td>";
		if($curArea == getEmployeeAreaByNetId($emp)){
			$returnVal.="Default";
		}else if(in_array($curArea,$permAreas)){
			$returnVal.= "<input type='checkbox' value='".$emp."_".$curArea."' id='".$emp."_".$curArea."' onclick='if(this.checked){grantAreaPerm(this.value);}else{revokeAreaPerm(this.value);}' checked />";
		}else {
			$returnVal.= "<input type='checkbox' value='".$emp."_".$curArea."' id='".$emp."_".$curArea."' onclick='if(this.checked){grantAreaPerm(this.value);}else{revokeAreaPerm(this.value);}' />";
		}
		$returnVal.= "</td>";
	}
	

	return $returnVal;

}

function PrintDefaultDropDown($emp){
	global $db;
	$defaultArea = getEmployeeAreaByNetId($emp);
	
	$retVal = "<select id='".$emp."' onchange='changeDefault(\"".$emp."\")' onclick='selectClick(\"".$emp."\")'>";
	try {
		$areasQuery = $db->prepare("SELECT * FROM employeeAreas");
		$areasQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $areasQuery->fetch()){
		if($cur->ID == $defaultArea){
			$retVal.= "<option value='".$cur->area."' selected>".$cur->area."</option>";
		} else {
			$retVal.= "<option value='".$cur->ID."'>".$cur->area."</option>";
		}
	}
	$retVal.= "</select>";
	return $retVal;
}


?>
