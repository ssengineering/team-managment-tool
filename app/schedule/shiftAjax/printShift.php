<?php 
//printShift.php
//used to print shift list via ajax

require('../../includes/includeMeBlank.php');

function pullShiftTypes($area)
{
	global $db;
	try {
		$hourTypesQuery = $db->prepare("SELECT * FROM scheduleHourTypes WHERE `area` = :area AND `deleted` = 0 ORDER BY `value` ASC");
		$hourTypesQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	echo "<table id='shiftTable' class='imagetable'style='margin:auto;'><thead><th>Shift Type</th><th>Long Name</th><th>Color</th><th>Permission Needed for Trades</th><th>Tradable</th><th>View by Default</th><th>Self-schedule</th><th>Non-work</th></thead><tbody>";

	while($right = $hourTypesQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<tr id='row".$right['ID']."' class='shiftType' draggable='true' ondragstart='rowDragStart(event)' ondragenter='rowDragEnter(event)' ondragover='rowDragOver(event)' ondragleave='rowDragLeave(event)' ondrop='rowDrop(event)' ondragend='rowDragEnd(event)'>";
		echo "<input id='".$right['ID']."value' name='".$right['ID']."value' type='hidden' value='".$right['value']."' />";
		echo "<td><input type='text' name='".$right['ID']."' style='width:120px' value='".$right['name']."' /></td>";
		echo "<td><input type='text' name='".$right['ID']."longName' style='width:180px' value='".$right['longName']."' /></td>";
		echo "<td bgcolor='".$right['color']."'><input type='text' name='".$right['ID']."color' style='width:80px' value='".$right['color']."' /></td>";
		echo "<td><select name='permission[".$right['ID']."]' id='permission[".$right['ID']."]'>";
		
		pullPermissions($area,$right['ID']);
	
		echo "</select></td>";
		echo "<td>";
	
		if($right['tradable'] == 1)
		{				
			echo "<input type='checkbox' name='".$right['ID']."trade' value='1' checked />";
		}
		else
		{
			echo "<input type='checkbox' name='".$right['ID']."trade' value='1' />";
		}
	
		echo "</td><td>";
	
		if($right['defaultView'] == 1)
		{
			echo "<input type='checkbox' name='".$right['ID']."view' value='1' checked />";
		}
		else
		{
			echo "<input type='checkbox' name='".$right['ID']."view' value='1' />";
		}
		
		echo "</td><td>";
		
		if($right['selfSchedulable'] == 1)
		{
			echo "<input type='checkbox' name='".$right['ID']."ss' value='1' checked />";
		}
		else
		{
			echo "<input type='checkbox' name='".$right['ID']."ss' value='1' />";
		}
		
		echo "</td><td>";
		
		if($right['nonwork'] == 1)
		{
			echo "<input type='checkbox' name='".$right['ID']."nw' value='1' checked />";
		}
		else
		{
			echo "<input type='checkbox' name='".$right['ID']."nw' value='1' />";
		}
		
		echo "</td></tr>";
	}
		
	echo "</tbody></table>";
}

function shiftTypeSelect($area)
{
	global $db;
	try {
		$hourTypesQuery = $db->prepare("SELECT * FROM scheduleHourTypes WHERE `area` = :area AND `value` > 0 AND `deleted` = 0");
		$hourTypesQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	while($type = $hourTypesQuery->fetch(PDO::FETCH_ASSOC))
	{
		if($type['ID'] > 0)
		{
			echo "<option value='".$type['ID']."'/>".$type['name']."</option>";
		}
	}
}

function pullPermissions($area,$selected)
{
	global $db;
	try {
		$hourTypesQuery = $db->prepare("SELECT * FROM scheduleHourTypes WHERE `ID`=:selected AND `deleted` = 0");
		$hourTypesQuery->execute(array(':selected' => $selected));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$cur = $hourTypesQuery->fetch(PDO::FETCH_ASSOC);
	$permissions = pullAllPermissionInfoCurrentArea();
	
	echo "<option value=''></option>";
	
	foreach($permissions as $singlePerm)
	{
		if($cur['permission'] == $singlePerm['index'])
		{
	 		echo "<option value='".$singlePerm['index']."' selected>".$singlePerm['longName']."</option>";
		}
		else
		{
			echo "<option value='".$singlePerm['index']."'>".$singlePerm['longName']."</option>";
		}    
	}
}

if(can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85"))//schedule resource
{
	echo "<br/>";
	
	pullShiftTypes($area);
	
	echo "<br/>";
	echo "<input type='button' class='button' name='deleteHour' value='Remove:' onclick='deleteShift()' />";
	echo  "<select name='shiftTypes' id='shiftTypes'>";

	shiftTypeSelect($area);
	
	echo "</select>";
}
?>
