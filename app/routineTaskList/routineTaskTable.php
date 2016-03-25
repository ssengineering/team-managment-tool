<?php

//routineTaskTable.php

//TODO: 
//  create functions that will output the whole table vs just a few hours for the dashboard
//	decide permissions for editing and creating.
//
//-----------------------------Functions--------------------------------------------
	function getMessages($netID,$permission,$date,$area){ //CHANGE: add a date varaible so we can pick the date/day we want tasks for
		global $db;
		$debug = 0;		
		$rtlist = "<tr> ";
		//set the variables for the day of the week and the date that we want the tasks for		
		$dayOfWeek = strtolower(date('D',strtotime($date)));

		try {
			$listQuery = $db->prepare("CALL createTaskList(:day,:dayOfWeek,:area)");
			$listQuery->execute(array(':day' => $date, ':dayOfWeek' => $dayOfWeek, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$firstIncomplete = 0;
		while($taskList = $listQuery->fetch(PDO::FETCH_ASSOC)){ //for each routine task returned by the query print it out.
			if(checkTime($taskList['timeDue'],$date) && $taskList['completed'] == 0){					
				echo "<td width='8%'>";
				if($firstIncomplete == 0){
					$firstIncomplete = 1;
					echo '<a id="incomplete"> </a>';
				}
				echo "<font color='red'><b>".date("h:i A",strtotime($taskList['timeDue']))."</b></font></td>"; //Print task time due.
			} else {
				echo "<td><b>".date("h:i A",strtotime($taskList['timeDue']))."</b></td>"; //Print task time due.
			}
			echo "<td>"."<a href="."\"javascript:newwindow('../routineTaskList/fullTask.php?taskId=".$taskList['ID']."')\">".$taskList['title']."</a></td>"; //print title as a link to the full task.

			if($taskList['completed'] == 1){ //if the task is completed
				$curNetID =  $taskList['completedBy'];
				echo "<td><div class=\"comments\">".nl2br($taskList['comments'])."</div></td>";	//print the comments			
				echo "<td>Completed At: ".$taskList['timeCompleted']."</td>"; //print the time completed
				echo "<td>".$curNetID ."</td>"; //print who it was completed by
			
			}else if($taskList['muted'] == 1){

				echo "<td><div class=\"comments\"><font color='red'>Muted:".$taskList['mutedComments']."</font></div></td>";
				echo "<td>Muted At: ".$taskList['timeMuted']."<br />";
				echo "<input class='button' type='button' name='complete' id='".$taskList['ID']."' value='Complete' onClick='completeTask(".$taskList['ID'].")'/></td>"; //complete button
				echo "<td><font color='red'>Muted By:".$taskList['mutedBy']."</font></td>";

			}else{

				echo "<td><div class=\"comments\"></div></td>";
				echo "<td><input class='button' type='button' name='complete' id='".$taskList['ID']."' value='Complete' onClick='completeTask(".$taskList['ID'].")'/>";// complete button
				if(checkTime($taskList['timeDue'],$date)){ //check if the mute button needs to be displayed

					echo "<input class='button' type='button' name='mute' id='mute".$taskList['ID']."' value='Mute' onClick='muteTask(".$taskList['ID'].")'/>";// mute button

				}
				echo "</td>"; 
				echo "<td> </td>"; // blank completedy by field
			}
			
			if($permission){ //This checks to see if the edit buttons need to be displayed.
				if($taskList['day'] == NULL){
					$form = 'editRepeated';
				}else{
					$form = 'editOneShot';
				}
				echo "<td>";
				echo'<input type="submit" class="button" onclick="javascript:newwindow(\'./'.$form.'.php?taskId='.$taskList['ID'].'\')" value="Edit"/>';
			}
			echo "</td></tr>";
		}	
	}
	
	//checks a whether a task can be muted or not based on the current time vs the time the task is due
	//@param $timeDue the time the task is Due to be completed
	function checkTime($timeDue,$date){
		$curTime = time();
		$checkTime = strtotime($timeDue);
		
		if(($checkTime - $curTime)  < 10 && date('Y-m-d') == $date){
			return true;
		}
		return false;
	}
//*****************************************************************************************************TODO
	function getDashboardTasks($netID,$date,$time,$area){
		global $db;
		$debug = 0;		
		$rtlist = "<tr> ";
		//set the variables for the day of the week and the date that we want the tasks for		
		$dayOfWeek = strtolower(date('D',strtotime($date)));

		$startHour = date('G:00',strtotime('00:00:00'));
		$endHour = date('G:i',strtotime('+1 hour'));

		try {
			$list2Query = $db->prepare("CALL createDashTaskList(:day,:dayOfWeek,:start,:end,:area)");
			$list2Query->execute(array(':day' => $date, ':dayOfWeek' => $dayOfWeek, ':start' => $startHour, ':end' => $endHour, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$resultsExist = false;
		while($taskList = $list2Query->fetch(PDO::FETCH_ASSOC)) {
			$resultsExist = true;
			if($taskList['completed'] == 1){
				
			}else if($taskList['muted'] == 1){
				echo "<td><b>".date("h:i A",strtotime($taskList['timeDue']))."</b></td>";
				echo "<td>"."<a href="."\"javascript:newwindow('../routineTaskList/fullTask.php?taskId=".$taskList['ID']."')\">".$taskList['title']."</a></td>";
				echo "<td><font color='#8f100a'><b>Muted:</b>".$taskList['mutedComments']."</font<br />";
				echo "<td>Muted At: ".$taskList['timeMuted']."<br />";
				echo "<input class='button' type='button' name='complete' id='".$taskList['ID']."' value='Complete' onClick='completeTask(".$taskList['ID'].")'/></td>"; //add functionality for a complete button
				echo "<td><font color='#8f100a'>Muted By:".$taskList['mutedBy']."</font></td>";
			}else{
				if(checkTime($taskList['timeDue'],$date)){					
					echo "<td width='8%'><font color='red'><b>".date("h:i A",strtotime($taskList['timeDue']))."</b></font></td>"; //Print task time due.
				} else {
					echo "<td><b>".date("h:i A",strtotime($taskList['timeDue']))."</b></td>"; //Print task time due.
				}
				echo "<td>"."<a href="."\"javascript:newwindow('../routineTaskList/fullTask.php?taskId=".$taskList['ID']."')\">".$taskList['title']."</a></td>";
				echo "<td></td>";
				echo "<td><input class='button' type='button' name='complete' id='".$taskList['ID']."' value='Complete' onClick='completeTask(".$taskList['ID'].")'/>";//add complete button
				if(checkTime($taskList['timeDue'],$date)){
					echo "<input class='button' type='button' name='mute' id='mute".$taskList['ID']."' value='Mute' onClick='muteTask(".$taskList['ID'].")'/>";//add mute button
				}
				echo "</td>"; //check if the mute button needs to be displayed
				echo "<td> </td>"; // blank completedy by field
			}
			
				echo "</td></tr>";
		}	
		if(!$resultsExist) {
			echo "<td>No routine Tasks this hour</td></tr>";
		}

	}
//*************************************************************************
function getSupDashboardTasks($netID,$area){
		global $db;
		echo "<table style='width: 100%; table-layout: fixed;'>";	
		$date = date("Y-m-d");
		$debug = 0;		
		$rtlist = "<tr> ";
		//set the variables for the day of the week and the date that we want the tasks for		
		$dayOfWeek = strtolower(date('D',strtotime($date)));
		try {
			$list3Query = $db->prepare("CALL createTaskList(:day,:dayOfWeek,:area)");
			$list3Query->execute(array(':day' => $date, ':dayOfWeek' => $dayOfWeek, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$noMoreTasks = 0;
		
		while($taskList = $list3Query->fetch(PDO::FETCH_ASSOC)) {
			$taskTitle = $taskList['title'];
			if($taskList['muted'] == 1 && $taskList['completed'] != 1){
				echo "<td style='width: 17%;'><font color='#8f100a'><b>".date("h:i A",strtotime($taskList['timeDue']))."</b></font></td>";
				echo "<td style='width: 52%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;'>"."<a href="."\"javascript:newwindow('../routineTaskList/fullTask.php?taskId=".$taskList['ID']."')\">".$taskTitle."</a></td>";
				echo "<td style='width: 31%;'><input class='button' type='button' name='complete' id='".$taskList['ID']."' value='Complete' onClick='completeTask(".$taskList['ID'].")'/></td>"; //add functionality for a complete button
				$noMoreTasks++;
			}else if(checkTime($taskList['timeDue'],$date) && $taskList['completed'] != 1){
					echo "<td style='width: 17%;'><font color='#8f100a'><b>".date("h:i A",strtotime($taskList['timeDue']))."</b></font></td>";
					echo "<td style='width: 52%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;'>"."<a href="."\"javascript:newwindow('../routineTaskList/fullTask.php?taskId=".$taskList['ID']."', 1015)\">".$taskTitle."</a></td>";
					echo "<td style='width: 31%;'><input class='button' type='button' name='mute' id='mute".$taskList['ID']."' value='Mute' onClick='muteTask(".$taskList['ID'].")'/>";//add mute button\
					echo "<input class='button' type='button' name='complete' id='".$taskList['ID']."' value='Complete' onClick='completeTask(".$taskList['ID'].")'/>";//add complete button
				echo "</td>"; //check if the mute button needs to be displayed
				$noMoreTasks++;
			}
			echo "</tr>";
		}	
		echo "</table>";
		if ($noMoreTasks == 0)
		{
			echo '<br /><div style="width: 100%; text-align: center; font-weight: bold; margin-bottom: 8px; font-size: 90%;"><table style="margin-left: auto; margin-right: auto;"><tr><th style="height: 2em; vertical-align: middle;">  No routine tasks are currently due.  </th></tr></table></div>';
		}
	}

function getTVMessages($date,$area){ //CHANGE: add a date varaible so we can pick the date/day we want tasks for
		global $db;
		$debug = 0;		
		$rtlist = "<tr> ";
		//set the variables for the day of the week and the date that we want the tasks for		
		$dayOfWeek = strtolower(date('D',strtotime($date)));

		try {
			$list4Query = $db->prepare("CALL createTaskList(:day,:dayOfWeek,:area)");
			$list4Query->execute(array(':day' => $date, ':dayOfWeek' => $dayOfWeek, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$firstIncomplete = 0;
		$sound = 0;
		while($taskList = $list4Query->fetch(PDO::FETCH_ASSOC)) {
			if($taskList['completed'] == 1){ //If Completed text is green.
				echo "<td><font color='#008500'><b>".$taskList['timeDue']."</b></font></td>";
				echo "<td><font color='#008500'>".$taskList['title']."</font></td>";				
				echo "<td><font color='#008500'>".$taskList['timeCompleted']."</font></td>";
				echo "<td><font color='#008500'>".$taskList['completedBy']."</font></td>";
			}else if($taskList['muted'] == 1){ //If Muted texted is Red
				echo "<td><font color='#FF7400'><b>".$taskList['timeDue']."</b></font>";
				if($firstIncomplete == 0){
					$firstIncomplete = 1;
					echo "<a name='incomplete'></a>";
				}
				echo "</td>";
				echo "<td><font color='#FF7400'>".$taskList['title']."</font></td>";
				echo "<td><font color='#FF7400'>Muted At: ".$taskList['timeMuted']."</font><br /></td>";
				echo "<td><font color='#FF7400'>Muted By: ".$taskList['mutedBy']."</font></td>";
			}else{
				echo "<td>";
				
				if(checkTime($taskList['timeDue'],$date)){
					if($firstIncomplete == 0){
						$firstIncomplete = 1;
						echo '<a id="incomplete"> </a>';
					}
					if($sound == 0){
						echo "<embed src='../includes/sounds/routine19.mp3' hidden='true' autostart='true' loop='false' />";
						$sound = 1;
					}
					echo "<font color='red'><b>".$taskList['timeDue']."</b></font></td>";
					echo "<td><font color='red'>".$taskList['title']."</font></td>";
					echo "<td></td><td></td>";
					
				}else{
					if($firstIncomplete == 0){
						$firstIncomplete = 1;
						echo '<a id="incomplete"> </a>';
					}
					echo "<b>".$taskList['timeDue']."</b></td>";
					echo "<td>".$taskList['title']."</td>";
					echo "<td></td><td></td>";
				}
			}
			echo "</tr>";
		}	
	}
		
	function tableTVHeader(){
		echo '<table>
		<th class="title">Time Due</th>
		<th class="title">Task</th>
		<th class="title">Completed At</th>
		<th class="title">Completed By</th></tr>';
	}
		

	//this function returns the table headers for the full app
	//@param $permission this variable holds the users permissions
	function tableHeader($permission){
		$header = '<table id="rtlTable" cellspacing="1" cellpadding="1" height="100%" border="1" ><tr>		
		<th width="7%" class="title">Time Due</th>
		<th width="30%" class="title">Task</th>
		<th width="30%" class="title">Comments</th>
		<th width="10%" class="title">Complete</th>
		<th width="10%" class="title">Completed By</th>

	<!-- add functionality here to only add these columns if the person has permission to edit or create routine tasks -->';
		if($permission){
			$header.='<th class="title">Edit</th>
			</tr>';
		}
		$header.='</tr>';
		echo $header;
	}

	function tableHeaderNoCSS($permission){
		$header = '<tr>		
		<th class="title">Time Due</th>
		<th class="title">Task</th>
		<th class="title">Comments</th>
		<th class="title">Complete</th>
		<th class="title">Completed By</th>

	<!-- add functionality here to only add these columns if the person has permission to edit or create routine tasks -->';
		if($permission){
			$header.='<th class="title">Edit</th>
			</tr>';
		}
		$header.='</tr>';
		echo $header;
			
	}
	
	//Produces the table headers for the edit HUD page
	function editHUDTableHeader(){
			echo '<table id="HUD" width="100%" cellspacing="1" cellpadding="1" height="100%" border="1" style="font-size:12px; border-collapse:collapse;" class="sortable"><tr>
		<th>Title</th>
		<th>Time Due</th>
		<th>One Shot Date</th>
		<th class="sortable_nosort">Days of the Week</th>
		<th>Creator</th>
		<th>Last Edit</th>
		<th>Enabled</th>
		<th class="sortable_nosort">Edit</th>
		</tr>';
	}
	

	//Produces a table full of all the information about each task.
	function editHUDTable($sort,$area){
		global $db;

		if($sort == 'all'){
			$sortString = "";
		}else if($sort == 'enabled'){
			$sortString = "enabled='1' AND";
		}else if($sort == 'disabled'){
			$sortString = "enabled='0' AND";
		}else if($sort == 'one'){
			$sortString = "day IS NOT NULL AND";
		}else if($sort == 'routine'){
			$sortString = "day IS NULL AND";
		}else{
			$sortString = "";
		}

		$queryString = "SELECT * FROM `routineTasks` WHERE ".$sortString." area = :area ORDER BY timeDue ASC";
		try {
			$routineTasksQuery = $db->prepare($queryString);
			$routineTasksQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo "<tr>";
		while($task = $routineTasksQuery->fetch(PDO::FETCH_ASSOC)) {
			echo "<td>".$task['title']."</td><td>".$task['timeDue']."</td><td>";
			if($task['day'] == NULL){
				echo "N/A</td><td>";
				//days of the week go here
				populateDays($task);
				echo "</td><td>";
			}else{
				echo $task['day']."</td><td>";
				echo "N/A</td><td>";
			}		
			echo $task['creator']." on: ".$task['createDate']."</td><td>";
			echo "By: ".$task['editor']."<br/>on: ".$task['editDate']."</td><td>";
			if($task['enabled']	== 1){
				echo "Yes";
			}else{
				echo "No";
			}	
			echo "</td><td>";

			if($task['day'] == NULL){
				$form = 'editRepeated';
			}else{
				$form = 'editOneShot';
			}
			echo'<input type="submit" class="button" onclick="javascript:newwindow(\'./'.$form.'.php?taskId='.$task['ID'].'\')" value="Edit"/>';
			
			echo "</td></tr><tr>"; //Change this for edit functionality****************************************
		}
		echo "</tr></table>";
	}

	function populateDays($curTask){
		echo '<div>';

		if($curTask['sat'] == 1){
			echo '<input type="checkbox" value="sat" name="day" disabled checked>Sat';
		}else{
			echo '<input type="checkbox" value="sat" name="day" disabled>Sat';
		}
		if($curTask['sun'] == 1){
			echo '<input type="checkbox" value="sat" name="day" disabled checked>Sun';
		}else{
			echo '<input type="checkbox" value="sat" name="day" disabled>Sun';
		}
		if($curTask['mon'] == 1){
			echo '<input type="checkbox" value="sat" name="day" disabled checked>Mon';
		}else{
			echo '<input type="checkbox" value="sat" name="day" disabled>Mon';
		}
		if($curTask['tue'] == 1){
			echo '<input type="checkbox" value="sat" name="day" disabled checked>Tue';
		}else{
			echo '<input type="checkbox" value="sat" name="day" disabled>Tue';
		}
		if($curTask['wed'] == 1){
			echo '<input type="checkbox" value="sat" name="day" disabled checked>Wed';
		}else{
			echo '<input type="checkbox" value="sat" name="day" disabled>Wed';
		}
		if($curTask['thu'] == 1){
			echo '<input type="checkbox" value="sat" name="day" disabled checked>Thu';
		}else{
			echo '<input type="checkbox" value="sat" name="day" disabled>Thu';
		}
		if($curTask['fri'] == 1){
			echo '<input type="checkbox" value="sat" name="day" disabled checked>Fri';
		}else{
			echo '<input type="checkbox" value="sat" name="day" disabled>Fri';
		}
		if($curTask['dayOfMonth'] != 0){
			echo '<input type="checkbox" value="sat" name="monthly" disabled checked>Monthly';
		}else{
			echo '<input type="checkbox" value="sat" name="monthly" disabled>Monthly';
		}
		echo '</div>';
	}

	function timeTest($t){
		$fulltime = date('G:i',strtotime('+1 hour'));
		echo "the time is: ".$fulltime;
	}
				
