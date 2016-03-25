// THIS IS LOADED BY THE "supervisorDashboard.php" FILE
function loadJs(){
// THE "View Your Report So-far" BUTTON IS BOUND TO THE "viewReportLog" FUNCTION 
	$('#viewSoFar').click(function () { isDialogOpen = true; viewReportLog(); });

// THE "Eng On-call" BUTTON IS BOUND TO THE "engOnCall" FUNCTION
	$('#viewOnCall').click(function () { isOnCallOpen = true; engOnCall(); });

// CREATE THE SPINNER DIALOG FOR WHEN THINGS ARE LOADING
	$spinner = $('<div id="spinner" class="spinner"></div>')
					.html("<div style='text-align: center; width: 100%;'><p>Please wait, processing.</p><img id='img-spinner' src='supReportLoading.gif' alt='Loading'/> </div>")
					.dialog({
						autoOpen: false,
						dialogClass: 'no-close',
						width: 'auto',
						height: 'auto',
						modal: true,
						title: 'Loading . . . ',
						draggable: false,
						resizable: false
					});


// BIND THE ENTER KEY TO SEARCHING THE BYU KB FOR THE BYU KB INPUT
	$('#byuKb:input').bind('keypress', function(e) 
	{
		var code = (e.keyCode ? e.keyCode : e.which);
	    if(code==13)
		{
			value = $('#byuKb:input').val();
			if (onlyDigits(value))
			{
				newwindow("https://kb.byu.edu/docsystem/displaykb.php?id="+value, 750, 750);
			}
			else
			{
				newwindow("https://kb.byu.edu/docsystem/index.php?advsearch=advsearch&startatnum=0&maxrelpass=-1&searchpass="+value+"&prodown=BYU&prodpass=&prodpassbyu=645&mrnum=25&kbcheck=true&softcheck=true&hardcheck=true&afterdate=&beforedate=&searchtype=AND&createdby=&statusfilter=standard&articletype=All", 1035, 750);
			}
			$('#byuKb').val('');
			$('#byuKb').focusout();
	    }
	});
	// END "loadJs" FUNCTION
}

// SEE ABOVE FOR USE OF THE SPINNER
var $spinner = null;

// CHECK IF STRING IS ONLY DIGITS
var isWhole_re = /^\s*\d+\s*$/;
function onlyDigits(s)
{
   return String(s).search (isWhole_re) != -1
}

// SEE IF ANY HIDDEN REMINDER DIVS EXIST AND GET THEIR CONTENT AND PRODUCE AN ALERT TO REMIND THE USER (USED WITH HIGH PRIORITY TICKETS AND RFC'S)
function reminderCheck()
{
	$(".reminderAlert").each(function () { alert($(this).attr('title')); });
}

// AN IMPROVED VERSION OF THE NEW WINDOW FUNCTION FOUND ON THE BYU KB PAGE, USED WITH THE ENG ON-CALL DIALOG BOX AND A VARIETY OF OTHER POP-UP WINDOWS 
function newwindow(urlpass, width, height)
{
	if (!width) var width = 500;
	if (!height) var height = 350;
    window.open(urlpass,"Routine Task","status=1,width="+width+",height="+height+",scrollbars=1");
}

// LOADS HIGH PRIORITY TICKETS FROM SERVICE NOW THAT ARE EITHER ACTIVE OR HAVE BEEN CLOSED WITHIN THE PAST 12 HOURS
function loadHiPri()
{
	var page = "highPriority.php?p="+passy;
	var cb = function (result) {
		$("#hiPriTickets").html(result);
		sorttable.makeSortable(document.getElementById("hiPriTable"));
	}
	callPhpPage(page, cb);
}

// LOADS WHO OUGHT TO BE WORKING FOR A SPECIFIC HOUR (THE "div" PARAMETER IS REALLY A REMENANT OF HOW THIS FUNCTION USED TO WORK AND COULD BE REPLACED WITH A STATIC "whoIs"
var hour = 0;
function whosHere(hourMod, div)
{
	var page = "whosHere.php?hourMod="+hourMod;
	var cb = function(result){
		$('#'+div).html(result);
	};
	supDisplayDate();
	callPhpPage(page, cb);
}

// USED TO SHOW ONLY PICTURES OF EMPLOYEES AFTER A SECURITY VIOLATION, ETC. HAS BEEN SUBMITTED OR CANCEL HAS BEEN CLICKED (SEE FUNCTIONS BELOW FOR USE CASES)
function whosHereCancel()
{
	whosHere(hour, "whoIs");
}

// LOAD ALL ROUTINE TASKS CURRENTLY DUE (THE FILE CALLED IS ACTUALLY JUST A DUMMY FILE THAT CALLS A FUNCTION "getSupDashboardTasks" IN ANOTHER PHP FILE "../routineTaskList/routineTaskTable.php" 
function loadList()
{
	var page = 'printTaskList.php';
	var cb = function(result) {
		$("#dueTasks").html(result);
	}
	callPhpPage(page, cb);
}

// A Union function for combining results from calls to the RFC API, it can handle arrays or objects of rfcs
function unionRfcs()
{
    // Determine how many arrays we need to union
    var args = arguments,
    l = args.length,
    obj = {},
    res = [],
    i, j, k;

    // For each array to be unioned
    while (l--)
    {
        k = args[l];
        i = k.length;

        // For each element in the array
        while (i--)
        {
            j = k[i];
            // Push it to our object if it is not already defined
            if (!(j.number in obj))
            {
                obj[j.number] = 1;
                res.push(j);
            }
        }   
    }
    return res;
}

// Reload and re-render rfcs
function displayRfcs()
{
    var rfcs = new Array();
    var isCompleted = false;
    $.ajax('/API/rfc/',{'data':
    {
        'query': "start_date<javascript:gs.nowNoTZ()^ORwork_start<javascript:gs.nowNoTZ()^end_date>javascript:gs.nowNoTZ()^ORwork_end>javascript:gs.nowNoTZ()^state<50^ORDERBYstart_date^ORDERBYnumber"
    }, 'type': 'POST'}).done(function(response, status)
    {
        response = JSON.parse(response);
        if (!response.error)
        {
            rfcs = unionRfcs(response.data, rfcs);
        }
        if (isCompleted)
        {
            if (rfcs)
            {
                getUsersAndGroups(rfcs);
            }
            else
            {
                // Print some message stating that no rfcs were pulled
                renderNoRfcs();
            }
        }
        isCompleted = true;
    });
    
    $.ajax('/API/rfc/',{'data':
    {
        'query': "start_dateBETWEENjavascript:gs.hoursAgoStart(12)@javascript:gs.hoursAgoEnd(-12)^ORwork_startBETWEENjavascript:gs.hoursAgoStart(12)@javascript:gs.hoursAgoEnd(-12)^ORend_dateBETWEENjavascript:gs.hoursAgoStart(12)@javascript:gs.hoursAgoEnd(-12)^ORwork_endBETWEENjavascript:gs.hoursAgoStart(12)@javascript:gs.hoursAgoEnd(-12)^state<50^ORDERBYstart_date^ORDERBYnumber"
    }, 'type': 'POST'}).done(function(response, status)
    {
        response = JSON.parse(response);
        if (!response.error)
        {
            rfcs = unionRfcs(response.data, rfcs);
        }
        if (isCompleted)
        {
            if (rfcs)
            {
                getUsersAndGroups(rfcs);
            }
            else
            {
                // Print some message stating that no rfcs were pulled
                renderNoRfcs();
            }
        }
        isCompleted = true;
    });
}

function getUsersAndGroups(rfcs)
{
    // Get unique set of user ids in the most straight-forward way I could think of (so that when you read my code you know what is actually going on and stop complaining about my bad comments)
    var users = {};
    // Generate a set of all groups we need info for
    var groups = {};
    for (rfc in rfcs)
    {
        if (!(rfcs[rfc].assigned_to in users))
        {
            users[rfcs[rfc].assigned_to] = 1;
        }
        if (!(rfcs[rfc].u_service_manager in users))
        {
            users[rfcs[rfc].u_service_manager] = 1;
        }
        if (!(rfcs[rfc].assignment_group in groups))
        {
            groups[rfcs[rfc].assignment_group] = 1;
        }
    } // The comment at the beginning of this for-loop is meant as a joke for you! XD
    
    // Make requests for user and group details, ensure that both have completed prior to rendering the rfcs
    var isCompleted = false;
    // Generate query to get Service-now user/group info for rfcs
    $.ajax('/API/snUser/',{'data':
    {
        'query': 'sys_id='+Object.keys(users).join('^ORsys_id=')
    }, 'type': 'POST', 'dataType':'json'}).done(function(response, status)
    {
        users = objectify(response.data, 'sys_id');
        if (isCompleted)
        {
            renderRfcs(rfcs, users, groups);
        }
        isCompleted = true;
    });
    
    // Generate query to get Service-now user/group info for rfcs
    $.ajax('/API/snGroup/',{'data':
    {
        'query': 'sys_id='+Object.keys(groups).join('^ORsys_id=')
    }, 'type': 'POST', 'dataType':'json'}).done(function(response, status)
    {
        groups = objectify(response.data, 'sys_id');
        if (isCompleted)
        {
            renderRfcs(rfcs, users, groups);
        }
        isCompleted = true;
    });
}

// Turn an array of objects into an object of objects
function objectify(array, key)
{
    var obj = new Object();
	if(array === undefined) {
		return obj;
	}
    var i = array.length;
    while (i--)
    {
        obj[array[i][key]] = array[i];
    }
    return obj;
}

// LOAD ALL RFCS -12 HOURS AGO TO 12 HOURS FROM NOW, THEN MAKE THE RETURNED TABLE OF RFC'S SORTABLE
function renderRfcs(rfcs, users, groups)
{
    // I know this format isn't really pretty, it is actually just space efficient
    dateFormat.masks.pretty = "d mmm H:MMTT";
    
    // Sort RFCs (since they may have gotten mixed up during the union operation
    var i = rfcs.length;
    rfcs.sort(function(rfc, otherRfc)
    {
        var start = rfc.start_date;
        var otherStart = otherRfc.start_date;
        
        // Compare by start date first, if those are equal compare by rfc number
        if (start != otherStart)
        {
            return start < otherStart ? 1 : start > otherStart ? -1 : 0;
        }
        else
        {
            var number = rfc.number;
            var otherNumber = otherRfc.number;
            return number < otherNumber ? 1 : number > otherNumber ? -1 : 0;
        }
    });
    
    // This is a map of the different RFC types and the color they are displayed with
    var rfcTypeColorMap =
    {
        'request_for_change': "style=\"color:#0000FF;\"",
        "standard": "style=\"color:#00AA00;\"",
        "byu_emergency": "style=\"color:#FF0000;\""
    };
    var rfcTypeMap =
    {
        'request_for_change': "Request For Change",
        "standard": "Standard Change",
        "byu_emergency": "Emergency Change"
    };
    var rfcStateMap = 
    {
    		10 : 'Not Submitted',
    		20 : 'Submitted',
    		30 : 'Approved',
    		35 : 'Not Approved',
    		50 : 'Finished',
    		55 : 'Cancelled'
    };
    
    // We work through them backwards because it is both faster and because we want to render them with
    // those that start first listed as first
    
    // Build the table
    $('#rfcs').html('<table id="rfcTable" class="sortable"><th style="width: 11%;">Date</th><th style="width: 24%;">Name</th><th style="width: 55%;" class="sorttable_nosort">Purpose</th><th style="width: 10%;">Status</th></table>');
    var row = '';
    while (i--)
    {
        // Get next RFC (Ordered by  start_date, number)
        var rfc = rfcs[i];
        try{
        // Parse Dates
        var start = parseDate(rfc.start_date);
        var work_start = parseDate(rfc.work_start);
        
        var end = parseDate(rfc.end_date);
        var work_end = parseDate(rfc.work_end);
        row = "<tr class='rfc_anchor_row' id='rfc_row_"+rfc['sys_id']+"' number='"+rfc['number']+"'><td style='padding: 0;' sorttable_customkey="+rfc['start_date']+"_"+rfc['number']+">"+start.format("pretty")+"<br /><br />"+end.format("pretty")+"</td><td>"+
        '<a title=';
        try{
        row+= '"Group:\''+groups[rfc['assignment_group']]['name'] +'\'\n';
        } catch(e){
        		console.log('Error getting assignment group for '+rfc['number']);
        		console.log(rfc);
        }
        try{
        row+= 'Assigned:\''+ users[rfc['assigned_to']]['name'] +'\'\n';
        } catch(e){
        		console.log('Error getting assigned to for '+rfc['number']);
        		console.log(rfc);
        }
        try{
        row+= 'Manager:\''+ users[rfc['u_service_manager']]['name']+"'";
        } catch(e){
        		console.log('Error getting service manager for '+rfc['number']);
        		console.log(rfc);
        }
        var site = (env == 2) ? 'it.byu.edu' : 'ittest.byu.edu';
        row+= '" href="javascript:newwindow(\'https://'+site+'/change_request.do?sys_id='+
            // This is super nasty, but in my defense this was more of a copy paste of someone elses code
            rfc['sys_id']+'\', 660)" '+rfcTypeColorMap[rfc['type']]+'>'+
            rfc['number']+'<br/>'+rfcTypeMap[rfc['type']]+'</a></td>'+
        '<td id="short_descr_'+rfc['number']+'">'+rfc['short_description']+'</td><td>'+rfcStateMap[rfc['state']]+'</td></tr>';
        $("#rfcTable").append(row); // Append row to the table
        $("#short_descr_"+rfc['number']).click({rfc: rfc}, expandRFCforInput);
        } catch(e){
        		console.log("Error loading "+rfc['number']);
        		console.log(rfc);
        		console.log(e.message);// If an RFC is missing any of this information (only possible if it has not been submitted), it will not show up in the table.
        }
    }
	sorttable.makeSortable(document.getElementById("rfcTable"));
}

// Rendering for no RFCs
function renderNoRfcs()
{
    // No RFCs received type message
    $('#rfcs').html('<br /><div style="width: 100%; text-align: center; font-weight: bold; margin-bottom: 8px;"><table style="margin-left: auto; margin-right: auto;"><tr><th style="height: 2em; vertical-align: middle;">  No RFCs were received from ITSM.  </th></tr></table></div>');
}

// Parse an RFC formatted date
function parseDate(dateTimeString)
{
    // Milliseconds are always set to 0
    var date = new Date(Date.UTC(dateTimeString.substr(0,4),dateTimeString.substr(5,2)-1,dateTimeString.substr(8,2),dateTimeString.substr(11,2),dateTimeString.substr(14,2),dateTimeString.substr(17,2)));
    date.setMilliseconds(0);
    return date;
}


// LOAD ALL SUPERVISOR NOTES (SUPERVISOR NOTES ARE MEANT TO BE LEFT FOR THE NEXT SUPERVISOR AS SOMETHING THEY SHOULD KNOW ABOUT, i.e. "Turn-over Notes") 
function supNotes()
{
	var page = 'supNotes.php';
	var cb = function (result){
		$('#supNoteLog').html(result);
	}
	callPhpPage(page, cb);
}

// This opens a popup window with the on-call calendar
var isOnCallOpen = false;
function engOnCall()
{
	var params = [
    'height='+screen.height,
    'width='+screen.width,
    'fullscreen=yes' // only works in IE, but here for completeness
].join(',');
	newwindow("https://kb.byu.edu/oncall/index.php");
	



}

// MAKE MY JAVASCRIPT DATES PRINT TO LOOK HOWEVER I WANT
function supDisplayDate()
{
	$.ajax({
		url:"getHourSize.php",
		method:"GET",
		dataType:"json"
	}).success(function(response){  
		//Gets hourSize from the php and uses it to increment the hours that are printed in "Viewing:"
		dateFormat.masks.myTime = "h:MMtt - ddd dS, mmm yyyy";
		var d = new Date();
		var hourDiff = hour*response["hourSize"];
		d.setHours(Math.floor(d.getHours()+hourDiff));
		if(hourDiff < 0){ //Check if the hours are negative (going backwards), and adjust the math accordingly.
			d.setMinutes(d.getMinutes()-d.getMinutes()%(response["hourSize"]*60)-(hourDiff % 1)*60);
		}
		else{
			d.setMinutes(d.getMinutes()-d.getMinutes()%(response["hourSize"]*60)+(hourDiff % 1)*60);
		}
		str = d.format("myTime");
		$("#supDisplayDate").html("<b>Viewing:</b> "+str);
	});
}

/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 * 
 * Mask 	Description
 * d 	Day of the month as digits; no leading zero for single-digit days.
 * dd 	Day of the month as digits; leading zero for single-digit days.
 * ddd 	Day of the week as a three-letter abbreviation.
 * dddd 	Day of the week as its full name.
 * m 	Month as digits; no leading zero for single-digit months.
 * mm 	Month as digits; leading zero for single-digit months.
 * mmm 	Month as a three-letter abbreviation.
 * mmmm 	Month as its full name.
 * yy 	Year as last two digits; leading zero for years less than 10.
 * yyyy 	Year represented by four digits.
 * h 	Hours; no leading zero for single-digit hours (12-hour clock).
 * hh 	Hours; leading zero for single-digit hours (12-hour clock).
 * H 	Hours; no leading zero for single-digit hours (24-hour clock).
 * HH 	Hours; leading zero for single-digit hours (24-hour clock).
 * M 	Minutes; no leading zero for single-digit minutes.
 * Uppercase M unlike CF timeFormat's m to avoid conflict with months.
 * MM 	Minutes; leading zero for single-digit minutes.
 * Uppercase MM unlike CF timeFormat's mm to avoid conflict with months.
 * s 	Seconds; no leading zero for single-digit seconds.
 * ss 	Seconds; leading zero for single-digit seconds.
 * l or L 	Milliseconds. l gives 3 digits. L gives 2 digits.
 * t 	Lowercase, single-character time marker string: a or p.
 * No equivalent in CF.
 * tt 	Lowercase, two-character time marker string: am or pm.
 * No equivalent in CF.
 * T 	Uppercase, single-character time marker string: A or P.
 * Uppercase T unlike CF's t to allow for user-specified casing.
 * TT 	Uppercase, two-character time marker string: AM or PM.
 * Uppercase TT unlike CF's tt to allow for user-specified casing.
 * Z 	US timezone abbreviation, e.g. EST or MDT. With non-US timezones or in the Opera browser, the GMT/UTC offset is returned, e.g. GMT-0500
 * No equivalent in CF.
 * o 	GMT/UTC timezone offset, e.g. -0500 or +0230.
 * No equivalent in CF.
 * S 	The date's ordinal suffix (st, nd, rd, or th). Works well with d.
 * No equivalent in CF.
 * '…' or "…" 	Literal character sequence. Surrounding quotes are removed.
 * No equivalent in CF.
 * UTC: 	Must be the first four characters of the mask. Converts the date from local time to UTC/GMT/Zulu time before applying the mask. The "UTC:" prefix is removed.
 * No equivalent in CF.
 * 
 */

var dateFormat = function () {
	var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function (val, len) {
			val = String(val);
			len = len || 2;
			while (val.length < len) val = "0" + val;
			return val;
		};

	// Regexes and supporting functions are cached through closure
	return function (date, mask, utc) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date;
		if (isNaN(date)) throw SyntaxError("invalid date");

		mask = String(dF.masks[mask] || mask || dF.masks["default"]);

		// Allow setting the utc argument via the mask
		if (mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}

		var	_ = utc ? "getUTC" : "get",
			d = date[_ + "Date"](),
			D = date[_ + "Day"](),
			m = date[_ + "Month"](),
			y = date[_ + "FullYear"](),
			H = date[_ + "Hours"](),
			M = date[_ + "Minutes"](),
			s = date[_ + "Seconds"](),
			L = date[_ + "Milliseconds"](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d:    d,
				dd:   pad(d),
				ddd:  dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m:    m + 1,
				mm:   pad(m + 1),
				mmm:  dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy:   String(y).slice(2),
				yyyy: y,
				h:    H % 12 || 12,
				hh:   pad(H % 12 || 12),
				H:    H,
				HH:   pad(H),
				M:    M,
				MM:   pad(M),
				s:    s,
				ss:   pad(s),
				l:    pad(L, 3),
				L:    pad(L > 99 ? Math.round(L / 10) : L),
				t:    H < 12 ? "a"  : "p",
				tt:   H < 12 ? "am" : "pm",
				T:    H < 12 ? "A"  : "P",
				TT:   H < 12 ? "AM" : "PM",
				Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
				o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
				S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};

		return mask.replace(token, function ($0) {
			return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
}();

// Some common format strings
dateFormat.masks = {
	"default":      "ddd mmm dd yyyy HH:MM:ss",
	shortDate:      "m/d/yy",
	mediumDate:     "mmm d, yyyy",
	longDate:       "mmmm d, yyyy",
	fullDate:       "dddd, mmmm d, yyyy",
	shortTime:      "h:MM TT",
	mediumTime:     "h:MM:ss TT",
	longTime:       "h:MM:ss TT Z",
	isoDate:        "yyyy-mm-dd",
	isoTime:        "HH:MM:ss",
	isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
	isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
	dayNames: [
		"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
		"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
	],
	monthNames: [
		"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
		"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
	]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
	return dateFormat(this, mask, utc);
};

/**
 * Provides a Jquery replacement for JavaScript's Alert Dialog
 *  
 *  Example:
 *     alert2('This an example','Title example');
 */
function alert2(text,title) {
    title = typeof(title) != 'undefined' ? title : 'Alert';
    $('<div id="modalWindowAlert" style="display:none"></div>').appendTo('body');
    $('#modalWindowAlert').html(text);
    $('#modalWindowAlert').dialog({
        buttons: {
            "Close": function() { 
                $(this).remove(); 
            }
        },
        closeOnEscape: true,
        closeText: "close",
        draggable: false,
        resizable: false,
        modal: true,
        width: 400,
        minHeight: 'auto',        
        hide: 'fade',
        modal: true,
        show: 'fade',
        title: title
    });
}

/**
 * Provides a Jquery replacement for JavaScript Confirm
 *  
 *  Example:
 *     confirm2('Are you sure?','Confirm',
 *                function () {
 *                   location.href = '../folder/index.php?id='+id;
 *               }); 
 */
function confirm2(msg,title,callback) {
    title = typeof(title) != 'undefined' ? title : 'Confirm';
    callback = typeof(callback) != 'undefined' ? callback : '';    
    $('<div id="modalWindowConfirm" style="display:none"></div>').appendTo('body');    
    $('#modalWindowConfirm').html(msg);
    $('#modalWindowConfirm').dialog({
        title: title,
        closeOnEscape: true,
        closeText: "close",
        draggable: false,
        resizable: false,
        modal: true,
        width: 400,
        minHeight: 'auto',        
        hide: 'fade',
        modal: true,
        buttons : {
            "Confirm" : function() {
                $(this).remove(); 
                // call the callback
                if ($.isFunction(callback)) {
                     callback.apply();
                }
            },
            "Cancel" : function() {
                $(this).remove(); 
            }
        }
    });

}


/**
 * Provides a Jquery replacement for JavaScript Prompt
 *  
 *  Example:
 *     prompt2('Type something:', 'Prefilled value', 'Prompt Dialog', 
 *              function(r) {
 *                if( r ) alert('You entered ' + r);
 *               });
 *  SIDE NOTE: I MADE THIS ONLY ALLOW CHARACTERS OF A MAX LENGTH OF 85
 *  I ALSO ADDED A CHARACTER COUNTER
 */
function prompt2(msg,value,title,callback) {
    title = typeof(title) != 'undefined' ? title : 'Prompt';
    value = typeof(value) != 'undefined' ? value : '';
    callback = typeof(callback) != 'undefined' ? callback : '';
    $('<div id="modalWindowPrompt" style="display:none"></div>').appendTo('body');
    $('#modalWindowPrompt').html(msg+" <textarea id='modalWindowInputPrompt' maxlength='80' value='"+value+"'/><br /><sup>Characters: <span id='characters'></span></sup>");
    $('#modalWindowInputPrompt').keyup(function(e)
    {
        var characterCount = $('#modalWindowInputPrompt').val().length;
        $('#characters').html(characterCount);
        
    });
    $('#modalWindowPrompt').dialog({
        title: title,
        closeOnEscape: true,
        closeText: "close",
        draggable: false,
        resizable: false,
        modal: true,
        width: 'auto',
        minHeight: 'auto',        
        hide: 'fade',
        modal: true,
        buttons : {
            "OK" : function() {
                // call the «callback function»
                if ($.isFunction(callback)) {
                     callback( document.getElementById("modalWindowInputPrompt").value );
                }
                $(this).remove(); 
            },
            "Cancel" : function() {
                $(this).remove(); 
            }
        }
    });

}

function searchRFCs(){
	$('.rfc_update_row').hide();
	$('.rfc_selected_row').removeClass('rfc_selected_row');
	var search_text = $("#rfcSearch").val();
	$(".rfc_anchor_row").each(function(index) {
		if($( this ).attr('number').indexOf(search_text) >= 0){
			$( this ).show();
		}
		else {
		 	$( this ).hide();
		}
	});
}
