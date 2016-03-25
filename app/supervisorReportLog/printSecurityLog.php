<?php //printLog.php This will print the Supervisor Report based on the given data
require('../includes/includeMeBlank.php');

$start = $_GET['start'];
$end = $_GET['end'];
$admin = can("update", "7db1df8d-0a15-46ed-9c83-701393e9596c"); //reportInstructions resource
$securityProblems ='';
$shiftProblems ='';
$misc ='';
$employee = '';
$params = array();

if(isset($_GET['employee'])  && $_GET['employee'] != ''){
	$employee = " AND submitter = :submitter ";
	$params[':submitter'] = $_GET['employee'];
}
if(isset($_GET['securityProblems'])  && $_GET['securityProblems'] != ''){
	$securityProblems = "AND securityProblems LIKE :problems ";
	$params[':problems'] = "%".$_GET['securityProblems']."%";
}
if(isset($_GET['shiftProblem'])  && $_GET['shiftProblem'] != ''){
	$shiftProblems = "AND shiftProblems LIKE :shift ";
	$params[':shift'] = "%".$_GET['shiftProblem']."%";
}
if(isset($_GET['misc'])  && $_GET['misc'] != ''){
	$misc = "AND misc LIKE :misc ";
	$params[':misc'] = "%".$_GET['misc']."%";
}


function printReport($employee,$start,$end,$securityProblems,$shiftProblems,$misc,$params){
	global $area, $admin, $db;
	$default = ' 1=1 ';
	$params[':area']  = $area;
	$params[':start'] = $start;
	$params[':end']   = $end;
	try {
		$securityDeskQuery = $db->prepare("SELECT * FROM `supervisorReportSecurityDesk` WHERE `area` = :area AND `date` >= :start AND `date` <= :end AND (".$default.$employee.$securityProblems.$shiftProblems.$misc.")");
		$securityDeskQuery->execute($params);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($first = $securityDeskQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<table>
				<tr>
				<th>Employee</th><th>Date</th><th>Shift</th><th>securityProblems</th><th>Shift Problems</th>
				<th>Misc</th><th></th></tr>";
		echo "<tr>";
		echo "<td>".nameByNetId($first['submitter'])."</td>";
		echo "<td>".$first['date']."</td>";
		echo "<td>".$first['startTime']." - ".$first['endTime']."</td>";
		echo "<td>".$first['securityProblems']."</td>";
		echo "<td>".$first['shiftProblems']."</td>";
		echo "<td>".$first['misc']."</td><td>";
		if($admin){
			echo "<input type='button' value='Edit' onclick=editReport('".$first['ID']."') />";
		}
		echo "</td></tr>";

		while($cur = $securityDeskQuery->fetch(PDO::FETCH_ASSOC)){
			echo "<tr>";
			echo "<td>".nameByNetId($cur['submitter'])."</td>";
			echo "<td>".$cur['date']."</td>";
			echo "<td>".$cur['startTime']." - ".$cur['endTime']."</td>";
			echo "<td>".$cur['securityProblems']."</td>";
			echo "<td>".$cur['shiftProblems']."</td>";
			echo "<td>".$cur['misc']."</td><td>";
			if($admin){
				echo "<input type='button' value='Edit' onclick=editReport('".$cur['ID']."') />";
			}
			echo "</td></tr>";
		}
		echo "</table>";
	} else {
		echo "No reports during this period";
	}
}


echo "<h2>Reports</h2>";
printReport($employee,$start,$end,$securityProblems,$shiftProblems,$misc,$params);
?>
