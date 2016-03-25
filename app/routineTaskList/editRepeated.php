<?php
require('../includes/includeMeSimple.php');
	$taskID = $_GET['taskId'];
	try {
		$tasksQuery = $db->prepare("SELECT * FROM routineTasks WHERE ID = :id");
		$tasksQuery->execute(array(':id' => $_GET['taskId']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$task = $tasksQuery->fetch(PDO::FETCH_ASSOC);

//------------------------------POST DATA----------------------------------------------------------
//when the submit button is clicked this collects the information and adds it to the database and then displays the post success page to the user.
if(isset($_POST['submit'])){

	$title = $_POST['title'];
	if(isset($_POST['sat'])){	
		$sat = $_POST['sat'];
	}else{ 
		$sat = 0;
	}
	if(isset($_POST['sun'])){	
		$sun = $_POST['sun'];
	}else{ 
		$sun = 0;
	}
	if(isset($_POST['mon'])){	
		$mon = $_POST['mon'];
	}else{ 
		$mon = 0;
	}
	if(isset($_POST['tue'])){	
		$tue = $_POST['tue'];
	}else{ 
		$tue = 0;
	}
	if(isset($_POST['wed'])){	
		$wed = $_POST['wed'];
	}else{ 
		$wed = 0;
	}
	if(isset($_POST['thu'])){	
		$thu = $_POST['thu'];
	}else{ 
		$thu = 0;
	}
	if(isset($_POST['fri'])){	
		$fri = $_POST['fri'];
	}else{ 
		$fri = 0;
	}
	if(isset($_POST['monthlyDay'])){	
		$monthly = $_POST['monthlyDay'];
	}else{ 
		$monthly = 0;
	}
	
	$timeDue = date("H:i",strtotime($_POST['time']));

	if(isset($_POST['enabled'])){	
		$enabled = $_POST['enabled'];
	}else{ 
		$enabled = 0;
	}
	
	$descr = $_POST['message'];
	$editDate = date('Y-m-d');

	try {
		$updateQuery = $db->prepare("UPDATE routineTasks SET title=:title,descr=:descr,editor=:editor,editDate=:editDate,timeDue=:timeDue,sat=:sat,sun=:sun,mon=:mon,tue=:tue,wed=:wed,thu=:thu,fri=:fri,enabled=:enabled,area=:area,dayOfMonth=:monthly WHERE ID=:id");
		$updateQuery->execute(array(':title' => $title, ':descr' => $descr, ':editor' => $netID, ':editDate' => $editDate, ':timeDue' => $timeDue, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':monthly' => $monthly, ':id' => $taskID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	if(isset($_POST['duplicate'])){
		try {
			$updateQuery = $db->prepare("UPDATE routineTasks SET title=:title,descr=:descr,editor=:editor,editDate=:editDate,sat=:sat,sun=:sun,mon=:mon,tue=:tue,wed=:wed,thu=:thu,fri=:fri,enabled=:enabled,area=:area, dayOfMonth=:monthly WHERE title = :title2 AND area = :area2 AND day IS NULL");
			$updateQuery->execute(array( ':title' => $title, ':descr' => $descr, ':editor' => $netID, ':editDate' => $editDate, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':monthly' => $monthly, ':title2' => $task['title'], ':area2' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
?>
<script language="javascript">
window.close();</script>	


<?php	
}else{
?>

<!--*****************************HTML******************************************* -->
<script type="text/javascript">
window.onload = function()
{
	$('#hours').timeEntry({useMouseWheel: true});
	CKEDITOR.replace('message');
}
</script>
<form name="editRoutineTask" method="post">
<div align="center"><h3>Edit Task</h3></div>
<!---------------------------TITLE----------------------------->
<table>
<tr>
<tr><th><font color='red'>Push Changes to ALL duplicate tasks</font><input type="checkbox" name="duplicate" value="1" checked/></th><td>*Changes in the time due will not be pushed to duplicate entries <br/>**Duplicate Entry = Any task with an identical title</td></tr>
	<th>Task Title:</th>
	<td colspan="3"><input type="text" name="title" maxlength=200 size=70 value="<?php echo $task['title'];?>"/></td>
	</td>
</tr>
<tr>
<th>
Days the task is to be completed:
</th>

<!-----------------------Days of the week-------------------------------->
<script>
	function chooseDayOfMonth()
	{
		if($("input[name='monthly']").prop("checked"))
		{
			$("input[name='monthly']").next().replaceWith("<span>Day of Month<span>");
			$("input[name='monthly']").after("<input type='text' name='monthlyDay' value='<?php if($task['dayOfMonth']!=0){ echo $task['dayOfMonth'];} else{echo "1";} ?>'  size=3/>");
		}
		else
		{
			$("input[name='monthlyDay']").next().replaceWith("<span>Monthly<span>");
			$("input[name='monthlyDay']").remove();
		}
	}
</script>
<td>
	<input type="checkbox" name="sat" value="1" <?php if($task['sat'] == 1){ echo "checked";} ?>/>sat
	<input type="checkbox" name="sun" value="1" <?php if($task['sun'] == 1){ echo "checked";} ?>/>sun
	<input type="checkbox" name="mon" value="1" <?php if($task['mon'] == 1){ echo "checked";} ?>/>mon
	<input type="checkbox" name="tue" value="1" <?php if($task['tue'] == 1){ echo "checked";} ?>/>tue
	<input type="checkbox" name="wed" value="1" <?php if($task['wed'] == 1){ echo "checked";} ?>/>wed
	<input type="checkbox" name="thu" value="1" <?php if($task['thu'] == 1){ echo "checked";} ?>/>thur
	<input type="checkbox" name="fri" value="1" <?php if($task['fri'] == 1){ echo "checked";} ?>/>fri
	<input type="checkbox" name="monthly" value="1" onclick="chooseDayOfMonth()" <?php if($task['dayOfMonth'] != 0){ echo "checked";} ?>/><?php if($task['dayOfMonth'] != 0){ echo "<input type='text' name='monthlyDay' value='$task[dayOfMonth]'  size=3/><span>Day of Month<span>";} else{ echo "<span>Monthly</span>"; } ?>
</td></tr>
<tr><th>
<!----------------------------------TIME DUE--------------------------------->
Time Due:</th><td><input type="text" name="time" id='hours' maxlength=5 size=10 value="<?php  echo date('h:iA',strtotime($task['timeDue']));?>"/>
				
</td>
</tr>
<!-----------------------MESSAGE CONTENT----------------------------->
<tr>
	<th>Description:</th>
	<td colspan="4">
		<textarea id="message" name="message" rows="10" cols="80"><?php echo $task['descr'];?></textarea>
	</td>
	</tr>
	<tr>
<!---------------------ENABLED------------------------------------------>
	<th>Enabled:</th><td><input type="checkbox" name="enabled" value="1" checked/>*Uncheck this if this task is not ready to be shown in the Task List yet.</td></tr>

<!-----------------------SUBMIT BUTTON----------------------------->
	<tr>
		<td><input type="submit" name="submit" value="Submit Changes"></td>
	</tr>
</table>
</form>
</body>
</html>

<?php } 
require('../includes/includeMeSimpleAtEnd.php')?>
