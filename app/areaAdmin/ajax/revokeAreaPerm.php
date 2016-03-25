<?php
require('../../includes/includeMeBlank.php');

$info = $_GET['data'];
$info = explode('_',$info);

try {
	$deleteQuery = $db->prepare("DELETE FROM employeeAreaPermissions WHERE netID = :netID AND area = :area");
	$deleteQuery->execute(array(':netID' => $info[0], ':area' => $info[1]));
} catch(PDOException $e) {
	exit("error in query");
}

?>