<?php
if (isset($_GET['employee']))
   $netId = $_GET['employee'];
else
   $netId = $netID;
if (isset($_GET['viewMode']))
   $viewMode = $_GET['viewMode'];
else
   $viewMode = 'trade';
if (isset($_GET['weeklyDefault']))
   $weeklyDefault = $_GET['weeklyDefault'];
else
   $weeklyDefault = 'weekly';
if (isset($_GET['date']) && $weeklyDefault == 'weekly')
{
   $dateViewed = $_GET['date'];
}
else if (isset($_GET['date']) && $weeklyDefault == 'default')
{
   $periodViewed = $_GET['date'];
   $dateViewed = date('Y-m-d');
}
else
{
   // NOTE: If no period is passed, despite the fact that the weeklyDefault parameter was passed as default it is reset to weekly
   // This is just to avoid us having to come up with some logic on what to do if no period is passed
   $weeklyDefault = 'weekly';
   $dateViewed = date('Y-m-d');
}
?>

<link rel="stylesheet" type="text/css" href="./index.css" />
<input id="user" type="hidden" value="<?php echo $netID; ?>" />
<?php
// All of what I am about to code is going to be ugly so I hope you will forgive me now, before I commit the sin
if (isset($periodViewed))
{
?>
	<input id="passedPeriod" type="hidden" value="<?php echo $periodViewed; ?>" />
<?php
}
?>
<h2 id="scheduleTitle">
   <span id="weeklyOrDefault"></span> Schedule for <span id ="titleEmployeeName"></span> for <span id="weekOrPeriod"></span><br />
   <div id="iCal"><a id="iCalLink">iCal Feed</a></div>
</h2>

<div id="viewMode">
   <input type="radio" id="trade" name="view" value="0" onClick="changeView();" <?php
   if ($viewMode == 'trade')
      echo 'checked';
 ?>>
   <label for="trade">Trade</label>
   <input type="radio" id="edit" name="view" value="1" onClick="changeView();" <?php
      if ($viewMode == 'edit')
         echo 'checked';
 ?>>
   <label for="edit">Edit</label>
</div>

<div id="weeklyDefault">
   <input type="radio" id="weekly" name="default" value="0" onClick="toggleDefault();" <?php
   if ($weeklyDefault == 'weekly')
      echo 'checked';
 ?>>
   <label for="weekly">Weekly</label>
   <input type="radio" id="default" name="default" value="1" onClick="toggleDefault();" <?php
      if ($weeklyDefault == 'default')
         echo 'checked';
 ?>>
   <label for="default">Default</label>
</div>

<div id="employeeSelector">
   <label for="employee">Employee: </label>
   <select name="employee" id="employee" onChange="changeEmployee()">
      <?php
      require_once $_SERVER['DOCUMENT_ROOT']."/includes/heimdall.php";
		try {
			$listQuery = $db->prepare("CALL getScheduleList(:area, :netId)");
			$listQuery->execute(array(':area' => $area, ':netId' => $netId));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$employees = array();
		while ($row = $listQuery->fetch(PDO::FETCH_ASSOC)){
   	 		$employees[] = $row;
		}
		//The function for comparison used by the PHP method usort (can see documentation online). This will sort
		//all of the members of the array alphabetically by the field "firstName".
		function compareFirstName($employeeA, $employeeB){
			return strcmp($employeeA['firstName'], $employeeB['firstName']);
		}
		usort($employees, "compareFirstName");
		//Declares variable to store the echo statement
		$employeeFiller = "";
		foreach($employees as $employee){
				$employeeFiller .= "<option value='".$employee['netID']."'";
				//Makes sure that the person accessing the app is the first selected,
				//if they are on the list
					if(isset($_GET['employee']) && $_GET['employee']==$employee['netID']){
						$selected = "selected ";
   	   				} else if($employee['netID'] == $netId && !isset($_GET['employee'])){
   	   					$selected = "selected ";      			
  		 	   		}
					 else{
					 	$selected = "";
					 }
				//Continues printing the option statement
				$employeeFiller .= $selected.">".$employee['firstName']." ".$employee['lastName'];
				//Another condition within the print, this time checking if the area
				//is the default area of the user, or not. If not, prints a star next
				//to their name in the option menu.
					$notDefaulted='';
					if ($employee['area'] != $area)	{
						$notDefaulted='*';
					}
				//Finishes printing the option statement	
				$employeeFiller .= $notDefaulted."</option>";
		}
   	  		echo $employeeFiller; 
 ?>
   </select>
</div>

<div id="timePickerDiv">
   <div id="periodDiv">
      <label for="period">Period: </label>
      <select id="period" onchange="changePeriod(this);" ></select>
   </div>
   <div id="dateDiv">
      <label for="date">Date: </label>
      <input type="text" id="date" class="myDatePicker" onchange="changeDate(this);" value="<?php echo $dateViewed; ?>" readonly />
      <a id="now">Now</a>
   </div>
</div>

<div id="shiftTypeSelector">
   <label for="shiftType">Shift Type: </label>
   <select class="shiftTypeSelector" id="shiftType" name="shiftType" onChange="setShiftType(this)"></select>
</div>

<div class="clearMe"></div>
<div id="employeeScheduleContainer"></div>

<div class="clearMe"></div>
<div id="schedulingNotes">
	<label for="hoursRequested">Requested hours: </label>
	<input type="text" id="hoursRequested" size="4" onkeyup="this.value=this.value.replace(/[^\d]/,'')">&nbsp;&nbsp;
	<label for="hoursRegistered">Registered hours: </label>
	<input type="text" id="hoursRegistered" size="4" onkeyup="this.value=this.value.replace(/[^\d]/,'')">
	<br />
	<label for="periodNotes">Scheduling Period Notes: </label>
	<br />
	<textarea id="periodNotes"></textarea>
</div>

<script src="index.js"></script>
<script type="text/javascript">window.onload = employeeScheduleOnload;</script>

<div id="shiftPopupDialog">
   <div class="popupLabels">
      Shift Type: <select class="shiftTypeSelector" id="popupShiftType"></select>
   </div>
   <div class="clearMe"></div>
   <div class="popupLabels">
      <div class="popupDates">
         Start Date:
         <div class="datePickerDiv">
            <input type="text" id="popupStartDate" class="myDatePicker" value="" readOnly />
         </div>
      </div>
      <div class="popupTimes">
         Start Time:
         <div class="timeEntryDiv">
            <input type="text" id="popupStartTime" class="myTimeEntry" value="" />
         </div>
      </div>
   </div>
   <div class="clearMe"></div>
   <div class="popupLabels">
      <div class="popupDates">
         End Date:
         <div class="datePickerDiv">
            <input type="text" id="popupEndDate" class="myDatePicker" value="" readonly />
         </div>
      </div>
      <div class="popupTimes">
         End Time:
         <div class="timeEntryDiv">
            <input type="text" id="popupEndTime" class="myTimeEntry" value="" />
         </div>
      </div>
   </div>
</div>
