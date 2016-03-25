<?php
	require('../includes/includeme.php');
    if(can("access", "c81d511e-6af0-4045-a53f-8e3c55ea3545")){//supervisorLog resource
?>
<script>
	window.onload = function(){
		$("#dateStart").datepicker({dateFormat: "yy-mm-dd"});
		$("#dateEnd").datepicker({dateFormat: "yy-mm-dd"});
	}

</script>

<h1>Select a Start and End date</h1>
<!-------------------------------------------------search-------------------------------------------------------------------------------------->
<form name='search' method='post' style='float:left;'>
	<font size='3'>Start Date:</font>
	<input type='text' name='dateStart' id='dateStart' size='10' value="<?php echo date('Y-m-d') ?>" >
	
	<font size='3'>End Date:</font>
	<input type='text' name='dateEnd' id='dateEnd' size='10' value="<?php echo date('Y-m-d') ?>" >
	<input type='submit' name='search' value='Search' />
</form></br></br><hr />
<!-------------------------------------------<div> results------------------------------------------------------------------------------------->
<div id='results' name='results'><font size="3">
<?php	
	if(isset($_POST['search'])){
		echo '<table>';
		echo '<tr><th>Report From</th><th>By</th><th>Info</th></tr>';
		try {
			$reportQuery = $db->prepare("SELECT * FROM `supReport` WHERE `timeSubmitted` >=:start and `timeSubmitted` <= :end");
			$reportQuery->execute(array(':start' => $_POST['dateStart'], ':end' => $_POST['dateEnd'].' 23:59:59'));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row = $reportQuery->fetch(PDO::FETCH_ASSOC)) {
			echo '<tr><td>'.$row['timeSubmitted'].'</td><td>'.$row['submittedBy'].'</td><td>'.$row['entry'].'</td></tr>';
		}
		echo '</table>';
		
		echo "<h1>Unapproved RFC's</h1>";
		echo '<table>';

		try {
			$unscheduledRFCQuery = $db->prepare("SELECT * FROM `unscheduledRFC` WHERE `startDate` >= :start and `startDate` <= :end");
			$unscheduledRFCQuery->execute(array(':start' => $_POST['dateStart'], ':end' => $_POST['dateEnd']));
		} catch(PDOException $e) {
			exit("error in query");
		}

		if($first = $unscheduledRFCQuery->fetch(PDO::FETCH_ASSOC)) {
			echo '<tr><th>Ticket/RFC #</th><th>Engineer</th><th>Start</th><th>End</th><th>Description</th><th>Impact</th></tr>';
			echo '<tr><td>'.$first['ticketNumber'].'</td><td>'.$first['engineerName'].'</td><td>'.$first['startTime'].' '.$first['startDate'].'</td><td>'.$first['endTime'].' '.$first['endDate'].'</td><td>'.$first['description'].'</td><td>'.$first['impact'].'</td></tr>';

			while($row = $unscheduledRFCQuery->fetch(PDO::FETCH_ASSOC)) {
				echo '<tr><td>'.$row['ticketNumber'].'</td><td>'.$row['engineerName'].'</td><td>'.$row['startTime'].' '.$row['startDate'].'</td><td>'.$row['endTime'].' '.$row['endDate'].'</td><td>'.$row['description'].'</td><td>'.$row['impact'].'</td></tr>';
			}
		} else {
			echo 'No unapproved RFC\'s between those dates.';
		}
		echo '</table>';

		echo "<h1>Absences</h1>";
		echo '<table>';
		try {
			$absenceQuery = $db->prepare("SELECT * FROM `reportAbsence` WHERE `timeStamp` >= :start and `timeStamp` <= :end AND `area` = :area");
			$absenceQuery->execute(array(':start' => $_POST['dateStart'], ':end' => $_POST['dateEnd'].' 23:59:59', ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($first = $absenceQuery->fetch(PDO::FETCH_ASSOC)) {
			echo '<tr><th>Employee</th><th>Date</th><th>Shift</th><th>No Show</th><th>Reason</th></tr>';
			echo '<tr><td>'.nameByNetId($first['employee']).'</td><td>'.$first['date'].'</td><td>'.$first['shiftStart'].' - '.$first['shiftEnd'].'</td><td>'.$first['noCall'].'</td><td>'.$first['reason'].'</td></tr>';

			while($row = $absenceQuery->fetch(PDO::FETCH_ASSOC)) {
				echo '<tr><td>'.nameByNetId($row['employee']).'</td><td>'.$row['date'].'</td><td>'.$row['shiftStart'].' - '.$row['shiftEnd'].'</td><td>'.$row['noCall'].'</td><td>'.$row['reason'].'</td></tr>';
			}
		} else {
			echo 'No Absences in log between those dates.';
		}
		echo '</table>';
		
		echo "<h1>Tardies</h1>";
		echo '<table>';
		try {
			$tardyQuery = $db->prepare("SELECT * FROM `reportTardy` WHERE `timeStamp` >= :start and `timeStamp` <= :end AND `area` = :area");
			$tardyQuery->execute(array(':start' => $_POST['dateStart'], ':end' => $_POST['dateEnd'].' 23:59:59', ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($first = $tardyQuery->fetch(PDO::FETCH_ASSOC)) {
			echo '<tr><th>Employee</th><th>Date</th><th>Shift</th><th>Arrived</th><th>No Show</th><th>Reason</th></tr>';
			echo '<tr><td>'.nameByNetId($first['employee']).'</td><td>'.$first['date'].'</td><td>'.$first['start'].' - '.$first['end'].'</td><td>'.$first['time'].'</td><td>'.$first['noCall'].'</td><td>'.$first['reason'].'</td></tr>';

			while($row = $tardyQuery->fetch(PDO::FETCH_ASSOC)) {
				echo '<tr><td>'.nameByNetId($row['employee']).'</td><td>'.$row['date'].'</td><td>'.$row['start'].' - '.$row['end'].'</td><td>'.$row['time'].'</td><td>'.$row['noCall'].'</td><td>'.$row['reason'].'</td></tr>';
			}
		} else {
			echo 'No Tardies between those dates.';
		}
		echo '</table>';
	}
?>
</font></div>
<!--------------------------------------------------------------------------------------------------------------------------------------->

<?php 
}else{
    echo "<h1>You are not authorized to view this page</h1>";
}
require('../includes/includeAtEnd.php'); ?>
