<?php
require('../includes/includeMeBlank.php');
//This script is run to post a trade

global $netID;
$name = nameByNetId($netID);
$tradeArray = array();
if(isset($_POST['JSON'])){
	$tradeArray = json_decode($_POST['JSON'],true);
}else{
	$tradeArray = json_decode($_GET['JSON'],true);
}
$tradeMessage = "$name put some shifts up for trade. See the available trades page for details.";
foreach($tradeArray as $trade)
{
	$postedBy = $trade['postedBy'];
	$postedDate = date("Y-m-d",strtotime("today"));
	$shiftId = $trade['shiftId'];
	$startDate = $trade['startDate'];
	$startTime = $trade['startTime'];
	$endDate = $trade['endDate'];
	$endTime = $trade['endTime'];
	$hourType = $trade['hourType'];
	if (isset($trade['notes']))
		$notes = $trade['notes'];
	else
		$notes = '';

	try {
		$insertQuery = $db->prepare("INSERT INTO `scheduleTrades` (postedBy,postedDate,shiftId,startTime,startDate,endTime,endDate,hourType,notes,area,guid)
					VALUES(:postedBy,:postedDate,:shiftId,:startTime,:startDate,:endTime,:endDate,:hourType,:notes,:area,:guid)");
		$insertQuery->execute(array(':postedBy' => $postedBy, ':postedDate' => $postedDate, ':shiftId' => $shiftId, ':startTime' => $startTime, ':startDate' => $startDate, ':endTime' => $endTime, ':endDate' => $endDate, ':hourType' => $hourType, ':notes' => $notes, ':area' => $area, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}

	if(count($tradeArray) == 1) {
		$tradeMessage = "$name put a shift up for trade on ".date("D, M jS, Y",strtotime($startDate))." from ".date("g:ia",strtotime($startTime))." to ".date("g:ia",strtotime($endTime)).".";
	}
}
notify("59f27b54-ce90-11e5-9646-0242ac110012", $tradeMessage);
echo 1;
?>
