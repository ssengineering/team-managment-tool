<?php //insertGroup.php 
//used to insert a group via ajax
require('../../includes/includeMeBlank.php');

try {
	$insertQuery = $db->prepare("INSERT INTO permissionsGroups (area, guid) VALUES (:area, :guid)");
	$insertQuery->execute(array(':area' => $area, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}
