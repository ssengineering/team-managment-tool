<?php
/*
*	Name: index.php
*	Application: Full Week Schedule
*	Site: ops.byu.edu
*	Author: Joshua Terrasas
*
*	Description: This application allows the user to view
*	the schedule for a week's worth of time. It includes
*	both the weekly version, and the default version of the
*	schedule. The user may select the week, the employees,
*	the shift types, and what version to view.
*/

//Standard include file for OPS header.
require_once('../includes/includeme.php');
?>

<!--CSS-->

<link rel="stylesheet" type="text/css" href="fullWeekSchedule.css" />

<!--JavaScript-->

<script language="JavaScript" src="fullWeekSchedule.js"></script>

<!--HTML-->

<input type="hidden" >
<h1 id="title">Full Week Schedule</h1>

<div id="toolbar" class="menu ui-widget-header ui-corner-all">
	<button id="previous" name="previous" >Previous</button>
	
	<span id="mode">	
		<input type="radio" id="modeWeekly" name="mode" class="toolbarButton" checked="checked"><label for="modeWeekly">Weekly</label>
		<input type="radio" id="modeDefault" name="mode" class="toolbarButton"><label for="modeDefault">Default</label>
	</span>
	
	<span id="dateTag"></span>
	
	<input id="date" name="date" type="hidden">
	
	<button id="calendarButton" type="button">Calendar</button>

	<button id="current" name="current">Current</button>

	<button id="settingsNew" name="settingsNew">Settings</button>

	<button id="next" name="next">next</button>
</div>

<div id="calendar">
</div>

<div id="settingsDialog">
	<div id="employeeMenu"></div>
</div>

<div id="allHourTypes" style="display: none;"></div>

<div id="allEmployees" style="display: none;"></div>

<div id="selectedHourTypes" style="display: none;"></div>

<div id="selectedEmployees" style="display: none;"></div>

<?php
//Standard include file for footer.
require_once('../includes/includeAtEnd.php');
?>
