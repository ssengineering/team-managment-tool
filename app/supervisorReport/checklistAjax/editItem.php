<?php //this edits an Item to a particular checklist
require('../../includes/includeMeBlank.php');

$text = $_GET['text'];
$id = $_GET['id'];

try {
	$updateQuery = $db->prepare("UPDATE supervisorReportSDTasks SET text = :text WHERE ID = :id");
	$updateQuery->execute(array(':text' => $text, ':id' => $id));
} catch(PDOException $e) {
	exit("error in query");
}
?>
