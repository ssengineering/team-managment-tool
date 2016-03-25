<?php 
 require('../includes/includeme.php'); 


	if(isset($_POST['employeeNetId']))
		{
			$employeeNetId = $_POST['employeeNetId'];
		} 
			else if( isset($_GET['employee']))
		{
			$employeeNetId = $_GET['employee'];
		}
	else
		{
		$employeeNetId = $netID;
		}
?>
<!-- javascript code specific for this app-->
<script type="text/javascript" src="javascript.js"></script>
<!--  css specific for this app -->
<link rel="styleSheet" type="text/css" href="styleSheet.css" />
<script src="../includes/nicEdit.js" type="text/javascript"></script>

<ul id='test'></ul>

<div id="calendarInfo"></div>

<div id='sidePanel' class="sidePanel">
<div id="datepicker"></div>
<span id='categories'></span>
</div>

<div id="curDate" style="display:none;"><?php echo date("Y-m-d"); ?></div>
<div id="dateRange" style="display:none;">week</div>
<div id="selectedDate" style="display:none;"></div>
<div id="selectedCategories" style="display:none"></div>
<div id='categoryJson'></div>

<!-- This has information about adding new entries -->
<input type="button" class="activityButtons" style='position:relative;left:600px;top:20px;float:right;' onclick="addWindow('addEntry');" value="Add">
<div id='addEntry' style="display:none; width:auto;">
<input type='text' id='addEntryTitle' placeholder="Enter Title of Entry" size=30>
<span style="float:right">Enter Date of Event: <input type="text" id='addEntryDate' readonly="readonly" placeholder="<?php echo date('m-d-Y'); ?>" size=10></span>
<br><br>

<span >Enter Start Time of Event: <input type="text" id='addEntryTime' placeholder="<?php echo date('h:i'); ?>" size=4>
<select id='addEntryAmPm'>
<option value='am' selected="selected">am</option>
<option value='pm'>pm</option>
</select>
<span style="float:right">Enter End Time of Event: <input type="text" id='addEntryEndTime' placeholder="<?php echo date('h:i'); ?>" size=4>
<select id='addEntryEndAmPm'>
<option value='am' selected="selected">am</option>
<option value='pm'>pm</option>
</select>

</span>
<br><br>
<span>Select Category for event: </span><span id="submitCategories" ></span>
<br><br>
<textarea id='newEntryDescription' style='width:100%;height:200px;position:relative;' placeholder="Enter description for the Event"></textarea>
<br><br>
<div style="text-align:center;"><input type='button' value="Submit Entry" onclick='submitNewEntry();'></div>
</div>

<!-- This is populated with edit related information that is brought up in the jquery diolog box-->
<div id='editEntry' style="display:none; width:auto;">
<span>Title: <input type='text' id='editEntryTitle' size=40></span>
<span style="float:right">Enter Date of Event: <input type="text" id='editEntryDate' readonly="readonly"  size=10></span>
<br><br>
<span>Enter Start Time of Event: <input type="text" id='editStartTime' size=4>
<select id="editStartAmPm">
<option selected="selected" value='am'>am</option>
<option value='pm'>pm</option>
</select>
</span>
<span style="float:right">Enter End Time of Event: <input type="text" id='editEndTime' size=4>
<select id="editEndAmPm">
<option selected="selected" value='am'>am</option>
<option value='pm'>pm</option>
</select>
</span>


<br><br>
<span>Select Category for event: </span><span id="editCategories" ></span>
<br><br>
<textarea id='editEntryDescription' style='width:100%;height:200px;position:relative;' ></textarea>
<br><br>
<div style="text-align:center;"><input type='button' value="Submit Edition" onclick='submitEditedEntry();'></div>
</div>

<!-- This is used if the event information in the API is different than it was originally when the event was editted-->
<div id='eventChange' style="display:none">
<span id='warningDialog'></span>
</div>
<table id='displayEventChanges' style='display:none'></table>
<?php

 require('../includes/includeAtEnd.php');
 ?>


