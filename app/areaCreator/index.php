<?php
require_once('../includes/includeme.php');
	
function printWeekDayOptions()
{
	echo <<<WEEKDAYOPTIONS
	<option>Pick a Day</option>
	<option value="0">Saturday</option>
	<option value="1">Sunday</option>
	<option value="2">Monday</option>
	<option value="3">Tuesday</option>
	<option value="4">Wednesday</option>
	<option value="5">Thursday</option>
	<option value="6">Friday</option>
WEEKDAYOPTIONS;
}

// Ensure that only people with the development permission can access the app.
if (isSuperuser())
{
?>

<link rel="stylesheet" type="text/css" href="./index.css" />

<h1 id="editorTitle">Area Creaditor</h1>
<div id="instructions">
	<p>
		This app is used in order to create a new Area on the website or make changes to an existing area.
		Click to fill out a form and select those apps and permissions you would like to include in the area.
		After successfully creating the new area it might be good to head over to the Area Admin page and select which employees you would like to have access to the area.
	</p>
	<p>
		If you single click an area it will "select" the area and you will be able to modify which apps are used in the selected area by scrolling down to the "Area App Selector" section of this page.
	</p>
</div>

<div id="addAreaPopup" class="popup">
	
	<label for="longName" class="form" title="Full Name of the Area">Area Name: </label>
	<input id="longName" class="form" type="text" name="longName" title="Full Name of the Area" placeholder="Full Area Name" />
	<div class="clearMe"></div>
	
	<label for="area" class="form" title="Abbreviated Name of the Area">Abbreviated Name: </label>
	<input id="area" class="form" type="text" name="area" title="Abbreviated Name of the Area" placeholder="Area Abbreviation" />
	<div class="clearMe"></div>
	
	<label for="hourSize" class="form" title="Smallest Allowable Schedulable Increment">Schedulable Increment: </label>
	<input id="hourSize" style="max-width: 50px;" class="form" name="hourSize" title="Smallest Allowable Schedulable Increment" type="number" value="60" min="1" max="360" />
	<div class="clearMe"></div>
	
	<label for="startDay" class="form" title="First Schedulable Day of the Week">Starting Day of Week: </label>
	<select id="startDay" class="form" title="First Schedulable Day of the Week" name="startDay">
		<?php printWeekDayOptions(); ?>
	</select>
	<div class="clearMe"></div>
	
	<label for="endDay" class="form" title="Last Schedulable Day of the Week">Ending Day of Week: </label>
	<select id="endDay" class="form" title="Last Schedulable Day of the Week" name="endDay">
		<?php printWeekDayOptions(); ?>
	</select>
	<div class="clearMe"></div>
	
	<label for="startTime" class="form" title="Earliest Schedulable Time">Starting Time: </label>
	<input id="startTime" class="form" name="startTime" title="Earliest Schedulable Time" class="hasTimeEntry" type="text" />
	<div class="clearMe"></div>
	
	<label for="endTime" class="form" title="**Start Time** of the Last Schedulable Increment">Ending Time: </label>
	<input id="endTime" class="form" name="endTime" title="**Start Time** of the Last Schedulable Increment" class="hasTimeEntry" type="text" />
	<div class="clearMe"></div>
	
	<label for="postSchedulesByDefault" class="form" title="Are schedules to be posted immediately or should they be posted manually?">Post Schedules by Default?: </label>
	<input id="postSchedulesByDefault" class="form" name="postSchedulesByDefault" title="Are schedules to be posted immediately or should they be posted manually?" checked="checked" type="checkbox" />
	<div class="clearMe"></div>
	
	<label for="canEmployeesEditWeeklySchedule" class="form" title="Do employees have the right to alter their unavailable hours, etc. on a weekly basis?">Self-schedule Non-work Weekly Shifts?: </label>
	<input id="canEmployeesEditWeeklySchedule" class="form" name="canEmployeesEditWeeklySchedule" title="Do employees have the right to alter their unavailable hours, etc. on a weekly basis?" checked="checked" type="checkbox" />
	<div class="clearMe"></div>
	
	<label for="homePage" class="form" title="The default page displayed after login">Home Page: </label>
	<input id="homePage" class="form" name="homePage" title="The default page displayed after login" type="text" value="whiteboard" />
	<div class="clearMe"></div>
	
</div>

<button id="addButton">New Area</button>

<table id="areaTable">
	<thead>
		<tr>
			<th>ID</th>
			<th title="Abbreviated Name of the Area">Area</th>
			<th title="Full Name of the Area">Long Name</th>
			<th title="First Schedulable Day of the Week">Start Day</th>
			<th title="Earliest Schedulable Time">Start Time</th>
			<th title="Last Schedulable Day of the Week">End Day</th>
			<th title="**Start Time** of the Last Schedulable Increment">End Time</th>
			<th title="Smallest Allowable Schedulable Increment">Schedulable Increment</th>
			<th title="The default page displayed after login">Home Page</th>
			<th title="Are schedules to be posted immediately or should they be posted manually?">Post by Default?</th>
			<th title="Do employees have the right to alter their unavailable hours, etc. on a weekly basis?">Self Schedule Non-work Weekly Shifts?</th>
			<th>Delete?</th>
		</tr>
	</thead>
	<tbody id="areaTbody"></tbody>
</table>

<br />
<br />
<h1 style="margin: auto; text-align: center;">Area Apps Selector</h1>
<div id="instructions">
	<p>
		Here simply find an app you would like to add to the area selected above and double-click it.
	</p>
	<p>
		After double-clicking an app a popup will appear that will allow you to add the app to the selected area.
		This will automatically add any necessary permissions and provide you an option to add a link to the app.
	</p>
	<p>
		If the area already has the selected app (the apps highlighted in light blue) then a popup will appear allowing you to remove the app from the area
		(this will remove any permissions that are no longer necessary and any links to the app for the area). Enjoy!
	</p>
</div>
<table id="areaApps">
	<thead>
		<tr>
			<th>App</th>
			<th>Description</th>
			<th>File Path</th>
			<th>Internal</th>
		</tr>
	</thead>
	<tbody></tbody>
</table>

<script src="index.js"></script>
<script>window.onload = loadMe;</script>

<?php
}
else
{
	echo "<h1>You are not authorized to view this page</h1>";
}
require ('../includes/includeAtEnd.php');
?>
