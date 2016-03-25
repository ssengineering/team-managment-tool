<?php
require('../../includes/includeMeBlank.php');

$empNetID = $_GET['empNetID'];
$area = $_GET['area'];
$start = $_GET['startDate'];
$end =$_GET['endDate'];

	$one=0; $two=0; $sscTally=0; $three=0; $four=0; $five=0; 
	$six=0; $seven=0; $eight=0; $nine=0; $total=0;


	
	//This query gives us the number of calls that we are doing the stats for	
	try {
		$callQuery = $db->prepare("SELECT COUNT(`smid`) FROM `silentMonitorCalls` WHERE `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$callQuery->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results = $callQuery->fetch(PDO::FETCH_NUM);
	$divBy = $results[0];


	//This query gives us the criteria that we are checking.
	try {
		$criteria1Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '1' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria1Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria1Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$one++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$one += 0.5;
		}
	}

	//This query gives us the criteria that we are checking.
	try {
		$criteria2Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '2' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria2Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria2Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$two++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$two += 0.5;
		}
	}

	//This query gives us the criteria that we are checking.
	try {
		$criteria3Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '3' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria3Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria3Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$three++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$three += 0.5;
		}
	}

//This query gives us the criteria that we are checking.
	try {
		$criteria4Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '4' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria4Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria4Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$four++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$four += 0.5;
		}
	}

//This query gives us the criteria that we are checking.
	try {
		$criteria5Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '5' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria5Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria5Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$five++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$five += 0.5;
		}
	}


//This query gives us the criteria that we are checking.
	try {
		$criteria6Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '6' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria6Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria6Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$six++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$six += 0.5;
		}
	}

//This query gives us the criteria that we are checking.
	try {
		$criteria7Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '7' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria7Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria7Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$seven++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$seven += 0.5;
		}
	}

//This query gives us the criteria that we are checking.
	try {
		$criteria8Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '8' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria8Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria8Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$eight++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$eight += 0.5;
		}
	}

//This query gives us the criteria that we are checking.
	try {
		$criteria9Query = $db->prepare("SELECT * FROM `silentMonitorCallCriteria` WHERE `criteriaIndex` = '9' AND `smid`  IN (SELECT `index` FROM `silentMonitor` WHERE submitDate >= :start AND submitDate <= :end)");
		$criteria9Query->execute(array(':start' => $start, ':end' => $end));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $criteria9Query->fetch(PDO::FETCH_ASSOC))
	{
		if($cur['rating'] == 'Yes')
		{
			$nine++;
		}
		else if($cur['rating'] == 'Partial')
		{
			$nine += 0.5;
		}
	}
	
	if($divBy != 0)
	{
		$total = round((($one + $two + $three + $four + $five + $six + $seven + $eight + $nine)/($divBy*9))*100, 0);
		$one = round(($one/$divBy)*100, 0);
		$two = round(($two/$divBy)*100, 0);
		$three = round(($three/$divBy)*100, 0);
		$four = round(($four/$divBy)*100, 0);
		$five = round(($five/$divBy)*100, 0);
		$six = round(($six/$divBy)*100, 0);
		$seven = round(($seven/$divBy)*100, 0);			
		$eight = round(($eight/$divBy)*100, 0);
		$nine = round(($nine/$divBy)*100, 0);
	}
	else
	{
		$one=100; $two=100; $sscTally=100; $three=100; $four=100; $five=100;
		$six=100; $seven=100; $eight=100; $nine=100; $total=100;
	}
	
	$results = array("one"=>$one,"two"=>$two,"three"=>$three,
					 "four"=>$four,"five"=>$five,"six"=>$six, "seven"=>$seven,"eight"=>$eight,
					 "nine"=>$nine, "total"=>$total, "ticketsThisSemester"=>$divBy); 
	echo json_encode($results);

?>
