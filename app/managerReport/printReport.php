<?php

/*	Name: printReport.php
*	Application: Manager Report
*
*	Description: This php file takes the filter parameters from the index.php page and returns results from the DB.
*/

	//Include file to include common functions used throughout the site
	require('../includes/includeMeBlank.php');

	//Declare variables
	global $area;
	$startDateString = $_POST["startDate"];
	$startDate = date("Y-m-d", strtotime($startDateString));
	
	$endDateString = $_POST["endDate"];
	$endDateString .= " +1 day";
	$endDate = date("Y-m-d", strtotime($endDateString));
	
	$categoriesList = $_POST["categoriesList"];
	$employeeList = $_POST["employeeList"];
	$checked = $_POST["checked"];
	
	
	/*Query - this will need to be updated when we start letting the user filter by start and end date, 
	which employees are included, and whether the entry was submitted to the director or not*/
    $query = "	SELECT `managerReports`.`ID`, `managerReports`.`netID`, `managerReports`.`submitDate`, `managerReports`.`comments`, `managerReports`.`checked`, `managerReportCategory`.`category`, `employee`.`firstName`, `employee`.`lastName`
    			FROM `managerReports`
    			
    			INNER JOIN `managerReportCategory`
				ON `managerReports`.`category`=`managerReportCategory`.`id`
				
				INNER JOIN `employee`
				ON `managerReports`.`netID` = `employee`.`netID`

				WHERE ( ";
	$params = array();

	//Add the categories to the query
	$i = 0;
	foreach($categoriesList as $category) {
		
		//If it's the first time through we don't want to add the OR
		if($i != 0) {
			$query .= "OR ";
		}//if
		
		$query .= "`managerReports`.`category` = :category".$i." ";
		$params[':category'.$i] = $category;
		
		$i++;
	}//foreach				
					
	$query .= ") AND (";
	
	//Add the employees to the query
	$i = 0;
	foreach($employeeList as $employeeNetID){
	
		//If it's the first time through we don't want to add the OR
		if($i != 0) {
			$query .= " OR ";
		}//if
		
		$query .= "`managerReports`.`netID` = :id".$i." ";
		$params[':id'.$i] = $employeeNetID;
		
		$i++;
	}//foreach
	
	$query .= ") ";
	
	$query .= "
				AND `managerReports`.`deleted` = '0' 
				AND `managerReports`.`area` = :area
				AND `managerReports`.`submitDate` BETWEEN :start AND :end";
	$params[':area'] = $area;
	$params[':start'] = $startDate;
	$params[':end'] = $endDate;

	//Add whether the comment was check or not ot the query
	if($checked != 2){
		$query .= " AND `managerReports`.`checked` = :checked";
		$params[':checked'] = $checked;
	}//if
	
				
	$query .= " ORDER BY `managerReportCategory`.`category` ASC, `managerReports`.`netID` ASC";
	try {
		$reportsQuery = $db->prepare($query);
		$reportsQuery->execute($params);
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	//Create an array of the results
	$reports = array();
    while($cur = $reportsQuery->fetch(PDO::FETCH_ASSOC))
    {
	    $reports[] = $cur;
    }//while

    
	//Return results
	echo json_encode($reports);

?>
