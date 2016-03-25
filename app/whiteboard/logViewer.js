// This function loads the log information regarding who has read the selected whiteboard
function loadHasReadList(e)
{
	var msg = e.data;
	var msgId = msg.messageId;
	
	var xmlhttp;
	if (window.XMLHttpRequest)
	{
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{
		// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			var employees = JSON.parse(xmlhttp.responseText);
			if (!employees.error)
			{
				employees = employees.data;
				// Build our lists of the employees that have read the whiteboard and those who have not
				var haveRead = employees.filter(function(employee){ return employee.hasRead == '1'; });
				var haveNotRead = employees.filter(function(employee) { return employee.hasRead == '0'; });

				var haveNotReadHtml = '';
				if (haveNotRead.length)
				{
					haveNotReadHtml += '<h4>Have Not Read</h4>';
					haveNotReadHtml += generateListString(haveNotRead);
				}

				var haveReadHtml = '';
				if (haveRead.length)
				{
					haveReadHtml += '<h4>Have Read</h4>';
					haveReadHtml += generateListString(haveRead);
				}

				// Add the two lists together and attach them as the innerHTML of the msgLogDiv
				document.getElementById("msgLogDiv"+msgId).innerHTML=haveReadHtml+haveNotReadHtml;

				// Now make it prettily open up to show the log
				try
				{
					if ( $.inArray(msgId, preferences[curUser.area].whiteboard.collapsed) == -1 )
					{
						$('#messageDiv'+msgId).slideToggle('fast');
					}
				}
				catch (e)
				{
					console.log(e);
				}
				$('#msgLogDiv'+msgId).slideToggle('fast');
			}
			else
			{
				notify("There was an error loading the log. Please try again!", {'status': 'failure'});
			}
		}
	}
	
	xmlhttp.open("GET", "printLog.php?msgId="+msgId, true);
	xmlhttp.send();
}

// Build a string of HTML for a list of employees
function generateListString(employees)
{
	var html = '<div>';
	var numberOfEmployees = employees.length;
	var employeesPerColumn = Math.ceil(numberOfEmployees/6);
	for (var i = 0; i < 6; i++)
	{
		html += '<ul>';
		var maxJ = Math.min((i*employeesPerColumn)+employeesPerColumn, numberOfEmployees);
		for (var j = i*employeesPerColumn; j < maxJ; j++)
		{
			html += '<li>'+employees[j].firstName+' '+employees[j].lastName+'</li>';
		}
		html += '</ul>';
	}
	return html+'</div>';
}
