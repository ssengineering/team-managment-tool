/*
*	Name: fullWeekSchedule.js
*	Application: Full Week Schedule
*	Site: ops.byu.edu
*	Author: Joshua Terrasas
*
*	Description: This is the main JavaScript file used
*	by the Full Week Schedule app. All of the JavaScript the
*	Full Week Schedule app uses is contained in this file. It
*	controls almost all the functionality of the app, as
*	well as a lot of the looks of the app because of the
*	jQuery UI that is used in several parts. The jQuery
*	library is used very heavily in this app. Comments
*	throughout the code are comprehensive since a lot of
*	the same things are done throughout the code. If
*	there is no comment, more than likely the same things
*	is done elsewhere, and a comment is included.
*/

//We may need to create some functions that return the name of the day, month, etc.

const DEFAULT_MODE = 0;
const WEEKLY_MODE = 1;
const DAYS_INDEX =
{
	"sunday" : 0,
	"monday" : 1,
	"tuesday" : 2,
	"wednesday" : 3,
	"thursday" : 4,
	"friday" : 5,
	"saturday" : 6
};
const MONTHS = Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
const DAYS = Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
const MILLI_SEC_PER_DAY = 86400000;
const DAY_OF_WEEK_INDEX_SHIFT = 1;
const FIRST_DAY_OF_WEEK = DAYS_INDEX.saturday;
var CURRENT_MODE = WEEKLY_MODE;

window.onload = function()
{
	//loadCalendar() is called after loadEmplyeeHour gets all the employees and hour types available
	loadEmployeeHour();
	loadUI();
}

function popUpSeconds()
{
	setTimeout(function()
	{
		$(".popup").hide("slow")
	}, 5000);
	;
}

function getStartOfWeek(date)
{
	if (date.getDay() == FIRST_DAY_OF_WEEK)
	{
		return date;
	}
	else
	{
		var weekStart = new Date(date.getTime() - ((date.getDay() + DAY_OF_WEEK_INDEX_SHIFT) * MILLI_SEC_PER_DAY));
		//Create date object

		return weekStart;
	}
}

function getEndOfWeek(date)
{
	var weekStart = getStartOfWeek(date);
	var weekEnd = new Date(weekStart.getTime() + (MILLI_SEC_PER_DAY * 6));
	//Create date object for end of week by adding six days to the weekStart object.

	return weekEnd;
}
//this loads the default employees and hour types and create a list that is sent to php pages for updates
function loadEmployeeHour()
{
	$.post("employeesAndHour.php", function(data)
	{
		$("#employeeMenu").html(data);
		//Sets the list of all available hour types
		var allHourTypes = "";
		$("#hourTypeList option").each(function()
		{
			if ($(this).val() != "All")
			{
				allHourTypes += $(this).val() + ",";

			}
		});
		//removes last comma
		allHourTypes = allHourTypes.slice(0, allHourTypes.length - 1);
		$("#allHourTypes").html(allHourTypes);

		var allEmployees = "";
		//Sets the list of all available employees
		$("#employees option").each(function()
		{
			if ($(this).val() != "All")
			{
				allEmployees += $(this).val() + ",";

			}
			else
			{
				$(this).attr("selected", "selected");
			}
		});
		allEmployees = allEmployees.slice(0, allEmployees.length - 1);
		//removes last comma
		$("#allEmployees").html(allEmployees);

		//Sets default hour types
		var defaultHourTypes = "";
		$("#hourTypeList option").each(function()
		{
			if ($(this).is(":selected") == true && $(this).val() != "All")
			{
				defaultHourTypes += $(this).val() + ",";
			}
		});
		//removes last comma
		defaultHourTypes = defaultHourTypes.slice(0, defaultHourTypes.length - 1);
		$("#selectedHourTypes").html(defaultHourTypes);

		//Creates list of selected
		$("#hourTypeList").change(function()
		{
			var selectedHourtype = ($("#hourTypeList").val()).join();
			if (selectedHourtype.search("All") == -1)
			{
				$("#selectedHourTypes").html(selectedHourtype);
			}
			else
			{
				$("#selectedHourTypes").html($("#allHourTypes").html());
			}
			updateCalendar(CURRENT_MODE, "maintain_date");
		});
		
		$("#employees").change(function()
		{
			var selectedEmployees = ($("#employees").val()).join();
			if (selectedEmployees.search("All") == -1)
			{
				$("#selectedEmployees").html(selectedEmployees);
			}
			else
			{
				$("#selectedEmployees").html($("#allEmployees").html());
			}
			updateCalendar(CURRENT_MODE, "maintain_date");
		});
		loadCalendar();
	});

}

function loadUI()
{
	$('#mode').buttonset();

	document.getElementById('modeWeekly').addEventListener('click', function()
	{
		window.CURRENT_MODE = WEEKLY_MODE;
		updateCalendar(CURRENT_MODE, "noFormat");//the no format is to prevent the function createJsDate to set the day to the last day of the week
	}, false);
	
	document.getElementById('modeDefault').addEventListener('click', function()
	{
		window.CURRENT_MODE = DEFAULT_MODE;
		updateCalendar(CURRENT_MODE, "noFormat");//the no format is to prevent the function createJsDate to set the day to the last day of the week
	}, false);

	$("#date").datepicker(
	{
		dateFormat : 'yy-mm-dd',
		showAnim : 'slideDown',
		onSelect : function()
		{
			updateCalendar(CURRENT_MODE, "noFormat");
		}
	});

	$('#calendarButton').button(
	{
		icons :
		{
			primary : "ui-icon-calendar",
		},
		text : false
	});

	document.getElementById('calendarButton').addEventListener('click', function()
	{
		$('#date').datepicker('show');
	}, false);

	$('#previous').button(
	{
		icons :
		{
			primary : "ui-icon-circle-triangle-w"
		},
		text : false
	});

	$('#current').button();

	document.getElementById('current').addEventListener('click', function()
	{
		$('#date').datepicker('setDate', 'null');
		updateCalendar(CURRENT_MODE)
	}, false);

	$('#settingsNew').button(
	{
		icons : 
		{
			primary : "ui-icon-gear"
		},
		text : false
	});

	$('#settingsDialog').dialog
	({
		autoOpen: false,
		height: 300,
		width: 400,
		modal: false,
		buttons: 
		{
			Okay: function()
			{
				$(this).dialog('close');
			}
		}
	});

	document.getElementById('settingsNew').addEventListener('click', function()
	{
		$('#settingsDialog').dialog('open');
	}, false);

	$('#next').button(
	{
		icons :
		{
			primary : "ui-icon-circle-triangle-e"
		},
		text : false
	});
// when clicking on next or previous when in weekly mode these function update the date and update calendar. When in default mode it calls the php page that figures out what is the start date of next period or end date of previous period, if no results are returned on a previous click for example it means there are no period before the current one and nothing happens.
	$("#next").click(function() 
	{
		if (CURRENT_MODE != DEFAULT_MODE)
		{
			var date = createJsDate();
			date.setDate(date.getDate() + 7);
			setDatepickerDate(date);
			updateCalendar(CURRENT_MODE);
		}
		else//takes care of default mode
		{
			$.post("periodDate.php",
			{
				"date" : $("#date").val(),
				"next" : "next"
			}, function(result)
			{
				if (result != "")
				{
					$("#date").val(result);
					updateCalendar(CURRENT_MODE, "noFormat", "next");
				}
			})
		}

	});

	$("#previous").click(function()
	{
		if (CURRENT_MODE != DEFAULT_MODE)
		{
			var date = createJsDate();
			date.setDate(date.getDate() - 7);
			setDatepickerDate(date);
			updateCalendar(CURRENT_MODE);
		}
		else//tales care of default mode
		{
			$.post("periodDate.php",
			{
				"date" : $("#date").val(),
				"previous" : "previous"
			}, function(result)
			{
				if (result != "")
				{
					$("#date").val(result);
					updateCalendar(CURRENT_MODE, "noFormat", "previous");
				}
			})
		}

	});
	//hoverHoldShow("#employeeMenuBar", "#employeeMenu", ".popup");
}
//this takes care of the settings menu that allows users to choose employees and hour types they want to see
/*function hoverHoldShow(triggerId, hiddenId, hidePopup)
{
	var timer;
	$(triggerId).mouseenter(function()
	{
		timer = setTimeout(function()
		{
			$(hiddenId).css("display", "inline");
			if (hidePopup != undefined)
			{
				$(hidePopup).css("display", "none");
			}
		}, 500);
	});
	$(employeeMenuBar).mouseleave(function()
	{
		clearTimeout(timer);
		$(hiddenId).css("display", "none");
	});
}*/
//loads calendar and is also called by the update function
function loadCalendar(mode, date, prevNext)
{
	if (date === undefined)
	{
		date = new Date();
		//date.setMonth(date.getMonth()+1);
	}

	if (mode === undefined)
	{
		mode = WEEKLY_MODE;
	}
	if ($("#selectedHourTypes").html() == "")
	{
		var hourTypes = $("#allHourTypes").html();
	}
	else
	{
		var hourTypes = $("#selectedHourTypes").html();
	}
	if ($("#selectedEmployees").html() == "")
	{
		var employees = $("#allEmployees").html()
	}
	else
	{
		var employees = $("#selectedEmployees").html()
	}

	var weekStart = getStartOfWeek(date);
	var weekEnd = getEndOfWeek(date);
	setDatepickerDate(date);
//date is set to beginning of the week so that the php file may create the headings of the table always starting from the first day of the week and avoiding problems of having to standardize the date in the php file
		var page = "fullWeekSchedule.php?mode=" + mode + "&date=" + weekStart.getFullYear() + "-" + (weekStart.getMonth() + 1) + "-" + weekStart.getDate() + "&employees=" + employees + "&hourTypes=" + hourTypes + "&prevNext=" + prevNext +"&periodDate="+date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();

	var cb = function(result)
	{
		$('#calendar').replaceWith(result);

		if (mode == WEEKLY_MODE)
		{
			$("#dateTag").html(MONTHS[weekStart.getMonth()] + " " + weekStart.getDate() + " - " + MONTHS[weekEnd.getMonth()] + " " + weekEnd.getDate());
		}
		else
		{
			$("#dateTag").html(document.getElementById('period').value);
			//if changing from weekly mode to default mode and if in a week that is not within any period display no period for this week
			if(document.getElementById('period').value=="")
			{
				$("#dateTag").html("Week Not in a period");
			}
		}
	}
	callPhpPage(page, cb);
	// AJAX call using GET.
}

//updates the calendar 
function updateCalendar(mode, noDateFormatting, prevNext)
{
	if (noDateFormatting == undefined)
	{
		loadCalendar(mode, createJsDate());
	}
	else
	{
		loadCalendar(mode, createJsDate(noDateFormatting), prevNext);
	}
}

//creates a js date from string due to differences in how firefox and chrome interpret string dates which was causing problems 
//if noFormating parameter has any value then the date is just created in JS, if there is no parameter the JS date created has its day changed to the last day of the week
function createJsDate(noFormatting)
{

	var currentTime = new Date();
	var dateString = $("#date").val();
	var dateArray = new Array();
	dateArray[0] = "";
	dateArray[1] = "";
	dateArray[2] = "";
	var arrayCount = 0;
	for ( i = 0; i < dateString.length; i++)
	{
		if (dateString[i] != "-")
		{
			dateArray[arrayCount] += dateString[i];
		}
		else
		{
			arrayCount++;
		}
	}
	var year = dateArray[0]
	var month = dateArray[1]
	var day = dateArray[2]
	if (noFormatting == undefined)
	{
		var newDate = new Date(getEndOfWeek(new Date(year, month - 1, day, currentTime.getHours(), currentTime.getMinutes())));
	}
	else
	{
		var newDate = new Date(year, month - 1, day, currentTime.getHours(), currentTime.getMinutes());
	}
	return newDate;

}

//this function is the opposite of the one above and it takes a JS date and converts it to a string acceptable to datepicker. 
function setDatepickerDate(jsDate)
{
	var year = jsDate.getFullYear();
	var month = jsDate.getMonth() + 1;
	var day = jsDate.getDate();
	var dateString = year;
	if (month < 10)
	{
		dateString += "-0" + month;
	}
	else
	{
		dateString += "-" + month;
	}
	if (day < 10)
	{
		dateString += "-0" + day;
	}
	else
	{
		dateString += "-" + day;
	}
	$("#date").val(dateString)
}
