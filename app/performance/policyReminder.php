<?php
require('../includes/includeMeSimple.php');

if (isset($_GET['reminded']))
	{
		$reminded = $_GET['reminded'];
		
	}

if(isset($_POST['policy'])){
	$employee = $_POST['employee'];
	$date = $_POST['date'];
	$reason = $_POST['reason'];

	try {
		$insertQuery = $db->prepare("INSERT INTO reportPolicyReminder (employee, date, reason, area, submitter, guid) VALUES (:employee,:day,:reason,:area,:netId,:guid)");
		$insertQuery->execute(array(':employee' => $_POST['employee'], ':day' => $_POST['date'], ':reason' => $_POST['reason'], ':area' => $area, ':netId' => $netID, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}

	//This function creates the $persons argument that will be passed into the notify function.
	$persons = getReceivers($employee, $areaGuid, "ffb6ffe7-c522-11e5-bdda-0242ac110003"); 
	//Call notify function using the object $persons created above as the third argument.
	notify("ffb6ffe7-c522-11e5-bdda-0242ac110003", "A policy reminder has been submitted for you. Please see the reminder on the Policy Reminder Log page.", $persons);
	
	echo"<script>alert('Policy Reminder Submitted');window.close();</script>";
}



?>
<html>



	<script type="text/javascript">
		window.onload=function()
			{
				$("#employee").val(<?php if(isset($reminded)){echo "\"$reminded\"";} ?>);
			}
	</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script language="JavaScript" src="../includes/templates/scripts/calendar_db.js"></script>
	<link rel="stylesheet" href="../includes/templates/styles/calendar.css">
	<style>
	#policyReminderTable{
		width: 40%;
		margin-left: 30%;
		margin-right: 30%;
	}
	</style>
<h1 style='text-align:center'>Report a Policy Reminder</h1>
<p style='text-align:center'><a href='policyReminderLog.php'>Return to Log</a></p>
<!---------------------------employee------------------------------- -->
<form name='policy' method='post'>
<table id='policyReminderTable'>
<tr>
	<td>
		<font size='3'>Employee:  </font>
	</td>
	<td>
		<select id='employee' name='employee' style='width:100%'>
		<?php
			employeeFill($area);//CHANGE for area
		?>
		</select>
	</td>
</tr>
<!--------------------------date---------------------- -->
<tr>
	<td>
		<font size='3'>Date:</font>
	</td>
	<td>
		<input type='text' name='date' size='10' value="<?php echo date('Y-m-d') ?>" onChange='isValidDate(document.postmessage.startdate)' />
		<script language="JavaScript">
			var d_today = new Date();
			d_today.setDate(d_today.getDate());
			var s_today = f_tcalGenerDate(d_today);
			new tcal ({
					    // name of the whole form
				'formname': 'policy',
					        // name for the input text field
				'controlname': 'date',
				'today' : s_today
			});
		</script>
	</td>
</tr>
<!------------------reason---------------------------- -->
<tr>
	<td>
		<font size='3'>Please describe the policy reminder:</font>
	</td>
	<td>
		<textarea name='reason' style="width: 99%; height: 164px; min-width: 300px;" ></textarea>
	</td>
</tr>
<!------------------submit--------------------------- -->
</table>
	<p style='text-align:center'><input name='policy' type='submit' value='Submit' /></p>
</form>

</html>
