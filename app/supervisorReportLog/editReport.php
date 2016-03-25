<?php //editReport.php This is the edit page for reports.
require('../includes/includeMeSimple.php');

$id = $_GET['id'];
try {
	$reportQuery = $db->prepare("SELECT * FROM supervisorReportSD WHERE ID = :id");
	$reportQuery->execute(array(':id' => $id));
} catch(PDOException $e) {
	exit("error in query");
}
$report = $reportQuery->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){
	//gather the data from the 4 text areas, time and date
	$first = $_POST['first'];
	$second = $_POST['second'];
	$third = $_POST['third'];
	$fourth = $_POST['fourth'];
	$start = $_POST['start'];
	$end = $_POST['end'];
	$reportDate = $_POST['reportDate'];
	//insert them into the database.
	try {
		$updateQuery = $db->prepare("UPDATE supervisorReportSD SET date=:reportDate,startTime=:start,endTime=:end,outages=:first,problems=:second,misc=:third,supTasks=:fourth WHERE ID=:id");
		$updateQuery->execute(array(':reportDate' => $reportDate, ':start' => $start, ':end' => $end, ':first' => $first, ':second' => $second, ':third' => $third, ':fourth' => $fourth, ':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
	?><script>window.close();</script><?php
}

?>
<script language="JavaScript" src="/includes/libs/tcal.js"></script>
<link rel="stylesheet" type="text/css" href="/includes/libs/tcal.css" />
<script type='text/javascript' >
window.onload = function() {
	
	$('#start').timeEntry({useMouseWheel: true});
	$('#end').timeEntry({useMouseWheel: true});
}
</script>
<form id='reportData' method='post'>
<div id='headInfo'>
<h1>Edit Supervisor Report Form</h1>
<table>
	<tr>
	<th>NAME: <?php echo nameByNetId($report['submitter']); ?></th><th colspan='2'>EMAIL: <?php echo getEmployeeEmailByNetId($report['submitter']); ?></th><th>Finished Editing?</th>
	</tr><tr>
	<td>DATE: <input type='text' class='tcal' size='10' id='reportDate' name='reportDate' value="<?php echo$report['date']; ?>" /></td>
	<td>Start Time: <input type="text" name="start" id='start' maxlength=5 size=8 value="<?php echo date('h:iA',strtotime($report['startTime'])); ?>"/></td>
	<td>End Time: <input type="text" name="end" id='end' maxlength=5 size=8 value="<?php echo date('h:iA',strtotime($report['endTime'])); ?>"/></td>
	<td><input type='submit' name='submit' id='submit' value="Submit Edit" /></td>
	</tr>
</table>
</div>
<br/>
<div id='textFields'>
<table>
	<tr>
	<th>PRODUCT/SERVICE OUTAGES EXPERIENCED AND MESSAGE RELAYS RECEIVED</th>
	</tr><tr>
	<td><textarea id='first' name='first' cols='90' rows='4'><?php echo $report['outages']; ?></textarea></td>
	</tr><tr>
	<th>SHIFT PROBLEMS</th>
	</tr><tr>
	<td><textarea id='second' name='second' cols='90' rows='4'><?php echo $report['problems']; ?> </textarea></td>
	</tr><tr>
	<th>MISCELLANEOUS INFORMATION</th>
	</tr><tr>
	<td><textarea id='third' name='third' cols='90' rows='4'><?php echo $report['misc']; ?> </textarea></td>
	</tr>
</table>
</div>
</form>
<?php
require('../includes/includeAtEnd.php');
?>
