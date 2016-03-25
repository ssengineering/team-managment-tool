<?php 

//deleteCallLog.php
//used for deleting entries in performance tables.

require('../includes/includeMeBlank.php');

if(can("access", "033e3c00-4989-4895-a4d5-a059984f7997"))//employeePerformance resource
{
	$type = $_GET['type'];
	$id = $_GET['id'];
	$call = $_GET['call'];
	
	if($type == 'call')
	{
		try {
			$updateQuery = $db->prepare("UPDATE silentMonitorCalls SET deleted = '1' WHERE smid = :id AND callNum = :call");
			$updateQuery->execute(array(':id' => $id, ':call' => $call));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	else if($type == 'monitor')
	{
		try {
			$updateQuery = $db->prepare("UPDATE `silentMonitor` SET deleted = '1' WHERE `index` = :id");
			$updateQuery->execute(array(':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}

?>
