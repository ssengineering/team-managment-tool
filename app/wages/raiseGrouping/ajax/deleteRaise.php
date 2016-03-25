<?php //deleteRaise.php this deletes a raise.
require('../../../includes/includeMeBlank.php');

if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af"))/*wages resource*/{

	$id = $_GET['id'];

try {
	$deleteQuery = $db->prepare("DELETE FROM employeeRaiseLog WHERE `index`=:id");
	$deleteQuery->execute(array(':id' => $id));
} catch(PDOException $e) {
	exit("error in query");
}
}
?>
