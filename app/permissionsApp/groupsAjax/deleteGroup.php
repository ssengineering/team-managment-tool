<?php //deleteGroup.php
//used to delete a group type via ajax
require('../../includes/includeMeBlank.php');

	$id = $_GET['id'];
	try {
		$deleteQuery = $db->prepare("DELETE FROM permissionsGroups WHERE ID=:id AND area = :area");
		$deleteQuery->execute(array(':id' => $id, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
?>
