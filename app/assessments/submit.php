<?php
/*
*	Name: submit.php
*	Application: Assessments
*	Site: ops.byu.edu
*	Author: Joshua Terrasas
*
*	Description: This file is used by the Assessments app to
*	submit information to the database. It covers both POST
*	and GET calls. A standard set of parameters are submitted
*	to the Submit page form the calling page. These parameters
*	tell the Submit page who is sending the information, what
*	information is being sent, and what to do with it. This
*	page then just parses the information and makes the correct
*	database calls. The Submit page also displays success messages
*	or errors for some calls.
*/

//Standard include file for header.
require('../includes/includeme.php');

if(isset($_POST['type']))
{
	$submitType = $_POST['type'];
	
	if($submitType == 'gradeMode')
	{
		$employee = $_POST['employee'];
		$grader = $netID;
		$test = $_POST['test'];
		$date = $_POST['date'];
		$passed = $_POST['result'];
		$score = $_POST['score'];
		$attempt = $_POST['attempt'];
		$notes = $_POST['notes'];
		
		try {
			$insertQuery = $db->prepare("INSERT INTO `assessmentsResults`(`employee`, `grader`, `test`, `date`, `passed`, `score`, `attempt`, `notes`, `guid`) VALUES (:employee,:grader,:test,:date,:passed,:score,:attempt,:notes,:guid)");
			$success = $insertQuery->execute(array(':employee' => $employee, ':grader' => $grader, ':test' => $test, ':date' => $date, ':passed' => $passed, ':score' => $score, ':attempt' => $attempt, ':notes' => $notes, ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}	

		if($success)
		{
			echo "<h2>Test results submitted successfully.</h2>";
			echo "<br />";
			echo "<br />";
			echo "<a href='index.php'>Back to Assessments App</a>";
		}	
	}
	else if($submitType == 'groupsMode')
	{
		$submitTab = $_POST['tab'];
		
		if($submitTab == 'addGroup')
		{
			$groupName = $_POST['groupName'];
		
			try {
				$insertGroupQuery = $db->prepare("INSERT INTO `assessmentsGroup`(`name`, `guid`) VALUES (:group,:guid)");
				$success = $insertGroupQuery->execute(array(':group' => $groupName, ':guid' => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}	

			if($success)
			{
				echo "<h2>Group added successfully.</h2>";
				echo "<br />";
				echo "<br />";
				echo "<a href='index.php'>Back to Assessments App</a>";
			}
		}
	}
	else if($submitType == 'testMode')
	{
		$submitTab = $_POST['tab'];
		$testType=(($_POST['testType']=="Quiz")? 1:0 );
		(($area==4)? $testPeriod = 0:$testPeriod = $_POST['testTimePeriod']);
		if($submitTab == 'addTest')
		{
			$testName = $_POST['testName'];
			try {
				$insertTestQuery = $db->prepare("INSERT INTO `assessmentsTest`(`name`, `timePeriod`, `points`, `passingPercentage`, `quizFlag`, `creationDate`, `guid`) VALUES (:name,:period,:points,:passing,:type,CURDATE(),:guid)");
				$success = $insertTestQuery->execute(array(':name' => $testName, ':period' => $testPeriod, ':points' => $_POST['testPoints'], ':passing' => $_POST['testPassingPercentage'], ':type' => $testType, ':guid' => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}	

			if($success)
			{
				echo "<h2>Test added successfully.</h2>";
				echo "<br />";
				echo "<br />";
				echo "<a href='index.php'>Back to Assessments App</a>";
			}
		}
		else if($submitTab == 'editTest')
		{	
			$testEditName = $_POST['testEditName'];
			
			try {
				$updateTestQuery = $db->prepare("UPDATE `assessmentsTest` SET `name`=:name, `timePeriod`=:period, `points`=:points, `passingPercentage`=:passing, `quizFlag`=:flag WHERE `ID`=:id");
				$success = $updateTestQuery->execute(array(':name' => $testEditName, ':period' => $_POST['testEditTimePeriod'], ':points' => $_POST['testEditPoints'], ':passing' => $_POST['testEditPassingPercentage'], ':flag' => $testType, ':id' => $_POST['testID']));
			} catch(PDOException $e) {
				exit("error in query");
			}	
			
			if($success)
			{
				echo "<h2>Test edited successfully.</h2>";
				echo "<br />";
				echo "<br />";
				echo "<a href='index.php'>Back to Assessments App</a>";
			}
		}
	}
}
else if(isset($_GET['type']))
{
	$submitType = $_GET['type'];
	
	if($submitType == 'groupsMode')
	{
		$submitTab = $_GET['tab'];
		
		if($submitTab == 'groupMembers')
		{
			$submitAction = $_GET['action'];
			
			if($submitAction == 'add')
			{
				try {
					$insertTestQuery = $db->prepare("INSERT INTO `assessmentsEmployeeGroupList`(`employee`, `group`, `startDate`, `guid`) VALUES (:employee,:group,:start,:guid)");
					$success = $insertTestQuery->execute(array(':employee' => $_GET['employee'], ':group' => $_GET['group'], ':start' => $_GET['startDate'], ':guid' => newGuid()));
				} catch(PDOException $e) {
					exit("error in query");
				}	
			}
			else if($submitAction == 'remove')
			{
				try {
					$updateGroupQuery = $db->prepare("UPDATE `assessmentsEmployeeGroupList` SET `endDate`=:end WHERE `id`=:id");
					$success = $updateGroupQuery->execute(array(':end' => $_GET['endDate'], ':id' => $_GET['ID']));
				} catch(PDOException $e) {
					exit("error in query");
				}	
			}
			
			if($success)
			{
				echo "<h2>Change made successfully.</h2>";
				echo "<br />";
				echo "<br />";
				echo "<a href='index.php'>Back to Assessments App</a>";
			}
		}
		else if($submitTab == 'groupTests')
		{
			$submitAction = $_GET['action'];
			
			if($submitAction == 'add')
			{
				try {
					$insertRequiredTestsQuery = $db->prepare("INSERT INTO `assessmentsGroupRequiredTests`(`group`, `test`, `guid`) VALUES (:group,:test,:guid)");
					$insertRequiredTestsQuery->execute(array(':group' => $_GET['group'], ':test' => $_GET['test'], ':guid' => newGuid()));
				} catch(PDOException $e) {
					exit("error in query");
				}	
			}
			else if($submitAction == 'remove')
			{
				try {
					$updateRequiredTestsQuery = $db->prepare("UPDATE `assessmentsGroupRequiredTests` SET `deleted`=1 WHERE `ID`=:id");
					$updateRequiredTestsQuery->execute(array(':id' => $_GET['ID']));
				} catch(PDOException $e) {
					exit("error in query");
				}	
			}
		}
		else if($submitTab == 'addGroup')
		{
			$submitAction = $_GET['action'];
			
			if($submitAction == 'delete')
			{
				$groupID = $_GET['group'];
				try {
					$updateGroupsQuery = $db->prepare("UPDATE `assessmentsGroup` SET `deleted`='1' WHERE `ID`=:id");
					$updateGroupsQuery->execute(array(':id' => $groupID));
				} catch(PDOException $e) {
					exit("error in query");
				}	
				try {
					$updateGroupListQuery = $db->prepare("UPDATE `assessmentsEmployeeGroupList` SET `endDate`=:end WHERE `group`=:group AND `endDate`='0000-00-00' AND `deleted`='0'");
					$updateGroupListQuery->execute(array(':end' => date('Y-m-d'), ':group' => $groupID));
				} catch(PDOException $e) {
					exit("error in query");
				}	
			}
		}
	}
	else if($submitType == 'testsMode')
	{
		$submitTab = $_GET['tab'];
		
		if($submitTab == 'editTest')
		{
			$submitAction = $_GET['action'];
			
			if($submitAction == 'delete')
			{
				$testID = $_GET['test'];
				try {
					$deleteTestsQuery = $db->prepare("UPDATE `assessmentsTest` SET `deleted`='1' WHERE `ID`= :id");
					$deleteTestsQuery->execute(array(':id' => $testID));
				} catch(PDOException $e) {
					exit("error in query");
				}	
			}
		}
	}
}
	
//Standard include file for footer.
require('../includes/includeAtEnd.php');

?>
