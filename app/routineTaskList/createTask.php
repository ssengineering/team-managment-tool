<?php
/*
This file populates the page for creating a recurring task.
*/

require('../includes/includeMeSimple.php');
?>
<!---------------------Functions--------------------->

<?php
$messege = '';
$title = '';
$time = '';
//------------------------------POST DATA----------------------------------------------------------
//when the submit button is clicked this collects the information and adds it to the database and then displays the post success page to the user.
if(isset($_POST['submit'])){

	if($_POST['title'] == '' || $_POST['message'] == '' || $_POST['hours'] == ''){
		echo "<div align='center'><font color='red' size='14px'>One or More fields left Blank</font><br/>Please verify all data is correct before submiting</div>";
		$messege = $_POST['message'];
		$title = $_POST['title'];
		$time = date("H:i",strtotime($_POST['hours']));
	} else {
		$title = $_POST['title'];
		if(isset($_POST['all'])){
			$sat = 1;
			$sun = 1;
			$mon = 1;
			$tue = 1;
			$wed = 1;
			$thu = 1;
			$fri = 1;
		} else {
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
		}
		$timeDue = date('G:i',strtotime($_POST['hours']));

		if(isset($_POST['enabled'])){	
			$enabled = $_POST['enabled'];
		}else{ 
			$enabled = 0;
		}
	
		$descr = $_POST['message'];
		$createDate = date('Y-m-d');
		$result = "";
		$query = "INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,:monthly,:enabled,:area,:guid)";
		$params = array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $timeDue, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':monthly' => $monthly, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid());

		if(isset($_POST['repeat'])){
			if($_POST['repeat'] == "hourly"){
				if($_POST['businessHours'] == 0){ //business hours only
					//we need to cycle through the hours from 08:00 to 17:00 and insert a task for each hour.
					for($i = 8; $i < 18; $i++){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				} else if($_POST['businessHours'] == 1){ //night shift
					for($i = 18; $i < 24; $i++){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				} else {
					//we need to cycle through the hours from 00:00 to 23:00 and insert a task for each hour.
					for($i = 0; $i < 24; $i++){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				
				}
			} else if($_POST['repeat'] == "two"){
				if($_POST['businessHours'] == '0'){  //business hours only
					//we need to cycle through the hours from 08:00 to 17:00 and insert a task for every other hour.
					for($i = 8; $i < 18; $i+=2){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				} else if($_POST['businessHours'] == 1){ //night shift
					for($i = 18; $i < 24; $i++){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				} else {
					for($i = 0; $i < 24; $i+=2){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				}
			} else if($_POST['repeat'] == "four"){
				if($_POST['businessHours'] == '0'){ //business hours only
					//we need to cycle through the hours from 08:00 to 17:00 and insert a task for every fourth hour.
					for($i = 8; $i < 18; $i+=4){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				} else if($_POST['businessHours'] == 1){ //night shift
					for($i = 18; $i < 24; $i++){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				} else {
					for($i = 0; $i < 24; $i+=4){
						$due = $i.":".date('i',strtotime($timeDue));
						try {
							$insertQuery = $db->prepare("INSERT INTO routineTasks (title,descr,creator,createDate,timeDue,sat,sun,mon,tue,wed,thu,fri,dayOfMonth,enabled,area,guid) VALUES (:title,:descr,:netId,:created,:due,:sat,:sun,:mon,:tue,:wed,:thu,:fri,0,:enabled,:area,:guid)");
							$insertQuery->execute(array(':title' => $title, ':descr' => $descr, ':netId' => $netID, ':created' => $createDate, ':due' => $due, ':sat' => $sat, ':sun' => $sun, ':mon' => $mon, ':tue' => $tue, ':wed' => $wed, ':thu' => $thu, ':fri' => $fri, ':enabled' => $enabled, ':area' => $area, ':guid' => newGuid()));
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				}
			} else if($_POST['repeat'] == "none"){
				try {
					$insertQuery = $db->prepare($query);
					$insertQuery->execute($params);
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}else{
			try {
				$insertQuery = $db->prepare($query);
				$insertQuery->execute($params);
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
?>
<script language="javascript">
window.close();</script>	
	
<?php
	}	
}
?>

<!--*****************************HTML******************************************* -->
<script type="text/javascript">
window.onload = function()
{
	$('#hours').timeEntry({useMouseWheel: true,spinnerImage: ''});
	CKEDITOR.replace('message');
}
</script>
<form name="createRoutineTask" method="post">
<div>
<div align="center"><h2>Create New Routine Task</h2></div>
<table>
<tr>
	<th>Task Title:</th>
	<td colspan="3"><input type="text" name="title" maxlength=200 size=70 value="<?php echo $title; ?>"/></td>
	</td>
</tr>
<th>
Days the task is to be completed:
</th>
<script>
	function chooseDayOfMonth()
	{
		if($("input[name='monthly']").prop("checked"))
		{
			$("input[name='monthly']").next().replaceWith("<span>Day of Month<span>");
			$("input[name='monthly']").after("<input type='text' name='monthlyDay' value='1'  size=3/>");
		}
		else
		{
			$("input[name='monthlyDay']").next().replaceWith("<span>Monthly<span>");
			$("input[name='monthlyDay']").remove();
		}
	}
</script>
<td>
	<input type="checkbox" name="sat" value="1" />sat
	<input type="checkbox" name="sun" value="1" />sun
	<input type="checkbox" name="mon" value="1" />mon
	<input type="checkbox" name="tue" value="1" />tue
	<input type="checkbox" name="wed" value="1" />wed
	<input type="checkbox" name="thu" value="1" />thur
	<input type="checkbox" name="fri" value="1" />fri
	<input type="checkbox" name="all" value="1" />All Week
	<input type="checkbox" name="monthly" value="1" onclick="chooseDayOfMonth()"/><span>Monthly</span>
</td></tr>
	<th>
	Time Due:
	</th><td><input type="text" name="hours" id="hours"  size=10 value="<?php if ($time ==''){ echo date('h:iA'); } else { echo $time; }?>"/> <br/>Repeat:
				<input type='radio' name='repeat' value='none' checked />None
				<input type='radio' name='repeat' value='hourly' />Hourly
				<input type='radio' name='repeat' value='two' />2 hours
				<input type='radio' name='repeat' value='four' />4 hours
				<br/>Hour Range to repeat during: <input type='radio'	name='businessHours' value='2' checked />All
							<input type='radio' name='businessHours' value='0' />Business Hours (8AM-6PM)
							<input type='radio' name='businessHours' value='1' />Night Shift	(6PM-12AM)
				<br/>*Only applies if this is a repeating task
	</td>
<tr>
	<th>Description:</th>
	<td colspan="5">
		<textarea id="message" name="message" rows="10" cols="80"><?php echo $messege; ?></textarea>
	</td>
	</tr>
	<tr>
	<th>Enabled:</th><td><input type="checkbox" name="enabled" value="1" checked/>
		*Uncheck this if this task is not ready to be shown in the Task List yet.<input type="submit" name="submit" value="Create Task"></td>
	</tr>
</table>
</div>
</form>
</body>
</html>

<?php require('../includes/includeMeSimpleAtEnd.php'); ?>
