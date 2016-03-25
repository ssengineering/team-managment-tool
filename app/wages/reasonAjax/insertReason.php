<?php //insertReason.php used to insert a new reason into the current area.
require('../../includes/includeMeBlank.php');
if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af"))/*wages resource*/{

	try {
		$insertQuery = $db->prepare("INSERT INTO employeeRaiseReasons (area,reason,guid) VALUES (:area,'new',:guid)");
		$insertQuery->execute(array(':area' => $area, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>
