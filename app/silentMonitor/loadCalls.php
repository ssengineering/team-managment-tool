<?php //loadCalls.php
//This file is called via ajax to load calls for the silent monitor application.
require('../includes/includeMeBlank.php');
require('printSilentMonitor.php');

//set variables here.

if(isset($_GET['id'])){
	$smid = $_GET['id'];
	//query the database for that silent monitor and use that data to populate the fields
	try {
		$callsQuery = $db->prepare("SELECT * FROM silentMonitorCalls WHERE smid = :id ORDER BY callNum");
		$callsQuery->execute(array(':id' => $smid));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($curCall = $callsQuery->fetch(PDO::FETCH_ASSOC)) {
		$comments = $curCall['comments'];
		$rating = $curCall['rating'];
		$callNum = $curCall['callNum'];
		$selectData = array();
		try {
			$criteriaQuery = $db->prepare("SELECT * FROM silentMonitorCallCriteria WHERE smid = :id AND callNum = :call ORDER BY criteriaIndex");
			$criteriaQuery->execute(array(':id' => $smid, ':call' => $callNum));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($curCrit = $criteriaQuery->fetch(PDO::FETCH_ASSOC)) {
			$selectData[$curCrit['criteriaIndex']] = $curCrit['rating'];
		} 
		PrintLoadedCall($callNum,$comments,$rating,$selectData,$curCall['date']);
	}
	//at somepoint we will need to call the following:
}
else if(isset($_GET['autosave']))
{
	$employee = $_GET['employee'];
	$submitDate = date('Y-m-d');
	
	try {
		$insertQuery = $db->prepare("INSERT INTO `sites_ops`.`silentMonitor` (`netID`, `submitter`, `submitDate`, `completed` `guid`) VALUES (:employee, :netId, :submit, '0', :guid)");
		$insertQuery->execute(array(':employee' => $employee, ':netId' => $netID, ':submit' => $submitDate, ':guid' => newGuid()));
		$monitorQuery = $db->prepare("SELECT `index` FROM silentMonitor ORDER BY `index` DESC");
		$monitorQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	$data = $monitorQuery->fetch(PDO::FETCH_ASSOC);
	
	echo $data['index'];
}
else
{
	$callNumber = 1;	
	echo "<div id='callForm".$callNumber."' class='callForm'>";	
	echo "<table class='invisibleTable'>";
	
	PrintBlankCall($callNumber);

	echo "</table>";
	echo "</div>";
}
//use a for loop to call "print calls"
//it will be necessary to modify print calls to take in form data. this could be troublesome since there are select fields.

?>
