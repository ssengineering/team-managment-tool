<?php

require('../includes/includeMeBlank.php');

$employee=$_GET['employee'];
$test=$_GET['test'];

	try {
		$startDateQuery = $db->prepare("SELECT assessmentsEmployeeGroupList.startDate FROM `assessmentsEmployeeGroupList` RIGHT JOIN `assessmentsGroupRequiredTests` 
										ON assessmentsEmployeeGroupList.group = assessmentsGroupRequiredTests.group WHERE 
										assessmentsEmployeeGroupList.employee = :employee AND assessmentsGroupRequiredTests.test = :test AND 
										assessmentsEmployeeGroupList.endDate = '0000-00-00' AND assessmentsGroupRequiredTests.deleted = '0'");
		$startDateQuery->execute(array(':employee' => $employee, ':test' => $test));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$row = $startDateQuery->fetch(PDO::FETCH_ASSOC);

	$testGroupStartDate = $row['startDate'];

	try {
		$assessmentsQuery = $db->prepare("SELECT * FROM `assessmentsTest` WHERE `ID` = :test AND `deleted` = '0'");
		$assessmentsQuery->execute(array(':test' => $test));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$row = $assessmentsQuery->fetch(PDO::FETCH_ASSOC);
	
	$testPassingPercentage = $row['passingPercentage'];
	$testMaxPoints = $row['points'];
	$timePeriod=$row['timePeriod'];
	$testCreation=$row['creationDate'];
	
	try {
		$resultsQuery = $db->prepare("SELECT * FROM `assessmentsResults` WHERE `employee` = :employee AND `test` = :test ORDER BY `attempt` DESC");
		$resultsQuery->execute(array(':employee' => $employee, ':test' => $test));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$row = $resultsQuery->fetch(PDO::FETCH_ASSOC);
	
	$testAttempts = $row['attempt'];
	$currentAttempt = $testAttempts + 1;
	
	echo '{ "joinGroupDate" : "'.$testGroupStartDate.'", "testPercentage":'.$testPassingPercentage.', "maxPoints":'.$testMaxPoints.', "currentAttempt":'.$currentAttempt.', "timePeriod":'.$timePeriod.', "testCreation":"'.$testCreation.'"}';
	
?>
