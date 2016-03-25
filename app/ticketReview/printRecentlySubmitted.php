<?php
require('../includes/includeMeBlank.php');
	$empNetIdentification = $_GET['empNetId'];
	$timeStamp = $_GET['timeStamp'];
	$timeStamp = rawurldecode($timeStamp);
    $tableHeaders = '<table name="submittedTicketsTable" BORDER="1" style="width:100%;"> <TR style="width:100%"><TH>Ticket#</TH><TH>Date</TH><TH>Req..</TH><TH>Contact Info</TH><TH>Service Category</TH><TH>Ticket Src</TH><TH>Prio..</TH><TH>KB Src</TH>
			         <TH>Work Odr#</TH><TH>Temp..</TH><TH>Trouble..</TH><TH>Closure Codes</TH><TH>Prof..</TH><TH colspan="4" >Comments</TH></TR>';
	$tableRows = '';
	$entireTable = '';
	//build table.
	try {
		$reviewQuery = $db->prepare("SELECT * FROM `ticketReview` WHERE  `timeStamp`=:timestamp AND `agentID`=:netId");
		$reviewQuery->execute(array(':timestamp' => $timeStamp, ':netId' => $empNetIdentification));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($ticketInfo = $reviewQuery->fetch(PDO::FETCH_ASSOC))
	{
		$tableRows=$tableRows.'<TR><TD>'.$ticketInfo['ticketNum'].'</TD><TD>'.$ticketInfo['ticketDate'].'</TD>
		 			<TD>'.$ticketInfo ['requestor'].'</TD><TD>'.$ticketInfo ['contactInfo'].'</TD><TD>'.$ticketInfo ['ssc'].'</TD>
		 			<TD>'.$ticketInfo ['ticketSource'].'</TD><TD>'.$ticketInfo['priority'].'</TD><TD>'.$ticketInfo['kbOrSource'].'</TD><TD>'.$ticketInfo['workOrderNumber'].'</TD>
		 			<TD>'.$ticketInfo['templates'].'</TD><TD>'.$ticketInfo['troubleshooting'].'</TD><TD>'.$ticketInfo['closureCodes'].'</TD><TD>'.$ticketInfo['professionalism'].'</TD><TD>'.$ticketInfo['comments'].'</TD></TR>';
	}
	echo $tableHeaders.$tableRows.'</TABLE>';
?>
