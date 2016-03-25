<?php
//editShiftPeriods.php
require('../includes/includeme.php');

if (can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85"))//schedule resource
{
?>
<style>
	.operationStatus
	{
	    position: fixed !important;
	    display: table-cell;
	    vertical-align: middle;
	    right: 10px;
	    bottom: 10px;
	    text-align: right;
	    padding: .5em;
	    height: auto;
	    width: auto;
	    background-color: #369;
	    z-index: 10;
	    border: .2em solid #011948;
	    color: lightsteelblue;
	    border-radius: .8em;
	}
	.operationStatus.success
	{
		background-color: #76913C;
		border: .2em solid #587B0E;
		color: lightgrey;
	}
	.operationStatus.warning
	{
	    background-color: #EED50B;
	    border: .2em solid #E1A216;
	    color: #4d4d4d;
	}
	.operationStatus.failure
	{
	    background-color: #ED2604;
	    border: .2em solid #A11A03;
	    color: silver;
	}
</style>
<script type='text/javascript'>
	window.onload = loadStart;
	function loadStart()
	{
		// This lets me use regular expressions for jquery selectors
		jQuery.expr[':'].regex = function(elem, index, match) {
			var matchParams = match[3].split(','), validLabels = /^(data|css):/, attr = {
				method : matchParams[0].match(validLabels) ? matchParams[0].split(':')[0] : 'attr',
				property : matchParams.shift().replace(validLabels, '')
			}, regexFlags = 'ig', regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g, ''), regexFlags);
			return regex.test(jQuery(elem)[attr.method](attr.property));
		}
		printPeriods();
		$('#confirmDialog').dialog({
				title: "Overwrite Weekly",
				autoOpen: false,
				modal: true,
				resizable: true,
				draggable: true,
				width: 600,
				minWidth: 500,
				buttons: [{
						text: "Remove Weekly Schedules",
						click: 	function() {
							$(this).removeClass("ui-state-focus");
							$('#overwriteWeekly').val(1);	
							updatePeriods();
							dateChange = false;
							$(this).dialog("close");
						}
				},{
						text: "Keep Weekly Schedules",
						click: function(){
							$('#overwriteWeekly').val(0);		
							updatePeriods();
							dateChange = false;
							$(this).dialog("close");
						}
				},{
						text: "Cancel",
						click: function(){
								$(this).dialog("close");
						}
				}]
		});
		$('#confirmDialog').dialog("widget")
				.find(".ui-dialog-titlebar-close")
				.hide();
	}

	function editPeriods()
	{
		// Validate input dates
		var count = $("#content input:text").length;

		$("#content input:text").each(function(index, obj) {
			if($(this).hasClass("datepicker")) {
				if(obj.value.length < 10 || obj.value.length > 10 || obj.value == "0000-00-00") {
					alert("Please enter valid start and end dates");
					return false;
				}
			} else {
				if(obj.value.length < 1) {
					alert("Please enter valid periods and names");
					return false;
				}
			}
			if(index+1 == count) {
				// This should do a confirm to determine if they want to overwrite the weekly schedule. It should default to leaving the weekly schedule.
				// Checks if the period changed in a way that could remove schedules, and only gives the confirm box if that is true. Only checks if
				// the one of the dates was changed.
				if(dateChange){
					var confirmed = false;
					$('#confirmDialog').dialog("open");
					$('.ui-dialog :button').blur();
				}
				updatePeriods();
			}
		});
	}

	function updatePeriods()
	{
		// This post needs to have a hidden input added to the form with the confirmation response on whether the weekly schedule should be overwritten
		$.post('periodsAjax/updatePeriods.php', $('#editPeriods').serialize(), function(data, textStatus, jqXHR)
		{
			var status = textStatus;

			// If we were able to connect to the backend set the status to be whatever status we got from the backend otherwise just fail
			if (status == 'success')
			{
				status = data['status'];
			}
			else
			{
				alert('Failed to connect. Do you still have an internet connection?');
				return false;
			}
			// Give status update in the corner
			$('<div class="operationStatus ' + status + '">' + data['message'] + '</div>').appendTo('body').animate({'opacity' : '0'},{'duration' : 10000,'complete' : function()
			{
				$(this).remove();
			}});
		
			if ( status == 'failure' )
			{
				var popupText = '<div><br />' + data['message'] + '<table><tr><th>Period</th><th>Status</th></tr>';

				function printPeriodStatus(statusArray, status)
				{
					for (period in statusArray)
					{
						popupText += '<tr><td>' + statusArray[period] + '</td><td>' + status + '</td></tr>';
					}
				}

				printPeriodStatus(data['failed'], '**Failed!**');
				printPeriodStatus(data['succeeded'], 'Succeeded');

				popupText += '</table></div>';
				$(popupText).dialog({
					autoOpen : true,
					width : 'auto',
					height : 'auto',
					modal : false,
					title : 'Update Results',
					draggable : true,
					resizable : true
				});
			}
			$('.imagetable input:regex(name,^[0-9]+changed)').val(0);
		}, 'JSON');
	}


	function printPeriods() {
		var page = 'periodsAjax/printPeriods.php';

		var cb = function(result) {
			document.getElementById("results").innerHTML = result;
			$("input:text.datepicker").datepicker({
				dateFormat : "yy-mm-dd"
			});
			var idSearch = /^[0-9]+/;
			$('input:visible:regex(name,^[0-9]+.*?),[name="locked[]"]').change(function() {
				var id = idSearch.exec(this.name);
				if (!id)
				{
					id = this.value;
				}
				$('[name="' + id + 'changed"]').val(1);
			});
		};

		callPhpPage(page, cb);
	}

	function insertPeriod() {
		var r = confirm("Are you sure you want to Insert a new Period?");
		if (r == true) {
			var page = 'periodsAjax/insertPeriod.php';

			var cb = function(result) {
				printPeriods();
			};

			callPhpPage(page, cb);
		}
	}

	
	//Checks if the dates were changed in order to give the appropriate dialog box.
	var dateChange = false;
	function trackDate() {	
		dateChange = true;
	}
</script>

<h1 align='center'>Shift Periods</h1>
<div align='center' style="margin:auto;">
	<br/>
	<b>Instructions:</b> The period Column indicates the short name of the semester or period to be referenced which will be used by the scheduling application.
	<br/>
	Each Entry must have a UNIQUE period.
	<br/>
	The Name column is the display name that will be displayed to employees.
	<br/>
	Please submit all name changes before inserting more periods.
	<br />
	<span style="color: red;">**Note: Currently all periods must begin on a Saturday and end on a Friday. Additionally there should be no gaps between periods. **</span>
	<br/>
	<br/>
	<form id='editPeriods' name='editPeriods' method='post'>
		<input type='button' name='newPeriod' value="Insert New Period" onclick='insertPeriod()' />
		<!--
		So the onclick event below uses window.editPeriods instead of just editPeriods because apparently the context for an
		html inline onclick event is special and referencing editPeriods would actually reference not the Global function but
		the actual DOM element of that ID (i.e. the form we are using to submit edits to periods with in this case)
		-->
		<input type='button' onclick='window.editPeriods()' class='button' id='submit' name='submit' value="Submit Changes" />
		<input type='hidden' name='overwriteWeekly' id='overwriteWeekly' value="0" />

		<div id='results' align='center'>

		</div>

	</form>
</div>
<div id="confirmDialog">The changes you have made may result in the deletion of default schedules from the old time period. Would you like to also remove the weekly schedules in this period?</div>
<br/>
<?php

}
else
{
echo "<h1>You are Not authorized to view this page!</h1>";
}

require('../includes/includeAtEnd.php');
?>
