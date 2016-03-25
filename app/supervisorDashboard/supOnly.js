// SEND A TEXT TO MARK PHILLIPS
function textBoss()
{
	
	prompt2('<b>Please note that your message will also be sent to Elaine via text</b><br>'+
			'Text message (80 characters max):<br />', '', 'Notify Christine', function(message)
	{
		if(!(message == 'null' || message== ''))
		{
			var page = 'textBoss.php?message=' + message;
			var cb = function (result) {
				alert(result);
			}
			callPhpPage(page, cb);
		}
	});
	
}

// LEAVE A NEW TURN-OVER NOTE FOR THE NEXT SUPERVISOR
function leaveNote()
{
	var note = escape(prompt("Enter Note below",""));
	if(!(note == 'null' || note== ''))
	{
		var page = 'leaveNote.php?note=' + note;
		var cb = function (result) {
			supNotes();
		}
		callPhpPage(page, cb);
	}
}

// MARK A TURN-OVER NOTE AS NO LONGER NEEDING TO BE SHOWN
function clearNote(noteId)
{
	var closingComment = prompt('Closing Comment: ');
	if (closingComment != null)
	{
		var page = "clearNote.php?noteId=" + noteId + '&closingComment=' + encodeURIComponent(closingComment);
		var cb = function (result) {
			supNotes();
		}
		callPhpPage(page, cb);
	}
}

// THIS IS WHAT CREATES THE DIALOG BOX WITH ALL OF THE USER'S SUPERVISOR REPORT ENTRIES MADE DURING THE CURRENT SESSION
var isDialogOpen = false;
function viewReportLog()
{
	var page = "viewSupReportSoFar.php";
	var cb = function (result) {
		if (isDialogOpen)
				{
					if ($('#myReportSoFar').length)
					{
						$('#myReportSoFar').html(result);
					}
					else
					{
						var $reportSoFar = $('<div id="myReportSoFar"></div>')
						.html(result)
						.dialog({
							autoOpen: false,
							width: '500',
							height: 'auto', 
							maxWidth: 800,
							title: 'Your Report So-Far',
							close: function(event, ui) { $reportSoFar.dialog('destroy'); $('#myReportSoFar').remove(); isDialogOpen = false; }
						});
				
						$reportSoFar.dialog('open');
					}
				}
	};
	callPhpPage(page, cb);
}

// THIS SUBMITS A NEW SUPERVISOR REPORT ENTRY
function supReportEntry()
{
	entry = $('#reportEntry').val();
	if (entry != '')
	{
		emailResponse = '';
		entry = entry.replace(/(\r\n|\r|\n)/g, '<br />');
		entry = entry.replace( /(\#)/g, '');
		entry = entry.replace( /(\&)/g, 'and');
		var page = 'supReportEntry.php?entry='+entry;
		var cb = function(result){
			$('#reportEntry').val('');
			$spinner.dialog('close');
			alert(result + '\n' + emailResponse);
			$(".supReportEntryNotificationGroup").attr('checked', false);
			viewReportLog();
		};

		// CHECK TO SEE IF ANY GROUPS HAVE BEEN SELECTED TO BE NOTIFIED IMMEDIATELY ABOUT THIS ENTRY, IF SO SEND AN EMAIL TO THOSE GROUPS
		if ($('.supReportEntryNotificationGroup:checked').length > 0)
		{
			$spinner.dialog('open');
			emailResponse = sendSupEntryEmail(entry);
		}
		callPhpPage(page, cb);
	}
}


// THIS IS A SYNCHRONOUS JAVASCRIPT FUNCTION USED TO SEND AN EMAIL FROM A SUPERVISOR REPORT ENTRY TO A GROUP OF PEOPLE RIGHT AWAY (i.e. NOT AS PART OF THE NORMAL SUPERVISOR REPORT EMAILS)
function sendSupEntryEmail(message)
{
	var xmlhttp;
	var activeVal = 1;

	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	groups = groupsToString();
	xmlhttp.open("GET","../supervisorDashboard/sendEmail.php?grp="+groups+'&mess='+message+'&subj=Notification from NOC Supervisor for you', false);
	xmlhttp.send();
	return xmlhttp.responseText;
}

// SHOW THE GROUPS THAT ARE AVAILABLE TO BE NOTIFIED RIGHT AWAY ABOUT THIS ENTRY OR HIDE THEM
function showGroups()
{
	if (document.getElementById('reportGroups').style.display == 'none')
	{
		document.getElementById('reportGroups').style.display = 'block';
	} 
	else
	{
		document.getElementById('reportGroups').style.display = 'none';
	}
}

// TAKE THE GROUPS CURRENTLY CHECKED TO BE NOTIFIED AND CREATE A STRING OF THEM TO BE PASSED TO THE "sendEmail.php" FILE
function groupsToString()
{
	var str = '';
	var count = 1;
	var supReportGroups = $(".supReportEntryNotificationGroup:checked").each(function () { if (count == 1) { str+=$(this).val(); } else { str+='~'+$(this).val(); } count++; });
	return str;
}


function submitUnscheduledRFC()
{
	//Store the field info
	var ticketNum = document.getElementById("ticketNumRFC").value;
	var name = document.getElementById("nameRFC").value;
	var startTime = document.getElementById("startTimeRFC").value;
	var startDate = document.getElementById("startDateRFC").value;
	var endTime = document.getElementById("endTimeRFC").value;
	var endDate = document.getElementById("endDateRFC").value;
	var desc = document.getElementById("descRFC").value;
	var impact = document.getElementById("impactRFC").value;
	//clear the fields for the next use
	document.getElementById("ticketNumRFC").value = "";
	document.getElementById("nameRFC").value = "";
	document.getElementById("descRFC").value = "";
	document.getElementById("impactRFC").value = "";
	//close the dialog box
	$("#unscheduledRFC").dialog("close");
	//submit the info
	$.post('submitUnscheduledRFC.php', 
		{ticketNum: ticketNum, engName: name, startTime: startTime, startDate: startDate, endTime: endTime, endDate: endDate, description: desc, impact: impact }, 
		function(data) {
	  alert('Unscheduled RFC Submitted');
	});
	
}

// OPEN THE SECURITY VIOLATION PAGE WITH THE SELECTED EMPLOYEES INFORMATION
function secViolationPage(netId)
{
	var url = "../performance/securityViolation.php?violator="+netId;
	window.open(url,'Security Violation','width=730,height=450,resizable=no,toolbar=no,location=no','false');
	whosHereCancel();
}

// OPEN THE POLICY REMINDER PAGE WITH THE SELECTED EMPLOYEES INFORMATION
function policyReminderPage(netId)
{
	var url = "../performance/policyReminder.php?reminded="+netId;
	window.open(url,'Security Violation','width=730,height=450,resizable=no,toolbar=no,location=no','false');
	whosHereCancel();
}

// OPEN THE COMMENDABLE PERFORMANCE PAGE WITH THE SELECTED EMPLOYEES INFORMATION (FEEL FREE TO CORRECT THE MISSPELLING -- LUIS WROTE THIS FUNCTION)
function commandablePage(netId)
{
	var url = "../performance/commendablePerformance.php?commended="+netId;
	window.open(url,'Security Violation','width=730,height=450,resizable=no,toolbar=no,location=no','false');
	whosHereCancel();
}

// REPLACE EMPLOYEE PHOTO GENERATED IN THE "whosHere.php" PAGE WITH OPTIONS TO MAKE A NEW SECURITY VIOLATION, POLICY REMINDER, OR COMMENDABLE PERFORMANCE
function giveOptions(div, netId)
{
	$(div).replaceWith("<div style='font-size:80%; width: 100px; height: 100px; text-align: left; margin-left: auto; margin-right: auto;'><div class='performance'><a style='color: #336699;' href=\"javascript:secViolationPage('"+netId+"')\"><img class='bottom' src='security2.png' alt='security violation' title='Security Violation' width='20' height='20' /> Security</a></div><div class='performance'><a style='color: #336699;' href=\"javascript:policyReminderPage('"+netId+"')\"><img class='bottom' src='policy2.png' alt='policy reminder' title='Policy Reminder' width='20' height='20' /> Policy</a></div><div class='performance'><a style='color: #336699;' href=\"javascript:commandablePage('"+netId+"')\"><img class='bottom' src='commendable2.png' alt='commendable' title='Commendables' width='20' height='20' /> Commend</a></div><div style='margin-top:5px; width: 100%; text-align: center;'><input align='center' type='button' value='Cancel' onclick='whosHereCancel()' /></div></div>");
}

// OPEN THE DIALOG FOR UNSCHEDULED RFCS
function openRFCpopup()
{
	$( "#unscheduledRFC" ).dialog({
		title: "Unapproved RFC",
		resizable: false,
		width: 550,
		modal: true,
		draggable: true,
		buttons: [{ text: "Submit", 
			click: 	function() {	submitUnscheduledRFC();	}  
				}]
	});

}
