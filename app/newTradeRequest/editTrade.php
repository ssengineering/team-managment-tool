<?php
require('../includes/includeMeBlank.php');

$id = '';
if (isset($_GET['id']))
{
	$id = $_GET['id'];
}

$tradeArray = array();
if(isset($_POST['JSON']))
{
	$tradeArray = json_decode($_POST['JSON'],true);
}else if(isset($_GET['JSON']))
{
	$tradeArray = json_decode($_GET['JSON'],true);
}
if(count($tradeArray, 1))
{
	foreach($tradeArray as $trade)
	{
		$id = $trade['ID'];
		try {
			$updateQuery = $db->prepare("UPDATE `scheduleTradeBids` SET `deleted`=1 WHERE `tradeID`=:id AND (`hour` >= :end OR `hour` < :start)");
			$updateQuery->execute(array(':id' => $trade['ID'], ':end' => $trade['endTime'], ':start' => $trade['startTime']));
			$update2Query = $db->prepare("UPDATE `scheduleTrades` SET `startTime`=:startTime, `startDate`=:startDate, `endTime`=:endTime, `endDate`=:endDate, `hourType`=:type, `notes`=:notes WHERE `ID` = :id");
			$update2Query->execute(array(
				':startTime' => $trade['startTime'],
				':startDate' => $trade['startDate'],
				':endTime'   => $trade['endTime'],
				':endDate'   => $trade['endDate'],
				':type'      => $trade['hourType'],
				':notes'     => $trade['notes'],
				':id'        => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}else if ($id != '')
{
	try {
		$updateQuery = $db->prepare("UPDATE `scheduleTradeBids` SET `deleted`=1 WHERE `tradeID`=:id AND (`hour` >= :end OR `hour` < :start)");
		$updateQuery->execute(array(':id' => $trade['ID'], ':end' => $trade['endTime'], ':start' => $trade['startTime']));
		$update2Query = $db->prepare("UPDATE `scheduleTrades` SET `startTime`=:startTime, `startDate`=:startDate, `endTime`=:endTime, `endDate`=:endDate, `hourType`=:type, `notes`=:notes WHERE `ID` = :id");
		$update2Query->execute(array(
			':startTime' => $trade['startTime'],
			':startDate' => $trade['startDate'],
			':endTime'   => $trade['endTime'],
			':endDate'   => $trade['endDate'],
			':type'      => $trade['hourType'],
			':notes'     => $trade['notes'],
			':id'        => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
echo 1;
?>
