<?php
//insertShift.php
//used to insert a shift via ajax

require('../../includes/includeMeBlank.php');

if(can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")) //schedule resource
{
	try {
		$hourTypesQuery = $db->prepare("SELECT * FROM scheduleHourTypes WHERE area = :area ORDER BY value DESC");
		$hourTypesQuery->execute(array(':area' => $area));
		$stuff = $hourTypesQuery->fetch(PDO::FETCH_ASSOC);
		$newValue = $stuff['value'] + 1;
		$insertQuery = $db->prepare("INSERT INTO scheduleHourTypes (area,value,name,color,longName,permission,guid) VALUES (:area,:value,'','','','',:guid)");
		$insertQuery->execute(array(':area' => $area, ':value' => $newValue, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>
