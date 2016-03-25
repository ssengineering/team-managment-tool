<?php //insertType.php
//used to insert a type via ajax
require('../../includes/includeMeBlank.php');
if(can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936")/*whiteboard resource*/){

	try {
		$insertQuery = $db->prepare("INSERT INTO tag (area,typeName,color,`mustApprove`,guid) VALUES (:area,'','',0,:guid)");
		$insertQuery->execute(array(':area' => $area, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

?>
