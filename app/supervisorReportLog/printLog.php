<?php //printLog.php This will print the Supervisor Report based on the given data
require('../includes/includeMeBlank.php');

$start = $_GET['start'];
$end = $_GET['end'];
$admin = can("update", "7db1df8d-0a15-46ed-9c83-701393e9596c"); //reportInstructions resource
$outages ='';
$problems ='';
$misc ='';
$employee = '';
$params = array();

if(isset($_GET['employee'])  && $_GET['employee'] != ''){
	$employee = " AND submitter = :submitter ";
	$params[':submitter'] = $_GET['employee'];
}
if(isset($_GET['outage'])  && $_GET['outage'] != ''){
	$outages = "AND outages LIKE :outages ";
	$params[':outages'] = "%".$_GET['outage']."%";
}
if(isset($_GET['problem'])  && $_GET['problem'] != ''){
	$problems = "AND problems LIKE :problems ";
	$params[':problems'] = "%".$_GET['problem']."%";
}
if(isset($_GET['misc'])  && $_GET['misc'] != ''){
	$misc = "AND misc LIKE :misc ";
	$params[':misc'] = "%".$_GET['misc']."%";
}

function printReport($employee,$outages,$problems,$misc,$params){
	global $area, $admin, $db;
	$default = ' 1=1 ';
	$params[':area'] = $area;
	try {
		$reportQuery = $db->prepare("SELECT * FROM supervisorReportSD WHERE area = :area AND date >= :start AND date <= :end AND (".$default.$employee.$outages.$problems.$misc." ) ");
		$reportQuery->execute($params);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($first = $reportQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<table>
				<tr>
				<th>Employee</th><th>Date</th><th>Shift</th><th>Outages</th><th>Shift Problems</th>
				<th>Misc</th><th></th></tr>";
		echo "<tr>";
		echo "<td>".nameByNetId($first['submitter'])."</td>";
		echo "<td>".$first['date']."</td>";
		echo "<td>".$first['startTime']." - ".$first['endTime']."</td>";
		echo "<td>".$first['outages']."</td>";
		echo "<td>".$first['problems']."</td>";
		echo "<td>".$first['misc']."</td><td>";
		if($admin){
			echo "<input type='button' value='Edit' onclick=editReport('".$first['ID']."') />";
		}
		echo "</td></tr>";

		while($cur = $reportQuery->fetch(PDO::FETCH_ASSOC)){
			echo "<tr>";
			echo "<td>".nameByNetId($cur['submitter'])."</td>";
			echo "<td>".$cur['date']."</td>";
			echo "<td>".$cur['startTime']." - ".$cur['endTime']."</td>";
			echo "<td>".$cur['outages']."</td>";
			echo "<td>".$cur['problems']."</td>";
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

function getTardyLog($params) {
	global $admin, $area, $db;

	$newParams = array(':area' => $area, ':start' => $params[':start'], ':end' => $params[':end']);
	try {
		$tardyQuery = $db->prepare("SELECT * FROM reportTardy WHERE date >= :start AND date <= :end AND area = :area");
		$tardyQuery->execute($newParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($first = $tardyQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<table>		
				<tr>
					<th>Employee</th>
					<th>Date</th>
					<th>Shift</th>
					<th>Time Arrived</th>
					<th>Mins. Late</th>
					<th>Reason</th>";
		if($area == 2){
			echo 	"<th>No Call</th>";
		} else {
			echo "<th>No Show</th>";
		}
		echo "<th>Submitted By</th>";
		if($admin){		
		echo "<th></th>
			<th></th>";
		}
		echo "</tr>";

		$to_time=strtotime($first['time']);
		$from_time=strtotime($first['start']);
		$minsLate =  round(abs($to_time - $from_time) / 60,2)." minute(s)";				
		echo "<tr>";
		echo "<td>".nameByNetId($first['employee'])."</td>";
		echo "<td>".$first['date']."</td>";
		echo '<td>'.date("g:i A",strtotime($first['start'])).' - '.date("g:i A",strtotime($first['end'])).'</td>';
		echo "<td>".date("g:i A",strtotime($first['time']))."</td>";
		echo "<td>".$minsLate."</td>";
		echo "<td>".$first['reason']."</td>";
		echo "<td>".$first['noCall']."</td>";
		echo "<td>".nameByNetId($first['submitter'])."</td>";
		if($admin){
			echo "<td><input type='button' value='Edit' onclick='editLog(\"".$first['ID']."\",\"tardy\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"".$first['ID']."\",\"Tardy\")' /></td>";
		}
		echo "</tr>";

		while($current = $tardyQuery->fetch(PDO::FETCH_ASSOC)){
				$to_time=strtotime($current['time']);
				$from_time=strtotime($current['start']);
				$minsLate =  round(abs($to_time - $from_time) / 60,2)." minute(s)";				
				echo "<tr>";
				echo "<td>".nameByNetId($current['employee'])."</td>";
				echo "<td>".$current['date']."</td>";
				echo '<td>'.date("g:i A",strtotime($current['start'])).' - '.date("g:i A",strtotime($current['end'])).'</td>';
				echo "<td>".date("g:i A",strtotime($current['time']))."</td>";
				echo "<td>".$minsLate."</td>";
				echo "<td>".$current['reason']."</td>";
				echo "<td>".$current['noCall']."</td>";
				echo "<td>".nameByNetId($current['submitter'])."</td>";
				if($admin){
					echo "<td><input type='button' value='Edit' onclick='editLog(\"".$current['ID']."\",\"tardy\")' /></td>";
					echo "<td><input type='button' value='Delete' onclick='deleteLog(\"".$current['ID']."\",\"Tardy\")' /></td>";
				}
				echo "</tr>";
		}		
		echo "</table>";
	} else {
		echo "0 Tardies during this time period";
	}
}

function getAbsenceLog($params){
	global $admin, $db, $area;

	$newParams = array(':area' => $area, ':start' => $params[':start'], ':end' => $params[':end']);
	try {
		$absenceQuery = $db->prepare("SELECT * FROM reportAbsence WHERE date >= :start AND date <= :end AND area = :area");
		$absenceQuery->execute($newParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($first = $absenceQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<table>		
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Shift</th>
			<th>Reason</th>";
			if($area == 2){
				echo 	"<th>No Call</th>";
			} else {
				echo "<th>No Show</th>";
			}
			echo "<th>Submitted By</th>";
			if($admin){		
			echo "<th></th>
				<th></th>";
			}
		echo "</tr>";

		echo "<tr>";
		echo "<td>".nameByNetId($first['employee'])."</td>";
		echo "<td>".$first['date']."</td>";
		echo '<td>'.date("g:i A",strtotime($first['shiftStart'])).' - '.date("g:i A",strtotime($first['shiftEnd'])).'</td>';
		echo "<td>".$first['reason']."</td>";
		echo "<td>".$first['noCall']."</td>";
		echo "<td>".nameByNetId($first['submitter'])."</td>";
		if($admin){
			echo "<td><input type='button' value='Edit' onclick='editLog(\"".$first['ID']."\",\"absence\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"".$first['ID']."\",\"Absence\")' /></td>";
		}
		echo "</tr>";

		while($current = $absenceQuery->fetch(PDO::FETCH_ASSOC)){
				echo "<tr>";
				echo "<td>".nameByNetId($current['employee'])."</td>";
				echo "<td>".$current['date']."</td>";
				echo '<td>'.date("g:i A",strtotime($current['shiftStart'])).' - '.date("g:i A",strtotime($current['shiftEnd'])).'</td>';
				echo "<td>".$current['reason']."</td>";
				echo "<td>".$current['noCall']."</td>";
				echo "<td>".nameByNetId($current['submitter'])."</td>";
				if($admin){
					echo "<td><input type='button' value='Edit' onclick='editLog(\"".$current['ID']."\",\"absence\")' /></td>";
					echo "<td><input type='button' value='Delete' onclick='deleteLog(\"".$current['ID']."\",\"Absence\")' /></td>";
				}
				echo "</tr>";
		}		
		echo "</table>";
	} else {
		echo "0 Absences during this time period";
	}
}
function getCommendables($params) {
	global $admin, $area, $db;

	$newParams = array(':area' => $area, ':start' => $params[':start'], ':end' => $params[':end']);
	try {
		$commendableQuery = $db->prepare("SELECT * FROM reportCommendable WHERE area=:area AND date >= :start AND date <= :end");
		$commendableQuery->execute($newParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	if($first = $commendableQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<table>		
				<tr>
					<th>Employee</th>
					<th>Date</th>
					<th>Submitted By</th>
					<th>Reason</th>
					<th>Public</th>";
				if($admin){	
					echo "<th></th>
					<th></th>";
				}
				echo "</tr>";

		echo "<tr>";
		echo "<td>".nameByNetId($first['employee'])."</td>";
		echo "<td>".$first['date']."</td>";
		echo "<td>".nameByNetId($first['submitter'])."</td>";
		echo "<td>".$first['reason']."</td>";
		echo "<td>";
			if($first['public'] == 1){ 
				echo "Yes";
			} else { 
				echo "No";
			}
		echo "</td>";	
		if($admin){
			echo "<td><input type='button' value='Edit' onclick='editLog(\"".$first['ID']."\",\"commendable\")' /></td>";
			echo "<td><input type='button' value='Delete' onclick='deleteLog(\"".$first['ID']."\",\"Commendable\")' /></td>";
		}
		echo "</tr>";

		while($current = $commendableQuery->fetch(PDO::FETCH_ASSOC)) {
				echo "<tr>";
				echo "<td>".nameByNetId($current['employee'])."</td>";
				echo "<td>".$current['date']."</td>";
				echo "<td>".nameByNetId($current['submitter'])."</td>";
				echo "<td>".$current['reason']."</td>";
				echo "<td>";
					if($current['public'] == 1){ 
						echo "Yes";
					} else { 
						echo "No";
					}
				echo "</td>";	
				if($admin){
					echo "<td><input type='button' value='Edit' onclick='editLog(\"".$current['ID']."\",\"commendable\")' /></td>";
					echo "<td><input type='button' value='Delete' onclick='deleteLog(\"".$current['ID']."\",\"Commendable\")' /></td>";
				}
			echo "</tr>";
		}	
		echo "</table>";
	} else {
		echo "0 Commendable Performances during this time period";
	}
}

$params[':start'] = $start;
$params[':end'] = $end;
echo "<h2>Reports</h2>";
printReport($employee,$outages,$problems,$misc,$params);
echo "<h2>Absences</h2>";
getAbsenceLog($params);
echo "<h2>Tardies</h2>";
getTardyLog($params);
echo "<h2>Commendable Performances</h2>";
getCommendables($params);
?>
