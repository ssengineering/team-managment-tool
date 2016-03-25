<?php 
require('../../includes/includeMeBlank.php');
if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af"))/*wages resource*/{

$reason = $_GET['comments'];

try {
	$raiseQuery = $db->prepare("SELECT raise FROM employeeRaiseReasons WHERE reason=:reason and area=:area");
	$raiseQuery->execute(array(':reason' => $reason, ':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}
$raisematch=$raiseQuery->fetch(PDO::FETCH_ASSOC);
echo $raisematch['raise'];

}

?>
