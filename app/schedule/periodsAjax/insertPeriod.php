<?php 
//deleteTeam.php
require('../../includes/includeMeBlank.php');
if(can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")){//schedule resource
	try {
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE area = :area AND `endDate` >= DATE_SUB(NOW(), INTERVAL 1.5 YEAR) ORDER BY endDate DESC");
		$semestersQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	if($semesters= $semestersQuery->fetch(PDO::FETCH_ASSOC)) {
    	$newStart = date('d',strtotime($semesters['endDate']))+1;
    	$tomorrow = date('Y-m-',strtotime($semesters['endDate'])).$newStart;
		try {
			$insertQuery = $db->prepare("INSERT INTO scheduleSemesters (semester,name,startDate,endDate,area,guid) VALUES ('','',:tomorrow,:tomorrow1,:area,:guid)");
			$insertQuery->execute(array(
				':tomorrow'  => $tomorrow,
				':tomorrow1' => $tomorrow,
				':area'      => $area,
				':guid'      => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
	} else {
    	$today = date("Y-m-d");
		try {
			$insertQuery = $db->prepare("INSERT INTO scheduleSemesters (semester,name,startDate,endDate,area,guid) VALUES ('','',:today,:today1,:area,:guid)");
			$insertQuery->execute(array(
				':today'  => $today,
				':today1' => $today,
				':area'   => $area,
				':guid'   => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
