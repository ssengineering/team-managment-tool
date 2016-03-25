<?php 
//deleteLog.php
//used for deleting entries in performance tables.
require('../includes/includeMeBlank.php');
if(can("access", "033e3c00-4989-4895-a4d5-a059984f7997")){//employeePerformance resource

	$id = $_GET['id'];
	$type = $_GET['type'];
	$queryString = "DELETE FROM report".$type." WHERE ID = :id";
	try {
		$deleteQuery = $db->prepare($queryString);
		$deleteQuery->execute(array(':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>
