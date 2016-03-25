<?php
require('../includes/includeMeSimple.php');

if (isset($_GET['violator']))
	{
		$violator = $_GET['violator'];
		
	}
	else
	{
		$violator = "";
	}

if(isset($_POST['security'])){
	try {
		$insertQuery = $db->prepare("INSERT INTO reportSecurityViolation (employee, date, violation, reason,submitter,guid) VALUES (:employee,:day,:violation,:reason,:netId,:guid)");
		$insertQuery->execute(array(':employee' => $_POST['employee'], ':day' => $_POST['date'], ':violation' => $_POST['violation'], ':reason' => $_POST['reason'], ':netId' => $netID, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit($e);
	}

	//Create $persons object to be passed into the notify function.
	$persons = getReceivers($violator, $areaGuid, "ffb6ffe7-c522-11e5-bdda-0242ac110003"); 
	//Call notify function using the object $persons created above as the third argument.
	notify("ffb6ffe7-c522-11e5-bdda-0242ac110003", "A security violation log has ben submitted for you. Please see your report on the Security Violation Log page.", $persons);	
	
	echo"<script>alert('Security Violation Submitted');window.close();</script>";
}
?>
	<script type="text/javascript">
		window.onload=function()
			{
				$("#date").datepicker({dateFormate:"yy-mm-dd"});
				$("#employee").val(<?php echo "\"$violator\""; ?>);
			}
	</script>
	<style>
	#securityViolationTable{
		width: 32%;
		min-width: 450px;
		margin-left: 34%;
		margin-right: 34%;
	}
	</style>
<h1 style='text-align:center'>Report a Security Violation</h1>
<!--------------------------employee---------------------->
<form name='security' method='post'>
<table id='securityViolationTable'>
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
<!--------------------------date------------------------>
	<tr>
		<td>
			<font size='3'>Date:</font>
		</td>
		<td>	
			<input type='text' name='date' id='date' size='10' value="<?php echo date('Y-m-d'); ?>"  />
		</td>
	</tr>

<!-- ----------------violation------------------------- -->
	<tr>
		<td>
			<font size='3'>Choose the security violation type:</font>
		</td>
		<td>
			<select name='violation' style='width:100%'>
				<option value="0" >Select violation...</option>
				<option value="No badge displayed" >No badge displayed</option>
				<option value="Tailgating" >Tailgating</option>
				<option value="Brought unauthorized visitors" >Brought unauthorized visitors</option>
				<option value="Accessed server room without signing in" >Accessed server room without signing in</option>
				<option value="Entered high security zone without approval" >Entered high security zone without approval</option>
				<option value="No yellow card while in the server room" >No yellow card while in the server room</option>
				<option value="Other" >Other</option>
			</select>
		</td>
	</tr>
<!------------------reason------------------------------>
	<tr>
		<td>
			<font size='3'>Please describe the<br />security violation:</font>
		</td>
		<td>
			<textarea name='reason' style="width: 99%; height: 100px;" ></textarea>
		</td>
	</tr>
</table>
<!------------------submit--------------------------- -->

	<p style='text-align:center'><input name='security' type='submit' value='Submit' /></p>
</form>
<?php
require('../includes/includeMeSimpleAtEnd.php');
?>
