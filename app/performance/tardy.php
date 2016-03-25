<?php
require('../includes/includeMeSimple.php');

$message='';
if(isset($_POST['tardy'])){
	if($_POST['reason'] == '' || $_POST['employee'] == ''){
			echo "<div align='center'><font color='red' size='14px'>One or More fields left Blank</font><br/>Please verify all data is correct before submiting</div>";
			$message = $_POST['reason'];
		} else {
		$employee = $_POST['employee'];
		$date = $_POST['date'];
		$reason = $_POST['reason'];
		$noCall = $_POST['noCall'];
		$start= date("H:i",strtotime($_POST['start']));
		$end = date("H:i",strtotime($_POST['end']));
		$time= date("H:i",strtotime($_POST['arrived']));
		$break = $_POST['break'];
		try {
			$insertQuery = $db->prepare("INSERT INTO reportTardy (employee, date, start, end, reason, time, noCall, break, submitter,area,guid) VALUES (:employee,:day,:start,:end,:reason,:time,:call,:break,:netId,:area,:guid)");
			$insertQuery->execute(array(':employee' => $_POST['employee'], ':day' => $_POST['date'], ':start' => $start, ':end' => $end, ':reason' => $_POST['reason'], ':time' => $time, ':call' => $_POST['noCall'], ':break' => $_POST['break'], ':netId' => $netID, ':area' => $area, ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$flagCheck = date("Y-m-d",strtotime(date('m').'/01/'.date('Y').' 00:00:00'));
		try {
			$tardyQuery = $db->prepare("SELECT COUNT(ID) FROM reportTardy WHERE employee = :employee AND date > :day");
			$tardyQuery->execute(array(':employee' => $_POST['employee'], ':day' => $flagCheck));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$results = $tardyQuery->fetch(PDO::FETCH_NUM);
		$flags = $results[0];
		$to_time= strtotime($time);
		$from_time=strtotime($start);
		$minsLate = round(abs($to_time - $from_time) / 60,2)." minute(s)";
		if($flags >= 3)
		{
			$flagWarning = true;
		}
		else
		{
			$flagWarning = false;
		}

		//Creates the $persons object to pass into the notify function.
		$persons = getReceivers($employee, $areaGuid, "ffb6ffe7-c522-11e5-bdda-0242ac110003"); 	
		//Call notify function using the object $persons created above as the third argument.
		notify("ffb6ffe7-c522-11e5-bdda-0242ac110003","A tardy report has been submitted for you. Please view your report on the Tardy Log page.", $persons);
		
		echo"<script>alert('Tardy Submitted');window.close();</script>";
	}
}

?>
<script type="text/javascript">
window.onload = function() {
	
	$("#date").datepicker({dateFormat: "yy-mm-dd"});
	$('#start').timeEntry({useMouseWheel: true,
		timeSteps: [1,1,1]});
	$('#end').timeEntry({useMouseWheel: true,
		timeSteps: [1,1,1]});
	$('#arrived').timeEntry({useMouseWheel: true,
		timeSteps: [1,1,1]});
}
</script>
<h1>Report a Tardy</h1>
<a href='tardyLog.php'>Return to Log</a>
</br><!---------------------------employee--------------------------------->
<form name='tardy' method='post'>
	<font size='3'>Employee:  </font>
	<select name='employee'>
	<?php
		 if(isset($_GET['employee'])){
	        employeeFillSelected($_GET['employee'],$area);
        }else{
		    employeeFill($area);//CHANGE for area
	    }
	?>
	</select>

</br></br><!------------------------date------------------------------------->

	<font size='3'>Date:</font>
	<input type='text' name='date' id='date' size='10' value="<?php echo date('Y-m-d') ?>"  />
	
</br></br><!---------------------------shift--------------------------------->
	<?php if(isset($_GET['startTime'])){
	   $shiftTime = date('h:iA',strtotime(hourToMilitary($_GET['startTime'])));
		 $shiftEnd = date('h:iA', strtotime(hourToMilitary(shiftEnd($_GET['employee'],date('Y-m-d')))));
	}else{ 
		$shiftTime = date('h:00A');
		$shiftEnd = date('h:00A');
	}?>
	<font size='3'>Shift Start Time:</font>
	<input type="text" id="start" name="start" size="10" value="<?php echo $shiftTime; ?>">
	</br></br><font size='3'>Shift End Time: </font>
	<input type="text" id="end" name="end" size="10" value="<?php echo $shiftEnd; ?>" />
</br></br><!--------------------------reason--------------------------------->

	<font size='3'>Reason for Tardy:</font>	
	<textarea name='reason' rows='4' columns='25' style='vertical-align:middle' ><?php echo $message; ?></textarea>
	
</br></br><!--------------------------timeArrived--------------------------->
	<font size='3'>Time Arrived: </font>
	<input type="text" id="arrived" name="arrived" size="10" value="<?php echo date('h:iA'); ?>" />
</br></br><!--------------------------nocall-------------------------------->
<?php if($area == 2){ ?>
	<font size='3'>No Call: </font>
<?php } else { ?>
	<font size='3'>No Show: </font>
<?php } ?>
	<select name='noCall'>
		<option value='no'>No</option>
		<option value='yes'>Yes</option>
	</select>

<br/><br/>	
	<font size='3'>Break: </font>
	<select name='break'>
		<option value='no' selected>No</option>
		<option value='yes'>Yes</option>
	</select>
<!------------------submit-----------------------------></br></br>

	<input name='tardy' type='submit' value='Submit' /><!--CHANGE: this will need to be updated to call a .php file that will update the database -->
</form>
<?php require('../includes/includeAtEnd.php'); ?>
