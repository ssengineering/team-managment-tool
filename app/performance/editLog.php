<?php //editLog.php
require('../includes/includeMeSimple.php');


$ID = $_GET['id'];
?>
<script type="text/javascript">
window.onload = function() {
	
	$('#start').timeEntry({useMouseWheel: true,
		timeSteps: [1,30,1]});
	$('#end').timeEntry({useMouseWheel: true,
		timeSteps: [1,30,1]});
	$('#arrived').timeEntry({useMouseWheel: true,
		timeSteps: [1,1,1]});
	$('#date').datepicker({dateFormat: "yy-mm-dd"});
}
</script>
<?php
if($_GET['type'] == "absence"){
	echo editAbsenceLog($ID);
} else if($_GET['type'] == "tardy"){
	echo editTardyLog($ID);
} else if($_GET['type'] == "policy"){
	echo editPolicyLog($ID);
} else if($_GET['type'] == "commendable"){
	echo editCommendableLog($ID);
} else if($_GET['type'] == "security"){
	echo editSecurityViolationLog($ID);
} else if($_GET['type'] == "comment"){
	echo editCommentLog($ID);
} else if($_GET['type'] == "silentMonitor"){
	echo editSilentMonitor($ID);
} else if($_GET['type'] == "silentMonitorCall"){
	echo editSilentMonitorCall($ID, $_GET['call']);
}

if(isset($_POST['submitSecurity'])){
	try {
		$updateQuery = $db->prepare("UPDATE reportSecurityViolation SET violation = :violation, reason = :reason WHERE ID = :id");
		$updateQuery->execute(array(':violation' => $_POST['violation'], ':reason' => $_POST['reason'], ':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
?>	<script>
		window.opener.printLog();
		window.close();
	</script>
<?php
}
if(isset($_POST['submitAbsence'])){
		$start = date("H:i",strtotime($_POST['start']));
	 	$end = date("H:i",strtotime($_POST['end']));
		try {
			$updateQuery = $db->prepare("UPDATE reportAbsence SET reason = :reason, shiftStart = :start, shiftEnd = :end, noCall = :call WHERE ID = :id");
			$updateQuery->execute(array(':reason' => $_POST['reason'], ':start' => $start, ':end' => $end, ':call' => $_POST['noCall'], ':id' => $ID));
		} catch(PDOException $e) {
			exit("error in query");
		}
?>	<script>
		window.opener.printLog();
		window.close()
	</script>
<?php
}
if(isset($_POST['submitComment'])){
	try {
		$updateQuery = $db->prepare("UPDATE reportComments SET comments = :comments, meetingRequest = :request WHERE id=:id");
		$updateQuery->execute(array(':comments' => $_POST['comments'], ':request' => $_POST['meetingRequest'], ':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
?>	<script>
		window.opener.printLog();
		window.close()
	</script>
<?php
}
if(isset($_POST['submitTardy'])){
		$start = date("H:i",strtotime($_POST['start']));
		$end = date("H:i",strtotime($_POST['end']));
		$time=date("H:i",strtotime($_POST['arrived']));
		try {
			$updateQuery = $db->prepare("UPDATE reportTardy SET reason=:reason, start=:start, end=:end, time=:time, noCall=:call, break=:break WHERE ID=:id");
			$updateQuery->execute(array(':reason' => $_POST['reason'], ':start' => $start, ':end' => $end, ':time' => $time, ':call' => $_POST['noCall'], ':break' => $_POST['break'], ':id' => $ID));
		} catch(PDOException $e) {
			exit("error in query");
		}
?>	<script>
		window.opener.printLog();
		window.close()
	</script>
<?php
}
if(isset($_POST['submitCommendable'])){
	try {
		$updateQuery = $db->prepare("UPDATE reportCommendable SET reason = :reason, submitter = :netId, public = :public WHERE ID=:id");
		$updateQuery->execute(array(':reason' => $_POST['reason'], ':netId' => $netID, ':public' => $_POST['public'], ':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
?>	<script>
		window.opener.printLog();
		window.close()
	</script>
<?php
}
if(isset($_POST['submitPolicy'])){
	try {
		$updateQuery = $db->prepare("UPDATE reportPolicyReminder SET reason=:reason, submitter=:netId WHERE ID=:id");
		$updateQuery->execute(array(':reason' => $_POST['reason'], ':netId' => $netID, ':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
?>	<script>
		window.opener.printLog();
		window.close()
	</script>
<?php
}
if(isset($_POST['submitSilentMonitor']))
{
	try {
		$updateQuery = $db->prepare("UPDATE silentMonitor SET overallComment = :comments WHERE `index` = :id");
		$updateQuery->execute(array(':comments' => $_POST['overallComments'], ':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	?><script>
		window.opener.printLog();
		window.close()
	</script><?php
}
if(isset($_POST['submitSilentMonitorCall']))
{
	$currentCall = $_POST['callNumber'];
	$criteriaRatingTotal = 0.0;
	$criteriaAverage = 0.0;

	try {
		$updateQuery = $db->prepare("UPDATE silentMonitorCalls SET `date` = :day, `comments` = :comments, `rating` = :rating WHERE `smid` = :id AND `callNum` = :callNum");
		$updateQuery->execute(array(':day' => $_POST['date'], ':comments' => $_POST['comments'], ':rating' => $_POST['rating'], ':id' => $ID, ':callNum' => $_POST['callNumber']));
	} catch(PDOException $e) {
		exit("error in query");
	}

	for($index = 1; $index <= count($_POST['criteria'][$currentCall]); $index++)
	{
		try {
			$updateCriteriaQuery = $db->prepare("UPDATE silentMonitorCallCriteria SET `rating` = :rating  WHERE `smid` = :id AND `callNum` = :call AND `criteriaIndex` = :index");
			$updateCriteriaQuery->execute(array(':rating' => $_POST['criteria'][$currentCall][$index], ':id' => $ID, ':call' => $currentCall, ':index' => $index));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}

	for($i = 1; $i <= count($_POST['criteria'][$currentCall]); $i++)
	{
		if($_POST['criteria'][$currentCall][$i] == 'Yes')
		{
			$criteriaRatingTotal += 1.0;
		}
		else if($_POST['criteria'][$currentCall][$i] == 'Partial')
		{
			$criteriaRatingTotal += 0.5;
		}
	}

	$criteriaAverage = ($criteriaRatingTotal / count($_POST['criteria'][$currentCall])) * 100;

	try {
		$updateCallQuery = $db->prepare("UPDATE silentMonitorCalls SET `criteriaAvg` = :average WHERE `smid` = :id AND `callNum` = :call");
		$updateCallQuery->execute(array(':average' => $criteriaAverage, ':id' => $ID, ':call' => $currentCall));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	?><script>
		window.opener.printLog();
		window.close()
	</script><?php
}
	
function editAbsenceLog($ID){
	global $area, $db;
	try {
		$absenceQuery = $db->prepare("SELECT * FROM reportAbsence WHERE ID = :id");
		$absenceQuery->execute(array(':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$curAbsence = $absenceQuery->fetch(PDO::FETCH_ASSOC);

	echo "<div align='center'>";
	echo "<h2>Edit Absence</h2>";
	echo "
	<form id='editAbsence' method='post'>
	<table>
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Start Time</th>
			<th>End Time</th>
			<th>Reason</th>";
			if($area == 2){
				echo 	"<th>No Call</th>";
			} else {
				echo "<th>No Show</th>";
			}
		echo "</tr>";
	echo"	<tr>
			<td>".nameByNetId($curAbsence['employee'])."</td>
			<td>".date("Y-m-d",strtotime($curAbsence['date']))."</td>
			<td>";
			echo '<input type="text" id="start" name="start" size="10" value="'.date("h:i A",strtotime($curAbsence['shiftStart'])).'" />';
			echo "</td>
			<td>"; 
			echo '<input type="text" id="end" name="end" size="10" value="'.date("h:i A",strtotime($curAbsence['shiftEnd'])).'" />';
			echo "</td>
			<td><textArea name='reason'>".$curAbsence['reason']."</textarea></td>
			<td><select name='noCall'>
						<option value='No' selected>No</option>
						<option value='Yes'>Yes</option>
					</select>
			</td>
		</tr>
	</table>
	<input type='submit' name='submitAbsence' value='Submit' method='post'>
	</form>
	</div>
	";
}

function editCommendableLog($ID){
	global $db;
	try {
		$commendableQuery = $db->prepare("SELECT * FROM reportCommendable WHERE ID = :id");
		$commendableQuery->execute(array(':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$curAbsence = $commendableQuery->fetch(PDO::FETCH_ASSOC);

	echo "<div align='center'>";
	echo "<h2>Edit Commendable</h2>";
	
	echo "
	<form id='editCommendable' method='post'>
	<table>
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Reason</th>
			<th>Public</th>
		</tr>";
	echo"	<tr>
			<td>".nameByNetId($curAbsence['employee'])."</td>
			<td>".date("Y-m-d",strtotime($curAbsence['date']))."</td>
			<td><textArea name='reason'>".$curAbsence['reason']."</textarea></td>
			<td>
					<select name='public'>
						<option value='0'>No</option>";
						if($curAbsence['public'] == 1){
							echo "<option value='1' selected>Yes</option>";
						}else{
							echo "<option value='1' >Yes</option>";
						}
	echo "</select>
			</td>
		</tr>
	</table>
	<input type='submit' name='submitCommendable' value='Submit' method='post'>
	</form>
	</div>
	";
}

function editCommentLog($ID){
	global $db;
	try {
		$commentsQuery = $db->prepare("SELECT * FROM reportComments WHERE id = :id");
		$commentsQuery->execute(array(':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$curComment = $commentsQuery->fetch(PDO::FETCH_ASSOC);

	echo "<div align='center'>";
	echo "<h2>Edit Comment</h2>";
	echo "
	<form id='editSecurityViolation' method='post'>
	<table>
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Meeting Request</th>
			<th>Comment</th>								
		</tr>";
	echo"	<tr>
			<td>".nameByNetId($curComment['netID'])."</td>
			<td>".date("Y-m-d",strtotime($curComment['date']))."</td>
			<td>";
	echo '<select name="meetingRequest">';
	if($curComment['meetingRequest'] == 1){
		echo '<option value=1>Yes</option>
		<option value=0>No</option>';
	} else {
		echo '<option value=0>No</option>
			<option value=1>Yes</option>';
	}
	echo '</select>';
	echo "
			<td><textArea name='comments'>".$curComment['comments']."</textarea></td>
		</tr>
	</table>
	<input type='submit' name='submitComment' value='Submit' method='post'>
	</form>
	</div>
	";
}


function editPolicyLog($ID){
	global $db;
	try {
		$policyQuery = $db->prepare("SELECT * FROM reportPolicyReminder WHERE ID = :id");
		$policyQuery->execute(array(':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$curAbsence = $policyQuery->fetch(PDO::FETCH_ASSOC);

	echo "<div align='center'>";
	echo "<h2>Edit Policy Reminder</h2>";
	
	echo "
	<form id='editPolicy' method='post'>
	<table>
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Reason</th>
		</tr>";
	echo"	<tr>
			<td>".nameByNetId($curAbsence['employee'])."</td>
			<td>".date("Y-m-d",strtotime($curAbsence['date']))."</td>
			<td><textArea name='reason'>".$curAbsence['reason']."</textarea></td>
		</tr>
	</table>
	<input type='submit' name='submitPolicy' value='Submit' method='post'>
	</form>
	</div>
	";
}

function editSecurityViolationLog($ID){
	global $db;
	try {
		$violationQuery = $db->prepare("SELECT * FROM reportSecurityViolation WHERE ID = :id");
		$violationQuery->execute(array(':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	$curAbsence = $violationQuery->fetch(PDO::FETCH_ASSOC);

	echo "<div align='center'>";
	echo "<h2>Edit Security Violation</h2>";
	
	echo "
	<form id='editSecurityViolation' method='post'>
	<table>
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Violation</th>			
			<th>Reason</th>		
		</tr>";
	echo"	<tr>
			<td>".nameByNetId($curAbsence['employee'])."</td>
			<td>".date("Y-m-d",strtotime($curAbsence['date']))."</td>
			<td>";
	echo '<select id="violation" name="violation">
		<option value="0" >Select Violation...</option>
		<option value="No badge displayed"';
	if($curAbsence['violation'] == "No badge displayed"){
		echo " selected";
	}
		echo '>No badge displayed</option>
			<option value="Tailgating"';
	if($curAbsence['violation'] == "Tailgating"){
		echo " selected";
	}
		echo '>Tailgating</option>
			<option value="Brought unauthorized visitors"';
	if($curAbsence['violation'] == "Brought unauthorized visitors"){
		echo " selected";
	}
		echo '>Brought unauthorized visitors</option>
			<option value="Accessed server room without signing in"';
	if($curAbsence['violation'] == "Accessed server room without signing in"){
		echo " selected";
	}
		echo '>Accessed server room without signing in</option>
			<option value="Entered high security zone without approval"';
	if($curAbsence['violation'] == "Entered high security zone without approval"){
		echo " selected";
	}	
		echo '>Entered high security zone without approval</option>
			<option value="No yellow card while in the server room"';
	if($curAbsence['violation'] == "No yellow card while in the server room"){
		echo " selected";
	}	
		echo '>No yellow card while in the server room</option>
			<option value="Other"';
	if($curAbsence['violation'] == "Other"){
		echo " selected";
	}	
		echo '>Other</option>
	</select>';
	echo "
			<td><textArea name='reason'>".$curAbsence['reason']."</textarea></td>
		</tr>
	</table>
	<input type='submit' name='submitSecurity' value='Submit' method='post'>
	</form>
	</div>
	";
}

function editTardyLog($ID){
	global $area, $db;
	try {
		$tardyQuery = $db->prepare("SELECT * FROM reportTardy WHERE ID = :id");
		$tardyQuery->execute(array(':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}

	$curAbsence = $tardyQuery->fetch(PDO::FETCH_ASSOC);

	$to_time=strtotime($curAbsence['time']);
	$from_time=strtotime($curAbsence['start']);
	$minsLate =  round(abs($to_time - $from_time) / 60,2)." minute(s)";					
	echo "<div align='center'>";
	echo "<h2>Edit Tardy</h2>";
	echo "
	<form id='editTardy' method='post'>
	<table>
		<tr>
			<th>Employee</th>
			<th>Date</th>
			<th>Start Time</th>
			<th>End Time</th>
			<th>Time Arrived</th>
			<th>Reason</th>";
			if($area == 2){
				echo 	"<th>No Call</th>";
			} else {
				echo "<th>No Show</th>";
			}
		echo "</tr>";
	echo"	<tr>
			<td>".nameByNetId($curAbsence['employee'])."</td>
			<td>".date("Y-m-d",strtotime($curAbsence['date']))."</td>
			<td>";
			echo '<input type="text" id="start" name="start" size="10" value="'.date("h:i A",strtotime($curAbsence['start'])).'" />';
			echo "</td>
			<td>"; 
			echo '<input type="text" id="end" name="end" size="10" value="'.date("h:i A",strtotime($curAbsence['end'])).'" />';
			echo "</td>
			<td>";
			echo '<input type="text" id="arrived" name="arrived" size="10" value="'.date("h:i A",strtotime($curAbsence['time'])).'" />';	
			echo "</td>
			<td><textArea name='reason'>".$curAbsence['reason']."</textarea></td>
			<td><select name='noCall'>
						<option value='No' selected>No</option>
						<option value='Yes'>Yes</option>
					</select></td>
		</tr>
	</table>
	<input type='submit' name='submitTardy' value='Submit' method='post'>
	</form>
	</div>
	";
}

function editSilentMonitor($ID)
{
	global $db;
	try {
		$silentMonitorQuery = $db->prepare("SELECT * FROM `silentMonitor` WHERE `index` = :id");
		$silentMonitorQuery->execute(array(':id' => $ID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$comment = $silentMonitorQuery->fetch(PDO::FETCH_ASSOC);

	echo "<h2 align='center'>Edit Silent Monitor</h2>

	<div align='center'>
		<form method='post' id='editSilentMonitor' name='editSilentMonitor'>
			<table>
				<tr>
					<th>Overall Comments</th>
				</tr>
				<tr>
					<td><textarea cols='60' rows='4' name='overallComments'>".$comment['overallComment']."</textarea></td>
				</tr>
			</table>
	
			<input type='submit' name='submitSilentMonitor' value='Submit' method='post'/>
		</form>
	</div>";
}

function editSilentMonitorCall($id, $callNumber)
{
	global $area, $db;
	try {
		$callQuery = $db->prepare("SELECT * FROM silentMonitorCalls WHERE `smid` = :id AND `callNum` = :call");
		$callQuery->execute(array(':id' => $id, ':call' => $callNumber));
		$criteriaQuery = $db->prepare("SELECT * FROM silentMonitorCallCriteria WHERE `smid` = :id AND `callNum` = :call");
		$criteriaQuery->execute(array(':id' => $id, ':call' => $callNumber));
	} catch(PDOException $e) {
		exit("error in query");
	}

	$call = $callQuery->fetch(PDO::FETCH_ASSOC);

	$comments = $call['comments'];
	$rating = $call['rating'];
	$date = $call['date'];

	echo "<h2 align='center'>Edit Call</h2>

	<div align='center'>
		<form method='post' id='editSilentMonitor' name='editSilentMonitor'>
			<input type='hidden' name='callNumber' value='".$callNumber."'>
			<table>
				<tr>
					<th>Comments</th>
					<th>Rating</th>
					<th>Date</th>
				</tr>
				<tr>
					<td>
						<textarea cols='60' rows='2' name='comments'>".$comments."</textarea>
					</td>
					<td>
						<table>
							<tr>
								<td>1</td>
								<td>2</td>
								<td>3</td>
								<td>4</td>
								<td>5</td>
							</tr>
							<tr>";
								if($rating == 1)
								{
									echo "<td><input type='radio' id='rating1' name='rating' value='1' checked /></td>";
								}
								else
								{
									echo "<td><input type='radio' id='rating1' name='rating' value='1' /></td>";
								}
								if($rating == 2)
								{
									echo "<td><input type='radio' id='rating2' name='rating' value='2' checked /></td>";
								}
								else
								{				
									echo "<td><input type='radio' id='rating2' name='rating' value='2' /></td>";
								}
								if($rating == 3)
								{
									echo "<td><input type='radio' id='rating3' name='rating' value='3' checked /></td>";
								}
								else
								{
									echo "<td><input type='radio' id='rating3' name='rating' value='3' /></td>";
								}
								if($rating == 4)
								{
									echo "<td><input type='radio' id='rating4' name='rating' value='4' checked /></td>";
								}
								else
								{
									echo "<td><input type='radio' id='rating4' name='rating' value='4' /></td>";
								}
								if($rating == 5)
								{
									echo "<td><input type='radio' id='rating5' name='rating' value='5' checked /></td>";
								}
								else
								{
									echo "<td><input type='radio' id='rating5' name='rating' value='5' /></td>";
								}
							echo "</tr>
						</table></td>
					<td>
						<input type='text' name='date' id='date' size='10'  value='".$date."' />
					</td>
				</tr>
			</table>
			<table>
				<tr>
					<th>Criteria</th>
					<th>Info</th>
					<th>Rating</th>
				</tr>";
			while($currentCriteria = $criteriaQuery->fetch(PDO::FETCH_ASSOC))
			{
				try {
					$criteriaInfoQuery = $db->prepare("SELECT * FROM silentMonitorCriteriaInfo WHERE `index` = :index AND `area` = :area");
					$criteriaInfoQuery->execute(array(':index' => $currentCriteria['criteriaIndex'], ':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
				$currentCriteriaInfo = $criteriaInfoQuery->fetch(PDO::FETCH_ASSOC);
								
				echo "<tr>
					<td>".$currentCriteriaInfo['index'].") ".$currentCriteriaInfo['title']."</td>
					<td>".$currentCriteriaInfo['contents']."</td>
					<td>";
						if($currentCriteria['rating'] == 'Yes')
						{
							echo "<select name = 'criteria[".$currentCriteria['callNum']."][".$currentCriteriaInfo['index']."]'>
								<option value='Yes' selected = 'true'>Yes</option>
								<option value='No'>No</option>
								<option value='Partial'>Partial</option>
							</select>";
						}
						if($currentCriteria['rating'] == 'No')
						{
							echo "<select name = 'criteria[".$currentCriteria['callNum']."][".$currentCriteriaInfo['index']."]'>
								<option value='Yes'>Yes</option>
								<option value='No' selected = 'true'>No</option>
								<option value='Partial'>Partial</option>
							</select>";
						}
						if($currentCriteria['rating'] == 'Partial')
						{
							echo "<select name = 'criteria[".$currentCriteria['callNum']."][".$currentCriteriaInfo['index']."]'>
								<option value='Yes'>Yes</option>
								<option value='No'>No</option>
								<option value='Partial' selected = 'true'>Partial</option>
							</select>";
						}
					echo "</td>
				</tr>";
			}
			echo "</table>
			<input type='submit' name='submitSilentMonitorCall' value='Submit' />
		</form>
	</div>";
}

require('../includes/includeAtEnd.php');
