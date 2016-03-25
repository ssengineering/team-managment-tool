<?php
/*
This file creates the page used to create a One-Shot task.
It consists of many fields that contain information about the Task

*/
require('../includes/includeMeSimple.php');
?>
<!---------------------JavaScript--------------->
<script type="text/javascript">
window.onload = function()
{
	$("#startDate").datepicker({dateFormat: "yy-mm-dd"});
	$('#hours').timeEntry({useMouseWheel: true,spinnerImage: ''});
	CKEDITOR.replace('message');
}
</script>
<!---------------------Functions--------------------->
<?php
$messege = '';
$description = '';
$date = '';
$time = '';
//----------------------------------------POST DATA--------------------------------------------
//when the submit button is clicked this collects the information and adds it to the database and then displays the post success page to the user.
if(isset($_POST['submit'])){

	if($_POST['title'] == '' || $_POST['message'] == '' || $_POST['startdate'] == '' || $_POST['hours'] == ''){
		echo "<div align='center'><font color='red' size='14px'>One or More fields left Blank</font><br/>Try again</div>";
		$messege = $_POST['message'];
		$description = $_POST['title'];
		$date = $_POST['startdate'];
		$time = $start = date("H:i",strtotime($_POST['hours']));
	} else {

		$title = "<b>One Shot:</b>".$_POST['title'];
		if($_POST['startdate'] == ""){
			$date = date('Y-m-d');
		}else{
			$date = $_POST['startdate'];
		}
		$timeDue = date('G:i',strtotime($_POST['hours']));
		$descr = $_POST['message'];
		if(isset($_POST['enabled'])){
			$enabled = $_POST['enabled'];
		}else{
			$enabled = 0;
		}
		$postDate = date('Y-m-d');
	
		try {
			$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,day,dayOfMonth,enabled,area,guid) VAlUES (:title,:descr,:netId,:postDate,:due,:day,0,:enabled,:area,:guid)");
			$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':postDate' => $postDate, ':due' => $timeDue, ':day' => $date, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
?>
<script language="javascript">
window.close();</script>	

<?php		
	}
}
?>


<!--*****************************HTML******************************************* -->

<form name="createRoutineTask" method="post">
<div align="center"><h3>Create new One-Shot Task</h3></div>
<!---------------------------TITLE----------------------------->
<div align='center'>
<table>
<tr>
	<th>Task Title:</th>
	<td colspan="3"><input type="text" name="title" maxlength=200 size=60 value="<?php echo $description; ?>"/></td>
	</td>
</tr>
<tr>
	<th title="Messages appear ON the date specified.">Task Date</th>	
	<td><input type='text' name='startdate' id='startDate' size='10'  value="<?php if($date == '') {echo date('Y-m-d'); }else{ echo $date; } ?>"></td>
</tr><tr>
<th>
	Time Due:</th><td><input type="text" name="hours" id='hours' maxlength=5 size=10 value="<?php if ($time ==''){ echo date('h:iA'); } else { echo $time; }?>"/>*You can use mousewheel or arrow keys to scroll through time.
</td></tr>
<tr>
	<th>Description:</th>

	<td colspan="3">
		<textarea id="message" name="message" rows="10" cols="60"><?php echo $messege; ?></textarea>
	</td>
	</tr>
	<tr>
	<th>Show In task List:</th><td><input type="checkbox" name="enabled" value="1" checked/>*Uncheck this if this task is not ready to be shown in the Task List yet.</td>
	</tr>
	<tr>
		<td><input type="submit" name="submit" value="Create Task"></td>
	</tr>
</table>
</div>
</form>

<?php  require('../includes/includeMeSimpleAtEnd.php')?>
