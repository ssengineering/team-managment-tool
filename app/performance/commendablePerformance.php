<?php
require('../includes/includeMeSimple.php');

if (isset($_GET['commended']))
	{
		$commended = $_GET['commended'];
	}

if(isset($_POST['commend'])){
	$employee = $_POST['employee'];
	$date = $_POST['date'];
	$reason = $_POST['reason'];
	$public = $_POST['public'];
	
	try {
		$insertQuery = $db->prepare("INSERT INTO reportCommendable (employee, date, reason, area, submitter, public, guid) VALUES (:employee,:day,:reason,:area,:netId,:public,:guid)");
		$insertQuery->execute(array(':employee' => $_POST['employee'], ':day' => $_POST['date'], ':reason' => $_POST['reason'], ':area' => $area, ':netId' => $netID, ':public' => $_POST['public'], ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}

	//Create $persons object to be passed in to the notify function.
	$persons = getReceivers($_POST['employee'], $areaGuid, "ffb6ffe7-c522-11e5-bdda-0242ac110003");
	//Call notify function using the object $persons created above as the third argument.
	notify("ffb6ffe7-c522-11e5-bdda-0242ac110003", "Congratulations! You have received a commendable performance report! See your report on the Wall of Fame or Commendable Log Page.", $persons);
	
	echo "<script>alert('Commendable Performance Submitted');window.close();</script>";
}

?>

<html>

	<script type="text/javascript">
		window.onload=function()
			{
				$("#employee").val(<?php echo "\"$commended\""; ?>);
			}
	</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script language="JavaScript" src="../includes/templates/scripts/calendar_db.js"></script>
	<link rel="stylesheet" href="../includes/templates/styles/calendar.css">
	<style>
	#commendableTable{
		width: 40%;
		margin-left: 30%;
		margin-right: 30%;
	}
	</style>
<h1 style='text-align:center'>Report a Commendable Performance</h1>
<p style='text-align:center'><a href='commendableLog.php'>Return to Log</a></p>
<!---------------------------employee------------------------------- -->
<form name='commend' method='post'>
<table id='commendableTable'>
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
<!------------------------public----------------------------------- -->
<tr>
	<td>
		<font size='3'>Public:</font>
	</td>
	<td>
		<select name='public'>
			<option value='0'>No</option>
			<option value='1'>Yes</option>
		</select>
		*Will appear in the Wall of Fame
	</td>
</tr>
	
<!------------------------date----------------------------------- -->
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
				'formname': 'commend',
					        // name for the input text field
				'controlname': 'date',
				'today' : s_today
			});
		</script>
	</td>
</tr>
<!------------------reason------------------------------>
<tr>
	<td>
		<font size='3'>Please describe the reason<br />for a commendable performance:</font>
	</td>
	<td>
		<textarea name='reason' style="width: 99%; height: 164px;" ></textarea>
	</td>
</tr>

<!------------------submit--------------------------- -->

	
</table>
<p style='text-align:center'><input name='commend' type='submit' value='Submit' /></p><!--CHANGE: this will need to be updated to call a .php file that will update the database -->
</form>

</html>
