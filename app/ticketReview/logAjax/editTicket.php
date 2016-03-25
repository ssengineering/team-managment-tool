<?php
require('../../includes/includeMeBlank.php');
$queryString = "UPDATE `ticketReview` SET ";
$queryParams = array();

// Basic Search 

if(isset($_GET['ticketNum']))
{
	$queryString .="ticketNum=:ticketNum, ";
	$queryParams[':ticketNum'] = $_GET['ticketNum'];
}
if(isset($_GET['ticketDate']))
{
	$queryString .="ticketDate=:day, "; 
	$queryParams[':day'] = $_GET['ticketDate'];
}

// Advanced Search
if(isset($_GET['requestor']))
{
	$queryString .="requestor=:requestor, ";
	$queryParams[':requestor'] = $_GET['requestor'];
}
if(isset($_GET['contact']))
{
	$queryString .="contactInfo=:contact, ";
	$queryParams[':contact'] = $_GET['contact'];
}
if(isset($_GET['serviceCat']))
{
	$queryString .="ssc=:service, ";
	$queryParams[':service'] = $_GET['serviceCat'];
}
if(isset($_GET['source']))
{
	$queryString .="ticketSource=:source, ";
	$queryParams[':source'] = $_GET['source'];
}
if(isset($_GET['priority']))
{
	$queryString .="priority=:priority, ";
	$queryParams[':priority'] = $_GET['priority'];
}
if(isset($_GET['kb']))
{
	$queryString .="kbOrSource=:kb, ";
	$queryParams[':kb'] = $_GET['kb'];
}
if(isset($_GET['workOrder']))
{
	$queryString .="workOrderNumber=:workOrder, ";
	$queryParams[':workOrder'] = $_GET['workOrder'];
}
if(isset($_GET['template']))
{
	$queryString .="templates=:template, ";
	$queryParams[':template'] = $_GET['template'];
}
if(isset($_GET['shoot']))
{
	$queryString .="troubleshooting=:shoot, ";
	$queryParams[':shoot'] = $_GET['shoot'];
}
if(isset($_GET['closure']))
{
	$queryString .="closureCodes=:closure, ";
	$queryParams[':closure'] = $_GET['closure'];
}
if(isset($_GET['professional']))
{
	$queryString .="professionalism=:professional, ";
	$queryParams[':professional'] = $_GET['professional'];
}
if(isset($_GET['comments']))
{
	$queryString .="comments=:comments ";
	$queryParams[':comments'] = $_GET['comments'];
}
if(isset($_GET['ticketEntryNum']))
{
	$queryString .="WHERE entryNum=:entry";
	$queryParams[':entry'] = $_GET['ticketEntryNum'];
}

try {
	$updateQuery = $db->prepare($queryString);
	$updateQuery->execute($queryParams);
} catch(PDOException $e) {
	exit("error in query");
}
?>
