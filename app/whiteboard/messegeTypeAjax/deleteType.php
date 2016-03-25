<?php //deleteType.php
//used to delete a messege type via ajax
require('../../includes/includeMeBlank.php');
if(can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936")/*whiteboard resource*/){
	$id = $_GET['id'];

	try {
		$deleteQuery = $db->prepare("DELETE FROM tag WHERE typeId=:id AND area = :area");
		$deleteQuery->execute(array(':id' => $id, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>
