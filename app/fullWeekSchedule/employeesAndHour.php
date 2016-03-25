<?php
require('../includes/includeMeBlank.php');
//gets hour types
try {
	$hourQuery = $db->prepare("SELECT * FROM `scheduleHourTypes` WHERE `area` = :area AND `deleted` = 0");
	$hourQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}

$content = "<div><span class='hourTypeList'>Shift Type</span><span style='padding-left: 3%;' class='employees'>Employees</span></div>";
$content .= "<select id='hourTypeList' multiple='multiple' class='selectable hourTypeList' size='9'>";
$content .= "<option>All</option>";
while($row=$hourQuery->fetch(PDO::FETCH_ASSOC))
{
	if($row['defaultView'] == 1)
	{
		$content .= "<option value='$row[ID]' selected>$row[name]</option>";
	}
	else
	{
		$content .= "<option value='$row[ID]'>$row[name]</option>";
	}
}
$content .= "</select>";

// This query now handles cross area employees ~Mika
try {
	$employeeQuery = $db->prepare("SELECT * FROM `employee` WHERE `active` = 1 AND (`area` = :area OR `netID` IN (SELECT `netID` FROM `employeeAreaPermissions` WHERE `area` = :area1)) ORDER BY CASE WHEN `area` = :area2 THEN 0 ELSE 1 END,`firstName`");
	$employeeQuery->execute(array(':area' => $area, ':area1' => $area, ':area2' => $area));
} catch(PDOException $e) {
	exit("error in query");
}

$content .= "<select id='employees' multiple='multiple' class='selectable employees' size='9'>";
$content .= "<option>All</option>";
while($row= $employeeQuery->fetch(PDO::FETCH_ASSOC))
{
	// Check if area is the default area, if not add a star to the name
	$notDefaulted = '';
	if ( $row['area'] != $area )
	{
		$notDefaulted = '*';
	}
	$content .= "<option value='{$row['netID']}'>{$notDefaulted}{$row['firstName']} {$row['lastName']}</option>";
}
$content .= "</select>";

echo $content;
?>
