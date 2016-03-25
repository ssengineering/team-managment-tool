<?php
require('includeMeBlank.php');
global $db;
/* The number "1" on the variables does not have a specific meaning other than distinguishing from variables the have been used in other files.  Everything
on this file should be self explanatory.  We are quering the database according to the value in the $viewType variable then we are ouputing the needed fields. if the $viewType variable = "".  Then we output every employee sorted by ascending order.  Else we query the database with a wildcard (%$viewType%) and retrieve data found in the first and last name fields of the database. */
$viewType = $_GET['q'];
$activeStatus = $_GET['t'];
$allEmployees = $_GET['e'];
//if set to false only show employees from the same area, excluding those that are from other
//areas that have access to the current area. Set default to show all employees with access.
if($allEmployees == "false")
	$allEmployees = false;
else
	$allEmployees = true;

	$fName1="";
	$lName1="";
	$netId1="";
	$area1 = "";
	$allPossibilities="";

$firstAndLast=explode(' ', $viewType);

$firstAndLastString = '';
	
	if($viewType=="") {
		$params = array(':activeStatus' => $activeStatus, ':area1' => $area, ':area2' => $area);
		$query = "SELECT * FROM `employee` WHERE `active` = :activeStatus AND ((`area` = :area1) OR (`netID` IN (SELECT `netID` FROM `employeeAreaPermissions` WHERE `area` = :area2))) ORDER BY `firstName` ASC";
		
	} else {
		if (isset($firstAndLast[1])) {
			$params = array(':activeStatus' => $activeStatus, ':area1' => $area, ':area2' => $area, ':firstLast1' => "%".$firstAndLast[0]."%", ':firstLast2' => "%".$firstAndLast[1]."%", ':firstLast3' => "%".$firstAndLast[1]."%", ':firstLast4' => "%".$firstAndLast[0]."%");
			$viewTypeString = "%".$viewType."%";
			for($i = 1; $i <= 9; $i++) {
				$params[':viewType'.$i] = $viewTypeString;
			}
			$query = "SELECT * FROM `employee` WHERE `active` = :activeStatus AND ((`area`= :area1) OR (`netID` IN (SELECT `netID` FROM `employeeAreaPermissions` WHERE `area` = :area2))) AND ((`firstName` LIKE :firstLast1 AND `lastName` LIKE :firstLast2 OR `firstName` LIKE :firstLast3 AND `lastName` LIKE :firstLast4) OR firstName LIKE :viewType1 OR lastName LIKE :viewType2 OR netID LIKE :viewType3 OR phone LIKE :viewType4 OR email LIKE :viewType5 OR languages LIKE :viewType6 OR position LIKE :viewType7 OR supervisor LIKE :viewType8 OR certificationLevel LIKE :viewType9) ORDER BY `firstName` ASC";
		} else {
			$params = array(':activeStatus' => $activeStatus, ':area1' => $area, ':area2' => $area);
			$viewTypeString = "%".$viewType."%";
			for($i = 1; $i <= 9; $i++) {
				$params[':viewType'.$i] = $viewTypeString;
			}
			$query = "SELECT * FROM `employee` WHERE `active` = :activeStatus AND ((`area`= :area1) OR (`netID` IN (SELECT `netID` FROM `employeeAreaPermissions` WHERE `area` = :area2))) AND (firstName LIKE :viewType1 OR lastName LIKE :viewType2 OR netID LIKE :viewType3 OR phone LIKE :viewType4 OR email LIKE :viewType5 OR languages LIKE :viewType6 OR position LIKE :viewType7 OR supervisor LIKE :viewType8 OR certificationLevel LIKE :viewType9) ORDER BY `firstName` ASC";
		}
	}
	try {
		$employeeQuery = $db->prepare($query);
		$employeeQuery->execute($params);
	} catch(PDOException $e) {
		exit("error in query");
	}
	$employeeListId = 0;
		
	while($row = $employeeQuery->fetch(PDO::FETCH_ASSOC)) {
		$fName1= $row['firstName'];
		$lName1= $row['lastName'];
		$netId1= $row['netID'];
		$area1 = $row['area'];
		
		if($area1 != $area)
		{
			//if allEmployees is set to false it will skip employees that are not in the same default area
			if(!$allEmployees)
				continue;

			$allPossibilities .= "<a id='employeeListId".$employeeListId."' title='".$netId1."' href='javascript:void(0)' onclick=\"javascript:employeeListSubmit('".$netId1."');\">".$fName1." ".$lName1."*</a><br />";
		}
		else
		{	
			$allPossibilities .= "<a id='employeeListId".$employeeListId."' title='".$netId1."' href='javascript:void(0)' onclick=\"javascript:employeeListSubmit('".$netId1."');\">".$fName1." ".$lName1."</a><br />";
		}	
		$employeeListId += 1;
	}

	echo $allPossibilities;
	
?>
