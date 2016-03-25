<?php
require('../../includes/includeMeBlank.php');
//***NOTE***: There is a customized copy of this file as a function in the "performance/printLog.php" file.  If you modify this file.  Make sure you do the same with the other one
// so that there is consistency in the system.  

$empNetID = $_GET['empNetID'];
$area = $_GET['area'];
$start = $_GET['startDate'];
$end =$_GET['endDate'];
$avgAllCategories = 0;

	$requestorTally=0; $contactInfoTally=0; $sscTally=0; $ticketSourceTally=0; $priorityTally=0; $kbTally=0; 
	$workOrderTally=0; $templatesTally=0; $troubleTally=0; $closureCodesTally=0; $professionalismTally=0;

	//Get semester Name based on start date
	$semesterName = '';
	try {
		$semesterQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE `startDate` >= :start AND `endDate` <= :end AND `area` = :area ORDER BY `scheduleSemesters`.`startDate` DESC LIMIT 1");
		$semesterQuery->execute(array(':start' => $start, ':end' => $end, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	if($cur = $semesterQuery->fetch(PDO::FETCH_ASSOC)) {
		$semesterName=$cur['name'];
	}

	try {
		$workOrderQuery = $db->prepare("SELECT COUNT(`entryNum`) FROM `ticketReview` WHERE `ticketDate` >= :start AND `ticketDate` <= :end AND `agentID` = :netId AND (`workOrderNumber`='Yes' OR `workOrderNumber`='No')");
		$workOrderQuery->execute(array(':start' => $start, ':end' => $end, ':netId' => $empNetID));
		$templateQuery = $db->prepare("SELECT COUNT(`entryNum`) FROM `ticketReview` WHERE `ticketDate` >= :start AND `ticketDate` <= :end AND `agentID` = :netId AND (`templates`='Yes' OR `templates`='No')");
		$templateQuery->execute(array(':start' => $start, ':end' => $end, ':netId' => $empNetID));
		$reviewQuery = $db->prepare("SELECT * FROM `ticketReview` WHERE `ticketDate` >= :start AND `ticketDate` <= :end AND `agentID` = :netId");
		$reviewQuery->execute(array(':start' => $start, ':end' => $end, ':netId' => $empNetID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	// WorkOrder divide by: 
	$workOrderResult   = $workOrderQuery->fetch(PDO::FETCH_NUM);
	$workOrderDivideBy = $workOrderResult[0];

	// Templates divide by:
	$templateResult    = $templateQuery->fetch(PDO::FETCH_NUM);
	$templatesDivideBy = $templateResult[0];

	$divBy = 0;

	while($cur = $reviewQuery->fetch(PDO::FETCH_ASSOC))
	{
		$divBy++;
		if($cur['requestor'] == 'Yes'){
			$requestorTally++;
		}
		if($cur['contactInfo'] == 'Yes'){
			$contactInfoTally++;
		}
		if($cur['ssc'] == 'Yes'){
			$sscTally++;
		}
		if($cur['ticketSource'] == 'Yes'){
			$ticketSourceTally++;
		}
		if($cur['priority'] == 'Yes'){
			$priorityTally++;
		}
		if($cur['kbOrSource'] == 'Yes'){
			$kbTally++;
		}
		if($cur['workOrderNumber'] == 'Yes'){  // what about NA
			$workOrderTally++;
		}
		if($cur['templates'] == 'Yes'){
			$templatesTally++;
		}
		if($cur['troubleshooting'] == 'Yes'){
			$troubleTally++;
		}
		if($cur['closureCodes'] == 'Yes'){
			$closureCodesTally++;
		}
		if($cur['professionalism'] == 'Yes'){
			$professionalismTally++;
		}
	}
	
	if($divBy != 0)
	{
		$requestorTally = round(($requestorTally/$divBy)*100, 0);
		$contactInfoTally = round(($contactInfoTally/$divBy)*100, 0);
		$sscTally = round(($sscTally/$divBy)*100, 0);
		$ticketSourceTally = round(($ticketSourceTally/$divBy)*100, 0);
		$priorityTally = round(($priorityTally/$divBy)*100, 0);
		$kbTally = round(($kbTally/$divBy)*100, 0);
		if($workOrderDivideBy !=0)
		{
			$workOrderTally = round(($workOrderTally/$workOrderDivideBy)*100, 0);
		}
		else
		{
			$workOrderTally=100;
		}
		if($templatesDivideBy!=0)
		{
			$templatesTally = round(($templatesTally/$templatesDivideBy)*100, 0);
		}
		else
		{
			$templatesTally=100;
		}		
		$troubleTally = round(($troubleTally/$divBy)*100, 0);
		$closureCodesTally = round(($closureCodesTally/$divBy)*100, 0);
		$professionalismTally = round(($professionalismTally/$divBy)*100, 0);
	}
	else
	{
		$requestorTally=100; $contactInfoTally=100; $sscTally=100; $ticketSourceTally=100; $priorityTally=100; $kbTally=100;
		$workOrderTally=100; $templatesTally=100; $troubleTally=100; $closureCodesTally=100; $professionalismTally=100;
	}
	
	//Average of all the categories:
	$avgAllCategories= $requestorTally+$contactInfoTally+$sscTally+$ticketSourceTally+$priorityTally+$kbTally+ 
						$workOrderTally+$templatesTally+$troubleTally+$closureCodesTally+$professionalismTally;
	$avgAllCategories=round(($avgAllCategories/11), 0);
	

	$results = array("requestor"=>$requestorTally,"contactInfo"=>$contactInfoTally,"ssc"=>$sscTally,"ticketSource"=>$ticketSourceTally,
					 "priority"=>$priorityTally,"kbOrSource"=>$kbTally,"workOrderNumber"=>$workOrderTally, "templates"=>$templatesTally,"troubleshooting"=>$troubleTally,
					 "closureCodes"=>$closureCodesTally, "professionalism"=>$professionalismTally, "ticketsThisSemester"=>$divBy, "semesterName"=>$semesterName, "avgAllCategories"=>$avgAllCategories); 
	echo json_encode($results);

?>
