<?php
//deleteShift.php
//used to delete a shift via ajax

require('../../includes/includeMeBlank.php');

if(can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85"))//schedule resource
{
	if(isset($_GET['id']))
	{
		try {
			$updateQuery = $db->prepare("UPDATE `scheduleHourTypes` SET `deleted` = 1 WHERE `ID` = :id");
			$updateQuery->execute(array(':id' => $_GET['id']));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
?>
