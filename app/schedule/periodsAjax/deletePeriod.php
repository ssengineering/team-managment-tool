<?php 
//deleteTeam.php
require('../../includes/includeMeBlank.php');
if(can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")){//schedule resource
	$semester = $_GET['semester'];
	try {
		$deleteQuery = $db->prepare("DELETE FROM scheduleSemesters WHERE ID=:semester AND area=:area");
		$deleteQuery->execute(array(':semester' => $semester, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>
