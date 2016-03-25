<?php //deleteLink
require('../../includes/includeMeSimple.php'); 
$linkId = $_GET['id'];
try {
	$deleteQuery = $db->prepare("DELETE FROM `link` WHERE `index` = :id");
	$deleteQuery->execute(array(':id' => $linkId));
} catch(PDOException $e) {
	exit("error in query");
}
?>
