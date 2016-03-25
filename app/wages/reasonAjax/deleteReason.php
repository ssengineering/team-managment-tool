<?php //deleteReason.php //used for deleting raise reasons app.
require('../../includes/includeMeBlank.php');
if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af"))/*wages resource*/{

	$id = $_GET['id'];

	try {
		$deleteQuery = $db->prepare("DELETE FROM employeeRaiseReasons WHERE `index`=:id AND area = :area");
		$deleteQuery->execute(array(':id' => $id, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>
