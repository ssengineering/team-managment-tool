<?php //delete.php this deletes an Item in a checklist
require('../../includes/includeMeBlank.php');

$id = $_GET['id'];

try {
	$deleteQuery = $db->prepare("DELETE FROM supervisorReportSDTasks WHERE ID = :id");
	$deleteQuery->execute(array(':id' => $id));
} catch(PDOException $e) {
	exit("error in query");
}
?>
