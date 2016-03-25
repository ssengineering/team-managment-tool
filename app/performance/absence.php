<?php
require('../includes/includeme.php');
if(isset($_POST['absence'])){	
	if($_POST['employee']==''||$_POST['date']=='' || $_POST['reason'] == '')
		echo"<font color='red' size='3'>Oops, You missed something.</font>";
	else{
		$flagWarning = false;
		$employee = $_POST['employee'];
		$reason = $_POST['reason'];
		$noCall = $_POST['noCall'];
		$date = $_POST['date'];
		$start = date("H:i",strtotime($_POST['start']));
		$end = date("H:i",strtotime($_POST['end']));
		
		try {
			$insertQuery = $db->prepare("INSERT INTO reportAbsence (employee, date, shiftStart, shiftEnd, reason, noCall,submitter,area,guid) VALUES (:employee,:day,:start,:end,:reason,:call,:netId,:area,:guid)");
			$insertQuery->execute(array(':employee' => $_POST['employee'], ':day' => $_POST['date'], ':start' => $start, ':end' => $end, ':reason' => $_POST['reason'], ':call' => $_POST['noCall'], ':netId' => $netID, ':area' => $area, ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$flagCheck = date("Y-m-d",strtotime(date('m').'/01/'.date('Y').' 00:00:00'));
		try {
			$countQuery = $db->prepare("SELECT COUNT(ID) FROM reportAbsence WHERE employee = :employee AND date > :day");
			$countQuery->execute(array(':employee' => $_POST['employee'], ':day' => $flagCheck));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $countQuery->fetch(PDO::FETCH_NUM);
		$flag = $result[0];
		if($flag >= 3)
		{
			$flagWarning = true;
		}

		//This function will create a $persons object which we will pass into the notify function.
		$persons = getReceivers($employee, $areaGuid, "ffb6ffe7-c522-11e5-bdda-0242ac110003");
		//Call notify function using the object $persons created above as the third argument.
		notify("ffb6ffe7-c522-11e5-bdda-0242ac110003", "An absence report has been submitted for you. Please see the report on the Absence Log page.", $persons);
		echo "<script>alert('Absence Submitted');window.close();</script>";
	}
}


?>
<script type="text/javascript">
window.onload = function() {
	
	$('#start').timeEntry({useMouseWheel: true,
		timeSteps: [1,30,1]});
	$('#end').timeEntry({useMouseWheel: true,
		timeSteps: [1,30,1]});
	$('#date').datepicker({dateFormat: "yy-mm-dd"});
}
</script>
<h1>Report an Absence</h1>
<a href='absenceLog.php'>Return to Log</a>
<form name='absence' method='post'>
</br><!---------------------------employee--------------------------------->
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
	<!--------------------------date------------------------></br></br><!------------------------date------------------------------------->
	<font size='3'>Date:</font>
	<input type='text' name='date' id='date' size='10' value="<?php echo date('Y-m-d') ?>" />
	</br></br><!---------------------------shift--------------------------------->
	<?php if(isset($_GET['startTime'])){
		$shiftTime = date('h:iA',strtotime(hourtoMilitary($_GET['startTime'])));
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
	<font size='3'>Reason for Absence:</font>	
	<textarea name='reason' rows='4' columns='25' style='vertical-align:middle' ></textarea>
	</br></br><!--------------------------nocall-------------------------------->
<?php if($area == 2){ ?>
	<font size='3'>No Call: </font>
<?php } else { ?>
	<font size='3'>No Show: </font>
<?php } ?>
	<select name='noCall'>
		<option value='No'>No</option>
		<option value='Yes'>Yes</option>
	</select>
	</br></br><!--------------------------submit-------------------------------->
	<input name='absence' type='submit' value='Submit' />
</form>

<?php require('../includes/includeAtEnd.php'); ?>
