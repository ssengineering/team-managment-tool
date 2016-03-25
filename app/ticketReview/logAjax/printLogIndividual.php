<?php //printLog.php This prints the ticket review log
require('../../includes/includeMeBlank.php');

$queryString ="SELECT * FROM ticketReview WHERE ";
$queryParams = array();

// Basic Search 
if(isset($_GET['employee']))
{
	$queryString .= "agentID LIKE :employee ";
	$queryParams[':employee'] = '%'.$_GET['employee'].'%';
}

if(isset($_GET['startDate'])&& $_GET['startDate']!='')
{
	$start=$_GET['startDate']; 
	$queryString .="AND ticketDate >=:start"; 
	$queryParams[':start'] = $start;
}
if(isset($_GET['endDate']) && $_GET['endDate']!='')
{
	$end=$_GET['endDate'];
	$queryString .=" AND ticketDate <=:end";
	$queryParams[':end'] = $end;
}
if(isset($_GET['timeStamp']) && $_GET['timeStamp']!='')
{
	$timeStamp = $_GET['timeStamp'];
	$timeStamp = rawurldecode($timeStamp);
	$queryString .=" AND timeStamp =:timestamp";
	$queryParams[':timestamp'] = $timeStamp;
}

$queryString .= " ORDER BY ticketDate DESC";
$ticketCount = 1; 
try {
	$reviewQuery = $db->prepare($queryString);
	$reviewQuery->execute($queryParams);
} catch(PDOException $e) {
	exit("error in query");
}
while($cur = $reviewQuery->fetch(PDO::FETCH_ASSOC)) {
	echo "<h3>For: ".nameByNetId($cur['agentID'])."</h3>";
	echo "<table style='margin-left:auto; margin-right:auto;' id='result".$ticketCount."'><tr><th>Ticket#</th><th>Date</th><th>Requestor</th><th>Contact Info</th> <th>Service/Symptom Category</th><th>Ticket Source</th><th>Priority</th><th>KB/Source</th><th>Work Order#</th>";

	echo "</tr><tr id='data1'>";
	echo "<td class='cellWidth' id='ticketNumField'>".$cur['ticketNum']."</td>"; //Ticket #
	echo "<td class='cellWidth' id='ticketDateField'>".$cur['ticketDate']."</td>"; //Date
	echo "<td class='cellWidth' id='requestorField'>".$cur['requestor']."</td>"; //Requestor
	echo "<td class='cellWidth' id='contactInfoField'>".$cur['contactInfo']."</td>"; //Contact Info
	echo "<td class='cellWidth' id='serviceCatField'>".$cur['ssc']."</td>"; //Service/Symptom Category
	echo "<td class='cellWidth' id='tickeSourceField'>".$cur['ticketSource']."</td>"; //Ticket Source
	echo "<td class='cellWidth' id='ticketPriorityField'>".$cur['priority']."</td>"; //Priority
	echo "<td class='cellWidth' id='kbField'>".$cur['kbOrSource']."</td>"; //KB/Source
	echo "<td class='cellWidth' id='workOrderField'>".$cur['workOrderNumber']."</td>"; //Work Order

	echo "</tr><tr><th>Template</th><th>Trouble-shooting</th><th>Closure Codes</th><th>Professionalism</th><th colspan='5'>Comments</th>";

	echo "</tr><tr id='data2'>";
	echo "<td class='cellWidth' id='templateField'>".$cur['templates']."</td>"; //Template
	echo "<td class='cellWidth' id='ticketTroubleField'>".$cur['troubleshooting']."</td>"; //Troubleshooting
	echo "<td class='cellWidth' id='ticketClosuresField'>".$cur['closureCodes']."</td>"; //Closure Codes
	echo "<td class='cellWidth' id='ticketProField'>".$cur['professionalism']."</td>"; //Professionalism
	echo "<td id='ticketCommentsField' colspan='5'>".$cur['comments']."</td>"; //Comments

	echo "</tr></table>";
	echo "<input name='ticketEntryNum".$ticketCount."' id='ticketEntryNum".$ticketCount."' type='hidden' value ='".$cur['entryNum']."' />";
	$ticketCount++;
}
?>
