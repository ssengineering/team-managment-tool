<?php
require('../../includes/includeMeBlank.php');

$info = $_GET['data'];
$info = explode('_',$info);

try {
	$insertQuery = $db->prepare("INSERT INTO employeeAreaPermissions (netID,area,guid) VALUES (:netID,:area,:guid)");
	$insertQuery->execute(array(':netID' => $info[0], ':area' => $info[1], ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}

?>
