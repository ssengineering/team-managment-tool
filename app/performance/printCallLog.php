<?php //printCallLog.php
//This file is the new window brought up when you click see call summary
require('../includes/includeMeSimple.php');

$id = $_GET['ID'];

function getCallSummary($dex){
	global $db;
	try {
		$silentMonitorQuery = $db->prepare("SELECT * FROM silentMonitor WHERE `index`=:index");
		$silentMonitorQuery->execute(array(':index' => $dex));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	//Loops through each silent monitor for the time period associated with the netID.
	while($cur = $silentMonitorQuery->fetch(PDO::FETCH_ASSOC)) {
		$ratingAvg = 0;
		$percentAvg = 0;
		$numCalls = 0;

		echo "<table><tr><th>Call #</th><th>Date</th>";
		getCriteriaHeadings();
		echo "<th>Comment</th><th>Percent</th><th>Rating</th></tr>";

		try {
			$callQuery = $db->prepare("SELECT * FROM silentMonitorCalls WHERE smid = :index");
			$callQuery->execute(array(':index' => $cur['index']));
		} catch(PDOException $e) {
			exit("error in query");
		}	
		//loops through each call associated with that monitor.
		while($curCall = $callQuery->fetch(PDO::FETCH_ASSOC)){
			$numCalls+=1;
			echo "<tr><th>".$curCall['callNum']."</th><th>".$curCall['date']."</th>";
			
			$ratingAvg+=$curCall['rating'];
			$percentAvg+=$curCall['criteriaAvg'];
			try {
				$criteriaQuery = $db->prepare("SELECT * FROM silentMonitorCallCriteria WHERE smid = :id AND callNum = :call ORDER BY criteriaIndex ASC");
				$criteriaQuery->execute(array(':id' => $curCall['smid'], ':call' => $curCall['callNum']));
			} catch(PDOException $e) {
				exit("error in query");
			}
			//Loop through each of the criteria entries for that calls.			
			while($curCrit = $criteriaQuery->fetch(PDO::FETCH_ASSOC)) {
				echo "<td>".$curCrit['rating']."</td>";
			}
			echo "<td>".$curCall['comments']."</td>";
			echo "<td>".$curCall['criteriaAvg']."</td><td>".$curCall['rating']."</td></tr>";
		}
		$ratingAvg = $ratingAvg/$numCalls;
		$percentAvg = $percentAvg/$numCalls;
		echo "</table><table><tr><td colspan=''>Overall Comments:<br/> ".$cur['overallComment']."</td>";
		echo "<td>Criteria Average: ".$percentAvg."</td><td>Rating Average: ".$ratingAvg."</td></tr></table>";

		
	}

}

function getCriteriaHeadings(){
	global $area, $db;
	try {
		$titleQuery = $db->prepare("SELECT title FROM silentMonitorCriteriaInfo WHERE area=:area ORDER BY `index` ASC");
		$titleQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $titleQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<th>".$cur['title']."</th>";
	}
}

?>
<style>
#callSummary{
	position:relative;
	left:40px;
	float:left;
}
</style>
<div id='callSummary'>
<h2>Call Summary</h2>
<?php getCallSummary($id); ?>
</div>
