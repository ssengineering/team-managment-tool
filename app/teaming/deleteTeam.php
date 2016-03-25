<?php 
//deleteTeam.php
//used for deleting teams in the teaming app.
require('../includes/includeMeBlank.php');
if(can("update", "28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource*/{

	$id = $_GET['id'];

	try {
		$deleteQuery = $db->prepare("DELETE FROM teams WHERE ID = :id AND area = :area");
		$deleteQuery->execute(array(':id' => $id, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>
