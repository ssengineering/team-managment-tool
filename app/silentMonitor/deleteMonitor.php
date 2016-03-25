<?php

require('../includes/includeMeBlank.php');

$id = $_GET['id'];
try {
	$updateQuery = $db->prepare("UPDATE `silentMonitor` SET `deleted` = '1' WHERE `index` = :id");
	$updateQuery->execute(array(':id' => $id));
} catch(PDOException $e) {
	exit("error in query");
}

header('Location: loadIncompleteMonitor.php');
?>

