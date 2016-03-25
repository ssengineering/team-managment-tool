<?php //comments.php
//used to add a comment to a performance review
require('../includes/includeme.php');

if(isset($_POST['submit'])){
	try {
		$insertQuery = $db->prepare("INSERT INTO reportComments (netID,comments,date,meetingRequest,submitter,area,guid) VALUES (:employee,:comments,:day,:request,:netId,:area,:guid)");
		$insertQuery->execute(array(':employee' => $_POST['employee'], ':comments' => $_POST['comments'], ':day' => $_POST['date'], ':request' => $_POST['meetingRequest'], ':netId' => $netID, ':area' => $area, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
	echo"<script>alert('Comment Submitted');window.close();</script>";
}
?>
	<script language="JavaScript" src="../includes/templates/scripts/calendar_db.js"></script>
	<link rel="stylesheet" href="../includes/templates/styles/calendar.css">
	<style>
	#commentTable{
		width: 70%;
		margin-right: 15%;
		margin-left: 15%;
	}
	</style>
<h1 style='text-align:center'>Post a Comment on Performance Summary</h1>
<p style='text-align:center'><a href='commentsLog.php'>Return to Log</a></p>
<!---------------------------employee------------------------------- -->
<form name='comment' method='post'>
<table id='commentTable'>
<tr>
	<td>
		<font size='3'>Employee:  </font>
	</td>
	<td>
		<select name='employee' style='width:100%'>
		<?php
			employeeFill($area);//CHANGE for area
		?>
		</select>
	</td>
</tr>
<!--------------------------meeting request------------------------>
<tr>
	<td>
		<font size='3'>Meeting Request:</font>
	</td>
	<td>
		<select id='meetingRequest' name='meetingRequest' style='width:100%'>
			<option value=0>No</option>
			<option value=1>Yes</option>
		</select>
	</td>
</tr>
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
				'formname': 'comment',
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
		<font size='3'>Please enter a comment:</font>
	</td>
	<td>
		<textarea name='comments' style="width: 99%; height: 164px;" ></textarea>
	</td>
</tr>
</table>
<!------------------submit--------------------------- -->
	<p style='text-align:center;'><input name='submit' type='submit' value='Submit' /></p><!--CHANGE: this will need to be updated to call a .php file that will update the database -->
</form>

<?php require('../includes/includeAtEnd.php'); ?>



