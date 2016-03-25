<?php //submitMonitor.php
//this file will input the information into the database. 
require('../includes/includeme.php'); ?>
<?php
if(isset($_GET['saveMonitor']))
{
	$curnetID = $_GET['employee'];
	$submitter = $netID;
	$submitDate = date('Y-m-d');
	$complete = 0;
	$index = $_GET['dex'];
	$overall = $_GET['overallComments'];
	$callNum = $_GET['callNumber'];
	
	//This query inserts the silent monitor contact information into the silentMonitor Table
	try {
		$updateQuery = $db->prepare("UPDATE silentMonitor SET netID=:netId, submitter=:submitter, submitDate=:submitDate, completed='0', overallComment=:overall WHERE `index` = :index");
		$updateQuery->execute(array(':netId' => $curnetID, ':submitter' => $submitter, ':submitDate' => $submitDate, ':overall' => $overall, ':index' => $index));
	} catch(PDOException $e) {
		exit("error in query");
	}

	//This loop will add each successive call to the database of calls.
	//for($callNum = 1; $callNum <= count($_GET['comments']); $callNum++){
	$criteriaAvg = 0;
	for($criteria = 1; $criteria <= count($_GET['select'][$callNum]); $criteria++){
		$cur = $_GET['select'][$callNum][$criteria];
		if($cur == "Yes"){
			$criteriaAvg+=1;
		}else if($cur == "Partial"){
			$criteriaAvg+=0.5;
		}
	}
	$criteriaAvg = ($criteriaAvg / count($_GET['select'][$callNum]))*100;
	
	try {
		$callsQuery = $db->prepare("SELECT * FROM `silentMonitorCalls` WHERE `smid`=:index AND `callNum`=:call");
		$callsQuery->execute(array(':index' => $index, ':call' => $callNum));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$data2 = $callsQuery->fetch(PDO::FETCH_ASSOC);
	
	if(empty($data2))
	{
		$insertCallQueryString = "INSERT INTO silentMonitorCalls (smid,callNum,comments,rating,date,guid) VALUES (:index, :callNum, :comments, :rating, :start, :guid)";
		$insertCallParams = array(':index' => $index, ':callNum' => $callNum, ':comments' => $_GET['comments'][$callNum], ':rating' => $_GET['rating'][$callNum], ':start' => $_GET['startDate'][$callNum], ':guid' => newGuid());
	}
	else
	{
		$insertCallQueryString = "UPDATE silentMonitorCalls SET `comments`=:comments, `rating`=:rating, `date`=:start WHERE `smid`=:index AND `callNum`=:callNum";
		$insertCallParams = array(':comments' => $_GET['comments'][$callNum], ':rating' => $_GET['rating'][$callNum], ':start' => $_GET['startDate'][$callNum], ':index' => $index, ':callNum' => $callNum);
	}
	
	try {
		$insertCallQuery = $db->prepare($insertCallQueryString);
		$insertCallQuery->execute($insertCallParams);
		//This loop will insert each of the criteria into the silentMonitorCallCriteria table
		$criteriaQuery = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `smid`=:index AND `callNum`=:callNum");
		$criteriaQuery->execute(array(':index' => $index, ':callNum' => $callNum));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$data4 = $criteriaQuery->fetch(PDO::FETCH_ASSOC);
	
	if(empty($data4))
	{
		for($criteria = 1; $criteria <= count($_GET['select'][$callNum]); $criteria++)
		{		
			try {
				$insertQuery = $db->prepare("INSERT INTO silentMonitorCallCriteria (smid,callNum,criteriaIndex,rating,guid) VALUES (:index,:call,:criteria,:rating,:guid)");
				$insertQuery->execute(array(':index' => $index, ':call' => $callNum, ':criteria' => $criteria, ':rating' => $_GET['select'][$callNum][$criteria], ':guid' => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
	else
	{
		for($criteria = 1; $criteria <= count($_GET['select'][$callNum]); $criteria++)
		{
			try {
				$updateQuery = $db->prepare("UPDATE silentMonitorCallCriteria SET `criteriaIndex`=:criteria, `rating`=:rating WHERE `smid`=:index AND `callNum`=:call");
				$updateQuery->execute(array(':criteria' => $criteria, ':rating' => $_GET['select'][$callNum][$criteria], ':index' => $index, ':call' => $callNum));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
	try {
		$updateCallQuery = $db->prepare("UPDATE silentMonitorCalls SET criteriaAvg = :average WHERE smid = :index AND callNum = :call");
		$updateCallQuery->execute(array(':average' => $criteriaAvg, ':index' => $index, ':call' => $callNum));
	} catch(PDOException $e) {
		exit("error in query");
	}	
}
else
{
	$curnetID = $_POST['employee'];
	$submitter = $netID;
	$submitDate = date('Y-m-d');
	$callDate = $_POST['startDate'];
	$complete = 1;
	$index = 0;
	$emailError = 0;
	
	if(isset($_POST['saveMonitor'])){
		$complete = 0;
	}
	if(isset($_POST['dex'])){
		$index = $_POST['dex'];
	}
	$overall = $_POST['overallComments'];

	if($index == 0){
		//This query inserts the silent monitor contact information into the silentMonitor Table
		try {
			$insertQuery = $db->prepare("INSERT INTO silentMonitor (netID,submitter,submitDate,completed,overallComment,guid) VALUES (:netId,:submitter,:submitDate,:complete,:overall,:guid)");
			$insertQuery->execute(array(':netId' => $curnetID, ':submitter' => $submitter, ':submitDate' => $submitDate, ':complete' => $complete, ':overall' => $overall, ':guid' => newGuid()));
			$monitorQuery = $db->prepare("SELECT `index` FROM silentMonitor ORDER BY `index` DESC");
			$monitorQuery->execute();
		} catch(PDOException $e) {
			exit("error in query");
		}

		
		//This query gets us the current ID number of the inserted Silent Monitor so that we can attach the comments
		//	and ratings to it for each individual call.
		$temp = $monitorQuery->fetch(PDO::FETCH_ASSOC);
		$smID = $temp['index'];	

		//This loop will add each successive call to the database of calls.
		for($callNum = 1; $callNum <= count($_POST['comments']); $callNum++){
			$criteriaAvg = 0;
			for($criteria = 1; $criteria <= count($_POST['select'][$callNum]); $criteria++){
				$cur = $_POST['select'][$callNum][$criteria];
				if($cur == "Yes"){
					$criteriaAvg+=1;
				}else if($cur == "Partial"){
					$criteriaAvg+=0.5;
				}
			}
			$criteriaAvg = ($criteriaAvg / count($_POST['select'][$callNum]))*100;	
			try {
				$callInsertQuery = $db->prepare("INSERT INTO silentMonitorCalls (smid,callNum,comments,rating,date,guid) VALUES (:smId, :call, :comments, :rating, :start, :guid)");
				$callInsertQuery->execute(array(':smId' => $smID, ':call' => $callNum, ':comments' => $_POST['comments'][$callNum], ':rating' => $_POST['rating'][$callNum] , ':start' => $_POST['startDate'][$callNum], ':guid' => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}
			//this loop will insert each of the criteria into the silentMonitorCallCriteria table
			for($criteria = 1; $criteria <= count($_POST['select'][$callNum]); $criteria++){		
				try {
					$criteriaInsertQuery = $db->prepare("INSERT INTO silentMonitorCallCriteria (smid,callNum,criteriaIndex,rating,guid) VALUES (:smId,:call,:criteria,:rating,:guid)");
					$criteriaInsertQuery->execute(array(':smId' => $smID, ':call' => $callNum, ':criteria' => $criteria, ':rating' => $_POST['select'][$callNum][$criteria], ':guid' => newGuid()));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
			try {
				$callUpdateQuery = $db->prepare("UPDATE silentMonitorCalls SET criteriaAvg = :average WHERE smid = :smId AND callNum = :call");
				$callUpdateQuery->execute(array(':average' => $criteriaAvg, ':smId' => $smID, ':call' => $callNum));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	} else {
		//This query inserts the silent monitor contact information into the silentMonitor Table
		try {
			$updateMonitorQuery = $db->prepare("UPDATE silentMonitor SET netID= :netId, submitter = :submitter, submitDate = :submitDate, completed = :complete, overallComment = :overall WHERE `index` = :index");
			$updateMonitorQuery->execute(array(':netId' => $curnetID, ':submitter' => $submitter, ':submitDate' => $submitDate, ':complete' => $complete, ':overall' => $overall, ':index' => $index));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$smID = $index;
		//This loop will add each successive call to the database of calls.
		for($callNum = 1; $callNum <= count($_POST['comments']); $callNum++){
			$criteriaAvg = 0;
			for($criteria = 1; $criteria <= count($_POST['select'][$callNum]); $criteria++){
				$cur = $_POST['select'][$callNum][$criteria];
				if($cur == "Yes"){
					$criteriaAvg+=1;
				}else if($cur == "Partial"){
					$criteriaAvg+=0.5;
				}
			}
			$criteriaAvg = ($criteriaAvg / count($_POST['select'][$callNum]))*100;	
			try {
				$callUpdateQuery = $db->prepare("UPDATE silentMonitorCalls SET date = :day, comments = :comments, rating = :rating WHERE smid = :smId AND callnum = :call");
				$callUpdateQuery->execute(array(':day' => $callDate[$callNum], ':comments' => $_POST['comments'][$callNum], ':rating' => $_POST['rating'][$callNum], ':smId' => $index, ':call' => $callNum));
			} catch(PDOException $e) {
				exit("error in query");
			}
			//this loop will insert each of the criteria into the silentMonitorCallCriteria table
			for($criteria = 1; $criteria <= count($_POST['select'][$callNum]); $criteria++){		
				try {
					$updateMonitorQuery = $db->prepare("UPDATE silentMonitorCallCriteria SET rating = :rating WHERE smid = :index AND callNum = :call AND criteriaIndex = :criteria");
					$updateMonitorQuery->execute(array(':rating' => $_POST['select'][$callNum][$criteria], ':index' => $index, ':call' => $callNum, ':criteria' => $criteria));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
			try {
				$updateMonitorQuery = $db->prepare("UPDATE silentMonitorCalls SET criteriaAvg = :average WHERE smid = :index AND callNum = :call");
				$updateMonitorQuery->execute(array(':average' => $criteriaAvg, ':index' => $index, ':call' => $callNum));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}

	if($env == 0){
		echo "netID of submitter: ".$netID;
		echo "<br/>netID of caller: ".$_POST['employee'];
		echo "<br/>date of Submittal: ".var_dump($_POST['startDate']);
		echo "<br/>";
		echo "Select box data <br/>";
		var_dump($_POST['select']);
		echo "<br/>Comment Box Data<br/>";
		var_dump($_POST['comments']);
		echo "<br/>Rating Box Data<br/>";
		var_dump($_POST['rating']);
	}
	
	if($complete == 1)
	{
	
		//Create $persons object to be passed in to the notify function
		$persons = getReceivers($curnetID, $areaGuid, "a9dd371f-1a43-45d1-a316-378623e01a4c");  	
		//Call notify function using the object $person created above as the third argument.
		notify("a9dd371f-1a43-45d1-a316-378623e01a4c", "A silent monitor has been submitted for you. Please see your report on the Silent Monitor Log page.", $persons);
	

	}
}
	


?>

<a href='./index.php'>Return to Silent Monitor Application</a>
<?php require('../includes/includeAtEnd.php'); ?>
