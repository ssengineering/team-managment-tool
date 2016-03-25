<?php
require('../../includes/includeMeBlank.php');

$emp = $_GET['employee'];
$newArea = $_GET['newArea'];

try {
	$deleteQuery = $db->prepare("DELETE FROM employeeAreaPermissions WHERE netID = :employee AND area = :area");
	$deleteQuery->execute(array(':employee' => $emp, ':area' => $newArea));

	$updateQuery = $db->prepare("UPDATE employee SET area = :area WHERE netID = :employee");
	$updateQuery->execute(array(':area' => $newArea, ':employee' => $emp));
} catch(PDOException $e) {
	exit("error in query");
}

?>
