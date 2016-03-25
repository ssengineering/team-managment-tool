/****
**
** I made some changes to raphael.js to make it a bit easier to have an MVC-esque framework
** I only recently did this and it would be nice to have the code changed to work so that
** whenever a change to Element.data('start') or Element.data('end') is made the rect were
** automatically updated/moved to the corresponding x and y coordinates. I believe there are
** quite a few other opportunities to make use of this throughout this file; feel free to make
** those changes.
**
** I'm pretty sure I won't fix that before I stop working on this.
**
** It would be good to minify this file and use the minified version. Then just use this for
** source. It is getting large enough that we might actually get a noticeable increase in
** performance if we do.
**
** Some things that I would like added to this app are the following:
** (1) A merge option for conflict resolution
** (2) The ability to manually change weekly schedules for conflict resolution
** (3) The ability to use the mouse scroll-wheel to change the currently selected Shift Type
** (4) Keyboard shortcuts (like ctrl+c to copy a shift and be able to paste it somewhere, or
**    hitting delete to remove the shift)
** (5) Undo and Redo buttons (you would just keep a history array of the changes that have
**    been made and then pop and push changes to and from the history to undo and redo)
**    Use classes ui-icon-arrowrefresh-1-w for undo and ui-icon-arrowrefresh-1-e for redo
**    Or use a custom made undo/redo graphic in raphael instead of the jquery ui stuff
** (6) Make a "loading" spinner dialog for the synchronous calls made to delete and create
**    shifts in the conflict resolution between the default and weekly schedules
** (7) Add the ability to have a shift go across days like in (opsdev/sandbox/lock.php--there
**    is a shift I create there automatically, it starts off white but if you scroll it will
**    change the shift type and the color, click on it and drag it past midnight, you will
**    see what type of functionality I am talking about) *This is a big project
**
** Just some thoughts on what you might want to do to improve the app. XD ~Mika
**
****/

// Maybe one day I will get around to implementing the history feature so you can go back to whatever point you want to in your edits (at least for in between saves)
var historyArray = new Array();

// Get the timezone offset in a nice format
var tzOffset = new Date().getTimezoneOffset();
tzOffset = ((tzOffset<0? '+':'-')+ pad(parseInt(Math.abs(tzOffset/60)), 2)+ pad(Math.abs(tzOffset%60), 2));

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
    { // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    { // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", 'getMinimumShiftLength.php', false);
    xmlhttp.send();
    areaInfo = JSON.parse(xmlhttp.responseText);
}
getAreaInfo();

var areaEmployees = false;

function getAreaEmployees()
{
	var XMLHTTP;
	
	if(window.XMLHttpRequest)
	{
		XMLHTTP = new XMLHttpRequest();
	}
	else
	{
		XMLHTTP = new ActiveXObject("Microsoft.XMLHTTP");
	}

	XMLHTTP.open("GET", 'returnAreaEmployees.php', false);
	XMLHTTP.send();

	areaEmployees = JSON.parse(XMLHTTP.responseText);
}

getAreaEmployees();

// How many pixels wide are the labels for the hours
var hourLabel = 80;

// How tall the Day Labels' section should be
var dayLabel = 25;

// The timeProportionRatio is used to adjust how many pixels tall an hour is and in determining how many and where the "half-hour" and "hour" line markers on the schedule
var timeProportionRatio = parseFloat(areaInfo['hourSize']);
if (timeProportionRatio < .5)
{
    timeProportionRatio = .5;
}

// How many pixels tall is an hour according to the yMax and dayLabel values, should be default of 30px
// but increases if the timeProportionRatio is less than 1 and decreases if the timeProportionRatio is greater than 1
var hour = 30 / timeProportionRatio;

// Set size of schedule canvas, raphael paper object
var xMax = 900;
var numberOfWorkHours = (areaInfo['endTime'] - areaInfo['startTime']) + parseFloat(areaInfo['hourSize']);
var yMax = (numberOfWorkHours * hour) + (dayLabel * 2);

// Determine which increments are allowed for scheduling, e.g. 1 min, 15 mins, a half hour, 2 hours, etc.
var sizeToSnapToHour = areaInfo['hourSize'] * hour;

// Get the number of days that should be allowed for scheduling
var numberOfSchedulableDays = (areaInfo['endDay'] - areaInfo['startDay']);
if (numberOfSchedulableDays < 0)
{
    numberOfSchedulableDays += 7;
}
// This is because the above calculations are really for the difference between end and start days for the week
// (i.e. the above will return 0 if the start day is Friday and the endDay is Friday, but we need to add one since Friday is still a schedulable day)
numberOfSchedulableDays += 1;

// How many pixels wide is a day depending on the xMax and Hour Labels values
var day = (xMax - (hourLabel * 2)) / numberOfSchedulableDays;

// How many milliseconds are there in an hour, this is used to determine the new start and end dates of a shift that has been moved
var millisecondsPerHour = 3600000;

// Get the Saturday of this fiscal week. This is used to get all the shifts during the 7 days from this Saturday.
// Remember to set the jQuery Date Picker to this by default
var currentWeek = getStartOfWeek(parseDate(document.getElementById('date').value, '00:00:00:00'));

// This just returns the browser being used, I use it for css issues
function detectBrowser()
{
    function testCSS(prop)
    {
        return prop in document.documentElement.style;
    }

    // FF 0.8+
    var isFirefox = testCSS('MozBoxSizing');
    if (isFirefox)
        return "Firefox";

    // At least Safari 3+: "[object HTMLElementConstructor]"
    var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
    if (isSafari)
        return "Safari";

    // Chrome 1+
    var isChrome = !isSafari && testCSS('WebkitTransform');
    if (isChrome)
        return "Chrome";

    // Opera 8.0+
    var isOpera = !!(window.opera && window.opera.version);
    if (isOpera)
        return "Opera";

    // At least IE6
    var isIE =  /*@cc_on!@*/false || testCSS('msTransform');
    if (isIE)
        return "IE";
}

// Used to nicely format timezone offset
function pad(number, length)
{
    var str = "" + number;
    while (str.length < length)
    {
        str = '0'+str;
    }
    return str;
}

// This takes two parameters, one for a date and one with a string in sql dateTime format
// It will return a javascript date object of the date and time passed in
function parseDate(dateString, timeString)
{
    if (!timeString)
    {
        timeString = '00:00:00';
    }
    else
    {
        timeString = timeString.substr(0,8);
    }
    //var date = new Date(Date.parse(dateString+'T'+timeString+tzOffset));
    // Milliseconds are always set to 0
    var date = new Date(dateString.substr(0,4),dateString.substr(5,2)-1,dateString.substr(8,2),timeString.substr(0,2),timeString.substr(3,2),timeString.substr(6,2));
    date.setMilliseconds(0);
    return date;
}


// The remainder is being calculated this way because javascripts division and modulus (%)
// operators at times give conflicting results with radical division
function remainder(number1, number2)
{
    var division = number1 / number2;
    var remainder = number1 - (Math.floor(division) * number2);
    return remainder;
}

// This takes a date object and will return the Saturday of the associated fiscal week (fiscal week starts on Saturday and ends on Friday)
function getStartOfWeek(weekDay)
{
    // I first create a duplicate of the date object passed in to manipulate until the Saturday of the fiscal week has been found
    var startOfWeek = new Date(weekDay.getTime());

    // I convert from our day of week system to javascripts's here (i.e. from Saturday=0, Sunday=1, ... to Sunday=0, Monday=1, ...)
    var jsStartDayOfWeek = areaInfo['startDay'] - 1;
    if (jsStartDayOfWeek == -1)
    {
        jsStartDayOfWeek = 6;
    }

    // If the date object is not Saturday then subtract a day from it and check if that day is Saturday
    if (startOfWeek.getDay() != jsStartDayOfWeek)
    {
        startOfWeek.setDate(startOfWeek.getDate() - 1);
        startOfWeek = getStartOfWeek(startOfWeek);
    }

    // Once this function has called itself recursively enough to get the associated Saturday return the Saturday date object
    return startOfWeek;
}

// Does the user have schedule edit permissions
var permissionToEdit = false;
function getPermissions()
{
    var xmlhttp;
    var activeVal = 1;

    if (window.XMLHttpRequest)
    { // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    { // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", 'checkPermissionToEdit.php', false);
    xmlhttp.send();
    permissionToEdit = xmlhttp.responseText;
}
getPermissions();

// Retrieve all shift types from the database (this is used in case someone gets assigned a shift from another area)
var allShiftTypes;

// This is used to store only those shifts that are for the current area
var shiftTypes = {};

// This is used to keep track of which shift type is currently selected and therefore determines the shift type of a new shift
var currentShiftType;

function setShiftType(newShiftType)
{
    currentShiftType = newShiftType['value'];
}


// Pull info for periods for default shifts
periods = [];
function getPeriods()
{
	if (!$('#period option').length)
	{
		var page = '../newSchedule/returnPeriods.php';
		var cb = function(result)
		{
			periods = JSON.parse(result);

			if (periods.length)
			{
				$('#period').html('');
				var selectedValue = '';
				for (var i = 0, length = periods.length; i < length; i++)
				{
				periods[i].start = parseDate(periods[i].startDate);
				periods[i].end = parseDate(periods[i].endDate);
				if (periods[i].start <= currentWeek && currentWeek <= periods[i].end)
					selectedValue = periods[i].ID;
				$('<option />', {value: periods[i].ID,text: periods[i].name}).appendTo($('#period'));
				}
				if ( $('#passedPeriod').length )
				{
				selectedValue = $('#passedPeriod').val();
				}
				$('#period>option[value="' + selectedValue + '"]').prop('selected', true);
				if (defaultMode)
				{
				$('#weekOrPeriod').html($('#period option:selected').html());
				}
			}
		};

		callPhpPage(page, cb);
	}
}

// Violently return to view/trade mode, we don't have permission to edit
function forceViewMode()
{
    var editing = parseInt($('#viewMode :radio:checked').val());
    $("#trade").attr("checked", "checked");
    $("#viewMode").buttonset("refresh");
    notify('You do not have rights to edit this schedule.<br />Forcing view-only mode.', {'status': 'warning', 'duration': 5000});
}

function periodLock()
{
    var editing = parseInt($('#viewMode :radio:checked').val());
    var isDefault = parseInt($("#weeklyDefault :radio:checked").val());

    // Are we even trying to edit the schedule? If not then yes we have view-rights, otherwise do all the junk below to determine if we should be able to edit the schedule.
    // I guess also that if we have scheduling permissions then yes we can also edit.
    if (editing && !permissionToEdit)
    {
	// If we are trying to edit the schedule, check what mode we are trying to edit in, is it the weekly schedule or the default?
	var isUsersSchedule = user == $('#employee').val();
	if (!isDefault)
	{
		// If we are dealing with the weekly schedule then determine if the current area allows employees to edit the weekly schedule
		var canEditWeekly = areaInfo.canEmployeesEditWeeklySchedule == '1'? true: false;
		// Make sure the employee is trying to edit their won schedule and no one else's if they do not have scheduling permissions.
		if (!canEditWeekly || !isUsersSchedule)
		{
			// If the area does not allow editing the weekly schedule or the user is trying to edit a schedule other than their own reject them and force them to return to the view/trade mode
			forceViewMode();
			return false;
		}
	}
	else
	{
		// If the user is trying to edit the default schedule then determine if the period has been locked and respond accordingly.
		var currentPeriod = $('#period').val();

		for (i in periods)
		{
			var period = periods[i];
			if (period.ID == currentPeriod)
			{
				// This period is locked or not?
				var isLocked = period.locked == '1'? true : false;
				if (isLocked)
				{
					forceViewMode();
					return false;
				} // If the period is unlocked  but we are trying to edit someone else's schedule then we still reject them
				else if (!isUsersSchedule)
				{
					forceViewMode();
					return false;
				}
			}
		}
	}
    }
	else if(isDefault)
	{
		// Check if this is the selected employee's default area
		for(var i = 0; i < areaEmployees.length; i++)
		{
			if(areaEmployees[i].netID == $("#employee").val())
			{
				if(areaEmployees[i].area != areaInfo.ID)
				{
					forceViewMode();
					return false;
				}
			}
		}
	}

    return true;
}

function changeDate(date)
{
    // Unlock schedule for current week before changing to the new date
    eve('schedule.lock', this, 0);
    var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
    if (confirmed)
    {
        var newDate = parseDate(date.value);
        currentWeek = getStartOfWeek(newDate);
	periodLock();
        employeeScheduleOnload();
    }
}

function changePeriod(period)
{
    // Unlock schedule for current period before changing to the next user selected period
    eve('schedule.lock', this, 0);
    var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
    if (confirmed)
    {
	periodLock();
        employeeScheduleOnload();
    }
}

function changeView()
{
    var editing = parseInt($('#viewMode :radio:checked').val());
    // Unlock or lock schedule depending on whether we are going to be editing or not
    eve('schedule.lock', this, editing);
    var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
    if (confirmed)
    {
	periodLock();
        employeeScheduleOnload();
    }
}

function changeEmployee()
{
    // Unlock schedule for current employee before changing to the new employee
    eve('schedule.lock', this, 0);
    var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
    if (confirmed)
    {
	periodLock();
        employeeScheduleOnload();
    }
}

function toggleDefault()
{
    // Unlock schedule for current week or period before changing to either default or weekly mode
    eve('schedule.lock', this, 0);
    var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
    if (confirmed)
    {
	periodLock();
        employeeScheduleOnload();
    }
}


var timeoutsArray = new Array();
// This function is used to refresh the raphael paper. This is not the most efficient way to do things
// I am using it this way because I am removing all objects off the raphael paper object using the paper.clear() function
// A more efficient way to do this would be to find some way off really completely removing the raphael shift objects and then
// running through the arrays with those objects and getting rid of them and then calling the getShifts function.
// The above method would also require keeping an array (or in other words a set) of the Day Labels and updating their text
// Another thing you could do to optimize things is only define certain functions within the editMode and defaultMode if's
function employeeScheduleOnload()
{
    getUser();
    // Kill any previous event listeners
    eve.off();
    for (i in timeoutsArray)
    {
        clearInterval(timeoutsArray[i]);
    }
    // Kill any older Raphael papers that might be left over
    $('#employeeScheduleContainer').html('');

    // This is a function expanded onto the raphael base to determine if a raphael object is currently visible or not
    Raphael.el.isVisible = function()
    {
        return (this.node.style.display !== "none");
    }

    // Listen for calls to employeeScheduleOnLoad
    eve.on('schedule.onload', checkForDirt);
    // This next function is specifically to handle unload events and determine whether there are dirty shifts or not
    window.onbeforeunload = function()
    {
        // Unlock employee's schedule for current week or period before leaving the page -- hopefully this gets off in time before the unload event is completed
        eve('schedule.lock', this, 0);
        // This won't create a confirm dialog like it normally would
        // during the onbeforeunload event all other confirms return false
        // I.e. if a dirty shift or trade exists confirmed will be false, otherwise it will return true
        var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
        // This checks to determine if there was a dirty trade/shift or not.
        // If there was it returns the string to be shown in the onbeforeunload confirm dialog box
        if (!confirmed)
            return 'You have unsaved changes!\nDo you want to abandon your changes and continue?';
    }
    function checkForDirt(pushed)
    {
        pushed = pushed['pushed'];
        if (!pushed)
        {
            var dirty = false;
            var confirmed = true;
            if (editMode)
            {
                // Forget all the setting for dirty and just mark everything dirty unless it would not be sent to the database for changes
                // TODO Refactor code to just use this method of determining if we are in a dirty state, i.e. eliminate all other places where dirty is being set.
                allShifts.forEach(function(currentShift)
                {
                    currentShift.data('dirty', true);
	                if (!(!currentShift.data('shiftId') && currentShift.isVisible()) && currentShift.data('originalStart') && currentShift.data('originalEnd') && currentShift.data('originalHourType') && currentShift.data('originalStart').getTime() == currentShift.data('start').getTime() && currentShift.data('originalEnd').getTime() == currentShift.data('end').getTime() && currentShift.data('originalHourType') == currentShift.data('hourType'))
	                {
	                    currentShift.data('dirty', false);
	                }
	                
	                if ( (!currentShift.isVisible() && currentShift.data('shiftId')) || (currentShift.isVisible() && currentShift.data('dirty')) || (currentShift.isVisible() && currentShift.data('shiftId') && currentShift.data('dirty')) )
	                {
                        dirty = true;
	                }
                });
            }
            else
            {
                allTrades.forEach(function(trade)
                {
                    trade.data('dirty', true);
                    if (!(!trade.data('ID') && trade.isVisible()) && trade.data('originalStart') && trade.data('originalEnd') && trade.data('originalHourType') && trade.data('originalStart').getTime() == trade.data('start').getTime() && trade.data('originalEnd').getTime() == trade.data('end').getTime() && trade.data('originalHourType') == trade.data('hourType') && trade.data('originalNotes') == trade.data('notes'))
                    {
                        trade.data('dirty', false);
                    }

                    // Determine which category the shift falls into (i.e. To-be-Deleted, To-be-Updated, or To-be-Created)
                    if ( (!trade.isVisible() && trade.data('ID')) || (trade.isVisible() && trade.data('ID') && trade.data('dirty')) || (trade.isVisible() && trade.data('dirty')) )
                    {
                        dirty = true;
                    }
                });
            }
            if (dirty)
                confirmed = confirm('You have unsaved changes!\nDo you want to abandon your changes and continue?');
            if (!confirmed)
            {
                eve.stop();
                return false;
            }
        }
        return true;
    }

    // Get shift types to later associate them with shifts as necessary and populate drop-down menus
    getShiftTypes();
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
                if (!parseInt(window.shiftTypes[shiftType]['deleted']) && (permissionToEdit || (parseInt(window.shiftTypes[shiftType]['selfSchedulable']) && user == $('#employee').val())))
                    $('<option />', {value: window.shiftTypes[shiftType].ID,text: window.shiftTypes[shiftType].longName}).appendTo($('.shiftTypeSelector'));
            }
            $('#shiftType').val(currentShiftType);
        //  console.log(shiftTypes);
        };
        callPhpPage(page, cb);
    }
    
    // Load period info -- this only really does something the first time it is called. It can be modified later to ensure that if switching to default from weekly the selected period is the closest one to the current week.
    // This call is made here because we need to ensure that jQuery has already been defined before calling the function. (Once things are refactored so that things that need to be reloaded all the time are separated from
    // the things that should only be loaded once on the page load then this call won't be a problem and won't be getting called needlessly. -- Good luck to the person who finally ends up refactoring it)
    getPeriods();


    browser = detectBrowser();

    // This decreases the size of the View, edit, (trade??) buttons
    $('#viewMode, #weeklyDefault').buttonset().css('font-size', '0.70em');

    // These variables are used to determine whether the schedule should be editable or view-only
    // (i.e. if the shifts and canvas are clickable) and whether to load the default or weekly schedule
    editMode = parseInt($("#viewMode :radio:checked").val());
    defaultMode = parseInt($("#weeklyDefault :radio:checked").val());

	// This array is used for tracking all the Keyboard shortcuts available and what they do
	// You should push objects in the following format {"keys":"Alt+S", "description": "Submit changes to the database."}
	var keyboardShortcutArray = new Array();
    // Keyboard functionality
    $(window).keydown(function(event)
    {
    	// Push key combo to the array so that later we can create a nifty little keyboard shortcut popup telling us what the combination is and what the functionality is
        keyboardShortcutArray.push({"keys":"Alt+S", "description": "Submit changes to the database."});
        if (event.altKey && event.keyCode == 83)
        {
            submitSchedule();
            event.preventDefault();
        }
        // If you hit the delete key when currentlySelectedShift is defined it will delete that shift
        keyboardShortcutArray.push({"keys":"Delete", "description": "If you hit the delete key the currently selected shift will be deleted. If no shift is selected it will do nothing."});
        if (event.keyCode == 46)
        {
            if ( currentlySelectedShift )
            {
                // This is ugly and dirty bad code. I am pretending that we are deleting this shift through the shiftPopup dialog (i.e. as if I had deleted it from the shift pop-up dialog box)
                $shiftPopup['shiftObject'] = currentlySelectedShift;
                shiftPopupCallBack('Delete');
                event.preventDefault();
            }
        }

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
    dateFormat.masks.timeOnly = 'HH:MM';
    dateFormat.masks.dayOnly = 'D';
    // This is used for the labels of each of the days of the week
    if (defaultMode)
        dateFormat.masks.dayLabels = 'dddd';
    else
        dateFormat.masks.dayLabels = 'dddd mmm dS';
    // This is used for showing trade shift data
    dateFormat.masks.shiftDay = 'ddd mmm dS';
    // This is used for the timeEntry inputs that let your scroll to change the hour/minute
    dateFormat.masks.timeEntry = 'hh:MMTT';
    // This is used for the hourLabels
    dateFormat.masks.hourLabels = 'h:MM tt';
    // This is used to compare the start and end date objects to the start/end date and time received from the database
    // This might be used to avoid writing the same information back to the database, but would require keeping track of the initial shiftType as well
    dateFormat.masks.compare = 'yyyy-mm-dd HH:MM:ss';

    // This is used to ensure that two people are not editing the same person's schedule at the same time for the same week/period
    function lockSchedule(locking)
    {
        var currentPeriodOrDate = currentWeek.format('dateOnly');
        if (defaultMode)
        {
            currentPeriodOrDate = $('#period option:selected').val();
        }
        var page = 'lock.php?period=' + currentPeriodOrDate + '&employee=' + $('#employee').val() + '&lock=' + locking + '&lockedBy=' + user;

        var cb = function(result)
        {
			result = JSON.parse(result);
			
			if (result.status == 'locked') {
				if (result.lockedBy == user) {
					// If a notification does not already exist (was not already shown) then give a notification showing locked status
					if (!$('.operationStatus').filter(function(){return this.innerHTML == 'Lock granted for editing schedule.'}).length)
                    	notify('Lock granted for editing schedule.', {'duration': 5000});
				} else if (locking == 1) { // If we were trying to lock the schedule and it is locked by someone else
					var msg = 'This schedule is already locked by ' + result.lockedBy + '.<br />If inactive his/her lock will expire in ' + (Math.round(result.ttl / 60)) + ' minutes.';
					// If a notification has not already been shown, show it. 
					if (!$('.operationStatus').filter(function(){return this.innerHTML == msg}).length)
                    	notify(msg, {'status': 'failure', 'duration':10000});
				}
			}
            
        };
        callPhpPage(page, cb);
    }

    // Listen for any changes to the lock status of the schedule for this employee for this period/week
    eve.on('schedule.lock', lockSchedule)

    // Unlock or lock schedule for current period depending on whether we are editing or not
    // this is kinda a backup in case I missed any of the events that would lead to either a lock or an unlock
    // I'm not sure if we would need it or not
    eve('schedule.lock', this, editMode);

    // Change page title to reflect the current schedule being viewed
    var employeeName = $('#employee option:selected').html();
    $('#titleEmployeeName').html(employeeName);
    if (defaultMode)
    {
        $('#weeklyOrDefault').html('Default');
        $('#weekOrPeriod').html($('#period option:selected').html());
    }
    else
    {
        $('#weeklyOrDefault').html('Weekly');
        $('#weekOrPeriod').html(currentWeek.format('dateOnly'));
    }
    $('#iCalLink').off('click').click(function()
    {
        if(!document.getElementById('iCalDialog'))
        {
           var origin = window.location.origin;
           if (!origin)
           {
               origin = window.location.protocol + '//' + window.location.host;
           }
           var innerHTML = '<div id="iCalDialog"><br /><b>iCalendar feed URL for ' + employeeName + ':</b><br /><input id="URL" class="iCalUrl" type="text" value="' + origin + '/newSchedule/icalFeed.php?netId=' + $('#employee option:selected').val() + '" />';
           innerHTML += '<br /><b>Select which shifts NOT to include in this feed:</b><br />';
           for(var shiftType in shiftTypes)
           {
              innerHTML += '<div class="icalExcluded"><input id="shiftType' + shiftType + '" name="shiftType" value="' + shiftType + '" type="checkbox" class="iCalShiftTypeCheckBox" /><label class="iCalShiftLabel" for="shiftType' + shiftType + '"> ' + shiftTypes[shiftType].name + ' </label></div>';
           }
           innerHTML += '</div>'; 
           $(innerHTML).dialog(
           {
               autoOpen: true,
               width: '550',
               height: 'auto',
               modal: false,
               title: 'iCal Feed',
               draggable: true,
               resizable: true,
               close: function(event,  ui) {$(this).remove();}
           });
           var checked = new Array();
           $('.iCalShiftTypeCheckBox').click(function()
           {
             if($(this).prop('checked'))
             {
             	var currentValue = $('#URL').val();
             	var urlWithoutExcluded = currentValue.replace('&excluded='+checked.join(','), '');
                checked.push(this.value);
                var newUrl = urlWithoutExcluded+'&excluded='+checked.join(',');
                $('#URL').val(newUrl);
             }
             else
             {
             	var index = $.inArray(this.id.replace('shiftType', ''), checked);
             	if ( -1 != index )
             	{
             		var currentValue = $('#URL').val();
             		var urlWithoutExcluded = currentValue.replace('&excluded='+checked.join(','), '');
             		checked.splice(index, 1);
             		var newUrl = urlWithoutExcluded+'&excluded='+checked.join(',');
             		if ( checked.length )
                	{
                		$('#URL').val(newUrl);
                	}
                	else
                	{
                		$('#URL').val(urlWithoutExcluded);
                	}
             	}
             }
           });
        }
    });

    // Hide shiftType dropdown if not in edit mode
    if (editMode)
        $('#shiftTypeSelector').show();
    else
        $('#shiftTypeSelector').hide();

    // Change datePicker for Period Dropdown if in default mode
    if (defaultMode)
    {
        $('#dateDiv').hide();
        $('#periodDiv').show();
        // Allowing people to see Schedule Notes in both Weekly and Default modes
        //$('#schedulingNotes').show();
        $('#hoursRequested,#hoursRegistered,#periodNotes').prop('disabled',false);
    }
    else
    {
        $('#dateDiv').show();
        $('#periodDiv').hide();
        // Allowing people to see Schedule Notes in both Weekly and Default modes
        //$('#schedulingNotes').hide();
        $('#hoursRequested,#hoursRegistered,#periodNotes').prop('disabled',true);
    }

    //
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

        var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
        if (confirmed)
        {
            // Unlock schedule for current week or period
            eve('schedule.lock', this, 0);

            // Get today's date and it's related Start of week then load that
            var newWeek = new Date();
            newWeek.setHours(0);
            newWeek.setMinutes(0);
            newWeek.setSeconds(0);
            newWeek.setMilliseconds(0);
            currentWeek.setTime(getStartOfWeek(newWeek));
            paper.clear();
            employeeScheduleOnload();
        }
    });

    // Get a date with time values for the min overall start dateTime
	var minAreaStart = new Date(currentWeek.getTime());
	minAreaStart.setHours(parseInt(areaInfo['startTime']));
	minAreaStart.setMinutes(60 * (areaInfo['startTime'] - parseInt(areaInfo['startTime'])));
	
	// Set the latest day and time people should be allowed to work (sometimes this will include the fist instance of the next day--i.e. midnight of next week's Saturday)
	var maxAreaEnd = new Date(currentWeek.getTime());
	maxAreaEnd.setDate(maxAreaEnd.getDate() + numberOfSchedulableDays - 1);
	maxAreaEnd.setHours(parseInt(areaInfo['endTime']));
	maxAreaEnd.setMinutes(60 * ((parseFloat(areaInfo['endTime']) + parseFloat(areaInfo['hourSize'])) - parseInt(areaInfo['endTime'])));
	
    // Make date pickers
    $('.myDatePicker').datepicker({'dateFormat': 'yy-mm-dd',firstDay: minAreaStart.getDay(),showOn: 'both',buttonImage: '../includes/libs/img/cal.gif',buttonImageOnly: true});

    $('#date').datepicker('option', {changeYear: true});

    // Set valid start and end dates for datePickers
    $('#popupStartDate').datepicker('option', 'minDate', minAreaStart);
    $('#popupStartDate').datepicker('option', 'maxDate', maxAreaEnd);
    $('#popupEndDate').datepicker('option', 'minDate', minAreaStart);
    $('#popupEndDate').datepicker('option', 'maxDate', maxAreaEnd);
    $('#popupStartDate, #popupEndDate').change(function()
    {
        // Later anyone who wants to can change this so that cross day shifts are possible
        var other = this.id == "popupStartDate" ? "#popupEndDate" : "#popupStartDate";

        var diff = $('#popupEndDate').datepicker('getDate') - $('#popupStartDate').datepicker('getDate');
        if (diff == 86400000 && this.id == 'popupEndDate')
        {
            $('#popupEndTime').timeEntry('setTime', '12:00AM');
        }
        else if (diff > 86400000 || $('#popupStartDate').datepicker('getDate') > $('#popupEndDate').datepicker('getDate'))
        {
            $(other).datepicker('setDate', $(this).datepicker('getDate'));
        }

        // Correct timeEntry Values if necessary as well
        var popupEnd = parseDate($('#popupEndDate').datepicker('getDate').format('dateOnly'), $('#popupEndTime').timeEntry('getTime').format('timeOnly')+':00');
        var popupStart = parseDate($('#popupStartDate').datepicker('getDate').format('dateOnly'), $('#popupStartTime').timeEntry('getTime').format('timeOnly')+':00');
        // Allow for only 12:00 AM on next week's Saturday to be accepted as a valid date and time.
        /*if ( popupEnd > endOfWeek )
      {
         popupEnd.setTime(endOfWeek.getTime());
         popupStart.setTime(endOfWeek.getTime()-3600000);
         //$('#popupStartTime').timeEntry('setTime', new Date(popupStart.getTime()));
         //$('#popupEndTime').timeEntry('setTime', new Date(popupEnd.getTime()));
      }*/
        if (popupStart >= popupEnd)
        {
            $('#popupEndTime').timeEntry('setTime', new Date(popupStart.setMinutes(popupStart.getMinutes() + ((sizeToSnapToHour / hour) * 60))));
        }
    });


    // Set min and max startTime and endTime for the timeEntry spinners, also set the schedulable interval
    // If the minStart Time and the maxEnd Time is the same then allow any times
    var minStartTime = minAreaStart.format('timeEntry');
    var maxEndTime = maxAreaEnd.format('timeEntry');
    if (minStartTime == maxEndTime)
    {
        minStartTime = null;
        maxEndTime = null;
    }
    $('.myTimeEntry').timeEntry({useMouseWheel: true,minTime: minStartTime,maxTime: maxEndTime,timeSteps: [1, 60 * (sizeToSnapToHour / hour), 0]});
    $('#popupStartTime').timeEntry('option', {beforeSetTime: startRange});
    $('#popupEndTime').timeEntry('option', {beforeSetTime: endRange});
    function startRange(oldTime, newTime, minTime, maxTime)
    {
        // FIXME This still doesn't handle changing the starttime to 11 PM and setting the endDate to the next day and then setting the end time to 12:00AM
        var start = parseDate($('#popupStartDate').datepicker('getDate').format('dateOnly'), newTime.format('timeOnly')+':00');
        var end = parseDate($('#popupEndDate').datepicker('getDate').format('dateOnly'), $('#popupEndTime').timeEntry('getTime').format('timeOnly')+':00');

        // If start is greater than or equal to the end set the end to be 1 hour greater than the start
        if (start.getTime() == end.getTime() && start.getHours() + start.getMinutes() == 0)
            return newTime;
        else if (start >= end)
        {
            var newEnd = new Date(start.setMinutes(start.getMinutes() + ((sizeToSnapToHour / hour) * 60)));
            // If the newEnd will set the time to 12:00AM we need to set the end date to be the next day
            if (newEnd.getHours() + newEnd.getMinutes() == 0)
            {
                $('#popupEndDate').datepicker('setDate', new Date(end.setDate(end.getDate() + 1)));
            }
            try
            {
                $('#popupEndTime').timeEntry('setTime', newEnd);
            }
            catch (e)
            {
                console.log(" -- startRange -- ");
                console.log("Error:");
            }
        }
        return newTime;
    }
    function endRange(oldTime, newTime, minTime, maxTime)
    {
        var endDatePicker = $('#popupEndDate').datepicker('getDate');
        var startDatePicker = $('#popupStartDate').datepicker('getDate');
        var end = parseDate(endDatePicker.format('dateOnly'), newTime.format('timeOnly')+':00');
        var start = parseDate(startDatePicker.format('dateOnly'), $('#popupStartTime').timeEntry('getTime').format('timeOnly')+':00');

        // If the endDate is a day after the startDate then the only time allowed is 12:00AM
        if (endDatePicker > startDatePicker)
        {
            newTime = new Date(endDatePicker.getTime());
        }

        // If the start date and end date are the same and the end time is 12:00AM then add an
        // hour to the end time so that the start time can be moved to the 12:00AM position
        if (end.getDay() == start.getDay() && end.getHours() == 0 && end.getMinutes() == 0)
        {
            try
            {
                $('#popupStartTime').timeEntry('setTime', new Date(end.getTime()));
            }
            catch (e)
            {
                console.log(" -- endRange -- ");
                console.log("Error:");
            }
            newTime.setMinutes(newTime.getMinutes() + ((sizeToSnapToHour / hour) * 60));
        }
        else if (start >= end)
        {
            try
            {
                $('#popupStartTime').timeEntry('setTime', new Date(end.setMinutes(end.getMinutes() - ((sizeToSnapToHour / hour) * 60))));
            }
            catch (e)
            {
                console.log(" -- endRange -- ");
                console.log("Error:");
            }
        }
        return newTime;
    }

    // Set containing div size to the desired xMax and yMax values
    $('#employeeScheduleContainer').width(xMax);
    $('#employeeScheduleContainer').height(yMax);

    // Create raphael paper object
    var paper = Raphael(document.getElementById("employeeScheduleContainer"), xMax, yMax);
    // For some reason in Chrome the entire containing SVG element gets rendered 1 pixel to the left, I use this to correct for that
    // Apparently the weirdness of the svg element being a pixel off is not an issue in our iframes
    if ( (browser == 'Chrome' || browser == 'IE') && window.top == window.self )
    {
    	$('#employeeScheduleContainer>svg').css('margin-left', 1)
    }

    // Holds all the raphael shift objects
    var allShifts = paper.set();
    // Keeps track of which shifts need to be added to the database
    var allNewShifts = paper.set();
    // Keeps track of which shifts need to be updated
    var allEditedShifts = paper.set();
    // Keeps track of which shifts need to be deleted
    var allDeletedShifts = paper.set();
    // Create jQuery UI popup dialog for editing shifts
    var $shiftPopup = $('#shiftPopupDialog')
    .dialog({
        autoOpen: false,
        width: '400',
        height: 'auto',
        modal: true,
        title: 'Shift Info',
        draggable: true,
        resizable: false,
        buttons:
        {
            "Delete": function()
            {
                shiftPopupCallBack('Delete');
                $(this).dialog('close');
            },
            "Ok": function()
            {
                shiftPopupCallBack('Ok');
                $(this).dialog('close');
            },
            "Cancel": function()
            {
                shiftPopupCallBack('Cancel');
                $(this).dialog('close');
            }
        }
    });

    function shiftPopupCallBack(action)
    {
        if (action == 'Ok')
        {
            // Renew our lock on the schedule since we are actively editing it
            eve('schedule.lock', this, 1);
            $shiftPopup.shiftObject.ox = $shiftPopup.shiftObject.attr('x');
            $shiftPopup.shiftObject.oy = $shiftPopup.shiftObject.attr('y');
            $shiftPopup.shiftObject.data('deleteOnCancel', false);
            var newStart = parseDate($('#popupStartDate').val(), $('#popupStartTime').timeEntry('getTime').format('timeOnly')+':00');
            $shiftPopup.shiftObject.data('start', newStart);
            var newEnd = parseDate($('#popupEndDate').val(), $('#popupEndTime').timeEntry('getTime').format('timeOnly')+':00');
            $shiftPopup.shiftObject.data('end', newEnd);
            $shiftPopup.shiftObject.attr('fill', window.shiftTypes[$('#popupShiftType').val()].color);
            $shiftPopup.shiftObject.data('hourType', window.shiftTypes[$('#popupShiftType').val()]);
            resolveShiftConflicts($shiftPopup.shiftObject);
            drawShift($shiftPopup.shiftObject);
            $shiftPopup.shiftObject.data('dirty', true);
        }
        if (action == 'Cancel')
        {
            if ($shiftPopup.shiftObject.data('deleteOnCancel'))
            {
                shiftPopupCallBack('Delete');
            }
        }
        if (action == 'Delete')
        {
            // Renew our lock on the schedule since we are actively editing it
            eve('schedule.lock', this, 1);
            $shiftPopup.shiftObject.hide();
            $shiftPopup.shiftObject.data('label').hide();
            $shiftPopup.shiftObject.data('sliderStart').hide();
            $shiftPopup.shiftObject.data('sliderEnd').hide();
            allNewShifts.exclude($shiftPopup.shiftObject);
            allEditedShifts.exclude($shiftPopup.shiftObject);
            allDeletedShifts.push($shiftPopup.shiftObject);
        }
    }

    // Create jQuery UI popup dialog for editing trades
    var $tradePopup = $('#tradePopupDialog')
    .dialog({
        autoOpen: false,
        width: '400',
        height: 'auto',
        modal: false,
        title: 'Trade Info',
        draggable: true,
        resizable: false
    });

    // Vertical Lines for Separating Labels and Days
    for (i = 0; i < 1 + numberOfSchedulableDays; i++)
    {
        var x = hourLabel + day * i;
        paper.path('M' + x + ',0V' + (yMax - dayLabel)).attr('stroke', 'silver');
    }
    // Day Labels
    var currentDay = new Date(currentWeek.getTime());
    for (i = 0; i < numberOfSchedulableDays; i++)
    {
        var dayLabelText = '';
        dayLabelText = currentDay.format('dayLabels');
        currentDay.setDate(currentDay.getDate() + 1);
        var x = hourLabel + day / 2 + day * i;
        paper.text(x, dayLabel / 2, dayLabelText).attr('font-weight', 'bold');
    }

    // Horizontal Lines and Days' Labels
    // Half minShiftLengths or 30 minute intervals at the smallest
    for (i = parseFloat(areaInfo['startTime']); i < parseFloat(areaInfo['endTime']) + parseFloat(areaInfo['hourSize']); i += timeProportionRatio)
    {
        var y = dayLabel + hour / (2 / timeProportionRatio) + hour * (i - parseFloat(areaInfo['startTime']));
        // The line commented out below is for a line to be drawn marking the half "hour" (hour here referring to the variable) point
        //paper.path('M' + hourLabel + ',' + y + 'H' + (xMax - hourLabel)).attr('opacity', .5).attr('stroke', 'silver');

        //var hourString = hourNumber+':00 '+amOrPm;
        currentDay.setMinutes((i - parseInt(i)) * 60);
        currentDay.setHours(i);
        var hourString = currentDay.format('hourLabels');

        // Left Hour Labels
        paper.text(2 * hourLabel / 3, y, hourString).attr('font-weight', 'bold');
        // Right Hour Labels
        paper.text(xMax - (2 * hourLabel / 3), y, hourString).attr('font-weight', 'bold');
    }

    // Full minShiftLength markers
    for (i = parseFloat(areaInfo['startTime']); i < (parseFloat(areaInfo['endTime']) + 2 * parseFloat(areaInfo['hourSize'])); i += timeProportionRatio)
    {
        var y = dayLabel + hour * (i - parseFloat(areaInfo['startTime']));
        var xStart = hourLabel / 3;
        var xEnd = xMax - (hourLabel / 3);
        paper.path('M' + xStart + ',' + y + 'H' + xEnd).attr('stroke-width', 2).attr('stroke', 'silver');

    }

    // Previous Week Button
    var previous = paper.rect(0, dayLabel, hourLabel / 3, yMax - (dayLabel * 2)).attr({'fill': 'gray','opacity': .5,'stroke': 'darkgrey','stroke-opacity': 1,'stroke-width': 2,cursor: 'pointer'});
    previous.node.setAttribute('class','changeWeekButtonRectangle');
    // This is the previous week button's arrow
    var previousArrow = paper.path('M24.316,5.318,9.833,13.682,9.833,5.5,5.5,5.5,5.5,25.5,9.833,25.5,9.833,17.318,24.316,25.682z');
    previousArrow.attr({fill: 'darkgray',stroke: 'darkgray'});
    var arrowY = yMax / 2;
    previousArrow.transform('T-2,' + arrowY);
    previousArrow.toBack();

    // Next Week Button
    var next = previous.clone().attr({x: xMax - (hourLabel / 3)});
    next.node.setAttribute('class','changeWeekButtonRectangle');
    // The Next week button's arrow
    var nextArrowX = xMax - (hourLabel / 3) - 2;
    var nextArrow = previousArrow.clone().transform('T' + nextArrowX + ',' + arrowY + 'R180');
    nextArrow.toBack();

    previous.click(function()
    {
        var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
        if (confirmed)
        {
            if (defaultMode)
            {
                // Unlock schedule for current period
                eve('schedule.lock', this, 0);
                var newPeriod = $('#period option').index($('#period option:selected')) + 1;
                if (newPeriod == -1)
                    newPeriod = 0;
                $('#period option').eq(newPeriod).prop('selected', true);
            }
            else
            {
                // Unlock schedule for current period
                eve('schedule.lock', this, 0);
                var previousInterval = numberOfSchedulableDays;
                if (numberOfSchedulableDays < 7)
                {
                    previousInterval = 7;
                }
                currentWeek.setDate(currentWeek.getDate() - previousInterval);
            }
            paper.clear();
            employeeScheduleOnload();
        }
    });

    // Same as previous but currentWeek adds seven days instead of subtracting
    next.click(function()
    {
        var confirmed = eve('schedule.onload', this, {'pushed': false})[0];
        if (confirmed)
        {
            if (defaultMode)
            {
                // Unlock schedule for current period
                eve('schedule.lock', this, 0);
                var newPeriod = $('#period option').index($('#period option:selected')) - 1;
                if (newPeriod == -1)
                    newPeriod = 0;
                $('#period option').eq(newPeriod).prop('selected', true);
            }
            else
            {
                // Unlock schedule for current period
                eve('schedule.lock', this, 0);
                var nextInterval = numberOfSchedulableDays;
                if (numberOfSchedulableDays < 7)
                {
                    nextInterval = 7;
                }
                currentWeek.setDate(currentWeek.getDate() + nextInterval);
            }
            paper.clear();
            employeeScheduleOnload();
        }
    });

    // Background used for catching onclick event when clicking over an empty area
    canvas = paper.rect(hourLabel, dayLabel, xMax - 2 * hourLabel, yMax - 2 * dayLabel, 0).attr('opacity', 0).attr('fill', 'white');
    if (editMode)
    {
        canvas.attr('cursor', 'pointer')
        canvas.dblclick(createNewShift);
        canvas.drag(canvasDragMove, canvasDragStart, canvasDragEnd);
    }
    // Insert Else statement here for non-edit mode

    // On double click within the schedule area, create a new shift to the nearest snapable values
    function createNewShift(e, x, y)
    {
        // Determine X and Y coordinates of double click event relative to Raphael paper
        var newShiftX = x - $('#employeeScheduleContainer').offset().left;
        var newShiftY = y - $('#employeeScheduleContainer').offset().top;

        // Determine the number of days away from Saturday the double click event occurred
        var daysFromSaturday = Math.floor((newShiftX - hourLabel) / day);

        // Determine the Starting X and Y position for the newShift to be created
        newShiftX = hourLabel + (daysFromSaturday * day);
        newShiftY = dayLabel + (Math.floor((newShiftY - dayLabel) / sizeToSnapToHour) * sizeToSnapToHour);

        var shiftData = {};

        if (defaultMode)
        {
            shiftData['period'] = $('#period').val();
        }

        // Get the time of the current week object to use in creating new date objects for the start and end of the shift
        var currentWeekTime = currentWeek.getTime();

        // Create a start date based off of where the user double clicked to make a new shift
        var newStart = new Date(currentWeekTime);
        newStart.setDate(newStart.getDate() + daysFromSaturday);
        newStart.setHours(Math.floor(((newShiftY + (hour * parseFloat(areaInfo['startTime']))) - dayLabel) / hour));
        var startMinutes = Math.round(remainder(((newShiftY + (hour * parseFloat(areaInfo['startTime']))) - dayLabel), hour) * 60 / hour);
        newStart.setMinutes(startMinutes);

        // Create an end date based off of where the user double clicked to make a new shift
        var newEnd = new Date(currentWeekTime);
        newEnd.setDate(newEnd.getDate() + daysFromSaturday);
        newEnd.setHours(Math.floor(((newShiftY + (hour * parseFloat(areaInfo['startTime']))) + sizeToSnapToHour - dayLabel) / hour));
        var endMinutes = Math.round(remainder(((newShiftY + (hour * parseFloat(areaInfo['startTime']))) + sizeToSnapToHour - dayLabel), hour) * 60 / hour);
        newEnd.setMinutes(endMinutes);

        // Set newShift's data
        shiftData['employee'] = $('#employee').val();
        shiftData['hourType'] = $('#shiftType').val();
        shiftData['start'] = newStart;
        shiftData['end'] = newEnd;


        // Create new raphael shift object and set attributes
        var newShift = makeRaphaelShift(shiftData);

        // newShift.attr({x: newShiftX, y: newShiftY, height: sizeToSnapToHour, width: day});
        newShift.data('deleteOnCancel', true);

        newShift.data('label').attr({x: newShift.attr('x') + (day / 2),y: newShift.attr('y') + (hour * timeProportionRatio / 4)});
        newShift.data('label').attr('text', newShift.data('start').format('timeEntry') + '-' + newShift.data('end').format('timeEntry'));

        // These need to be remembered, don't forget you still need to create trading logic, ugh
        //        rect.data('posted', '');

        // Add to allNewShifts set
        allNewShifts.push(newShift);

        // Simulate a double click on the new shift to bring up the shiftPopup dialog
        var shiftEvent = document.createEvent("MouseEvents");
        shiftEvent.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
        newShift.data('label').node.dispatchEvent(shiftEvent);
    }

    function canvasDragStart(pageX, pageY, event)
    {
        // Determine X and Y coordinates of double click event relative to raphael paper
        var newShiftX = pageX - $('#employeeScheduleContainer').offset().left;
        var newShiftY = pageY - $('#employeeScheduleContainer').offset().top;

        // Determine the number of days away from Saturday the double click event occured
        var daysFromSaturday = Math.floor((newShiftX - hourLabel) / day);

        this.newShiftDaysFromSaturday = daysFromSaturday;

        // Determine the Starting X and Y position for the newShift to be created
        newShiftX = hourLabel + (daysFromSaturday * day);
        newShiftY = dayLabel + (Math.floor((newShiftY - dayLabel) / sizeToSnapToHour) * sizeToSnapToHour);

        this.shiftX = newShiftX;
        this.shiftY = newShiftY;
        this.newFlag = true;
    }

    function canvasDragMove(dx, dy, cursorX, cursorY, event)
    {
    	var shouldBeCreated = 4;
    	var delta = dy;
    	var newShiftY = cursorY - $('#employeeScheduleContainer').offset().top;
        var newHeight = Math.ceil((newShiftY-this.shiftY) / sizeToSnapToHour) * sizeToSnapToHour;
        // If delta is greater than or equal to should be created then the shift ought to be created--delta is just making sure we don't make shifts from accidental shifts
        if ( delta >= shouldBeCreated )
        {
        	if ( newHeight < sizeToSnapToHour )
        		newHeight = sizeToSnapToHour;
            if ( this.newFlag )
            {
                var shiftData = {};
                if ( defaultMode )
                {
                    shiftData['period'] = $('#period').val();
                }

                // Get the time of the current week object to use in creating new date objects for the start and end of the shift
                var currentWeekTime = currentWeek.getTime();

                // Create a start date based off of where the user double clicked to make a new shift
                var newStart = new Date(currentWeekTime);
                newStart.setDate(newStart.getDate() + this.newShiftDaysFromSaturday);
                newStart.setHours(Math.floor(((this.shiftY + (hour * parseFloat(areaInfo['startTime']))) - dayLabel) / hour));
                var startMinutes = Math.round(remainder(((this.shiftY + (hour * parseFloat(areaInfo['startTime']))) - dayLabel), hour) * 60 / hour);
                newStart.setMinutes(startMinutes);

                shiftData['start'] = newStart;
                shiftData['hourType'] = $('#shiftType').val();
                
                shiftData['end'] = new Date(getTimeFromPoint(this.shiftX, this.shiftY+newHeight+(hour * parseFloat(areaInfo['startTime']))));
                shiftData['employee'] = $('#employee').val();

                var newShift = makeRaphaelShift(shiftData);
                
                newShift.dataset({'originalStart': false, 'originalEnd': false});

                newShift.attr({x: this.shiftX,y: this.shiftY,height: newHeight,width: day});
                
                newShift.data('label').attr({x: newShift.attr('x') + (day / 2),y: newShift.attr('y') + (hour * timeProportionRatio / 4)});
	            newShift.data('label').attr('text', newShift.data('start').format('timeEntry') + '-' + newShift.data('end').format('timeEntry'));
	
	            var sliderStart = newShift.data('sliderStart');
	            if (sliderStart)
	            {
	                sliderStart.attr({x: newShift.attr('x'),y: newShift.attr('y')});
	            }
	
	            var sliderEnd = newShift.data('sliderEnd');
	            if (sliderEnd)
	            {
	                sliderEnd.attr({x: newShift.attr('x'),y: newShift.attr('y') + newShift.attr('height') - 2});
	            }

				allNewShifts.push(newShift);
                this.newShift = newShift;
                this.newFlag = false;
            }
            else
            {
            	var newShift = this.newShift;
                if (newHeight + newShift.attr('y') > yMax - dayLabel)
                    newHeight = (yMax - dayLabel) - newShift.attr('y');
                newShift.data('end', new Date(getTimeFromPoint(this.shiftX, this.shiftY+newHeight+(hour * parseFloat(areaInfo['startTime'])))));
                newShift.attr('height', newHeight);
                newShift.data('label').attr({x: newShift.attr('x') + (day / 2),y: newShift.attr('y') + (hour * timeProportionRatio / 4)});
	            newShift.data('label').attr('text', newShift.data('start').format('timeEntry') + '-' + newShift.data('end').format('timeEntry'));
	
	            var sliderStart = newShift.data('sliderStart');
	            if (sliderStart)
	            {
	                sliderStart.attr({x: newShift.attr('x'),y: newShift.attr('y')});
	            }
	
	            var sliderEnd = newShift.data('sliderEnd');
	            if (sliderEnd)
	            {
	                sliderEnd.attr({x: newShift.attr('x'),y: newShift.attr('y') + newShift.attr('height') - 2});
	            }
            }
        }
    }

    function canvasDragEnd()
    {
        var newShift = this.newShift;
        if (newShift)
        {
            var currentWeekTime = currentWeek.getTime();

            // Create an end date based off of where the user double clicked to make a new shift
            /*
             *  var newEnd = new Date(currentWeekTime);
	            newEnd.setDate(newEnd.getDate() + this.newShiftDaysFromSaturday);
	            newEnd.setHours(Math.floor(((newShift.attr('y') + newShift.attr('height') + (hour * parseFloat(areaInfo['startTime']))) - dayLabel) / hour));
	            var endMinutes = Math.round(remainder(((newShift.attr('y') + newShift.attr('height') + (hour * parseFloat(areaInfo['startTime']))) - dayLabel), hour) * 60 / hour);
	            newEnd.setMinutes(endMinutes);
	            newShift.data('end', newEnd);
             */

            resolveShiftConflicts(newShift);

            // Renew our lock on the schedule since we are actively editing it
            eve('schedule.lock', this, 1);

            this.newShift = undefined;
            this.newFlag = false;
            this.newShiftDaysFromSaturday = undefined;
            this.shiftX = undefined;
            this.shiftY = undefined;
        }
    }

    function copyRaphaelShift(shiftToCopy)
    {
        // This is a function that I created and added to OUR Raphael JS include
        // It doesn't exist anywhere else, so to use it just call the function with either an
        // object that has a set of new key:value pairings you would like to set or call it
        // with an array of keys you would like the values for. It returns an object with
        // key:value pairings or the object itself if you are setting the data.
        // If called with no parameter it returns an object of all the elements data key:value
        // pairings. See the source code I built our raphael-min.js from in
        // "includes/template/js/libs/raphael/raphael.core.js" if you have questions ask "keyneom122@hotmail.com"
        var duplicateShiftData = shiftToCopy.dataset(['shiftId', 'employee', 'area', 'start', 'end', 'hourType', 'duration', 'period']);
        duplicateShiftData['hourTotal'] = duplicateShiftData['duration'];
        duplicateShiftData['hourType'] = duplicateShiftData['hourType'].ID;
        duplicateShiftData['start'] = new Date(duplicateShiftData['start'].getTime());
        duplicateShiftData['end'] = new Date(duplicateShiftData['end'].getTime());
        return makeRaphaelShift(duplicateShiftData);
    }
    
	function combineShifts(shiftToCheck, conflictingShift)
	{
		var checkStart = shiftToCheck.data('start');
        var checkEnd = shiftToCheck.data('end');
        var conflictStart = conflictingShift.data('start');
        var conflictEnd = conflictingShift.data('end');
		// new start time for the combined shift is the earlier of checkStart and conflictStart 
    	checkStart.setTime(checkStart < conflictStart ? checkStart : conflictStart);
    	checkEnd.setTime(checkEnd > conflictEnd ? checkEnd : conflictEnd);
    	// This is ugly and dirty bad code. I am pretending that we are deleting this shift through the shiftPopup dialog (i.e. as if I had deleted it from the shift pop-up dialog box)
        $shiftPopup['shiftObject'] = conflictingShift;
        shiftPopupCallBack('Delete');
        drawShift(shiftToCheck);
        resolveShiftConflicts(shiftToCheck);
	}

    // You still need to catch the situation where the shiftToCheck has an end or start time equal
    // to the end or start time of the conflictingShift (respectively) and has a duration greater
    // than the conflictingShift
    function resolveShiftConflicts(shiftToCheck)
    {
        var checkStart = shiftToCheck.data('start');
        var checkEnd = shiftToCheck.data('end');
        allShifts.forEach(function(conflictingShift)
        {
            if (conflictingShift.isVisible())
            {
            	var isConflictEditable = false;
            	// Conflict is editable if the hourType is not from another area and (we have permission to edit or it is self-schedulable and it is our schedule)
            	if ( (conflictingShift.data('hourType') && shiftTypes[conflictingShift.data('hourType')['ID']]) && (permissionToEdit || (conflictingShift.data('hourType') && parseInt(conflictingShift.data('hourType')['selfSchedulable']) && user == $('#employee').val())) )
            	{
            		isConflictEditable = true;
            	}
                var conflictStart = conflictingShift.data('start');
                var conflictEnd = conflictingShift.data('end');
                
                var isConflict = false;
                // This determines if the two shifts actually conflict
                if ( shiftToCheck != conflictingShift && ((checkStart >= conflictStart && conflictEnd > checkStart) || (checkEnd > conflictStart && conflictEnd >= checkEnd) || (checkStart < conflictStart && checkEnd > conflictEnd)) )
                {
                	isConflict = true;
                }
                
                if ( isConflict )
                {
	                // If the conflict is not editable AND it actually conflicts with the change we are trying to make just refuse the change and return
	                var editableShift = function()
	                {
	                    if ( !isConflictEditable )
	                    {
	                        // 
	                        // This will need to change once we get endChild working
	                        checkStart.setTime(getTimeFromPoint(shiftToCheck.ox, shiftToCheck.oy + (parseFloat(areaInfo['startTime'] * hour)), true));
	                        checkEnd.setTime(getTimeFromPoint(shiftToCheck.ox, shiftToCheck.oy + shiftToCheck.attr('height') + (parseFloat(areaInfo['startTime'] * hour)), true));
	                        drawShift(shiftToCheck);
	                        return false;
	                    }
	                    return true;
	                }
	                
	                // If conflict is of the same shift type, they should be combined
	                if ( (conflictingShift.data('hourType')['ID'] == shiftToCheck.data('hourType')['ID']) )
	                {
	                	combineShifts(shiftToCheck, conflictingShift);
		                return true;
	                }
	                
	                // This catches any shift that would be engulfed by the current shift (i.e. shiftToCheck)
	                if ( conflictEnd < checkEnd && conflictStart > checkStart )
	                {
	                    // Do something here, like maybe create two shifts that surround the shift that is being covered
	                    // Or something else that would make sense and would be useful
	                    var copy = copyRaphaelShift(shiftToCheck);
	                    checkEnd.setTime(conflictStart.getTime());
	                    copy.data('start').setTime(conflictEnd.getTime());
	                    drawShift(shiftToCheck);
	                    drawShift(copy);
	                    resolveShiftConflicts(copy);
	                }
	                // This catches any shift that would engulf the current shift (i.e. shiftToCheck)
	                else if ( conflictEnd > checkEnd && conflictStart < checkStart )
	                {
	                    // Do something useful here like split the shift that surrounds this one (I am already hating that idea)
	                    // Think of just giving an error or maybe going back to the original coordinates, or maybe delete the shift . . . I dunno
	                    var allowChange = editableShift();
	                    if (allowChange)
	                    {
	                        var copy = copyRaphaelShift(conflictingShift);
	                        conflictEnd.setTime(checkStart.getTime());
	                        copy.data('start').setTime(checkEnd.getTime());
	                        drawShift(conflictingShift);
	                        drawShift(copy);
	                        resolveShiftConflicts(copy);
	                    }
	                    else
	                    {
	                        return false;
	                    }
	                }
	                // This catches any shift that has the same start and end date as the shiftToCheck
	                else if ( conflictStart.getTime() == checkStart.getTime() && conflictEnd.getTime() == checkEnd.getTime() )
	                {
	                    // This will need to change once we get endChild working
	                    checkStart.setTime(getTimeFromPoint(shiftToCheck.ox, shiftToCheck.oy + (parseFloat(areaInfo['startTime'] * hour)), true));
	                    checkEnd.setTime(getTimeFromPoint(shiftToCheck.ox, shiftToCheck.oy + shiftToCheck.attr('height') + (parseFloat(areaInfo['startTime'] * hour)), true));
	                    drawShift(shiftToCheck);
	                }
	                // This catches a shift that has the same start and a duration that is less than the shiftToCheck's
	                if ( checkStart.getTime() == conflictStart.getTime() && checkEnd > conflictEnd )
	                {
	                    checkStart.setTime(conflictEnd.getTime());
	                    drawShift(shiftToCheck);
	                }
	                // This catches a shift that has the same end and a duration that is less than the shiftToCheck's
	                else if ( checkEnd.getTime() == conflictEnd.getTime() && checkStart < conflictStart )
	                {
	                    checkEnd.setTime(conflictStart.getTime());
	                    drawShift(shiftToCheck);
	                }
	                // This catches any shift that has an end time inbetween the start and end of the current shift (i.e. shiftToCheck)
	                // It then adjusts the start of the current Shift so that it will no longer conflict
	                if ( conflictStart < checkStart && conflictEnd > checkStart )
	                {
                        checkStart.setTime(conflictEnd.getTime());
                        if (checkStart >= checkEnd)
                        {
                            var allowChange = editableShift();
	                        if (allowChange)
	                        {
                                checkStart.setMinutes(checkStart.getMinutes() - ((sizeToSnapToHour * 60) / hour));
                                conflictEnd.setTime(checkStart.getTime());
                            }
	                        else
	                        {
	                            return false;
	                        }
                        }
                        drawShift(conflictingShift);
                        drawShift(shiftToCheck);
	                }
	                // This catches any shift that has a start time inbetween the start and end of the current shift (i.e. shiftToCheck)
	                // It then adjusts the end of the current Shift so that it will no longer conflict
	                // This is a separate if than the one above so that we can know which part of the current shift we should adjust, the end or the start
	                if ( conflictEnd > checkEnd && conflictStart < checkEnd )
	                {
                        checkEnd.setTime(conflictStart.getTime());
                        if (checkEnd <= checkStart)
                        {
                            var allowChange = editableShift();
	                        if (allowChange)
	                        {
                                checkEnd.setMinutes(checkEnd.getMinutes() + ((sizeToSnapToHour * 60) / hour));
                                conflictStart.setTime(checkEnd.getTime());
                            }
	                        else
	                        {
	                            return false;
	                        }
                        }
                        drawShift(conflictingShift);
                        drawShift(shiftToCheck);
	                }
				}
				// Ensure that shifts of the same type are combined even if they don't conflict but do border with each other, except if the overlapping times are not 12:00AM since this creates cross-day shifts
				else if ( shiftToCheck != conflictingShift && shiftToCheck.data('hourType')['ID'] == conflictingShift.data('hourType')['ID'] && (checkStart.getTime() == conflictEnd.getTime() || checkEnd.getTime() == conflictStart.getTime()) && !(((conflictingShift.data("start").format("timeEntry") == "12:00AM") && (shiftToCheck.data('end').format('timeEntry') == "12:00AM")) || ((conflictingShift.data("end").format("timeEntry") == "12:00AM") && (shiftToCheck.data('start').format('timeEntry') == "12:00AM"))) )
                {
                	combineShifts(shiftToCheck, conflictingShift);
                	return true;
                }
            }
        });
    }

    // Pull shifts for the current week from the database
    getShifts();

    // Submit button, the if statement below determines which callback function to use depending
    // on whether we are editing shifts or doing trades
    // Give it a hover or something so you can tell its a button
    var submitButton = paper.rect((xMax - hourLabel) + 4, (yMax - dayLabel) + 4, hourLabel - 8, dayLabel - 8, 3).attr({opacity: 0.5,fill: 'gray',cursor: 'pointer',stroke: '#404040','stroke-width': 2});
    var submitButtonText = paper.text(submitButton.attr('x') + (submitButton.attr('width') / 2), submitButton.attr('y') + (submitButton.attr('height') / 2), 'Submit').toBack();
    submitButton.node.setAttribute('class', 'submitClass');
    submitButtonText.node.setAttribute('class', 'submitClass');

    if (editMode)
    {

        function prepareRaphaelShiftForDb(currentShift)
        {
            var newShiftObject = new Object();

            if (defaultMode)
                var weeklyOrDefaultFormat = 'dayOnly';
            else
                var weeklyOrDefaultFormat = 'dateOnly';

            // Both default and weekly shifts
            if (currentShift.data('shiftId'))
                newShiftObject.ID = currentShift.data('shiftId');

            if (currentShift.data('employee'))
                newShiftObject.employee = currentShift.data('employee');

            if (currentShift.data('hourType'))
                newShiftObject.hourType = currentShift.data('hourType').ID;

            // We calculate duration here no matter what
            newShiftObject.hourTotal = calculateDuration(currentShift);

            if (currentShift.data('area'))
                newShiftObject.area = currentShift.data('area');

            if (currentShift.data('start'))
            {
                newShiftObject.startDate = currentShift.data('start').format(weeklyOrDefaultFormat);
                newShiftObject.startTime = currentShift.data('start').format('timeOnly');
            }

            if (currentShift.data('end'))
            {
                newShiftObject.endDate = currentShift.data('end').format(weeklyOrDefaultFormat);
                newShiftObject.endTime = currentShift.data('end').format('timeOnly');
            }

            // Only for default Shifts
            if (defaultMode)
            {
                if (currentShift.data('period'))
                    newShiftObject.period = currentShift.data('period');
            }
            // Only for weekly shifts
            else
            {
                if (currentShift.data('defaultID') && !currentShift.data('dirty'))
                    newShiftObject.defaultID = currentShift.data('defaultID');
                else
                    newShiftObject.defaultID = null;

                // This needs to be changed to handle the new method we are using for trades
                if (currentShift.data('trade').length)
                    newShiftObject.trade = currentShift.data('trade');

                if (currentShift.data('posted'))
                    newShiftObject.posted = currentShift.data('posted');
            }

            return newShiftObject;
        }

        var shiftsToBeDeleted = new Array();
        var shiftsToBeEdited = new Array();
        var shiftsToBeCreated = new Array();

        function submitSchedule()
        {
        	var currentPeriodOrDate = currentWeek.format('dateOnly');
	        if ( defaultMode )
	        {
	            currentPeriodOrDate = $('#period option:selected').val();
	        }
	        var page = 'lock.php?period=' + currentPeriodOrDate + '&employee=' + $('#employee').val() + '&lock=1&lockedBy=' + user;
	        
	        var lock = false;
	        var lockedInfo = false;
	        $.ajax(page, {'async': false}).done(function (result)
			{
				lockedInfo = JSON.parse(result);
				if (lockedInfo['status'] == 'unlocked' || lockedInfo['lockedBy'] == user)
					lock = 1;
				else 
					lock = 0;
			});
	        
	        if ( lock )
	        {
	        	if ( defaultMode )
	        	{
		        	$.ajax('periodNotes.php',{'data':
		        	{
			        	'note': $('#periodNotes').val(),
			        	'hoursRequested': $('#hoursRequested').val(),
			        	'hoursRegistered': $('#hoursRegistered').val(),
			        	'employee': $('#employee').val(),
			        	'period': $('#period').val()
		        	}, 'type': 'POST'}).done(function(data, status)
		        	{
		        		if ( data == '0' )
		        		{
		        			alert('Error saving period notes!');
		        		}
		        	});
	        	}
	            allShifts.forEach(function(currentShift)
	            {
	                // Determine if the shift is dirty or not (i.e. if there is an originalStart, originalEnd,
	                // originalHourType, and those are all the same as their current value it is not dirty)
	                currentShift.data('dirty', true);
	                if (!(!currentShift.data('shiftId') && currentShift.isVisible()) && currentShift.data('originalStart') && currentShift.data('originalEnd') && currentShift.data('originalHourType') && currentShift.data('originalStart').getTime() == currentShift.data('start').getTime() && currentShift.data('originalEnd').getTime() == currentShift.data('end').getTime() && currentShift.data('originalHourType') == currentShift.data('hourType'))
	                {
	                    currentShift.data('dirty', false);
	                }
	
	                // Determine which category the shift falls into (i.e. To-be-Deleted, To-be-Updated, or To-be-Created)
	                if (!currentShift.isVisible() && currentShift.data('shiftId'))
	                {
	                    shiftsToBeDeleted.push(prepareRaphaelShiftForDb(currentShift));
	                }
	                else if (currentShift.isVisible() && currentShift.data('shiftId') && currentShift.data('dirty'))
	                {
	                    shiftsToBeEdited.push(prepareRaphaelShiftForDb(currentShift));
	                }
	                else if (currentShift.isVisible() && currentShift.data('dirty'))
	                {
	                    shiftsToBeCreated.push(prepareRaphaelShiftForDb(currentShift));
	                }
	            });
	            deleteShiftsFromDb();
	        }
	        else
	        {
	        	var msg = 'The schedule was not submitted.<br />We did not have a lock anymore.';
	        	if ( lockedInfo )
	        	{
	        		msg = 'This schedule is already locked by ' + lockedInfo['lockedBy'] + '.<br />Please wait until they have finished, then refesh.';
	        	}
	        	notify(msg, {'status': 'failure', 'duration': 5000});
	        }
        }

        function deleteShiftsFromDb(shiftArray)
        {
            if (shiftArray)
            {
                shiftsToBeDeleted = shiftArray;
            }
            if (defaultMode)
                var page = '../newSchedule/deleteDefaultShift.php';
            else
                var page = '../newSchedule/deleteWeeklyShift.php';

            var cb = function(result)
            {
                switch (result.trim())
                {
                    case 'VALID':
                    case '':
                        {
                            editShiftsInDb();
                            break;
                        }
                    default:
                        {
                            var undeletedArray = JSON.parse(result);
                            alert('Error!\n Unable to delete some shifts.\n Try refreshing the page and trying again.');
                            break;
                        }
                }
            };
            $.ajax(page,{'data':
        	{
	        	'JSON': JSON.stringify(shiftsToBeDeleted)
        	}, 'type': 'POST'}).done(function(data, status)
        	{
        		cb(data);
        	});
        }

        function editShiftsInDb(shiftArray)
        {
            if (shiftArray)
            {
                shiftsToBeEdited = shiftArray;
            }
            if (defaultMode)
                var page = '../newSchedule/updateDefaultShift.php';
            else
                var page = '../newSchedule/editShift.php';

            var cb = function(result)
            {
                switch (result.trim())
                {
                    case 'VALID':
                    case '':
                        {
                            createShiftsInDb();
                            break;
                        }
                    default:
                        {
                            var weeklyConflicts = JSON.parse(result);
                            confirm2(shiftsToBeEdited, weeklyConflicts, createShiftsInDb);
                            console.log(weeklyConflicts);
                            break;
                        }
                }
            };
            $.ajax(page,{'data':
        	{
	        	'JSON': JSON.stringify(shiftsToBeEdited)
        	}, 'type': 'POST'}).done(function(data, status)
        	{
        		cb(data);
        	});
        }

        function createShiftsInDb(shiftArray)
        {
            if (shiftArray)
            {
                shiftsToBeCreated = shiftArray;
            }
            if (defaultMode)
                var page = '../newSchedule/newDefaultShift.php';
            else
                var page = '../newSchedule/newShift.php';

            var cb = function(result)
            {
                switch (result.trim())
                {
                    case 'VALID':
                    case '':
                        {
                            notify('All operations successful!', {'status':'success', 'duration':10000});
                            break;
                        }
                    default:
                        {
                            var weeklyConflicts = JSON.parse(result);
                            confirm2(shiftsToBeCreated, weeklyConflicts);
                            console.log(weeklyConflicts);
                            break;
                        }
                }
                // Renew lock on schedule
                eve('schedule.lock', this, 1);
                // I call the event here just because I am calling the onload, but I don't care what response it gives since I just pushed to the database
                var confirmed = eve('schedule.onload', this, {'pushed': true});
                paper.clear();
                employeeScheduleOnload();
            };
            $.ajax(page,{'data':
        	{
	        	'JSON': JSON.stringify(shiftsToBeCreated)
        	}, 'type': 'POST'}).done(function(data, status)
        	{
        		cb(data);
        	});
        }
        // Set callback for Submit Button when in edit mode to submit Schedule shifts
        submitButton.click(submitSchedule);
    }
    // Else Trade mode and therefore we should use functions for submitting trades
    else
    {
        function prepareRaphaelTradeForDb(raphaelTrade)
        {
            var newTradeObject = new Object();
            if (raphaelTrade.data('ID'))
                newTradeObject['ID'] = raphaelTrade.data('ID');
            if (raphaelTrade.data('postedBy'))
                newTradeObject['postedBy'] = raphaelTrade.data('postedBy');
            if (raphaelTrade.data('postedDate'))
                newTradeObject['postedDate'] = raphaelTrade.data('postedDate');
            if (raphaelTrade.data('approvedBy'))
                newTradeObject['approvedBy'] = raphaelTrade.data('approvedBy');
            if (raphaelTrade.data('approvedOn'))
                newTradeObject['approvedOn'] = raphaelTrade.data('approvedOn');
            if (raphaelTrade.data('shiftId'))
                newTradeObject['shiftId'] = raphaelTrade.data('shiftId');
            if (raphaelTrade.data('start'))
            {
                newTradeObject['startDate'] = raphaelTrade.data('start').format('dateOnly');
                newTradeObject['startTime'] = raphaelTrade.data('start').format('timeOnly');
            }
            if (raphaelTrade.data('end'))
            {
                newTradeObject['endDate'] = raphaelTrade.data('end').format('dateOnly');
                newTradeObject['endTime'] = raphaelTrade.data('end').format('timeOnly');
            }
            if (raphaelTrade.data('hourType'))
                newTradeObject['hourType'] = raphaelTrade.data('hourType')['ID'];
            if (raphaelTrade.data('bids'))
                newTradeObject['bids'] = raphaelTrade.data('bids');
            if ( raphaelTrade.data('notes') || raphaelTrade.data('notes') === '' )
                newTradeObject['notes'] = raphaelTrade.data('notes');
            if (raphaelTrade.data('area'))
                newTradeObject['area'] = raphaelTrade.data('area');

            return newTradeObject;
        }

        // These will hold the trades that will be deleted from the DB, updated, or created
        var tradesToBeDeleted = new Array();
        var tradesToBeEdited = new Array();
        var tradesToBeCreated = new Array();

        function submitTrades()
        {
        	if ( defaultMode )
        	{
	        	$.ajax('periodNotes.php',{'data':
	        	{
		        	'note': $('#periodNotes').val(),
		        	'hoursRequested': $('#hoursRequested').val(),
		        	'hoursRegistered': $('#hoursRegistered').val(),
		        	'employee': $('#employee').val(),
		        	'period': $('#period').val()
	        	}, 'type': 'POST'}).done(function(data, status)
	        	{
	        		if ( data == '0' )
	        		{
	        			alert('Error saving period notes!');
	        		}
	        	});
        	}
            allTrades.forEach(function(trade)
            {
                // Determine if the shift is dirty or not (i.e. if there is an originalStart, originalEnd,
                // originalHourType, and those are all the same as their current value it is not dirty)
                if (!(!trade.data('ID') && trade.isVisible()) && trade.data('originalStart') && trade.data('originalEnd') && trade.data('originalHourType') && trade.data('originalStart').getTime() == trade.data('start').getTime() && trade.data('originalEnd').getTime() == trade.data('end').getTime() && trade.data('originalHourType') == trade.data('hourType') && trade.data('originalNotes') == trade.data('notes'))
                {
                    trade.data('dirty', false);
                }

                // Determine which category the shift falls into (i.e. To-be-Deleted, To-be-Updated, or To-be-Created)
                if (!trade.isVisible() && trade.data('ID'))
                {
                    tradesToBeDeleted.push(prepareRaphaelTradeForDb(trade));
                }
                else if (trade.isVisible() && trade.data('ID') && trade.data('dirty'))
                {
                    tradesToBeEdited.push(prepareRaphaelTradeForDb(trade));
                }
                else if (trade.isVisible() && trade.data('dirty'))
                {
                    tradesToBeCreated.push(prepareRaphaelTradeForDb(trade));
                }
            });
            deleteTradesFromDb();
        }
        function deleteTradesFromDb(tradeArray)
        {
            if (tradeArray)
                tradesToBeDeleted = tradeArray;

            var page = '../newTradeRequest/deleteTrade.php';
            var cb = function(result)
            {
                switch (result.trim())
                {
                    case '1':
                    case '':
                        {
                            editTradesInDb();
                            break;
                        }
                    default:
                        {
                            var undeletedArray = JSON.parse(result);
                            alert('Error!\n Unable to delete some trades.\n Try refreshing the page and trying again.');
                            break;
                        }
                }
            };
            $.ajax(page,{'data':
        	{
	        	'JSON': JSON.stringify(tradesToBeDeleted)
        	}, 'type': 'POST'}).done(function(data, status)
        	{
        		cb(data);
        	});
        }
        function editTradesInDb(tradeArray)
        {
            if (tradeArray)
                tradesToBeEdited = tradeArray;

            var page = '../newTradeRequest/editTrade.php';
            var cb = function(result)
            {
                switch (result.trim())
                {
                    case '1':
                    case '':
                        {
                            createTradesInDb();
                            break;
                        }
                    default:
                        {
                            var uneditedArray = JSON.parse(result);
                            alert('Error!\n Unable to adjust some trades.\n Try refreshing the page and trying again.');
                            break;
                        }
                }
            };
            $.ajax(page,{'data':
        	{
	        	'JSON': JSON.stringify(tradesToBeEdited)
        	}, 'type': 'POST'}).done(function(data, status)
        	{
        		cb(data);
        	});
        }
        function createTradesInDb(tradeArray)
        {
            if (tradeArray)
                tradesToBeCreated = tradeArray;
            var page = '../newSchedule/postNewTrade.php';

            var cb = function(result)
            {
                switch (result.trim())
                {
                    case '1':
                    case '':
                        {
                            notify('All operations successful!', {'status': 'success', 'duration': 10000});
                            break;
                        }
                    default:
                        {
                            var uncreatedArray = JSON.parse(result);
                            alert('Error!\n Unable to create some trades.\n Try refreshing the page and trying again.');
                            break;
                        }
                }
                // Right now there is no lock for making trades . . . should there be a lock?
                var confirmed = eve('schedule.onload', this, {'pushed': true});
                paper.clear();
                employeeScheduleOnload();
            };
            $.ajax(page,{'data':
        	{
	        	'JSON': JSON.stringify(tradesToBeCreated)
        	}, 'type': 'POST'}).done(function(data, status)
        	{
        		cb(data);
        	});
        }
        submitButton.click(submitTrades);
    }

    var repeatForAllConflicts = false;
    function confirm2(shiftsToBeSaved, conflictingShifts, onCompleteFunction)
    {
        var conflictingShift = conflictingShifts.pop();
        var shiftsToBeWrittenToDb = getConflictingShifts(conflictingShift, shiftsToBeSaved);

        function keepDefault()
        {
            var nextConflict = conflictingShifts.pop();
            if (nextConflict)
            {
                var nextConflictShiftsToBeWrittenToDb = getConflictingShifts(nextConflict, shiftsToBeSaved);
                createConflictDialog(nextConflictShiftsToBeWrittenToDb, nextConflict, keepDefault, keepWeekly);
            }
            else if (onCompleteFunction)
                onCompleteFunction();
        }

        function keepWeekly(weeklyShift, defaultShifts)
        {
            // Used to delete the conflicting weekly shift
            function deleteWeeklyInstanceOfDefaultShift(defaultShift, weeklyShift)
            {
                var xmlhttp;
                var activeVal = 1;

                if (window.XMLHttpRequest)
                { // code for IE7+, Firefox, Chrome, Opera, Safari
                    xmlhttp = new XMLHttpRequest();
                }
                else
                { // code for IE6, IE5
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.open("GET", 'deleteWeeklyDefaultShift.php?JSON=' + JSON.stringify([defaultShift, weeklyShift]), false);
                xmlhttp.send();
                return xmlhttp.responseText.trim();
            }

            // Used to create a weekly shift instance of the default shift
            function createWeekly(weeklyShift)
            {
                var xmlhttp;
                var activeVal = 1;

                if (window.XMLHttpRequest)
                { // code for IE7+, Firefox, Chrome, Opera, Safari
                    xmlhttp = new XMLHttpRequest();
                }
                else
                { // code for IE6, IE5
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.open("GET", 'newShift.php?JSON=' + JSON.stringify([weeklyShift]), false);
                xmlhttp.send();
                return xmlhttp.responseText.trim();
            }

            var deleteCheck = true;
            // Find out which default shift
            for (i in defaultShifts)
            {
                // Delete the weekly shift that had been conflicting
                deleteResponse = deleteWeeklyInstanceOfDefaultShift(defaultShifts[i], weeklyShift);
                if (deleteResponse != 'VALID' && deleteResponse != '')
                {
                    deleteCheck = false;
                }
            }
            if (deleteCheck)
            {
                // For each default Shift that had been conflicting create a weekly shift instance
                var createResponse = createWeekly(weeklyShift);
                if (createResponse != 'VALID' && createResponse != '')
                {
                    var moreConflicts = JSON.parse(createResponse);
                    for (j in moreConflicts)
                    {
                        alert('There is another shift that conflicts with the weekly schedule.\nThe weekly shift can be kept when you resolve that conflict.');
                    // conflictingShifts.unshift(moreConflicts[j]);
                    }
                }

                // Get next conflict
                var nextConflict = conflictingShifts.pop();
                if (nextConflict)
                {
                    var nextConflictShiftsToBeWrittenToDb = getConflictingShifts(nextConflict, shiftsToBeSaved);
                    createConflictDialog(nextConflictShiftsToBeWrittenToDb, nextConflict, keepDefault, keepWeekly);
                }
                else if (onCompleteFunction)
                    onCompleteFunction();
            }
            else
            {
                alert('There was an error deleting a shift. Please refresh the page.\nSorry for the inconvenience!');
            }
        }

        createConflictDialog(shiftsToBeWrittenToDb, conflictingShift, keepDefault, keepWeekly);
    }

    function createConflictDialog(shiftsToBeWrittenToDb, conflictingShift, keepDefault, keepWeekly)
    {
        // Check if we already know what to do with the conflict
        if (!repeatForAllConflicts)
        {
            // If we don't know what to do, create a dialog box asking what to do with the conflict
            var popupHtml = '<div><span class="ui-icon yellow ui-icon-alert" style="float: left; margin: 10px 21px 10px 7px;"></span><p style="margin-top: 7px; margin-bottom: 7px;">A shift in the weekly schedule conflicts with the following default shifts!</p>';
            popupHtml += '<div style="float: left; width: 50%;"><h3 style="text-align: center;">Weekly Shift:</h3><b>Start:</b> ' + conflictingShift['startDate'] + ' ' + conflictingShift['startTime'] + '<br /><b>End:</b> ' + conflictingShift['endDate'] + ' ' + conflictingShift['endTime'] + '<br /><b>Type:</b> ' + window.shiftTypes[conflictingShift['hourType']]['longName'] + '</div>';
            for (i in shiftsToBeWrittenToDb)
            {
                popupHtml += '<div style="float: left; width: 50%;"><h3 style="text-align: center;">Default Shift:</h3><b>Start:</b> ' + shiftsToBeWrittenToDb[i]['startDate'] + ' ' + shiftsToBeWrittenToDb[i]['startTime'] + '<br /><b>End:</b> ' + shiftsToBeWrittenToDb[i]['endDate'] + ' ' + shiftsToBeWrittenToDb[i]['endTime'] + '<br /><b>Type:</b> ' + window.shiftTypes[shiftsToBeWrittenToDb[i]['hourType']]['longName'] + '</div>';
            }
            popupHtml += '<p style="float: left; margin-bottom: 3px; width: 100%;">What would you like to do?<sub style="float: right; bottom: -0.75em;"><input style="vertical-align: text-bottom;" type="checkbox" id="repeatForConflicts" /> Repeat for all conflicts</sub></p>';
            popupHtml += '</div>';
            $(popupHtml).dialog(
            {
                autoOpen: true,
                close: function()
                {
                    $(this).dialog('destroy');
                },
                position: ['center', 'middle'],
                title: 'Conflict Resolution',
                width: 400,
                resizable: false,
                modal: true,
                closeOnEscape: false,
                dialogClass: 'yellow no-close-dialog',
                buttons:
                {
                    "Keep Default": function()
                    {
                        if ($('#repeatForConflicts').prop('checked'))
                            repeatForAllConflicts = keepDefault;
                        keepDefault();
                        // Renew our lock on the schedule since we are actively editing it
                        eve('schedule.lock', this, 1);
                        $(this).dialog("close");
                    },
                    "Keep Weekly": function()
                    {
                        if ($('#repeatForConflicts').prop('checked'))
                            repeatForAllConflicts = keepWeekly;
                        keepWeekly(conflictingShift, shiftsToBeWrittenToDb);
                        // Renew our lock on the schedule since we are actively editing it
                        eve('schedule.lock', this, 1);
                        $(this).dialog("close");
                    }
                },
                create: function()
                {
                    $(this).closest(".ui-dialog").find(".ui-dialog-titlebar").addClass("yellow");
                }
            });
        }
        else
        {
            repeatForAllConflicts(conflictingShift, shiftsToBeWrittenToDb);
        }
    }

    // Function to find all shifts that conflict with the one passed in
    function getConflictingShifts(comparisonShift, otherShifts)
    {
        var checkStartDay = parseDate(comparisonShift['startDate']).getDay() + 1;
        var checkEndDay = parseDate(comparisonShift['endDate']).getDay() + 1;

        // Create Start Date Object
        // shifts[i].start = parseDate('2012-01-0'+shifts[i].startDate, shifts[i].startTime);
        var defaultWeekStart = new Date(currentWeek.getTime());
        var startDaysFromSaturday = parseInt(checkStartDay);
        if (startDaysFromSaturday == 7)
        {
            startDaysFromSaturday = 0;
        }
        defaultWeekStart.setDate(defaultWeekStart.getDate() + startDaysFromSaturday);
        var checkStart = parseDate(defaultWeekStart.format('dateOnly'), comparisonShift.startTime)

        // Create End Date Object
        // shifts[i].end = parseDate('2012-01-0'+shifts[i].endDate, shifts[i].endTime);
        var defaultWeekEnd = new Date(currentWeek.getTime());
        var endDaysFromSaturday = parseInt(checkEndDay);
        if (endDaysFromSaturday == 7)
        {
            endDaysFromSaturday = 0;
        }
        defaultWeekEnd.setDate(defaultWeekEnd.getDate() + endDaysFromSaturday);
        var checkEnd = parseDate(defaultWeekEnd.format('dateOnly'), comparisonShift.endTime);

        var conflictingShifts = new Array();
        if (!otherShifts)
        {
            otherShifts = allShifts;
        }
        otherShifts.forEach(function(conflictingShift)
        {
            var typeOfShift = conflictingShift.constructor.prototype;
            if (typeOfShift == Object().constructor.prototype)
            {
                var conflictStartDate = new Date(currentWeek.getTime());
                conflictStartDate.setDate(conflictStartDate.getDate() + parseInt(conflictingShift['startDate']));
                var conflictStart = parseDate(conflictStartDate.format('dateOnly'), conflictingShift['startTime']);

                var conflictEndDate = new Date(currentWeek.getTime());
                conflictEndDate.setDate(conflictEndDate.getDate() + parseInt(conflictingShift['endDate']));
                var conflictEnd = parseDate(conflictEndDate.format('dateOnly'), conflictingShift['endTime']);
            }
            else if (typeOfShift == Raphael.el && conflictingShift.isVisible())
            {
                var conflictStart = conflictingShift.data('start');
                var conflictEnd = conflictingShift.data('end');
            }
            // Check if the comparisonShift is conflicting in any way with the conflictingShift
            if ( (checkStart >= conflictStart && conflictEnd > checkStart) || (checkEnd > conflictStart && conflictEnd >= checkEnd) || (checkStart < conflictStart && checkEnd > conflictEnd) )
            {
                conflictingShifts.push(conflictingShift);
            }
        });
        return conflictingShifts;
    }

    // Loop through all shifts to see if any are dirty
    function existsDirtyShift()
    {
        var dirty = false;
        allShifts.forEach(function(currentShift)
        {
            if (currentShift.data('dirty') == true)
                dirty = true;
        });
        return dirty;
    }

    // This could be used to mark a specific shift as the current shift to interact with
    // In this case I am using it to allow you to delete a shift without opening the popup just by hitting the delete key
    // It could be used for adjusting shift Start and End Times, moving shifts to different days etc.
    var currentlySelectedShift;

    // Raphael Shift Hover Start
    function shiftHoverStart(inContext)
    {
        // This code is to allow for child rect functionality with cross day shifts
        /*if (this.data('type')!='mainRectangle')
      {
         currentShift = this.data('parent');
      }
      else
      {
         currentShift = this;
      }*/
        eve('schedule.currentlySelectedShift.set', this, currentlySelectedShift);
    }

    // Raphael Shift Hover End
    function shiftHoverEnd(outContext)
    {
    // This code is to allow for child rect functionality with cross day shifts
    /*if (this.data('type')!='mainRectangle')
      {
         currentShift = this.data('parent');
      }
      else
      {
         currentShift = this;
      }*/
        eve('schedule.currentlySelectedShift.unset', this);
    }

    function selectShift(oldSelected)
    {
        if (oldSelected)
        {
            oldSelected.unhover(shiftHoverStart, shiftHoverEnd);
            currentlySelectedShift = undefined;
        }
        currentlySelectedShift = this;
        currentlySelectedShift.attr({ 'stroke-opacity':0.5 });
    }
    eve.on('schedule.currentlySelectedShift.set', selectShift);

    function deselectShift()
    {
        if ( currentlySelectedShift )
        {
            currentlySelectedShift.attr({ 'stroke-opacity':0.25 });
            currentlySelectedShift = undefined;
        }
    }
    eve.on('schedule.currentlySelectedShift.unset', deselectShift);

    var underline = function(element)
    {
        // This ensures you don't get any underlining artifacts when moving shifts around
        if (element.data('underline') && !element.data('underline').removed)
        {
            element.data('underline').remove();
        }
        var textBBox = element.getBBox();
        var textUnderline = paper.path("M" + textBBox.x + " " + (textBBox.y + textBBox.height) + "L" + (textBBox.x + textBBox.width) + " " + (textBBox.y + textBBox.height));
        element.data('underline', textUnderline);
    };


    // Raphael Shift Label Hover Start
    function labelHoverStart(inContext)
    {
        underline(this);
    }
    // Raphael Shift Label Hover end
    function labelHoverEnd(outContext)
    {
        this.data('underline').remove();
    }

    function adjustShiftStartStart()
    {
        currentShift = this.data('parent');

        this.oy = this.attr('y');
    }

    function adjustShiftStartMove(dx, dy, cursorX, cursorY, event)
    {
        currentShift = this.data('parent');

        var snappyY = dayLabel + (Math.floor(((cursorY - $('#employeeScheduleContainer').offset().top) - dayLabel) / sizeToSnapToHour) * sizeToSnapToHour);
        // Uses the inverse delta of Y to determine height should be added to the current height to ensure the end time remains the same
        var newHeight = (currentShift.attr('y')-snappyY)+currentShift.attr('height');
        if (newHeight > 0 && snappyY >= dayLabel)
        {
        	var start = currentShift.data('start');
        	var end = currentShift.data('end');
        	var label = currentShift.data('label');
        	if (label.data('underline'))
                label.data('underline').remove();
        	start.setTime(getTimeFromPoint(currentShift.attr('x'), snappyY + (parseFloat(areaInfo['startTime'] * hour)), true));
            currentShift.attr({'y':snappyY, 'height':newHeight});
            label.attr({'text': start.format('timeEntry') + '-' + end.format('timeEntry'), 'y': currentShift.attrs.y + (hour * timeProportionRatio / 4)});
            this.attr({y: currentShift.attr('y')}).toFront();
        }
    }

    function adjustShiftStartEnd()
    {
        currentShift = this.data('parent');
        if (this.oy != this.attr('y'))
        {
        	// Check for and resolve any conflicts
            resolveShiftConflicts(currentShift);

            // Renew our lock on the schedule since we are actively editing it
            eve('schedule.lock', this, 1);
        }

        this.toFront();
    }

    function adjustShiftEndStart()
    {
        currentShift = this.data('parent');

        this.oy = this.attr('y');
    }

    function adjustShiftEndMove(dx, dy, cursorX, cursorY, event)
    {
        currentShift = this.data('parent');

        var snap = dayLabel + (Math.ceil(((cursorY - $('#employeeScheduleContainer').offset().top) - dayLabel) / sizeToSnapToHour) * sizeToSnapToHour);
        var shiftHeight = snap - currentShift.attr('y');
        var endY = currentShift.attr('y') + shiftHeight;
        if (shiftHeight > 0 && endY <= yMax - dayLabel)
        {
        	var start = currentShift.data('start');
            var end = currentShift.data('end');
            var label = currentShift.data('label');
            
            end.setTime(getTimeFromPoint(currentShift.attr('x'), endY + (parseFloat(areaInfo['startTime'] * hour)), true));
            label.attr('text', start.format('timeEntry') + '-' + end.format('timeEntry'));
            currentShift.attr('height', shiftHeight);
            this.attr({y: currentShift.attr('y') + currentShift.attr('height') - 2});
        }
    }

    function adjustShiftEndEnd()
    {
        currentShift = this.data('parent');
        if (this.oy != this.attr('y'))
        {
        	// Check for and resolve conflicts
            resolveShiftConflicts(currentShift);

            // Renew our lock on the schedule since we are actively editing it
            eve('schedule.lock', this, 1);
        }

        this.toFront();
    }

    function tradeStart(pageX, pageY, event)
    {
        if ( parseInt(this.data('hourType')['tradable']) )
        {
            // Determine X and Y coordinates of double click event relative to raphael paper
            var tradeX = this.getBBox()['x'] + (this.getBBox()['width'] / 2);
            var tradeY = pageY - $('#employeeScheduleContainer').offset().top;

            var tradeStart = getTimeFromPoint(tradeX, tradeY + (hour * parseFloat(areaInfo['startTime'])), true);
            var tradeStartPoint = getPointFromTime(new Date(tradeStart));

            this.tradeShiftX = tradeStartPoint['x'];
            this.tradeShiftY = tradeStartPoint['y'];
            this.tradeStart = tradeStart;
            this.tradeNewFlag = true;
        }
    }

    function tradeMove(dx, dy, cursorX, cursorY, event)
    {
        if ( parseInt(this.data('hourType')['tradable']) )
        {
        	var delta = dy;
        	var shouldBeCreated = 4;
        	var newTradeY = cursorY - $('#employeeScheduleContainer').offset().top;
            var newHeight = Math.ceil((newTradeY-this.tradeShiftY) / sizeToSnapToHour) * sizeToSnapToHour;
            // Check to make sure that the trade is not more than 2 days in the past. If it is then give an alert saying that it cannot be made.
            var tradeCheck = new Date(this.tradeStart);
            if ( delta > shouldBeCreated && tradeCheck.setDate(tradeCheck.getDate() + 2) > new Date() )
            {
                var shiftTrades = this.data('trade');
                if (this.tradeNewFlag)
                {
                    if (shiftTrades.length)
                    {
                        for (shiftTrade in shiftTrades)
                        {
                            if (shiftTrades[shiftTrade].isVisible())
                            {
                                checkShiftStart = shiftTrades[shiftTrade].data('start');
                                checkShiftEnd = shiftTrades[shiftTrade].data('end');
                                if (this.tradeStart >= checkShiftStart && checkShiftEnd > this.tradeStart)
                                    return console.log('Conflicting Trade');
                            }
                        }
                    }
                    // Ensure that the trade has the associated shift's data included
                    var shiftData = this.dataset(['shiftId', 'employee', 'area', 'start', 'end', 'hourType', 'duration', 'period']);
                    shiftData['postedBy'] = shiftData['employee'];
                    shiftData['hourTotal'] = shiftData['duration'];
                    shiftData['hourType'] = shiftData['hourType'].ID;
                    shiftData['start'] = new Date(shiftData['start'].getTime());
                    shiftData['end'] = new Date(shiftData['end'].getTime());
                    var tradeShift = makeRaphaelTrade(shiftData);

                    // Adjust trade data to reflect the trade's start, etc.
                    tradeShift.data('start', new Date(this.tradeStart));
                    tradeShift.data('originalStart', false);
                    tradeShift.data('originalEnd', false);
                    this.tradeNewFlag = false;
                }
                else
                {
                    if ( shiftTrades.length )
                    {
                        var lastTrade = shiftTrades[shiftTrades.length - 1];
                        if ( newHeight + lastTrade.attr('y') > this.attr('y') + this.attr('height') )
                            var newHeight = (this.attr('y') + this.attr('height')) - lastTrade.attr('y');
                        var possibleNewEnd = getTimeFromPoint(lastTrade.attr('x'), lastTrade.attr('y') + newHeight + (hour * areaInfo['startTime']), true);

                        // Get new end time and check to see if the trade would now conflict with any other trades for this shift
                        // If the trade would now conflict return it to the last none conflicting end point
                        // Or do those checks during the call to tradeEnd

                        for (shiftTrade in shiftTrades)
                        {
                            if ( shiftTrades[shiftTrade].isVisible() )
                            {
                                checkTradeStart = shiftTrades[shiftTrade].data('start');
                                checkTradeEnd = shiftTrades[shiftTrade].data('end');
                                // This "if" needs to get changed to be checking for the trade end, not comparing against the start
                                if (shiftTrades[shiftTrades.length - 1] !== shiftTrades[shiftTrade] && (possibleNewEnd > checkTradeStart && checkTradeEnd >= possibleNewEnd) || (this.tradeStart < checkTradeStart && checkTradeEnd <= possibleNewEnd))
                                    return console.log('Conflicting Trade on change to trade end');
                            }
                        }

                        // Set the trade's height
                        shiftTrades[shiftTrades.length - 1].attr('height', newHeight);
                    }
                }
            }
            else if ( delta > shouldBeCreated && !$('.warning').length)
            {
                notify('Cannot trade shift more <br />than 2 days in the past.', {'status': 'warning', 'duration': 10000});
            }
        }
        else if ( !$('.warning').length )
        {
        	notify('This is a non-tradable shift type.', {'status': 'warning', 'duration': 5000});
        }
    }

    function tradeEnd()
    {
        if ( parseInt(this.data('hourType')['tradable']) )
        {
            var tradeShifts = this.data('trade');
            if (tradeShifts.length && !this.tradeNewFlag)
            {
                var tradeShift = tradeShifts[tradeShifts.length - 1];
                // Create an end date based off of where the user double clicked to make a new shift
                var newEnd = getTimeFromPoint(tradeShift.attr('x'), tradeShift.attr('y') + tradeShift.attr('height') + (hour * areaInfo['startTime']), true);

                // Set tradeShift's data
                tradeShift.data('end', new Date(newEnd));

                // We need to either get checkForConflictingShifts to distinguish between shifts and trades
                // or we should make an alternate conflict handling function specifically for trades
                //resolveShiftConflicts(tradeShift);

                this.tradeShift = undefined;
                this.tradeNewFlag = false;
                this.tradeShiftX = undefined;
                this.tradeShiftY = undefined;
            }
        }
    }

    // This is function is called whenever the "*.data.set.*.hourType" event is fired
    // It updates the rect's fill to use the associated hourType's color
    // This is only possible using the customized Raphael.js I wrote
    // This is done as an example to show how other event catching could be achieved
    // to give more of a MVC type of framework
    function changeHourType(value, key)
    {
        if (this.data('type') != 'trade')
        {
            this.attr({"fill": value['color'], 'title': value['longName']});
        }   
    }
    eve.on('*.data.set.*.hourType', changeHourType);

    // This is used to remove trades from their associated shift. We still need to decide if this is
    // a literal removal from the trade array or if this just needs to be hiding the trade. I think
    // we are going to go with hiding it and then just need to check while doing the for-loop to see
    // if the trade is visible when creating the popup
    function removeTrade(event)
    {
        var parent = event.data['parent'];
        var tradeId = event.data['tradeId'];
        parent.data('trade')[tradeId].data('dirty', true);
        parent.data('trade')[tradeId].hide();
        $('#trade' + tradeId).parent().remove();
    }

    // Create a comment for a trade
    function commentTrade(event)
    {
        var tradeId = $(this).attr('tradeId');
        var parentShift = event.data['parentShift'];
        parentShift.data('trade')[tradeId].data('dirty', true);
        parentShift.data('trade')[tradeId].data('notes', prompt('Trade Notes: ', ''));
    }
    
    // This creates a raphael shift object, if no data is passed then the shift is made but has a width and height of 0
    // If a shift jSON object is passed then the raphael shift is created and has the corelated data assigned to it
    function makeRaphaelShift(shiftData)
    {
        // Creates a rectangle with coordinates of x = 0, y = 0, width = 0, height = 0, and a 5px rounded corner
        var rect = paper.rect(0, 0, 0, 0, 0);
        // Sets the fill attribute of the circle to white (i.e. gives the object a fill but since the background is white it isn't very visible)
        rect.attr("fill", "white");
        // Sets the stroke opacity to 0 to make the stroke invisible
        rect.attr('stroke-opacity', 0.25);

        if (shiftData)
        {
            // Set Shift Data
            rect.data('shiftId', shiftData.ID);
            rect.data('employee', shiftData.employee);
            rect.data('area', shiftData.area);
            rect.data('startDate', shiftData.startDate);
            rect.data('startTime', shiftData.startTime);
            rect.data('endDate', shiftData.endDate);
            rect.data('endTime', shiftData.endTime);
            var originalStart = false;
            if (shiftData.start)
            {
                originalStart = new Date(shiftData.start.getTime());
            }
            rect.data('originalStart', originalStart);
            rect.data('start', shiftData.start);
            var originalEnd = false;
            if (shiftData.end)
            {
                originalEnd = new Date(shiftData.end.getTime());
            }
            rect.data('originalEnd', originalEnd);
            rect.data('end', shiftData.end);
            rect.data('originalHourType', window.allShiftTypes[shiftData.hourType]);
            rect.data('hourType', window.allShiftTypes[shiftData.hourType]);
            if ( !shiftTypes[shiftData.hourType] )
            {
            rect.attr({'stroke': 'black', 'stroke-dasharray': '. ', 'opacity': 0.8});
            }
            rect.data('duration', shiftData.hourTotal);

            if (defaultMode)
            {
                rect.data('period', shiftData.period);
            }
            else
            {
                rect.data('defaultId', shiftData.defaultID);
                // Trade array needs to have trades pushed. Either here or in another call.
                // rect.data('trade', shiftData.trade);
                rect.data('trade', new Array());
                rect.data('posted', shiftData.posted);
            }
        }

        if (editMode)
        {
            if ( (rect.data('hourType') && shiftTypes[shiftData.hourType]) && (permissionToEdit || (rect.data('hourType') && parseInt(rect.data('hourType')['selfSchedulable']) && user == $('#employee').val())) )
            {
                if (browser == 'Firefox')
                    var shiftCursor = '-moz-grab';
                else
                    var shiftCursor = '-webkit-grab';
                // Gives the pointing hand cursor when hovered over
                rect.attr('cursor', shiftCursor);
                rect.drag(move, start, up);
                rect.hover(shiftHoverStart, shiftHoverEnd);
            }
        }
        else if ( !defaultMode && (rect.data('hourType') && shiftTypes[shiftData.hourType]) )
        {
            rect.drag(tradeMove, tradeStart, tradeEnd);
        }
        rect.data('type', 'mainRectangle');
        rect.data('dirty', true);


        // Create related labels for shift objects
        var text = paper.text(rect.attrs.x + (day / 2), rect.attrs.y + (hour * timeProportionRatio / 4), '')
        text.data("name", "text");
        text.data('type', 'shiftLabel');
        text.data('parent', rect);
        // If in edit mode give shift edit popup on click
        if (editMode)
        {
            if ( (rect.data('hourType') && shiftTypes[shiftData.hourType]) && (permissionToEdit || (rect.data('hourType') && parseInt(rect.data('hourType')['selfSchedulable']) && user == $('#employee').val())) )
            {
                text.attr('cursor', 'pointer');
                text.hover(labelHoverStart, labelHoverEnd);
                text.click(function()
                {
                    $shiftPopup.shiftObject = rect;
                    $('#popupShiftType>option[value="' + rect.data('hourType').ID + '"]').prop('selected', true);
                    $('#popupStartDate').datepicker('setDate', rect.data('start'));
                    $('#popupStartTime').val(rect.data('start').format('timeEntry'));
                    $('#popupEndDate').datepicker('setDate', rect.data('end'));
                    $('#popupEndTime').val(rect.data('end').format('timeEntry'));
                    $shiftPopup.dialog('open');
                    $(".ui-dialog-buttonset").css('width', '100%').find('button:not(:first)').css('float', 'right');
                });
            }
        }
        // Otherwise on click of the label show the trades popup
        else if ( !defaultMode && (rect.data('hourType') && shiftTypes[shiftData.hourType]) && (permissionToEdit || user == $('#employee').val()) )
        {
            text.attr('cursor', 'pointer');
            text.hover(labelHoverStart, labelHoverEnd);
            text.click(function(clickEvent, clickX, clickY)
            {
                var parent = this.data('parent');
                var trades = parent.data('trade');
                var tradePopupContent = '<div id="tradePopupDialog">'
                + '<h3>Associated Trades</h3>'
                + '<div class="tradeShiftInfo" id="tradeShiftInfo' + parent.data('shiftId') + '">' + parent.data('start').format('shiftDay') + ' ' + parent.data('start').format('timeEntry')
                + ' -- ' + parent.data('end').format('shiftDay') + ' ' + parent.data('end').format('timeEntry')
                + '<br />' + 'Shift Type: ' + parent.data('hourType')['longName'] + '</div>';
                if (trades.length)
                {
                    for (trade in trades)
                    {
                        if (trades[trade].isVisible())
                        {
                            // Add ui-icon-comment
                            tradePopupContent += '<div><input trade="' + trade + '" type="text" class="tradeLabel" id="trade' + trade + 'Label" disabled />'
                            + '<div class="trade" id="trade' + trade + '" '
                            + 'start="' + trades[trade].data('start').getTime() + '" '
                            + 'end="' + trades[trade].data('end').getTime() + '">'
                            + '</div><span tradeId="' + trade + '" class="tradeDelete ui-icon ui-icon-circle-close" title="Delete Trade"></span><div class="clearMe"></div>'
                            + '<label class="tradeCommentBoxLabel">Comments:</label><textarea class="tradeCommentBox" tradeId="'+trade+'" rows="2">';
                            if(trades[trade].data('notes'))
                            	tradePopupContent += trades[trade].data('notes');
                            tradePopupContent+='</textArea></div>'; 
                        }
                    }
                }
                else
                {
                    tradePopupContent += '<h4>No trades.</h4>';
                }
                tradePopupContent += '</div>';
                // Create jQuery UI popup dialog for editing shifts
                var $tradePopup = $(tradePopupContent)
                .dialog({
                    autoOpen: true,
                    width: '450',
                    height: 'auto',
                    modal: true,
                    title: 'Trade Info',
                    draggable: true,
                    resizable: false,
                    close: function()
                    {
                        $('.tradeCommentBox').each(function(){
                            trades[$(this).attr('tradeId')].data('dirty', true);
                            trades[$(this).attr('tradeId')].data('notes', $(this).val());
                        });
                        $(this).dialog('destroy').remove();
                    },
                    open: function()
                    {
                        $(this).find('.trade').each(function() {
                            var tradeStart = $(this).attr('start');
                            var tradeEnd = $(this).attr('end');
                            tradeStart = new Date(parseInt(tradeStart));
                            tradeEnd = new Date(parseInt(tradeEnd));
                            var minimum = parent.data('start').getTime();
                            var maximum = parent.data('end').getTime();
                            var label = $(this).attr('id') + 'Label';
                            $('#' + label).val('From: ' + tradeStart.format('compare') + ' To: ' + tradeEnd.format('compare'));
                            $(this).slider({
                                range: true,
                                min: minimum,
                                max: maximum,
                                values: [tradeStart, tradeEnd],
                                step: (window.sizeToSnapToHour / hour) * millisecondsPerHour,
                                slide: function(event, ui)
                                {
                                    var starting = new Date(ui.values[0]);
                                    var ending = new Date(ui.values[1]);
                                    if (starting.getTime() == ending.getTime())
                                        return false;
                                    else
                                    {
                                        // Check here to ensure there are no conflicts against all other trades for the shift
                                        var allTradesInShift = parent.data('trade');
                                        for (otherTrade in allTradesInShift)
                                        {
                                            if (otherTrade != label.slice(5, 6) && allTradesInShift[otherTrade].isVisible())
                                            {
                                                var otherStarting = allTradesInShift[otherTrade].data('start');
                                                var otherEnding = allTradesInShift[otherTrade].data('end');
                                                if ((starting >= otherStarting && otherEnding > starting) || (ending > otherStarting && otherEnding >= ending) || (starting < otherStarting && ending > otherEnding))
                                                    return false;
                                            }
                                        }
                                    }
                                    // Update the associated raphael trade object with the new start and end time
                                    allTradesInShift[label.slice(5, 6)].dataset({'dirty': true,'start': starting,'end': ending});
                                    $(this).attr('start', starting.getTime()).attr('end', ending.getTime());
                                    var startingLabel = starting.format('compare');
                                    var endingLabel = ending.format('compare');
                                    $('#' + label).val('From: ' + startingLabel + ' To: ' + endingLabel);
                                }
                            });
                        });
                    }
                });
                $('.tradeDelete').hover(function()
                {
                    $(this).addClass('red');
                }, function()
                {
                    $(this).removeClass('red');
                }).each(function(index, element)
                {
                    var tradeId = $(this).attr('tradeId');
                    $(this).click({'parent': parent,'tradeId': tradeId}, removeTrade);
                });
                $('.tradeComment').button({'icons': {'primary': 'ui-icon-comment'},'text': false})
                .click({'parentShift': parent}, commentTrade);
            });
        }
        rect.data('label', text);

        if ( editMode && (rect.data('hourType') && shiftTypes[shiftData.hourType]) && (permissionToEdit || (rect.data('hourType') && parseInt(rect.data('hourType')['selfSchedulable']) && user == $('#employee').val())) )
        {
        	// Slider for adjusting the start time of the shift
        	var sliderStart = paper.rect(0, 0, day, 2);
            sliderStart.data('name', 'sliderStart');
            sliderStart.data('type', 'shiftSliderStart');
            sliderStart.data('parent', rect);
            sliderStart.attr({cursor: 'row-resize',fill: 'gray',opacity: 0.01,'stroke-opacity': 0.01});
            sliderStart.drag(adjustShiftStartMove, adjustShiftStartStart, adjustShiftStartEnd);
            rect.data('sliderStart', sliderStart);

            // Slider for adjusting the end time of the shift
            var sliderEnd = paper.rect(0, 0, day, 2);
            sliderEnd.data('name', 'sliderEnd');
            sliderEnd.data('type', 'shiftSliderEnd');
            sliderEnd.data('parent', rect);
            sliderEnd.attr({cursor: 'row-resize',fill: 'gray',opacity: 0.01,'stroke-opacity': 0.01});
            sliderEnd.drag(adjustShiftEndMove, adjustShiftEndStart, adjustShiftEndEnd);
            rect.data('sliderEnd', sliderEnd);
        }

        allShifts.push(rect);

        return rect;
    }

    // Pull shift objects in ajax request, then draw them to the schedule/canvas
    function getShifts()
    {
        var page = '../newSchedule/returnWeeklyShifts.php?employee=' + $('#employee').val() + '&startDate=' + currentWeek.format('dateOnly');
        // Call the default shifts page if in defaultMode... More needs to be done here and the default shift is hard coded
        if ( defaultMode )
        {
        	var period = $('#period').val();
        	if ( !period && $('#passedPeriod').val() )
        	{
        		period = $('#passedPeriod').val();
        	}
            page = '../newSchedule/returnDefaultShifts.php?employee=' + $('#employee').val() + '&period=' +period;
            // Get related period's notes
            $.ajax('periodNotes.php',{'data':
        	{
	        	'employee': $('#employee').val(),
	        	'period': $('#period').val()
        	}, 'type': 'POST'}).done(function(data, status)
        	{
        		data = JSON.parse(data);
        		$('#periodNotes').val(data['notes']);
        		$('#hoursRequested').val(data['requestedHours']);
        		$('#hoursRegistered').val(data['registeredHours']);
        	});
        }
        else
        {
            // Get related period's notes
            $.ajax('periodNotesWeekly.php',{'data':
        	{
	        	'employee': $('#employee').val(),
	        	'weekStart': currentWeek.format('dateOnly')
        	}, 'type': 'POST'}).done(function(data, status)
        	{
        	    try
        	    {
        		    data = JSON.parse(data);
        		    $('#periodNotes').val(data['notes']);
            		$('#hoursRequested').val(data['requestedHours']);
            		$('#hoursRegistered').val(data['registeredHours']);
        		}
        		catch (e)
        		{
        		    notify('No schedule notes received.', {'status': 'warning'});
        		}
        	});
        }
        var cb = function(result)
        {
            var shifts = JSON.parse(result);

            if ( shifts.length )
            {
                for (var i = 0, length = shifts.length; i < length; i++)
                {
                    if ( defaultMode )
                    {
                        // Create Start Date Object
                        // shifts[i].start = parseDate('2012-01-0'+shifts[i].startDate, shifts[i].startTime);
                        var dbShiftStart = new Date(currentWeek.getTime());
                        var fromSaturdayToWeekStart = currentWeek.getDay()+1;
                        if ( fromSaturdayToWeekStart == 7 )
                        {
                        	fromSaturdayToWeekStart = 0;
                        }
                        
                        var startDaysFromSaturday = parseInt(shifts[i].startDate);
                        if (startDaysFromSaturday == 7)
                        {
                            startDaysFromSaturday = 0;
                        }
                        dbShiftStart.setDate(dbShiftStart.getDate() + startDaysFromSaturday - fromSaturdayToWeekStart);
                        shifts[i].start = parseDate(dbShiftStart.format('dateOnly'), shifts[i].startTime)

                        // Create End Date Object
                        // shifts[i].end = parseDate('2012-01-0'+shifts[i].endDate, shifts[i].endTime);
                        var dbShiftEnd = new Date(currentWeek.getTime());
                        var endDaysFromSaturday = parseInt(shifts[i].endDate);
                        if (endDaysFromSaturday == 7)
                        {
                            endDaysFromSaturday = 0;
                        }
                        dbShiftEnd.setDate(dbShiftEnd.getDate() + endDaysFromSaturday - fromSaturdayToWeekStart);
                        shifts[i].end = parseDate(dbShiftEnd.format('dateOnly'), shifts[i].endTime);
                    }
                    else
                    {
                        // Create Start Date Object
                        shifts[i].start = parseDate(shifts[i].startDate, shifts[i].startTime);
                        // Create End Date Object
                        shifts[i].end = parseDate(shifts[i].endDate, shifts[i].endTime);
                    }
                    if ( shifts[i].start > shifts[i].end )
                    {
                        shifts[i].end.setDate(shifts[i].end.getDate()+7);
                    }
                	var newShift = drawShift(shifts[i]);
                	newShift.data('dirty', false);
                }
                // Get associated trades for shifts if in trade mode
                if (!defaultMode)
                {
                    getTrades();
                }
            }
        };

        callPhpPage(page, cb);
    }

    // Returns the hex code for the opposite color of a color passed in hex
    function getOppositeColor(c)
    {
        c = c.toUpperCase();
        var result = '';
        var ch = '';
        var list1 = '0123456789ABCDEF';
        var list2 = 'FEDCBA9876543210';
        for (var i = 0; i < c.length; i++)
        {
            ch = c.charAt(i);
            for (var n = 0; n < list1.length; n++)
            {
                if (ch == list1.charAt(n))
                    result += list2.charAt(n);
            }
        }
        return '#' + result;
    }

    var allTrades = Array();
    // Pull shift objects in ajax request, then draw then to the schedule
    if (!defaultMode)
    {
        function getTrades()
        {
            var page = '../newSchedule/returnTrades.php?employee=' + $('#employee').val() + '&startDate=' + currentWeek.format('dateOnly');

            var cb = function(result)
            {
                var trades = JSON.parse(result);

                if (trades.length)
                {
                    if (editMode)
                    {
                        alert('\nWarning:\n\nThere are shifts with associated trades for this week.\nAltering those shifts will alter or remove the trades.');
                    }
                    else
                    {
                        for (var i = 0, length = trades.length; i < length; i++)
                        {
                            // If trade has been approved don't worry about it
                            // I'm doing this because showing approved trades gets ugly!!
                            // You can change this once we stop using the checkbox, hour-to-hour trading system
                            if (trades[i]['bids'] != 2)
                            {
                                // Create Start Date Object
                                trades[i].start = parseDate(trades[i].startDate, trades[i].startTime);

                                // Create End Date Object
                                trades[i].end = parseDate(trades[i].endDate, trades[i].endTime);

                                var newTrade = drawTrade(trades[i]);
                                newTrade.data('dirty', false);
                            }
                        }
                    }
                }
            };

            callPhpPage(page, cb);
        }
    }

    // Draw Trade to Canvas
    function drawTrade(tradeObject)
    {
        var type = tradeObject.constructor.prototype;
        if (type == Raphael.el)
        {
            // Create Date Objects from shiftData
            var startDate = tradeObject.data('start');
            var endDate = tradeObject.data('end');

            // Calculate position on canvas
            var startDayOfWeek = startDate.getDay() + 1;
            if (startDayOfWeek == 7)
                startDayOfWeek = 0;
            var startTimeOfDay = startDate.getHours() + startDate.getMinutes() / 60;
            var duration = (endDate.getTime() - startDate.getTime()) / millisecondsPerHour;
            var strokeColor, dashArray;
            if (tradeObject.data('shift'))
            {
                strokeColor = getOppositeColor(Raphael.getRGB(tradeObject.data('shift').attr('fill'))['hex']);
                if (parseInt(tradeObject.data('bids')))
                    dashArray = '';
                else
                    dashArray = '- .';
            }
            else
            {
                strokeColor = 'green';
                dashArray = '';
            }
            tradeObject.attr({'x': hourLabel + day * startDayOfWeek - (day * areaInfo['startDay']),'y': dayLabel + hour * startTimeOfDay - (hour * areaInfo['startTime']),'width': day,'height': duration * hour,'fill': 'none','stroke-width': 2,'stroke': strokeColor,'stroke-dasharray': dashArray});
        }
        if (type == Object().constructor.prototype)
        {

            // Draw Shift according to shift data
            tradeObject = drawTrade(makeRaphaelTrade(tradeObject));

        }
        return tradeObject;
    }

    // Create a raphael trade object
    function makeRaphaelTrade(tradeData)
    {
        // Creates a rectangle with coordinates of x = 0, y = 0, width = 0, height = 0, and a 5px rounded corner
        var rect = paper.rect(0, 0, 0, 0, 0);
        // Sets the stroke opacity to 0 to make the stroke invisible
        rect.attr('stroke-opacity', 1);

        if (tradeData)
        {
            // Set Trade Data
            rect.data('ID', tradeData.ID);
            rect.data('postedBy', tradeData.postedBy);
            rect.data('postedDate', tradeData.postedDate);
            rect.data('approvedBy', tradeData.approvedBy);
            rect.data('approvedOn', tradeData.approvedOn);
            rect.data('shiftId', tradeData.shiftId);
            if (rect.data('shiftId'))
            {
                allShifts.forEach(function(parent)
                {
                    if (parent.data('shiftId') == rect.data('shiftId'))
                    {
                        rect.data('shift', parent);
                        parent.data('trade').push(rect);
                        return false;
                    }
                });
            }
            rect.data('startDate', tradeData.startDate);
            rect.data('startTime', tradeData.startTime);
            rect.data('endDate', tradeData.endDate);
            rect.data('endTime', tradeData.endTime);
            var originalStart = false;
            if (tradeData.start)
            {
                originalStart = new Date(tradeData.start.getTime());
            }
            rect.data('originalStart', originalStart);
            rect.data('start', tradeData.start);
            var originalEnd = false;
            if (tradeData.end)
            {
                originalEnd = new Date(tradeData.end.getTime());
            }
            rect.data('originalEnd', originalEnd);
            rect.data('end', tradeData.end);
            rect.data('originalHourType', window.shiftTypes[tradeData.hourType]);
            rect.data('hourType', window.shiftTypes[tradeData.hourType]);
            rect.data('bids', tradeData.bids);
            rect.data('notes', tradeData.notes);
            rect.data('originalNotes', tradeData.notes);
            rect.data('area', tradeData.area);
        }
        rect.data('type', 'trade');
        rect.data('dirty', true);
        allTrades.push(rect);

        return rect;
    }


    function onTradeChange(value, key)
    {
        if (this.data('type') == 'trade')
            drawTrade(this);
    }
    eve.on('*.data.set.*.start', onTradeChange);
    eve.on('*.data.set.*.end', onTradeChange);

    // Determine the duration of a raphael shift object
    function calculateDuration(raphaelShift)
    {
        var duration = raphaelShift.data('end') - raphaelShift.data('start');
        duration = duration / millisecondsPerHour;
        return duration;
    }

    var hourTotalRaphaelObjects = new Object();
    var rTotalHours = undefined;
    // Calculate the hour totals for each type of shift and display that information at the bottom of the canvas
    function calculateHourTotals()
    {
        allShifts.forEach(function(currentShift)
        {
            // Checks to see if the current shift has been deleted
            if (currentShift.isVisible())
            {
                var duration = calculateDuration(currentShift);
                currentShift.data('duration', duration);
                // This can be used to calculate the number of hours being traded, etc.
                // allowing us to make changes to the number of hours allowed or whatever we want
                if (currentShift.data('type') == 'trade')
                {

                }
                else if (typeof currentShift.data('hourType').totalDuration == 'undefined' || isNaN(currentShift.data('hourType').totalDuration))
                {
                	// This is directly accessing the shiftType object found in the allShiftTypes and shiftTypes arrays
                    currentShift.data('hourType').totalDuration = duration;
                }
                else
                {
                    currentShift.data('hourType').totalDuration += duration;
                }
            }
        });

        var yValue = yMax - (0.75 * dayLabel);
        var xValue = hourLabel / 12;
        var yTextValue = yMax - (dayLabel / 2);
        var heightAndWidth = dayLabel / 2;
        var totalTimeForAllTypes = 0;
        for (shiftType in allShiftTypes)
        {
            if (shiftType in hourTotalRaphaelObjects)
            {
                hourTotalRaphaelObjects[shiftType].data('label').remove();
                hourTotalRaphaelObjects[shiftType].remove();
                delete hourTotalRaphaelObjects[shiftType];
            }
            if (allShiftTypes[shiftType].totalDuration > 0)
            {
                if (typeof hourTotalRaphaelObjects[shiftType] == 'undefined')
                {
					/****
					***
					** At some point it might be nice to dynamically extend the height of the container
					** div when the number of hour types being scheduled exceeds the width of the canvas
					** just check to see if the bounding box is past the allowable area if it is then
					** push the hour type to a second row and move it back to the furthest left point
					**
					** To increase the paper size
					** paper.setSize(window.xMax, window.ymax+=25)
					**
					** To increase the containing div size
					** $('#employeeScheduleContainer').animate({'height': '+=25'}, 500)
					**
					** Good Luck (Read that in the voice of the guy from 'Taken')
					***
					****/

                    var variableStroke = 'silver';
                    var dashArray = '';
                    var opacity = 1;
                    if ( !shiftTypes[shiftType] )
                    {
                    	variableStroke = 'black';
	                    dashArray = '. ';
	                    opacity = 0.8;
                    }
                    var shiftTypeTotal = paper.rect(xValue, yValue, heightAndWidth, heightAndWidth, 0).attr({'fill': allShiftTypes[shiftType].color, 'title': allShiftTypes[shiftType].longName, 'stroke': variableStroke, 'stroke-dasharray': dashArray, 'opacity': opacity});
                    var shiftTypeTotalText = paper.text(xValue + heightAndWidth + (hourLabel / 12), yTextValue, Math.floor(allShiftTypes[shiftType].totalDuration) + ' h ' + Math.round(60 * remainder(allShiftTypes[shiftType].totalDuration, 1)) + ' m ').attr({'text-anchor': 'start'});
                    shiftTypeTotal.data('label', shiftTypeTotalText);
                    var labelBox = shiftTypeTotalText.getBBox();
                    xValue = (hourLabel / 6) + labelBox.x + labelBox.width;
                    if ( !parseInt(allShiftTypes[shiftType]['nonwork']) )
                    {
                    	totalTimeForAllTypes += allShiftTypes[shiftType].totalDuration;
                    }
                    hourTotalRaphaelObjects[shiftType] = shiftTypeTotal;
                    allShiftTypes[shiftType].totalDuration = 0;
                }
                else
                    console.log("Problem with function 'calculateHourTotals'!");
            }
        }
        if (!(typeof rTotalHours == 'undefined'))
        {
            rTotalHours.remove();
            rTotalHours = undefined;
        }
        rTotalHours = paper.text(xValue, yTextValue, 'All: ' + Math.floor(totalTimeForAllTypes) + ' h ' + Math.round(60 * remainder(totalTimeForAllTypes, 1)) + ' m ').attr({'text-anchor': 'start','font-weight': 'bold'});
    }

    // Check for hour totals every second
    timeoutsArray.push(setInterval(calculateHourTotals, 1000));

    // This is used to get a point on the paper element for a given time/date
    // The isSnapped variable is to state whether or not you would like the nearest
    // allowed point for the given time, or if you would prefer an exact point for the
    // exact time given.
    function getPointFromTime(dateTimeObject, isSnapped)
    {
        var xAndY = new Object();
        // Calculate position on canvas
        var dayOfWeek = dateTimeObject.getDay() + 1;
        if (dayOfWeek == 7)
            dayOfWeek = 0;
        var timeOfDay = dateTimeObject.getHours() + dateTimeObject.getMinutes() / 60;
        xAndY['y'] = dayLabel + hour * timeOfDay - (hour * areaInfo['startTime']);
        xAndY['x'] = hourLabel + day * dayOfWeek - (day * areaInfo['startDay']);
        if (isSnapped === true)
        {
            // Determine the number of days away from Saturday the double click event occured
            var daysFromSaturday = Math.floor((xAndY['x'] - hourLabel) / day);

            // Determine the Starting X and Y position for the newShift to be created
            xAndY['x'] = hourLabel + (daysFromSaturday * day);
            xAndY['y'] = dayLabel + (Math.floor((xAndY['y'] - dayLabel) / sizeToSnapToHour) * sizeToSnapToHour);
        }

        return xAndY;
    }

    // This function does the inverse of the above function, i.e. it gives you a time
    // for any given point on the canvas. If the isSnapped variable is set to true then
    // the time returned will be the closest floored allowable moment in time to the given
    // coordinates. This means if the time that would be returned were 5:45 and we only schedule
    // on the hour, then the time returned at 5:00.
    function getTimeFromPoint(x, y, isSnapped)
    {
        var dateTime = new Date(currentWeek.getTime());

        // I add an extra pixel here because I was having a problem with some rounding
        // down to the previous day
        var daysFromAreaStartDay = Math.floor((x + 0.4 - hourLabel) / day);

        dateTime.setDate(dateTime.getDate() + daysFromAreaStartDay);

        var hours = Math.floor((y - dayLabel) / hour);
        var minutes = Math.round(remainder((y - dayLabel), hour) * 60 / hour);
        if (isSnapped === true)
        {
            var totalTime = hours * 60 + minutes;
            var snappyness = (sizeToSnapToHour / hour) * 60;
            totalTime = Math.floor(totalTime / snappyness) * snappyness;
            hours = Math.floor(totalTime / 60);
            minutes = Math.round(remainder(totalTime, 60));
        }

        dateTime.setHours(hours);
        dateTime.setMinutes(minutes);
        dateTime.setMilliseconds(0);

        return dateTime.getTime();
    }

    // Draw Shift to Canvas
    function drawShift(shiftObject)
    {
        var type = shiftObject.constructor.prototype;
        if (type == Raphael.el)
        {
        	// Create Date Objects from shiftData
            var startDate = shiftObject.data('start');
            var endDate = shiftObject.data('end');

            // Calculate position on canvas
            var startDayOfWeek = startDate.getDay() + 1;
            if (startDayOfWeek == 7)
                startDayOfWeek = 0;
            var startTimeOfDay = startDate.getHours() + startDate.getMinutes() / 60;
            var duration = (endDate.getTime() - startDate.getTime()) / millisecondsPerHour;
            var xCoordinate = hourLabel + day * startDayOfWeek - (day * areaInfo['startDay']);
            var yCoordinate = dayLabel + hour * (startTimeOfDay - parseFloat(areaInfo['startTime']));
            var width = day;
            var height = duration * hour;
            
            // If the shift is from another area it is possible that it might fall outside our allowable x and y mins and maxes, therefore we have the huge mess of if-elses that follows
            // What is the earliest any shift getting displayed can start (in hours)
        	var timeStartLimit = new Date(startDate.getTime());
        	timeStartLimit.setMinutes(minAreaStart.getMinutes());
        	timeStartLimit.setHours(minAreaStart.getHours());
        	
        	var timeEndLimit = new Date(startDate.getTime());
        	timeEndLimit.setMinutes(maxAreaEnd.getMinutes());
        	timeEndLimit.setHours(maxAreaEnd.getHours());
        	// If we end at midnight we need to add a day since it is technically the next day.
        	if ( timeEndLimit.getHours()+(timeEndLimit.getMinutes()/60) == 0 )
        	{
        		timeEndLimit.setDate(1+timeEndLimit.getDate());
        	}
        	
        	// Make sure the shift falls within a displayable time range and date
            if ( endDate <= minAreaStart || startDate >= maxAreaEnd )
            {
            	// If it is completely outside our minAreaStart or maxAreaEnd then don't show it
            	// This is horribly hackish but there isn't a nice way to hide things in raphael and still make sure the shift is treated as existing
            	xCoordinate += xMax*2;
            	yCoordinate += yMax*2;
            }
            else if ( startDate >= timeStartLimit && endDate <= timeEndLimit )
            {
            	// Do nothing this shift should be displayed this is just here to prevent the other scenarios from running on any of these cases
            	// I.e. case number three--the strangest case o.O
            }
            else if ( (startDate < timeStartLimit && endDate < timeStartLimit) || (startDate > timeEndLimit && endDate > timeEndLimit) )
            {
            	// This catches all shifts that are completely outside of the **schedulable hours** for this area
            	xCoordinate += xMax*2;
            	yCoordinate += yMax*2;
            }
            else if ( timeEndLimit < timeStartLimit && startDate < timeEndLimit && endDate > timeStartLimit )
            {
            	// This is the strangest scenario, it occurs when two portions of the same shift are displayable, we therefore treat it as if it were two shifts (this situation could only ever occur with cross day shifts)
                // It looks insane but look carefully at how timeStartLimit and timeEndLimit are defined, they use (respectively) the shift's end date and start date as the basis for their own date
                // Therefore it is possible to have a start < the timeEndLimit and an end > than the timeStartLimit that does not meet the start > timeStartLimit and end < timeEndLimit case
                // AND it is possible for the timeEndLimit to be less than the timeStartLimit
            
            	// We are going to split this shift into two separate parts, one will be pushed to the shifts array for later processing but will be a clone of the current shift except for its start and end
            	// this includes its id number, etc. (we will be preventing any editing of shifts later on for areas other than the one we are currently in so that should prevent any problems with how
            	// this is treated on the backend and the database since it should never be marked as dirty)
            	
            	// Clone shift
            	var rectClone = shiftObject.clone();
            	
            	// Now adjust start and end dates in both objects as necessary
            	height += ((startDate-timeEndLimit)/millisecondsPerHour)*hour;
            	
            	// Get clone start and end points
            	var startpoint = getPointFromTime(timeStartLimit.getTime());
            	var endPoint = getPointFromTime(endDate.getTime());
            	
            	// SIDE NOTE: None of this code has ever been tested because it requires that cross day shifts be working first, nonetheless I think it should work
            	rectClone.attr( {'height': endPoint['y']-startPoint['y'], 'y': startPoint['y']} );
            }
            else
            {
            	// The same thing applies here as in the previous case, none of these should be marked as dirty, so we shouldn't need to worry about them getting back to the database and wreaking havoc there
            	// If the start is before the timeStartLimit the start is now the timeStartLimit
            	if ( startDate < timeStartLimit && endDate.getHours()+(endDate.getMinutes()/60) != 0.00 )
                {
                	height += ((startDate-timeStartLimit)/millisecondsPerHour)*hour;
                	yCoordinate=dayLabel;
                }
                // If the end time is past the timeEndLimit the end is now the timeEndLimit
                if ( endDate > timeEndLimit )
                {
                	height += ((timeEndLimit-endDate)/millisecondsPerHour)*hour;
                	yCoordinate=yMax-dayLabel-height;
                }
            }

            // Finally we are ready to set the width, height, and x/y Coordinates
            shiftObject.attr({'x': xCoordinate,'y': yCoordinate,'width': width,'height': height});
            if (text = shiftObject.data('label'))
            {
                text.attr({x: shiftObject.attrs.x + (shiftObject.attrs.width / 2)});
                text.attr({y: shiftObject.attrs.y + (hour * timeProportionRatio / 4)});
                text.attr('text', shiftObject.data('start').format('timeEntry') + '-' + shiftObject.data('end').format('timeEntry'));
                text.toFront();
            }
            if ( sliderStart = shiftObject.data('sliderStart') )
            {
                var sliderStartY = shiftObject.attr('y');
                sliderStart.attr({x: shiftObject.attr('x'),y: sliderStartY});
                sliderStart.toFront();
            }
            if ( sliderEnd = shiftObject.data('sliderEnd') )
            {
                var sliderEndY = shiftObject.attr('y') + shiftObject.attr('height') - 2;
                sliderEnd.attr({x: shiftObject.attr('x'),y: sliderEndY});
                sliderEnd.toFront();
            }
        }
        if (type == Object().constructor.prototype)
        {

            // Draw Shift according to shift data
            shiftObject = drawShift(makeRaphaelShift(shiftObject));

        }
        return shiftObject;
    }

    // Drag and Drop functionality can be added later
    // DRAG AND DROP

    // Variable to determine if warning about a shift being shortened automatically ought to be displayed on up call.
    // This variable is not yet in use, it would be used to notify a user when they are shortening a shift by
    // trying to put it into the next schedulable week/period. We do not plan on supporting cross week/period shifts
    // unless we use a 2 week scheduling period. Even in that case we would not support scheduling into the next two
    // week period. Instead we shorten the shift automagically.
    var autoShortenedShiftAlert = false;

    var start = function()
    {
        if (this.data('type') != 'mainRectangle')
        {
            currentShift = this.data('parent');
        }
        else
        {
            currentShift = this;
        }

        currentShift.toFront();
        currentShift.dataset(['start']);

        // storing original coordinates
        currentShift.ox = currentShift.attr("x");
        currentShift.oy = currentShift.attr("y");
        if (browser == 'Firefox')
            var shiftCursor = '-moz-grabbing';
        else
            var shiftCursor = '-webkit-grabbing';
        currentShift.attr({opacity: .66,cursor: shiftCursor});

        // Get text data if exists
        var text = currentShift.data('label');
        if (text)
        {
            if (text.data('underline'))
                text.data('underline').remove();
            text.ox = text.attr("x");
            text.oy = text.attr("y");
            text.attr({opacity: 1});
            text.toFront();
        }
    },
    move = function(dx, dy, cursorX, cursorY, event)
    {
        if (this.data('type') != 'mainRectangle')
            currentShift = this.data('parent');
        else
            currentShift = this;

        currentShift.attr('cursor', 'grabbing');

        // Determine New Coordinates for shift
        var newY = currentShift.oy + Math.round(dy / sizeToSnapToHour) * sizeToSnapToHour;
        var newX = currentShift.ox + Math.round(dx / day) * day;

        // This is a temp fix for dealing with the shifts prior to using the endChild
        if (newY < dayLabel)
            newY = dayLabel;
        if (newY + currentShift.attr('height') > yMax - dayLabel)
            newY = yMax - dayLabel - currentShift.attr('height');
        if (newX < hourLabel)
            newX = hourLabel;
        if (newX + currentShift.attr('width') > xMax - hourLabel)
            newX = xMax - hourLabel - currentShift.attr('width');

        // Now actually set the new X and Y values
        currentShift.attr({y: newY});
        currentShift.attr({x: newX});
        // Use this for debugging if you want
        // console.log('cursor('+cursorX+', '+cursorY+'), old('+currentShift.ox+', '+currentShift.oy+'), delta('+dx+', '+dy+'), new('+newX+', '+newY+')');

        // Use this code to work with endChilds for cross-day shifts instead of using the above code
        // if (newY >= dayLabel && (newY+currentShift.attr('height')) <= (yMax-dayLabel))
        // if (newX >= hourLabel && (newX+currentShift.attr('width')) <= (xMax-hourLabel))

        // Update Label position
        if (currentShift.data('label'))
        {
            currentShift.data('label').attr({x: currentShift.attr('x') + (day / 2),y: currentShift.attr('y') + (hour * timeProportionRatio / 4)})
        }
        // Update SliderStart position
        var sliderStart = currentShift.data('sliderStart');
        if (sliderStart)
        {
            sliderStart.attr({x: currentShift.attr('x'),y: currentShift.attr('y')});
        }
        // Update slider end position
        var sliderEnd = currentShift.data('sliderEnd');
        if (sliderEnd)
        {
            sliderEnd.attr({x: currentShift.attr('x'),y: currentShift.attr('y') + currentShift.attr('height') - 2});
        }
    },
    up = function()
    {
        if (this.data('type') != 'mainRectangle')
            currentShift = this.data('parent');
        else
            currentShift = this;

        // restoring state
        if (browser == 'Firefox')
            var shiftCursor = '-moz-grab';
        else
            var shiftCursor = '-webkit-grab';
        currentShift.attr({opacity: 1,cursor: shiftCursor});
        if (currentShift.data('label'))
        {
            currentShift.data('label').toFront();
            currentShift.data('label').attr({opacity: 1});
        }
        if (currentShift.data('endChild') && !currentShift.data('endChild').removed)
        {
            currentShift.data('endChild').attr({opacity: 1,cursor: shiftCursor});
        }
        if (currentShift.data('sliderStart'))
        {
            currentShift.data('sliderStart').toFront();
        }
        if (currentShift.data('sliderEnd'))
        {
            currentShift.data('sliderEnd').toFront();
        }

        // Remember to fix this so it will work with child rectangles

        // Update Start of Shift
        var start = currentShift.data('start');
        var xChange = parseInt(((currentShift.attr('x') - currentShift.ox) / day).toFixed(0));
        start.setDate(start.getDate() + xChange)
        start.setHours(Math.floor(((currentShift.attr('y') + (parseFloat(areaInfo['startTime'] * hour))) - dayLabel) / hour));
        var newStartMinutes = Math.round(remainder(((currentShift.attr('y') + (parseFloat(areaInfo['startTime'] * hour))) - dayLabel), hour) * 60 / hour);
        start.setMinutes(newStartMinutes);

        // Update End of Shift
        var end = currentShift.data('end');
        end.setTime(getTimeFromPoint(currentShift.attr('x'), currentShift.attr('y') + currentShift.attr('height') + (parseFloat(areaInfo['startTime'] * hour))));
        // end.setDate(start.getDate());
        // // Get virtual height here once you create the child element
        // end.setHours(start.getHours()+Math.floor((currentShift.attr('height')/hour)));
        // var newEndMinutes =Math.round(remainder(currentShift.attr('height'),hour)*60/hour)+start.getMinutes();
        // end.setMinutes(newEndMinutes);

        // Renew our lock on the schedule since we are actively editing it
        eve('schedule.lock', this, 1);

        if (currentShift.data('label'))
            currentShift.data('label').attr('text', currentShift.data('start').format('timeEntry') + '-' + currentShift.data('end').format('timeEntry'));

        resolveShiftConflicts(currentShift);

        if (currentShift.attr('y') != currentShift.oy || currentShift.attr('x') != currentShift.ox)
            currentShift.data('dirty', true);
    };

}
