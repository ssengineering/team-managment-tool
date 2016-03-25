// iframes is an object that has the following structure {String netId: jQuery
// Object reference to iframe for netId}
var iframes =
{
};

// Get user netID
var user = false;
function getUser()
{
	user = $('#user').val();
}

var areaInfo = false;
function getAreaInfo()
{
	var xmlhttp;
	var activeVal = 1;

	if (window.XMLHttpRequest)
	{
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	}
	else
	{
		// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.open("GET", 'getMinimumShiftLength.php', false);
	xmlhttp.send();
	areaInfo = JSON.parse(xmlhttp.responseText);
}

getAreaInfo();
// Get the Saturday of this fiscal week. This is used to get all the shifts
// during the 7 days from this Saturday.
// Remember to set the jQuery Date Picker to this by default
var dateForFirstRun = new Date();
var firstRunYear = dateForFirstRun.getFullYear();
var firstRunMonth = dateForFirstRun.getMonth() + 1;
var firstRunDate = dateForFirstRun.getDate();
var currentWeek = getStartOfWeek(parseDate(firstRunYear + '-' + firstRunMonth + '-' + firstRunDate, '00:00:00:00'));

// This takes two parameters, one for a date and one with a string in sql
// dateTime format
// It will return a javascript date object of the date and time passed in
function parseDate(dateString, timeString)
{
	var date = new Date();

	// Milliseconds are always set to 0
	date.setMilliseconds(0);

	// I am setting the time first from smallest intervals of time to larger ones
	// because of more crazy javascript
	// Date magic. If you make a setMonth(1) call on a date set to January 30 it
	// will set the date to the 2nd of
	// March because there is no 30th of February. So set the smallest intervals
	// first to make sure the date you
	// are using is a valid one for the larger time interval. I hate javascript
	// dates!
	if (timeString)
	{
		var timeParts = timeString.split(":");
	}
	else
	{
		var timeParts = new Array(0, 0, 0);
	}
	date.setSeconds(timeParts[2]);
	date.setMinutes(timeParts[1]);
	date.setHours(timeParts[0]);

	var dateParts = dateString.split("-");
	date.setDate(dateParts[2]);
	// Javascript's setMonth function uses 0 as January, etc. hence the need to
	// subtract 1 from the value passed in
	// Don't ask me why the devil they decided to use 0-11 instead of 1-12. They
	// use 1-30, etc. for day of the month.
	date.setMonth(dateParts[1] - 1);
	date.setFullYear(dateParts[0]);

	return date;
}

// This takes a date object and will return the Saturday of the associated fiscal
// week (fiscal week starts on Saturday and ends on Friday)
function getStartOfWeek(weekDay)
{
	// I first create a duplicate of the date object passed in to manipulate until
	// the Saturday of the fiscal week has been found
	var startOfWeek = new Date(weekDay.getTime());

	// I convert from our day of week system to javascripts's here (i.e. from
	// Saturday=0, Sunday=1, ... to Sunday=0, Monday=1, ...)
	var jsStartDayOfWeek = areaInfo['startDay'] - 1;
	if (jsStartDayOfWeek == -1)
	{
		jsStartDayOfWeek = 6;
	}

	// If the date object is not Saturday then subtract a day from it and check if
	// that day is Saturday
	if (startOfWeek.getDay() != jsStartDayOfWeek)
	{
		startOfWeek.setDate(startOfWeek.getDate() - 1);
		startOfWeek = getStartOfWeek(startOfWeek);
	}

	// Once this function has called itself recursively enough to get the
	// associated Saturday return the Saturday date object
	return startOfWeek;
}

// Does the user have schedule edit permissions
var permissionToEdit = false;
function getPermissions()
{
	var xmlhttp;
	var activeVal = 1;

	if (window.XMLHttpRequest)
	{
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	}
	else
	{
		// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.open("GET", 'checkPermissionToEdit.php', false);
	xmlhttp.send();
	permissionToEdit = xmlhttp.responseText;
}

// Retrieve all shift types from the database (this is used in case someone gets
// assigned a shift from another area)
//var allShiftTypes;
// This is used to store only those shifts that are for the current area
//var shiftTypes = {};
// This is used to keep track of which shift type is currently selected and
// therefore determines the shift type of a new shift
//var currentShiftType;
/*
* function setShiftType(newShiftType)
{
currentShiftType = newShiftType['value'];
}
*/

// This event is used at various times by the other events to replicate the
// desired results across all iframes
var clickEvent = document.createEvent("MouseEvents");
clickEvent.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);

function changeDate(date)
{
	var loading = createLoadingDialog();
	var newDate = parseDate(date.value);
	currentWeek = getStartOfWeek(newDate);
	for (var i in iframes)
	{
		if (iframes.hasOwnProperty(i))
		{
			window[i].$('#date').val(date.value).change();
		}
	}
	// var dateRegEx = /date=[0-9]{4}-[0-9]{2}-[0-9]{2}/
	// for (var i in iframes)
	// {
	// if (iframes.hasOwnProperty(i))
	// {
	// iframes[i].attr('src', iframes[i].attr('src').replace(dateRegEx, 'date=' +
	// date.value));
	// }
	// }
}

function changePeriod(period)
{
	var loading = createLoadingDialog();
	var newPeriod = period.value;
	for (var i in iframes)
	{
		if (iframes.hasOwnProperty(i))
		{
			window[i].$('#period').val(newPeriod).change();
		}
	}

	/*
	 * Think about maybe using this code, it might be more user-friendly even
	 * though it should be slower--it doesn't freeze up the ui in the smae way the
	 * code above does
	 *
	 var dateRegEx = /(date=)(.*?)(&|$)/;
	 for (i in iframes)
	 {
	 if (iframes.hasOwnProperty(i))
	 {
	 iframes[i].attr('src', iframes[i].attr('src').replace(dateRegEx, '$1' +
	 period.value + '$3'));
	 }
	 }
	 */
}

function changeView()
{
	var loading = createLoadingDialog();
	var newView = $('#viewMode input:checked').attr('id');
	for (var i in iframes)
	{
		if (iframes.hasOwnProperty(i))
		{
			window[i].$('#' + newView).prop('checked', true).get(0).dispatchEvent(clickEvent);
		}
	}

	setIframeHeights();

	/*
	 * Think about maybe using this code, it might be more user-friendly even
	 * though it should be slower--it doesn't freeze up the ui in the smae way the
	 * code above does
	 *
	 var viewRegEx = /(viewMode=)(trade|edit)/;
	 for (i in iframes)
	 {
	 if (iframes.hasOwnProperty(i))
	 {
	 iframes[i].attr('src', iframes[i].attr('src').replace(viewRegEx,
	 function(fullMatch, urlParameterKey, currentViewMode)
	 {
	 if (currentViewMode == 'edit')
	 return urlParameterKey + 'trade';
	 else
	 return urlParameterKey + 'edit';
	 }));
	 }
	 }
	 */
}

function changeMode(newModeElement)
{
	var loading = createLoadingDialog();
	var newMode = newModeElement.id;
	for (var i in iframes)
	{
		if (iframes.hasOwnProperty(i))
		{
			window[i].$('#' + newMode).prop('checked', true).get(0).dispatchEvent(clickEvent);
		}
	}

	// Update date or period for iframes now that mode has changed
	if (newMode == 'default')
	{
		$('#dateDiv').hide();
		$('#periodDiv').show();
		changePeriod($('#period').get(0));
	}
	else
	{
		$('#periodDiv').hide();
		$('#dateDiv').show();
		changeDate($('#date').get(0));
	}
	// Correct iframe heights for new mode
	setIframeHeights();

	// This is old code for changing src of iframes
	// On change of default or weekly mode, change all iframes' src to the new
	// parameter
	// var modeRegEx = /(weeklyDefault=)(.*?)(&|$)/;
	// for (var i in iframes)
	// {
	// if (iframes.hasOwnProperty(i))
	// {
	// iframes[i].attr('src', iframes[i].attr('src').replace(modeRegEx, '$1' +
	// newMode + '$3'));
	// // Might want to use this if we end up switching to making a javascript
	// // call
	// // var newHeight = window[i].$('body').height()+25;
	// // iframes[i].height(newHeight);
	// }
	// }
	//
	// if (newMode == 'default')
	// {
	// $('#dateDiv').hide();
	// $('#periodDiv').show();
	// // Pull info for periods for default shifts
	//
	// getPeriods();
	// }
	// else
	// {
	// $('#periodDiv').hide();
	// $('#dateDiv').show();
	// // New Mode is trade and etc.
	// var dateRegEx = /(date=)([0-9]{4}-[0-9]{2}-[0-9]{2}|.*?)(&|$)/
	// for (var i in iframes)
	// {
	// if (iframes.hasOwnProperty(i))
	// {
	// iframes[i].attr('src', iframes[i].attr('src').replace(dateRegEx, '$1' +
	// $('#date').val() + '$3'));
	// }
	// }
	// }
}

function teamSelect(teams)
{
	var teamId = teams.value;
	
	// Deselect all previously selected employees
	var oldSelection = $('#employees').val();
	for (var i in oldSelection)
	{
		$('#employees option[value="'+oldSelection[i]+'"]').prop('selected', false);
	}
	
	if ( teamId != 'None' )
	{
		var page = '../teams/ajax/pullTeamMembersArray.php?id='+teamId;
		
		var cb = function(response)
		{
			var employeeArray = JSON.parse(response);
			$.each(employeeArray, function(i, employee)
			{
				$('#employees option[value="'+employee['netID']+'"]').prop('selected', true);
			});
			$('#employees').change();
		}
		
		callPhpPage(page, cb);
	}
	else
	{
		$('#employees').change();
	}
}

function employeeList(employees)
{
	var loading = createLoadingDialog();
	var employeeArray = $(employees).val();
	var dateOrPeriod;
	var viewMode = $('#viewMode input:checked').attr('id');
	var weeklyOrDefault = $('#weeklyDefault input:checked').attr('id');
	if (weeklyOrDefault == 'weekly')
	{
		dateOrPeriod = $('#date').val();
	}
	else
	{
		dateOrPeriod = $('#period').val();
	}

	// If all is selected just overwrite whatever else may have been selected and
	// add everyone to the array
	if ($.inArray('All', employeeArray) != -1)
	{
		allEmployees = document.getElementById('employees').options;
		var length = allEmployees.length;
		employeeArray = new Array();
		for (var i = 1; i < length; i++)
		{
			employeeArray.push(allEmployees[i].value);
		}
	}

	// Remove the iframes for employees that are no longer selected
	for (var i in iframes)
	{
		if ($.inArray(i, employeeArray) == -1)
		{
			iframes[i].remove();
			delete iframes[i];
		}
	}

	// Look for newly added employees and add their associated iframe in the
	// correct location on the page
	var lastEmployee = false;
	var currentShiftType = $('#shiftType').val();
	length = employeeArray.length;
	for (var i = 0; i < length; i++)
	{
		// Insert any newly selected employees as an iframe after the last
		// employee's iframe that we iterated through
		if (!iframes.hasOwnProperty(employeeArray[i]))
		{
			iframes[employeeArray[i]] = $('<iframe id="' + employeeArray[i] + '" name="' + employeeArray[i] + '" class="schedule" src="employeeSchedule.php?employee=' + employeeArray[i] + '&date=' + dateOrPeriod + '&viewMode=' + viewMode + '&weeklyDefault=' + weeklyOrDefault + '" seamless="seamless" scrolling="no" style="display: block; width: 920px; margin: auto; overflow: hidden;"></iframe>');
			// If this is the first iteration then we should prepend the iframe to
			// our iframe container div.
			if (!lastEmployee)
			{
				$('#iframeContainer').prepend(iframes[employeeArray[i]]);
			}
			else
			{
				iframes[employeeArray[i]].insertAfter('#' + lastEmployee);
			}

			onIframeLoaded(employeeArray[i], function(argumentsArray)
			{
				var currentShiftType = argumentsArray[0];
				var currentEmployee = argumentsArray[1];
				window[currentEmployee].$('#shiftType').val(currentShiftType).change();
			}, [currentShiftType, employeeArray[i]]);
		}
		lastEmployee = employeeArray[i];
	}
}

function onIframeLoaded(iframeId, callback, callbackArguments)
{
	// Get iframe reference
	var iframe = window[iframeId];

	// If Jquery is not defined yet, reset timeout
	if (!iframe.$)
	{
		setTimeout(function()
		{
			return onIframeLoaded.apply(null, [iframeId, callback, callbackArguments])
		}, 250);
		return;
	}

	// If shiftTypes have not been loaded yet, reset timeout
	var $shiftTypes = iframe.$('#shiftType').children();
	if (!$shiftTypes.length)
	{
		setTimeout(function()
		{
			onIframeLoaded.apply(null, [iframeId, callback, callbackArguments])
		}, 250);
		return;
	}

	// Callback whatever function we want to run once the iframe has finished loading
	callback(callbackArguments);
}

function shiftTypeList(shiftTypes)
{
	shiftTypeArray = $(shiftTypes).val();
	// My thoughts are to make it so this automatically selects all employees that
	// have any shifts of the selected hour types
	// But maybe it isn't worth it, also maybe I should make it so we have an
	// option to select a team, which would select the employees that belong to
	// that team--Just thoughts
}

function setIframeHeights()
{
	for (var i in iframes)
	{
		if (iframes.hasOwnProperty(i))
		{
			var newIframeHeight = window[i].$('body').height() + 25;
			iframes[i].height(newIframeHeight);
		}
	}
}

function superSubmit()
{
	var loading = createLoadingDialog();
	for (var i in iframes)
	{
		if (iframes.hasOwnProperty(i))
		{
			window[i].$('rect.submitClass').get(0).dispatchEvent(clickEvent);
		}
	}
}

function superShiftTypeChange(shiftTypeSelector)
{
	var newShiftType = $(shiftTypeSelector).val();
	for (var i in iframes)
	{
		if (iframes.hasOwnProperty(i))
		{
			window[i].$('#shiftType').val(newShiftType).change();
		}
	}
}

function createLoadingDialog()
{
	var $loading = $('<div class="operationStatus">Loading... Please wait.</div>').appendTo('body').animate(
	{
		'opacity' : '0'
	},
	{
		'duration' : 10000,
		'complete' : function()
		{
			$(this).remove();
		}
	});
	return $loading;
}

function getPeriods()
{
	var page = '../newSchedule/returnPeriods.php';
	var cb = function(result)
	{
		var periods = JSON.parse(result);

		if (periods.length)
		{
			if (!$('#period option').length)
			{
				$('#period').html('');
				var selectedValue = '';
				for (var i = 0, length = periods.length; i < length; i++)
				{
					if (parseDate(periods[i].startDate) <= currentWeek && currentWeek <= parseDate(periods[i].endDate))
						selectedValue = periods[i].ID;
					$('<option />',
					{
						value : periods[i].ID,
						text : periods[i].name
					}).appendTo($('#period'));
				}
				$('#period>option[value="' + selectedValue + '"]').prop('selected', true);
			}
		}
	};
	callPhpPage(page, cb);
}

function createEmployeeSelector()
{
	var $employeeSelector = $('#employeeSelectors').dialog(
	{
		'title' : 'Employee Selector',
		'height' : 350,
		'width' : 350,
		'autoOpen' : true,
		'close' : function()
		{
			$(this).dialog('option', 'position',
			{
				'my' : 'left top',
				'at' : 'left+' + parseInt($(this).parent().css('left')) + ' top+' + parseInt($(this).parent().css('top')),
				'of' : window
			});
		},
		'resizeStop' : function()
		{
			var scrollTop = $(window).scrollTop();
			var badTop = parseInt($(this).parent().css('top'));
			var goodTop = badTop - scrollTop;
			$(this).parent().css('top', goodTop + 'px');
			$(this).parent().css('position', 'fixed');
		}
	});
	return $employeeSelector;
}

function employeeScheduleOnload()
{
	// Multi-employee controls in order to interact with multiple schedules at once
	var controlsHtml = '<div id="controls">';
	controlsHtml += '<div id="controlsTitle">Multi-employee Controls</div><div class="clearMe"></div>';
	controlsHtml += '<div id="timePickerDiv">';
	controlsHtml += '<div id="periodDiv">';
	controlsHtml += '<label for="period">Period: </label><select id="period" onchange="changePeriod(this);" ></select>';
	controlsHtml += '</div>';
	controlsHtml += '<div id="dateDiv">';
	controlsHtml += '<label for="date">Date: </label><input type="text" id="date" class="myDatePicker" onchange="changeDate(this);" readonly /><a id="now">Now</a>';
	controlsHtml += '</div>';
	controlsHtml += '</div>';
	controlsHtml += '<div id="shiftTypeSelector"><label for="shiftType">Shift Type: </label><select class="shiftTypeSelector" id="shiftType" name="shiftType" onChange="superShiftTypeChange(this)"></select></div>';
	controlsHtml += '<div id="superSubmitDiv"><input id="superSubmit" type="button" onclick="superSubmit()" value="Submit" /><div class="clearMe"></div></div>';
	controlsHtml += '</div>';
	var $controls = $(controlsHtml).appendTo('body');
	$controls.css('opacity', 1).delay(4000).animate(
	{
		'opacity' : 1 / 3
	},
	{
		'duration' : 2000,
		'complete' : function()
		{
			$(this).animate(
			{
				'opacity' : '.5'
			},
			{
				'duration' : 2000,
				'complete' : function()
				{
					$(this).removeAttr('style');
				}
			});
		}
	}).one('hover',
	{
	}, function()
	{
		$(this).stop(true, false);
		$(this).removeAttr('style');
	});

	var $controlsTitle = $('#controlsTitle');
	var $viewMode = $('#viewMode').insertAfter($controlsTitle);
	var $weeklyDefault = $('#weeklyDefault').insertAfter($controlsTitle);

	$employeeDialog = createEmployeeSelector();
	$employeeDialog.parent().css('position', 'fixed');
	$employeeDialog.dialog('option', 'position',
	{
		'my' : 'center center',
		'at' : 'center center',
		'of' : window
	});

	$employeeGear = $('<div id="employeeGear"></div>').button(
	{
		'icons' :
		{
			'primary' : "ui-icon-gear"
		},
		'text' : false
	}).click(function()
	{
		if ($employeeDialog.dialog('isOpen'))
		{
			$employeeDialog.dialog('close');
		}
		else
		{
			$employeeDialog.dialog('open');
		}
	}).insertAfter($controlsTitle);

	// This decreases the size of the View, edit, (trade??) buttons
	$('#weeklyDefault, #viewMode').buttonset().css('font-size', '0.70em');

	// These variables are used to determine whether the schedule should be
	// editable or view-only
	// (i.e. if the shifts and canvas are clickable) and whether to load the
	// default or weekly schedule
	editMode = parseInt($("#viewMode :radio:checked").val());
	defaultMode = parseInt($("#weeklyDefault :radio:checked").val());

	// This array is used for tracking all the Keyboard shortcuts available and
	// what they do
	// You should push objects in the following format {"keys":"Alt+S",
	// "description": "Submit changes to the database."}
	var keyboardShortcutArray = new Array();
	// Keyboard functionality
	$(window).keydown(function(event)
	{
		/*
		* These are ideas for keyboard shortcuts for editing/viewing the schedule without the mouse.
		* They have not been verified to see if any browsers already intercept these combinations for their own functionality.
		*
		* You might be able to use tab in order to change the currentlySelectedShift and then use the arrows to adjust shiftLength/dayOfTheWeek
		* Alt+[Arrow-left/Arrow-right] for changing the week or the period
		* Alt+[Arrow-up/Arrow-down] for changing employee
		* Alt+E for edit mode
		* Alt+T for tradeMode
		* Alt+W for Weekly
		* Alt+D for default
		*/
		// Character codes:
		// 37 - left
		// 38 - up
		// 39 - right
		// 40 - down
		// 69 - E
		// 84 - T
		// 87 - W
		// 68 - D
	});

	// These are masks for dateFormat (in the includes file)
	dateFormat.masks.dateOnly = 'yyyy-mm-dd';
	dateFormat.masks.timeOnly = 'HH:MM:ss';
	dateFormat.masks.dayOnly = 'D';
	// This is used for the labels of each of the days of the week
	if (defaultMode)
		dateFormat.masks.dayLabels = 'dddd';
	else
		dateFormat.masks.dayLabels = 'dddd mmm dS';
	// This is used for showing trade shift data
	dateFormat.masks.shiftDay = 'ddd mmm dS';
	// This is used for the timeEntry inputs that let your scroll to change the
	// hour/minute
	dateFormat.masks.timeEntry = 'hh:MMTT';
	// This is used for the hourLabels
	dateFormat.masks.hourLabels = 'h:MM tt';
	// This is used to compare the start and end date objects to the start/end
	// date and time received from the database
	// This might be used to avoid writing the same information back to the
	// database, but would require keeping track of the initial shiftType as well
	dateFormat.masks.compare = 'yyyy-mm-dd HH:MM:ss';

	$('#date').datepicker(
	{
		'changeYear' : true,
		'dateFormat' : 'yy-mm-dd',
		'firstDay' : currentWeek.getDay(),
		'showOn' : 'both',
		'buttonImage' : '../includes/libs/img/cal.gif',
		'buttonImageOnly' : true,
		'defaultDate' : currentWeek
	}).val(currentWeek.format('dateOnly'));

	getPermissions();
	getUser();
	getPeriods();

	// Get shift types to later associate them with shifts as necessary and
	// populate drop-down menus
	/*
	* getShiftTypes();
	function getShiftTypes()
	{

	function toObject(array)
	{
	var objectifiedArray = {};
	for (var i = 0; i < array.length; ++i)
	if (array[i] !== undefined)
	objectifiedArray[array[i].ID] = array[i];
	return objectifiedArray;
	}

	var page = '../newSchedule/returnShiftTypes.php';
	var cb = function(result)
	{
	var shiftTypesArray = JSON.parse(result);
	allShiftTypes = toObject(shiftTypesArray);

	shiftTypes = {};
	for (i in allShiftTypes)
	{
	if (window.allShiftTypes[i]['area'] == areaInfo['ID'])
	{
	shiftTypes[i] = window.allShiftTypes[i];
	}
	}

	Object.keys(shiftTypes).forEach(function(shiftKey)
	{
	// If currentShiftType has not been set then set it to the shift type with value of 1
	if (!window.currentShiftType && window.shiftTypes[shiftKey].value == 1)
	{
	window.currentShiftType = shiftKey;
	}
	// If current shiftType doesn't exist in our available shiftTypes array set it to who cares what
	if (!shiftTypes[window.currentShiftType])
	{
	window.currentShiftType = shiftKey;
	}
	});

	$('.shiftTypeSelector').find('option').remove();
	// Set the dropdown options
	for (var shiftType in window.shiftTypes)
	{
	// Remember to do this once permissions are implemented
	if (permissionToEdit || (parseInt(window.shiftTypes[shiftType]['selfSchedulable']) && user == $('#employee').val()))
	$('<option />', {value: window.shiftTypes[shiftType].ID,text: window.shiftTypes[shiftType].longName}).appendTo($('.shiftTypeSelector'));
	}
	$('#shiftType').val(currentShiftType);
	//  console.log(shiftTypes);
	};
	callPhpPage(page, cb);
	}
	*/

	// Hide shiftType dropdown if not in edit mode
	/*
	*  if (editMode)
	$('#shiftTypeSelector').show();
	else
	$('#shiftTypeSelector').hide();
	*/

	// Change datePicker for Period Dropdown if in default mode
	if (defaultMode)
	{
		$('#dateDiv').hide();
		$('#periodDiv').show();
		$('#schedulingNotes').show();
	}
	else
	{
		$('#dateDiv').show();
		$('#periodDiv').hide();
		$('#schedulingNotes').hide();
	}

	// Get shift types to later associate them with shifts as necessary and
	// populate drop-down menus
	getShiftTypes();
	function getShiftTypes()
	{

		function toObject(array)
		{
			var objectifiedArray =
			{
			};
			for (var i = 0; i < array.length; ++i)
				if (array[i] !== undefined)
					objectifiedArray[array[i].ID] = array[i];
			return objectifiedArray;
		}

		var page = '../newSchedule/returnShiftTypes.php';
		var cb = function(result)
		{
			var shiftTypesArray = JSON.parse(result);
			allShiftTypes = toObject(shiftTypesArray);

			shiftTypes =
			{
			};
			for (i in allShiftTypes)
			{
				if (window.allShiftTypes[i]['area'] == areaInfo['ID'])
				{
					shiftTypes[i] = window.allShiftTypes[i];
				}
			}

			Object.keys(shiftTypes).forEach(function(shiftKey)
			{
				// If currentShiftType has not been set then set it to the shift type
				// with value of 1
				if (!window.currentShiftType && window.shiftTypes[shiftKey].value == 1)
				{
					window.currentShiftType = shiftKey;
				}
				// If current shiftType doesn't exist in our available shiftTypes
				// array set it to who cares what
				if (!shiftTypes[window.currentShiftType])
				{
					window.currentShiftType = shiftKey;
				}
			});

			$('.shiftTypeSelector').find('option').remove();
			// Set the dropdown options
			for (var shiftType in window.shiftTypes)
			{
				// Remember to do this once permissions are implemented
				if (!parseInt(window.shiftTypes[shiftType]['deleted']) && (permissionToEdit || (parseInt(window.shiftTypes[shiftType]['selfSchedulable']) && user == $('#employee').val())))
					$('<option />',
					{
						value : window.shiftTypes[shiftType].ID,
						text : window.shiftTypes[shiftType].longName
					}).appendTo($('.shiftTypeSelector'));
			}
			$('#shiftType').val(currentShiftType);
			//  console.log(shiftTypes);
		};
		callPhpPage(page, cb);
	}


	$('#now').click(function()
	{
		/*
		// Build new url search string from currently selected options
		var link = '?employee='+$('#employee').val();
		if (defaultMode)
		link+='&weeklyDefault=default';
		if (editMode)
		link+='&viewMode=edit';

		// Redirect to employee schedule with currently selected options but for the real world's current time and date
		window.location.search = link;*/

		// Get today's date and it's related Start of week then load that
		$('#date').datepicker('setDate', new Date());
	});
}
