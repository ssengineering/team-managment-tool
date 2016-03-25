<?php
require("../includes/includeMeBlank.php");

$entry = $_GET['entry'];
try {
	$insertQuery = $db->prepare("INSERT INTO `supReport` (submittedBy, entry, guid) VALUES (:netId, :entry, :guid)");
	$insertQuery->execute(array(':netId' => $netID, ':entry' => $entry, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}
$_SESSION['supReport'][date('Y-m-d G:i:s:u')] = array(date('D. @ G:i:s'), $netID, $entry);

	echo "Successfully submitted Supervisor Report Log Entry!";
?>
