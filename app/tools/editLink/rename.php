<?php require('../../includes/includeMeBlank.php'); 
$old_title = $_POST['old_title'];
$new_title = $_POST['new_title'];
try {
	$updateQuery = $db->prepare("UPDATE `link` SET `name` = :new WHERE `name` = :old");
	$updateQuery->execute(array(':new' => $new_title, ':old' => $old_title));
} catch(PDOException $e) {
	exit("error in query");
}
require('../../includes/includeAtEnd.php');
?>
