<?php

function sendEmail($employeeNetID,$timeStamp, $supervisor)
{
	global $area;
	global $env;
	$server = '';
	$to = '';
	

	if ($env==0){
		$server = getenv("DEV_URL");
	}
	else if($env==1){
		$server = getenv("STAGE_URL");
	}
	else if($env==2){
		$server = getenv("PROD_URL");
	}
	
	$timeStampDate = explode(" ",$timeStamp);  // $timeStampDate[0] = yyyy/mm/dd // $timeStampDate[1]  hh:mm:ss 
	$timeStampTime = explode(":", $timeStampDate[1]);
	$formattedTimeStamp = $timeStampDate[0].'-'.$timeStampTime[0].'-'.$timeStampTime[1].'-'.$timeStampTime[2];
	
	$subject = 'Ticket Reviews were submitted for you';
	$emailBody = 'Dear '.nameByNetId($employeeNetID).', <br /><br />';
	$emailBody.= 'Ticket Reviews were submitted for you by '.nameByNetId($supervisor).'.  Please view the information in the link below.<br /> <br />';
	$emailBody.='<a href=https://'.$server.'/ticketReview/individualTicketReview.php?employee='.$employeeNetID.'&timeSubmitted='.urlencode($formattedTimeStamp).'>Click here to see your ticket reviews</a><br /><br />Or copy this link in the address bar of your browser.<br /> 
	https://'.$server.'/ticketReview/individualTicketReview.php?employee='.$employeeNetID.'&timeSubmitted='.urlencode($formattedTimeStamp).'<br /><br />Thanks!';
	
	if($env == 2)
	{
		$to=getEmployeeEmailByNetId($employeeNetID);
	}
	else
	{
		$to = getenv("DEV_EMAIL_ADDRESS");
	}
	
	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.nameByNetId($supervisor).' <'.getEmployeeEmailByNetId($supervisor).'>' . "\r\n";
	$headers .= 'Return-Path: '.getEmployeeEmailByNetId($supervisor). "\r\n";

	if(mail($to,$subject,$emailBody,$headers))
	{
		return true;	
	}
	else
	{
		return false;
	}
}

function individualStats($empNetID, $empArea,$start,$end)
{
	global $db;
	$requestorTally=0; $contactInfoTally=0; $sscTally=0; $ticketSourceTally=0; $priorityTally=0; $kbTally=0; 
	$workOrderTally=0; $templatesTally=0; $troubleTally=0; $closureCodesTally=0; $professionalismTally=0;

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
	
	$results = array("requestor"=>$requestorTally,"contactInfo"=>$contactInfoTally,"ssc"=>$sscTally,"ticketSource"=>$ticketSourceTally,
					 "priority"=>$priorityTally,"kbOrSource"=>$kbTally,"workOrderNumber"=>$workOrderTally, "templates"=>$templatesTally,"troubleshooting"=>$troubleTally,
					 "closureCodes"=>$closureCodesTally, "professionalism"=>$professionalismTally, "ticketsThisSemester"=>$divBy);
	
	return $results;

}
?>
