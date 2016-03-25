var toggleAdvaceSearchDisplay = 1;

function submitSearch(){
	var page = '';
	var employee = document.getElementById("employee").value;
	var startDate = document.getElementById("ticketDate1").value;
	var endDate = document.getElementById("ticketDate2").value;
	var ticket = document.getElementById("ticketNum1").value;
	var requestor= document.getElementById("requestor1").value;
	var contact = document.getElementById("contactInfo1").value;
	var symptom = document.getElementById("serviceOrSymtomCat1").value;
	var source = document.getElementById("research1").value;
	var priority = document.getElementById("priority1").value;
	var kb = document.getElementById("kbOrSource1").value;
	var workOrder = document.getElementById("workOrder1").value;
	var template = document.getElementById("template1").value;
	var shoot = document.getElementById("troubleshooting1").value;
	var closure = document.getElementById("closureCodes1").value;
	var professional = document.getElementById("professionalism1").value;
	var comments = document.getElementById("comment1").value;
	
	if(document.getElementById("advancedSearch").style.display=="none")	
	{
		page = "logAjax/printLog.php?startDate="+startDate+"&endDate="+endDate+"&ticketNum="+ticket+"&employee="+employee;
	}	
	else
	{
		page = "logAjax/printLog.php?startDate="+startDate+"&endDate="+endDate+"&ticketNum="+ticket+"&requestor="+requestor+"&contact="+contact+"&symptom="+symptom+"&source="+source+"&priority="+priority+"&kb="+kb+"&workOrder="+workOrder+"&template="+template+"&shoot="+shoot+"&closure="+closure+"&professional="+professional+"&comments="+comments+"&employee="+employee;
	}
	
	var cb = function(result)
	{ 
		if(result=='')
		{
			document.getElementById("results").innerHTML = '<p align="center">No results for this selection</p>';
		}
		else
		{	
			document.getElementById("results").innerHTML = result; 
		}
	}
	callPhpPage(page,cb);
	
}

// Make search dynamic when fields change.
window.onload = function()
{

	var initialStartDate = $('#ticketDate1').datepicker({dateFormat:"yy-mm-dd", onSelect: function() { submitSearch(); }});
	var initialEndDate = $('#ticketDate2').datepicker({dateFormat:"yy-mm-dd", onSelect: function() { submitSearch(); } });

	//Select employee
	$("#employee").change(function (){ submitSearch(); });
	
	//ticket number
	$("#ticketNum1").keyup(function () { submitSearch(); });
	// Requestor
	$("#requestor1").change(function () { submitSearch(); });
	// Contact Info
	$("#contactInfo1").change(function () { submitSearch(); });
	// Sysmtom Caterogy 
	$("#serviceOrSymtomCat1").change(function () { submitSearch(); });
	// Ticket Source 
	$("#research1").change(function () { submitSearch(); });
	// Priority
	$("#priority1").change(function () { submitSearch(); });
	// KB Source 
	$("#kbOrSource1").change(function () { submitSearch(); });
	// Work Order
	$("#workOrder1").change(function () { submitSearch(); });
	// Template
	$("#template1").change(function () { submitSearch(); });
	// Troubleshooting
	$("#troubleshooting1").change(function () { submitSearch(); });
	// Closure Codes
	$("#closureCodes1").change(function () { submitSearch(); });
	// Professionalism
	$("#professionalism1").change(function () { submitSearch(); });
	// comments
	$("#comment1").keyup(function () { submitSearch(); });
}

// Display Advanced Search
function showAdvancedSearch()
{
	if(toggleAdvaceSearchDisplay)
	{
		document.getElementById("advancedSearch").style.display="inline-table";
		toggleAdvaceSearchDisplay = 0;
	}
	else
	{
		document.getElementById("advancedSearch").style.display="none";
		toggleAdvaceSearchDisplay = 1;
	}
}
// Edit ticket
function editTicket(str)
{
	var ticketNum=''; var ticketDate=''; var requestor=''; var contactInfo=''; var serviceCat=''; var ticketSource=''; var priority=''; var kbSource='';
	var workOrder=''; var template=''; var trouble=''; var closureCodes=''; var professionalism=''; var comments=''; var ticketEntryNum='';
 
	
	ticketEntryNum = $('#ticketEntryNum'+str).val();
	
	//Second row of selected ticket to edit
	ticketNum = $('#result'+str).find("tbody tr").eq(1).children(':first').html();
	ticketDate = $('#result'+str).find("tbody tr").eq(1).children('TR TD:nth-child(2)').html();
	requestor = $('#result'+str).find("tbody tr").eq(1).children('TR TD:nth-child(3)').html();
	contactInfo = $('#result'+str).find("tbody tr").eq(1).children('TR TD:nth-child(4)').html();
	serviceCat = $('#result'+str).find("tbody tr").eq(1).children('TR TD:nth-child(5)').html();
	ticketSource = $('#result'+str).find("tbody tr").eq(1).children('TR TD:nth-child(6)').html();
	priority = $('#result'+str).find("tbody tr").eq(1).children('TR TD:nth-child(7)').html();
	kbSource = $('#result'+str).find("tbody tr").eq(1).children('TR TD:nth-child(8)').html();
	workOrder= $('#result'+str).find("tbody tr").eq(1).children('TR TD:nth-child(9)').html();
	// fourth row
	template= $('#result'+str).find("tbody tr").eq(3).children(':first').html();
	trouble= $('#result'+str).find("tbody tr").eq(3).children('TR TD:nth-child(2)').html();
	closureCodes= $('#result'+str).find("tbody tr").eq(3).children('TR TD:nth-child(3)').html();
	professionalism= $('#result'+str).find("tbody tr").eq(3).children('TR TD:nth-child(4)').html();
	comments= $('#result'+str).find("tbody tr").eq(3).children('TR TD:nth-child(5)').html();

	//Replace values on the ticket that was chosen. to be edited.
	$('#editTicketNumEntry').val(ticketEntryNum);
	$('#editTicketNum').val(ticketNum);
	$('#editTicketDate').val(ticketDate);
	$('option','#editRequestor').each(function(){  //Requestor
		if($(this).text()==requestor)
			$(this).attr('selected', 'selected');
		});	
	$('option','#editContactInfo').each(function(){  //Edit Contact Info
		if($(this).text()==contactInfo)
			$(this).attr('selected', 'selected');
		});	
	$('option','#editServiceOrSymtomCat').each(function(){  //Edit Service or Symptom Cat
		if($(this).text()==serviceCat)
			$(this).attr('selected', 'selected');
		});
	$('option','#editTicketSource').each(function(){  //Edit Ticket Source
		if($(this).text()==ticketSource)
			$(this).attr('selected', 'selected');
		});
	$('option','#editPriority').each(function(){  //Edit Priority
		if($(this).text()==priority)
			$(this).attr('selected', 'selected');
		});
	$('option','#editKBOrSource').each(function(){  //Edit KB or Source
		if($(this).text()==kbSource)
			$(this).attr('selected', 'selected');
		});
	$('option','#editWorkOrder').each(function(){  //Edit Work Order
		if($(this).text()==workOrder)
			$(this).attr('selected', 'selected');
		});
	$('option','#editTemplate').each(function(){  //Edit Template
		if($(this).text()==template)
			$(this).attr('selected', 'selected');
		});
	$('option','#editTroubleshooting').each(function(){  //Edit Troubleshooting
		if($(this).text()==trouble)
			$(this).attr('selected', 'selected');
		});
	$('option','#editClosureCodes').each(function(){  //Edit Closure Codes
		if($(this).text()==closureCodes)
			$(this).attr('selected', 'selected');
		});
	$('option','#editClosureCodes').each(function(){  //Edit Troubleshooting
		if($(this).text()==closureCodes)
			$(this).attr('selected', 'selected');
		});
	$('option','#editProfessionalism').each(function(){ // Edit Professionalism
		if($(this).text()==professionalism)
			$(this).attr('selected', 'selected');
		});
	$('#editComment').val(comments); // Edit Comments
	
	// Dialog box
	$("#EditTicketTable").dialog({
			resizable: true,
			width: 925,
			modal: true,
			draggable: true,
			buttons: [{ text: "Submit", click: function() { updateTicket(); } }]
		});

}

//function
function updateTicket()
{
	//see search function to get the id's of the required fields. but it is a diffferent table. 
  // get values of the fields that were changed and update results with ajax. call the search fucntion again to display updated results. 
	var ticketEntryNum = $('#editTicketNumEntry').val();
	var ticketNum = document.getElementById("editTicketNum").value;
	var ticketDate = document.getElementById("editTicketDate").value;
	var requestor= document.getElementById("editRequestor").value;
	var contact = document.getElementById("editContactInfo").value;
	var serviceCat = document.getElementById("editServiceOrSymtomCat").value;
	var source = document.getElementById("editTicketSource").value;
	var priority = document.getElementById("editPriority").value;
	var kb = document.getElementById("editKBOrSource").value;
	var workOrder = document.getElementById("editWorkOrder").value;
	var template = document.getElementById("editTemplate").value;
	var shoot = document.getElementById("editTroubleshooting").value;
	var closure = document.getElementById("editClosureCodes").value;
	var professional = document.getElementById("editProfessionalism").value;
	var comments = document.getElementById("editComment").value;
	
	// Validate form
	var isTicketInfoValid = validateForm(ticketNum,ticketDate);
	
	if(isTicketInfoValid)
	{
			var request = $.ajax({
				url: "logAjax/editTicket.php",
				type: "GET",
				data: {ticketNum:ticketNum, ticketDate:ticketDate, requestor:requestor, contact:contact, serviceCat:serviceCat, source:source, priority:priority, 
				kb:kb, workOrder:workOrder, template:template, shoot:shoot, closure:closure, professional:professional, comments:comments, ticketEntryNum:ticketEntryNum},
				async: true,
				cache: false
				});

				request.done(function(msg) {
				//  alert("Success");
				submitSearch();
				$("#EditTicketTable").dialog("close");
				//  window.location.reload();
				});

				request.fail(function(jqXHR, textStatus) {
				alert( "Request failed: " + textStatus );
				});

	}

}

// Validate edit fields
function validateForm(ticketNumber,ticketDate)
{
	  // If missingRequirements = 0 ---> Form is valid
	  // else if missingRequirements = 1 ---> empty fields
	  // else if missingRequirements = 2 ---> Incorrect Date
	  // else if missingRequirements = 3 ---> Incorrect Ticket Number
	  // else if missingRequirements = 4 ---> SCXXXXXX ticket with an extra digit. 
	  var missingRequirements = 0;
	  
	
	  //Check for missing/incorrect form values
	  	
		// Valid date format
		var validDateFormat = /^([0-9]{4})\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/;
		var checkForValidDate = validDateFormat.test(ticketDate);
		//Incident ticket number
		var validINCTicketNum = /(\INC)([0-9]{7})/g;
		//Enhancement ticket number
		var validENHTicketNum = /(\ENH)([0-9]{7})/g;
		//RITM ticket
		var validRITMTicketNum = /(\RITM)([0-9]{6})/g;
		//Service Desk ticket number
		var validSCTicketNum = /(\SC)([0-9]{6})/g;
		//Request Ticket
		var validREQTicketNum = /(\REQ)([0-9]{6})/g;
		//ENG tickets
		var validENGTicketNum = /(\ENG)([0-9]{6})/g;
	
		// Check 
		var checkForValidTicket = validINCTicketNum.test(ticketNumber);
		if(checkForValidTicket==false)
		{
			checkForValidTicket = validENHTicketNum.test(ticketNumber);
		
			if(checkForValidTicket==false)
			{
				checkForValidTicket = validRITMTicketNum.test(ticketNumber);
				
				if(ticketNumber.length==8)
				{
					checkForValidTicket = validSCTicketNum.test(ticketNumber);
				}
				else if(ticketNumber.length==9)
				{
					checkForValidTicket = validREQTicketNum.test(ticketNumber);
					if(checkForValidTicket==false)
					{
						checkForValidTicket = validENGTicketNum.test(ticketNumber);
					}
				}
			}
		}
		
		// check for ticket number
		
		if(ticketDate =="")
		  {
		  	missingRequirements = 1;
		  }
	    else if(checkForValidDate==false)
		  {
			missingRequirements = 2;

		  }
		  
		if (ticketNumber =="")
		  {
		  	missingRequirements = 1;
		  }
		else if(checkForValidTicket==false)
		  {
		  	missingRequirements = 3;
		  }  
	  
		
	    if(missingRequirements)
		{
			if(missingRequirements == 1)
			{
				alert("Please make sure the 'Ticket#' and 'Ticket Date' fields are not empty.");
		  	}
			else if (missingRequirements==2)
			{
				alert("Please enter a date in the following format YYYY-MM-DD.");
			}
			else if (missingRequirements==3)
			{
				alert("Please enter a ticket number in the following format e.g. INC0001234, ENH0001234, SC001234, RITM100001, ENG000001 or REQ100001.");
			}
			return false;
		}
		else
		{
			return true;
		}
}


