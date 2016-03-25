<?php

require('../includes/includeMeBlank.php');

$id = $_GET['id'];
	try {
		$deleteQuery = $db->prepare("DELETE FROM executiveNotification WHERE `ID` = :id");
		$deleteQuery->execute(array(':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}

header('Location: loadOpen.php');
?>

