<?php
require('../includes/includeMeSimple.php');
//----------------GET DATA----------------------------------------
	$taskID = $_GET['taskId'];
	try {
		$tasksQuery = $db->prepare("SELECT * FROM routineTasks WHERE ID = :id");
		$tasksQuery->execute(array(':id' => $_GET['taskId']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$task = $tasksQuery->fetch(PDO::FETCH_ASSOC);
?>

<!---------------------Functions--------------------->
<?php
	
//----------------------------------------POST DATA--------------------------------------------
//when the submit button is clicked this collects the information and adds it to the database and then displays the post success page to the user.
if(isset($_POST['submit'])){
	$title = $_POST['title'];
	if($_POST['startdate'] == ""){
		$date = date('Y-m-d');
	}else{
		$date = $_POST['startdate'];
	}
	$time = date("H:i",strtotime($_POST['time']));
	$descr = $_POST['message'];
	if(isset($_POST['enabled'])){
		$enabled = $_POST['enabled'];
	}else{
		$enabled = 0;
	}
	$editDate = date('Y-m-d');
	
	try {
		$insertQuery = $db->prepare("INSERT INTO routineTasks (ID,title,descr,editor,editDate,timeDue,day,enabled,area,guid) VALUES (:taskId,:title,:descr,:netId,:editDate,:time,:day,:enabled,1,:guid) ON DUPLICATE KEY UPDATE title=:title2,descr=:descr2,editor=:editor,editDate=:editDate2,timeDue=:time2,day=:day2,enabled=:enabled2,area=:area2");
		$insertQuery->execute(array(':taskId' => $taskID, ':title' => $title, ':descr' => $descr, ':netId' => $netID, ':editDate' => $editDate, ':time' => $time, ':day' => $date, ':enabled' => $enabled, ':guid' => newGuid(), ':title2' => $title, ':descr2' => $descr, ':editor' => $netID, ':editDate2' => $editDate, ':time2' => $time, ':day2' => $date, ':enabled2' => $enabled, ':area2' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	echo $date;
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
	$("#startDate").datepicker({dateFormat: "yy-mm-dd"});
	$('#hours').timeEntry({useMouseWheel: true});
	CKEDITOR.replace('message');
}
</script>
<form name="createRoutineTask" method="post">
<div align="center"><h3>Create new One-Shot Task</h3></div>
<!---------------------------TITLE----------------------------->
<table>
<tr>
	<th>Task Title:</th>
	<td colspan="3"><input type="text" name="title" maxlength=200 size=70 value="<?php echo $task['title']; ?>"/></td>
	</td>
</tr>
<!-----------------------START DATE-------------------------------->
<tr>
	<th title="Messages appear ON the date specified.">Task Date</th>
	
	<td><input type='text' name='startdate' id='startDate' size='10'  value="<?php echo $task['day']; ?>"></td>
</tr>
<tr>
<!----------------------------------TIME DUE--------------------------------->
<th>Time Due:</th><td><input type="text" name="time" id='hours' maxlength=5 size=10 value="<?php  echo date('h:iA',strtotime($task['timeDue']));?>"/>
</td></tr>
<!-----------------------MESSAGE CONTENT----------------------------->
<tr>
	<th>Description:</th>
	<td colspan="5">
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
require('../includes/includeMeSimpleAtEnd.php') ?>
