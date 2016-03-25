<?php //this adds an Item to a particular checklist
require('../../includes/includeMeBlank.php');

$text = $_GET['text'];
$checklist = $_GET['list'];

try {
	$insertQuery = $db->prepare("INSERT INTO supervisorReportSDTasks (text,checklist,area,guid) VALUES (:text,:checklist,:area,:guid)");
	$insertQuery->execute(array(':text' => $text, ':checklist' => $checklist, ':area' => $area, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}

$id = $db->lastInsertId();

echo "<tr  id='row".$id."'><td><input onclick='fadeRow(\"row".$id."\")' type='checkbox' id='".$id."'  name='".$id."' value = '".$_GET['text']."' ><label for='".$id."' id='label".$id."'> ".$_GET['text']."</label></td><td><input type='button' value='Edit' onclick='editItem(\"".$id."\")' /><input type='button' value='Delete' onclick='deleteItem(\"".$id."\")' /></td></tr>";

?>
