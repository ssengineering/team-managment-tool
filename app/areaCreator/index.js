function addArea()
{
	// Determine allowable time increments and the time components in hours and minutes
	var snapSize = 60;
	
	// Create the timeEntry for start time
	$('#startTime').timeEntry(
	{
		'useMouseWheel': true,
		'timeSteps': [1,snapSize,1],
		'spinnerImage': '',
		'beforeShow': limitTimeRange
	}).timeEntry('setTime', new Date(0,0,0,8,0,0));
	
	// Create the timeEntry for end time
	$('#endTime').timeEntry(
	{
		'useMouseWheel': true,
		'timeSteps': [1,snapSize,1],
		'spinnerImage': '',
		'beforeShow': limitTimeRange
	}).timeEntry('setTime', new Date(0,0,0,17,0,0));
	
	// Update allowable start time and end time timeSteps to match changes to snap size
	$('#hourSize').change(function ()
	{
		var newIncrement = parseInt($(this).val());
		// We use the change command here because the version of timeEntry that we have is old and does not have the 'option' command
		$('#startTime,#endTime').timeEntry('option', 'timeSteps', [1,newIncrement,1]);
	});
	
	$('#startDay').val('0');
	$('#endDay').val('6');
	
	var $shiftPopup = $('#addAreaPopup').dialog(
	{
        width: '350',
        height: 'auto',
        title: 'New Area',
        draggable: true,
        resizable: false,
        buttons:
        {
            "Ok": function()
            {
                ok();
                $(this).dialog('close');
            },
            "Cancel": function()
            {
                cancel();
                $(this).dialog('close');
            }
        }
    });
}

function ok()
{
	// Get start time in database format
	var startTime = $('#startTime').timeEntry('getTime');
	var hours = startTime.getHours();
	var minutes = startTime.getMinutes()/60;
	startTime = hours+minutes;
	
	// Get end time in database format
	var endTime = $('#endTime').timeEntry('getTime');
	hours = endTime.getHours();
	minutes = endTime.getMinutes()/60;
	endTime = hours+minutes;
	
	// Submit new area to database
	$.post("newArea.php",
	{
		'area': $('#area').val(),
		'longName': $('#longName').val(),
		'homePage': $('#homePage').val(),
		// Convert hourSize into hours instead of minutes
		'hourSize': $('#hourSize').val()/60,
		'startDay': $('#startDay').val(),
		'startTime': startTime,
		'endDay': $('#endDay').val(),
		'endTime': endTime,
		'postSchedulesByDefault': $('#postSchedulesByDefault').prop('checked')? 1:0,
		'canEmployeesEditWeeklySchedule': $('#canEmployeesEditWeeklySchedule').prop('checked')? 1:0
	}, function (response)
	{
		response = JSON.parse(response);
		if (response['status'] == 'OK')
		{
			notify("Successfully added!", {'status': 'success'});
			
			// The following code is nasty because datatables does not support updating the table you built from ajax unless you want to implement server-side processing
			$dataTable._fnAjaxUpdate();
			// Once the datatable has updated from the source re-draw the table
			on_fnAjaxUpdate(function() { $dataTable.fnDraw(); });
		}
		else
		{
			notify("Failed to create area!<br />Please try creating it again.", {'status': 'failure', 'clickToDismiss': true});
		}
		
		// Restore defaults to form
		resetFormToDefaults();
	});
}

function cancel()
{
	resetFormToDefaults();
}

function resetFormToDefaults()
{
	$('#area,#longName').val('');
	$('#hourSize').val('60');
	$('#startDay').val('0');
	$('#endDay').val('6');
	$('#startTime').timeEntry('setTime', new Date(0,0,0,8,0,0));
	$('#endTime').timeEntry('setTime', new Date(0,0,0,17,0,0));
	$('#postSchedulesByDefault').prop('checked', true);
	$('#canEmployeesEditWeeklySchedule').prop('checked', true);
	$('#homePage').val('whiteboard');
}

function on_fnAjaxUpdate(callback, callbackArguments)
{
	// Get reference to datatables processing div to check if it is still there
	var $processing = $('#areaTable_processing');
	
	// If processing is still visible i.e. if the ajax call has not been completed then set another identical timeout
	var isVisible = $processing.css('visibility') != 'hidden'? true:false;
	if (isVisible)
	{
		setTimeout(function()
		{
			on_fnAjaxUpdate.apply(null, [callback, callbackArguments])
		}, 100);
		return;
	}

	// Callback whatever function we want to run once the datatable has finished updating from the ajax source
	callback(callbackArguments);
}

function deleteArea(areaId)
{
	$.post("deleteArea.php", {'id': areaId}, function (response)
	{
		var response = JSON.parse(response);
		if (response.status == 'OK')
		{
			notify("Successfully deleted!", {'status': 'success'});
			
			// This is naughty of me to do because it isn't defined in the DataTables API but it is way cleaner than the other options currently available
			// The following code is nasty because datatables does not support updating the table you built from ajax unless you want to implement server-side processing
			$dataTable._fnAjaxUpdate();
			// Once the datatable has updated from the source re-draw the table
			on_fnAjaxUpdate(function() { $dataTable.fnDraw(); });
		}
		else
		{
			notify("Failed to delete! Please refresh!", {'status': 'failure', 'clickToDismiss': true});
		}
	});
}

function updateArea(dbId, column, value, selectedRows)
{
	
	$.post('editArea.php',
	{
		'id' : dbId,
		'column' : column,
		'value' : value
	}, function (response)
	{
		var response = JSON.parse(response);
		if (response.status == "OK")
		{
			notify("Updated Successfully!", {"status": "success"});
			
			// This is naughty of me to do because it isn't defined in the DataTables API but it is way cleaner than the other options currently available
			// The following code is nasty because datatables does not support updating the table you built from ajax unless you want to implement server-side processing
			$dataTable._fnAjaxUpdate();
			// Once the datatable has updated from the source re-draw the table
			on_fnAjaxUpdate(function() 
			{ 
				$dataTable.fnDraw(); 
				
				//reselect rows, because rows have no unique ID available outside the datatable structure the update is done by the row position
				var counter=0;
				$("#areaTable tr").each(function()
				{
					if(selectedRows.indexOf(counter)!=-1)
					{
						$(this).addClass("selected");
					}
					counter++;
				});
			});
			
			
		}
		else
		{
			notify("Failed to update the server!<br /><div style=\"text-align: center;\">Please try again!</div>", {'status': 'failure', 'clickToDismiss': true});
		}
	});
}

function textCreated(nTd, sData, full, iRow, iCol, dbField)
{
	$(nTd).dblclick(function()
	{
		var $td = $(this);
		var width = $td.width();
		$td.html('<input value="'+sData+'" id="'+dbField+'_'+full['ID']+'" name="'+dbField+'_'+full['ID']+'" style="max-width: '+width+'px" type="text" />');
		var $input = $td.find('input');
		$input.focusout(function()
		{
			// Get input and database info
			var $input = $(this);
			var dbFieldAndId = $input.attr('id').split('_');
			
			// get id, column and value that needs to b updated in database
			var dbField = dbFieldAndId[0];
			var id = dbFieldAndId[1];
			var newValue = $input.val();
			
			// Remove input and insert the new text value
			$input.parent().append(newValue);
			
			//store selected rows to reselect them after the table is done refreshing
			$selectedRows = new Array();
			var counter=0;
			$("#areaTable tr").each(function()
			{
				if($(this).hasClass("selected"))
				{
					$selectedRows.push(counter);
				}
				counter++;
			})
			// Update Database and refresh table
			updateArea(id, dbField, newValue, $selectedRows);
			$input.remove();
		});
		$input.focus();
	});
}

function renderDays(data, type, full, startOrEnd)
{
	if (type === 'display')
	{
		var $select = $('#startDay').clone().attr('id', startOrEnd+'Day_'+full['ID']);
		$select.children().filter('[value='+data+']').attr('selected', 'selected');
		return $select.prop('outerHTML');
	}
	return data;
}

function dayCreated(nTd, sData, full, iRow, iCol)
{
	var $select = $(nTd).children().filter('select');
	$select.change(function()
	{
		var $select = $(this);
		var idAndField = $select.attr('id').split('_');
		
		var id = idAndField[1];
		var dbField = idAndField[0];
		var newValue = $select.val();
		updateArea(id, dbField, newValue);
	});
}

function limitTimeRange(element)
{
	var startOrEnd = element.id.split('_');
	if (startOrEnd[1])
	{
		return {minTime: (startOrEnd[0] == 'endTime' ?
			$('#startTime_'+startOrEnd[1]).timeEntry('getTime') : null),  
				maxTime: (startOrEnd[0] == 'startTime' ? 
					$('#endTime_'+startOrEnd[1]).timeEntry('getTime') : null)};
	}
	else
	{
		return {minTime: (startOrEnd[0] == 'endTime' ?
			$('#startTime').timeEntry('getTime') : null),  
				maxTime: (startOrEnd[0] == 'startTime' ? 
					$('#endTime').timeEntry('getTime') : null)};
	}
}

function renderTime(data, type, full, startOrEnd)
{
	if (type === 'display')
	{
		var $input = $('<input id="'+startOrEnd+'Time_'+full['ID']+'" name="'+startOrEnd+'Time_'+full['ID']+'" size="7" type="text" />');
		
		// This is a super nasty fix for the fact that no browsers responsibly handle events on disabled elements
		// Therefore we overlay our soon to be disabled input with a div that will capture any double-click events (i.e. intents to edit the timeEntry)
		var $span = $input.wrap('<span style="position: relative;" />').parent();
		$span.append('<div style="position:absolute; left:0; right:0; top:0; bottom:0; cursor: pointer;" ></div>');
		return $span.prop('outerHTML');
	}
	return data;
}

function timeCreated(nTd, sData, full, iRow, iCol)
{
	// Get the time Entry input for this td
	var $input = $(nTd).find('input');
	var $div = $(nTd).find('div');
	
	// Get whether this is the startTime or the endTime (plus the id number of the area in the database)
	var id = $input.attr('id').split('_');
	
	// Determine allowable time increments and the time components in hours and minutes
	var snapSize = 60*full['hourSize'];
	var time = full[id[0]].split('.');
	var hour = 1*time[0];
	var minute = 0;
	if (time[1])
	{
		minute = 60*time[1];
	}
	
	// Create the timeEntry object
	$input.timeEntry(
	{
		'useMouseWheel': true,
		'timeSteps': [1,snapSize,1],
		'spinnerImage': '',
		'beforeShow': limitTimeRange
	}).timeEntry('setTime', new Date(0,0,0,hour,minute,0));
	
	// Enable when the user double clicks to allow editing
	$div.dblclick(function()
	{
		$(this).hide();
		var $input = $(this).parent().find('input');
		$input.timeEntry('enable');
		$input.focus();
	});
	
	// On blur update the database and disable the timeEntry again (we are going to refresh the table data and redraw things anyways)
	$input.focusout(function()
	{
		var $time = $(this);
		var idAndField = $time.attr('id').split('_');
		
		var id = idAndField[1];
		var dbField = idAndField[0];
		var newValue = $time.timeEntry('getTime');
		var hour = newValue.getHours();
		var minute = newValue.getMinutes();
		newValue = hour+(minute/60);
				
		updateArea(id, dbField, newValue);
		
		$time.parent().next().show();
		$time.timeEntry('disable');
	});
	
	// Disable entry by default
	$input.timeEntry('disable');
}

function renderRange(data, type, full)
{
	if (type === 'display')
	{
		var schedulableInterval = 60*data;
		var $range = $('<input id="hourSize_'+full['ID']+'" name="hourSize_'+full['ID']+'" type="number" value="'+schedulableInterval+'" style="max-width: 50px;" min="1" max="360" disabled="disabled" />');
		
		// This is a super nasty fix for the fact that no browsers responsibly handle events on disabled elements
		// Therefore we overlay our soon to be disabled input with a div that will capture any double-click events (i.e. intents to edit the timeEntry)
		var $span = $range.wrap('<span style="position: relative;" />').parent();
		$span.append('<div style="position:absolute; left:0; right:0; top:0; bottom:0; cursor: pointer;" ></div>');
		return $span.prop('outerHTML');
	}
	return data;
}

function rangeCreated(nTd, sData, full, iRow, iCol)
{
	var $div = $(nTd).find('div');
	$div.dblclick(function()
	{
		$(this).hide();
		var $range = $(this).prev();
		$range.prop('disabled', false);
		$range.focus();
	});
	
	var $range = $(nTd).find('input');
	$range.focusout(function()
	{
		var $snap = $(this);
		var idAndField = $snap.attr('id').split('_');
		
		var id = idAndField[1];
		var dbField = idAndField[0];
		var newValue = $snap.val()/60;
		
		updateArea(id, dbField, newValue);
		
		$(this).next().show();
		$(this).prop('disabled', true);
	});
}

function renderCheckbox(data, type, full, postOrSelf)
{
	if (type === 'display')
	{
		var checked = '';
		if (parseInt(data))
		{
			checked = ' checked="checked"';
		}
		var $checkbox = $('<input id="'+postOrSelf+'_'+full['ID']+'" name="'+postOrSelf+'_'+full['ID']+'" type="checkbox"'+checked+' />');
		return $checkbox.prop('outerHTML');
	}
	return data;
}

function checkboxCreated(nTd, sData, full, iRow, iCol)
{
	$(nTd).find('input').change(function()
	{
		var $checkbox = $(this);
		var fieldAndId = $checkbox.attr('id').split('_');
		var postOrSelf = fieldAndId[0];
		var dbId = fieldAndId[1];
		var checked = $checkbox.prop('checked')? 1:0;
		
		updateArea(dbId, postOrSelf, checked);
	});
}

function renderDelete(data, type, full)
{
	if (type === 'display')
	{
		return $("<span onmouseover=\"$(this).addClass('red')\" onmouseout=\"$(this).removeClass('red')\" onclick=\"deleteArea("+full['ID']+")\" class=\"delete ui-icon ui-icon-circle-close\"></span>").prop('outerHTML');
	}
	return data;
}

var areaInfo = new Array();
var $rowInfo = new Array();
function loadAncillaryAreaInfo($row)
{
	if (areaInfo)
	{
		for(x in areaInfo)
		{
			areaInfo[x]['areaId'] = areaInfo[x]['ID'];
		}
	}
	else
	{
		areaInfo = {};
	}
	
	// Return a list of all Apps and determine whether they ought to be selected or not
	$('#areaApps').dataTable(
	{
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bProcessing": true,
		"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
		"iDisplayLength": 10,
		"bDestroy": true,
		"fnCreatedRow": function(nRow, oData, iDataIndex)
		{
			var $row = $(nRow);
			var oData = oData;
			// On double click add or remove this app from the list of apps used in this area
			$row.dblclick(function ()
			{
				//set up for multiple selections
				    		
				var areaIds="";
				var rowsToAdd="";
				var appId = parseInt($(this).attr('id'));
				for(x in areaInfo)
				{
					for (var key in areaInfo[x])
					{
						if(key=="areaId")
						{
							areaIds+=areaInfo[x]["areaId"]+",";
							
							$.ajax({
							async:false,
							url:"hasApp.php",
							type:'POST',
							data:
							{
							'areaId' : areaInfo[x]["areaId"],
							'appId' : appId
							},
							success:function(response)
							{
								if(response=="false")
								{
									rowsToAdd += areaInfo[x]["areaId"]+",";
								}
								console.log(response);
							}
							});
						}
					}
				}
				areaIds = areaIds.slice(0, areaIds.length-1);
				rowsToAdd = rowsToAdd.slice(0, rowsToAdd.length-1);
				
				
				//rows to add in case of add app
				
				

				// Ensure that an area is selected, otherwise alert the user if they try interacting with the Apps section
				if ($('#areaTbody>tr.selected').length)
				{
					// Get area and app info
					var areaId = parseInt($(this).attr('data-area'));
					var appId = parseInt($(this).attr('id'));
					
					
					// Determine whether or not the area already has the app
					var hasApp = false;
					if ($(this).hasClass('selected'))
					{
						hasApp = true;
					}
					
					// Create Buttons Object (with a cancel button to begin with)
					var buttons = {};
					
					// If the area does not already have the app we will provide the options necessary to add the app to the area
					// Otherwise the area already has the app we will provide options to edit the app and also to remove the app
					if (!hasApp)
					{
						// Give an add option
						buttons['Add App to Area'] = function ()
						{
							// Get the values for the Link Info
							//needs to be converted to string separated values like ids
							var visible ="";
							var name="";
							var permission="";
							var newTab="";
							var parent="";
							var rowsToAddArray = rowsToAdd.split(",");
							for(x=0;x<rowsToAddArray.length;x++)
							{
								visible += $('#visible_'+rowsToAddArray[x]+'_'+appId).prop('checked')? "1,":"0,";
								name += $('#name_'+rowsToAddArray[x]+'_'+appId).val()+",";
								
								if ($('#permission_'+rowsToAddArray[x]+'_'+appId).val() == 'None')
								{
									permission += 'NULL,';
								}
								else
								{
									permission += $('#permission_'+rowsToAddArray[x]+'_'+appId).val()+",";
								}
								newTab += $('#newTab_'+rowsToAddArray[x]+'_'+appId).prop('checked')? "1,":"0,";
								
								if ($('#parent_'+rowsToAddArray[x]+'_'+appId).val() == 'None')
								{
									parent += 'NULL,';
								}
								else
								{
									parent += $('#parent_'+rowsToAddArray[x]+'_'+appId).val()+",";
								}
								
							}
							visible = visible.slice(0, visible.length-1);
								name = name.slice(0, name.length-1);
								permission = permission.slice(0, permission.length-1);
								newTab = newTab.slice(0, newTab.length-1);
								parent = parent.slice(0, parent.length-1);
							// Add the app to the selected area
							$.post('addAppToArea.php',
							{
								'areaId': rowsToAdd,
								'appId': appId,
								'visible': visible,
								'name': name,
								'permission': permission,
								'newTab': newTab,
								'parent': parent
							}, function(response)
							{
								var response = JSON.parse(response);
								
								// Notify the user of whether the app was successfully added or not and if so remind them that they should grant permissions to employees in the given area
								if (response.status == "OK")
								{
									notify("The app has been successfully linked to the selected area!<br />You will want to grant employees any necessary permissions.", {"status": "success", 'duration': 12000});
									$row.addClass("selected");
								}
								else
								{
									notify("Failed to add the app to the area!<br /><div style=\"text-align: center;\">Please try again!</div>", {'status': 'failure', 'clickToDismiss': true});
								}
							});
							
							// Now kill the dialog
							$(this).dialog('destroy');
							$(this).remove();
						};
					}
					else
					{
						// Give a remove option
						
						buttons['Remove App from Area'] = function ()
						{
							
						// Remove the app from the selected area(s)
							
							$.post('removeAppFromArea.php',
							{
								'areaId': areaIds,
								'appId': appId
							}, function(response)
							{

								var response = JSON.parse(response);
								
								// Notify the user of whether the app was successfully added or not and if so remind them that they should grant permissions to employees in the given area
								if (response.status == "OK")
								{
									notify("The app has been successfully removed from the area!", {"status": "success"});
									$row.removeClass("selected");
								}
								else
								{
									notify("Failed to remove the app from the area!<br /><div style=\"text-align: center;\">Please try again!</div>", {'status': 'failure', 'clickToDismiss': true});
								}
							});
							
							// Now kill the dialog
							$(this).dialog('destroy');
							$(this).remove();
						};
					}
					
					// Add Cancel button to the popup
					buttons['Cancel'] = function ()
					{
						$(this).dialog('destroy');
						$(this).remove();
					};
					
					// Build HTML to be used for the popup based off whether we are adding or removing the area app association
					var areaAppHtml = '<div id="areaAppPopup_'+areaId+'_'+appId+'" class="areaAppPopup popup">';
					if (!hasApp)
					{
						// Adding the app to the area
						for(x in areaInfo)
						{		
							$.ajax({
							async:false,
							url:"hasApp.php",
							type:'POST',
							data:
							{
							'areaId' : areaInfo[x]["areaId"],
							'appId' : appId
							},
							success:function(response){
								
								if(response=="false")
								{		
									areaAppHtml += '<h3 class="currentArea">'+areaInfo[x]["area"]+'</h3>';
									areaAppHtml += '<h3 class="popupTitle">Permissions to be Added: </h3>';
									areaAppHtml += '<div id="permissionsToAdd_'+areaInfo[x]["areaId"]+'_'+appId+'"></div>';
									areaAppHtml += '<h3 class="popupTitle">Link Info: </h3>';
									
									areaAppHtml += '<label for="visible_'+areaInfo[x]["areaId"]+'_'+appId+'" class="form">Visible?: </label>';
									areaAppHtml += '<input id="visible_'+areaInfo[x]["areaId"]+'_'+appId+'" checked="checked"" name="visible" class="form" type="checkbox"/>';
									areaAppHtml += '<div class="clearMe"></div>';
									
									areaAppHtml += '<label for="name_'+areaInfo[x]["areaId"]+'_'+appId+'" class="form">Link Name: </label>';
									areaAppHtml += '<input id="name_'+areaInfo[x]["areaId"]+'_'+appId+'" name="name" class="form" type="text" placeholder="Link Name" value="'+oData['appName']+'"/>';
									areaAppHtml += '<div class="clearMe"></div>';
									
									areaAppHtml += '<label for="permission_'+areaInfo[x]["areaId"]+'_'+appId+'" class="form">Required Permission: </label>';
									areaAppHtml += '<select id="permission_'+areaInfo[x]["areaId"]+'_'+appId+'" name="permission" class="form"><option>None</option></select>';
									areaAppHtml += '<div class="clearMe"></div>';
									
									areaAppHtml += '<label for="newTab_'+areaInfo[x]["areaId"]+'_'+appId+'" class="form">New Tab?: </label>';
									areaAppHtml += '<input id="newTab_'+areaInfo[x]["areaId"]+'_'+appId+'" name="newTab" class="form" type="checkbox"/>';
									areaAppHtml += '<div class="clearMe"></div>';
									
									areaAppHtml += '<label for="parent_'+areaInfo[x]["areaId"]+'_'+appId+'" class="form">Parent Link: </label>';
									areaAppHtml += '<select id="parent_'+areaInfo[x]["areaId"]+'_'+appId+'" name="parent" class="form" type="text"><option>None</option></select>';
									areaAppHtml += '<div class="clearMe"></div>';
										
								}
								}
							});	
							
						}
					}
					else
					{
						// Removing the app from the area
						for(x in areaInfo)
						{
							areaAppHtml += '<h3 class="popupTitle">'+areaInfo[x]['area']+'</h3>';
							areaAppHtml += '<h3 class="popupTitle">Permissions to be removed: </h3>';
							areaAppHtml += '<div id="permissionsToRemove_'+areaInfo[x]['areaId']+'_'+appId+'"></div>';
							areaAppHtml += '<h3 class="popupTitle">Employees with those permissions:</h3>';
							areaAppHtml += '<div id="employees_'+areaInfo[x]['areaId']+'_'+appId+'"></div>';
						}
					}
					areaAppHtml += '</div>';
					
					// Build popup for adding the app to the area (including necessary permissions) and allowing you to add a link to the app
					$(areaAppHtml).dialog(
					{
				        'width': '405',
				        'height': 'auto',
				        'title': 'Area App Creeletor',
				        'draggable': true,
				        'resizable': false,
				        'buttons': buttons,
				        'close' : buttons['Cancel']
			    	});
			    	
			    	if (!hasApp)
			    	{
			    		// TODO Create listener for "visible" to disable all other link values if visible is false
			    		$('#visible_'+areaId+'_'+appId).change(function()
			    		{
			    			// If the link between the app and the area is not visible, then disable all other inputs, otherwise allow the user to define what will become a new link on the floating nav-bar
			    			if (!$(this).prop('checked'))
			    			{
			    				// Disable all form inputs, selects, checkboxes, etc. except for the visible checkbox itself
			    				$('#areaAppPopup_'+areaId+'_'+appId+'>.form:not(label,[name="visible"])').prop('disabled', true);
			    			}
			    			else
			    			{
			    				// Enable all form inputs, selects, checkboxes, etc. except for the visible checkbox itself
			    				$('#areaAppPopup_'+areaId+'_'+appId+'>.form:not(label,[name="visible"])').prop('disabled', false);
			    			}
			    		});
			    		
			    		// Get the permissions that would be added to the area if this app were added
			    		var ids = areaIds.split(',');
			    		for(x in ids)
			    		$.ajax({
						async:false,
						url:"getNecessaryPermissions.php",
						type:'POST',
						data:
						{
							'areaId' : ids[x],
							'appId' : appId
						}, 
						success: function (response)
						{
							var response = JSON.parse(response);
							var permissionTableHtml = '<table style="width: 100%; max-width: 100%; word-break: break-word;"><tr><th style="text-align: center;">Short-name</th><th style="text-align: center;">Long-name</th></tr><tr><th style="text-align: center;" colspan="2">Description</th></tr>';
							var ps = response.permissions;
							for (permission in ps)
							{
								permissionTableHtml += '<tr id="permission_'+ps[permission]['permissionId']+'">';
								permissionTableHtml += '<td>'+ps[permission]['shortName']+'</td>';
								permissionTableHtml += '<td>'+ps[permission]['longName']+'</td></tr>';
								permissionTableHtml += '<tr><td colspan="2">'+ps[permission]['description']+'</td>';
								permissionTableHtml += '</tr>';
							}
							if (!ps.length)
							{
								permissionTableHtml += '<tr><td colspan="2">No permissions to be added.</td></tr>';
							}
							permissionTableHtml += '</table>';
							$('#permissionsToAdd_'+ids[x]+'_'+appId).html(permissionTableHtml);
						}
						});
						
						// Get all possible permissions for associating with the link if the app is added
						// This includes those permissions that will be added if this app is added to the area
						var values = areaIds.split(",");
						for(x in values)
						{
							
							$.ajax({
							async:false,
							url:"getAreaPermissions.php",
							type:'POST',
							data:
							{
							   'areaId': values[x]
							}, 
							success:function (response)
						{
							// Add the permissions that will be added to the area if the app is added first
							$('#permissionsToAdd_'+values[x]+'_'+appId+' tr').each(function()
							{
								var $permission = $(this);
								var id = $permission.attr('id');
								if (id != undefined)
								{
									var dbId = id.split('_')[1];
									var shortName = $permission.find('td:first').text();
									$('#permission_'+values[x]+'_'+appId).append($('<option />').attr('value', dbId).text(shortName));
								}
							});
							
							// Now add the permissions that the area currently has as options to select for the required permission to see the link
							var permissions = JSON.parse(response).permissions;
							for (var i in permissions)
							{
								$('#permission_'+values[x]+'_'+appId).append($('<option />').attr('value', permissions[i].permissionId).text(permissions[i].shortName));
							}
						}});
						}
						
						// Get all possible parent links from link list
						var values = areaIds.split(",");
						
						for(x in values)
						{
							
							$.ajax({
							async:false,
							url:"getLinks.php",
							type:'POST',
							data:
							{
							   'areaId': values[x]
							}, 
							success:function(response)
							{
								console.log(values[x]);
								// All links for the currently selected area
								var links = JSON.parse(response).links;
								
								// The following code could be a bit prettier if I used a recursive algorithm instead but Leslie is on her way and I already wrote it like this
								// A list to store all the top-level links
								var greatestAncestors = [];
								for (var i = links.length-1; i >= 0; i--)
								{
									if (links[i].parent == null)
									{
										// Add this to the list of root links
										greatestAncestors.unshift(links[i]);
										links.splice(i, 1);
									}
								}
								
								// Get all second-level links and store them as the children of the root links
								for (var i in greatestAncestors)
								{
									// A list to store 2nd-level links
									greatestAncestors[i].children = [];
									for (var j = links.length-1; j >= 0; j--)
									{
										if (links[j].parent == greatestAncestors[i].index)
										{
											greatestAncestors[i].children.unshift(links[j]);
											links.splice(j, 1);
										}
									}
								}
								
								// Now add the links that are valid parents as options for the "parent" select
								for (var i in greatestAncestors)
								{
									$('#parent_'+values[x]+'_'+appId).append($('<option />').attr('value', greatestAncestors[i].index).text(greatestAncestors[i].name));
									for (var j in greatestAncestors[i].children)
									{
										$('#parent_'+values[x]+'_'+appId).append($('<option />').attr('value', greatestAncestors[i].children[j].index).text(' -'+greatestAncestors[i].children[j].name));
									}
								} 
							}});
						}
			    	}
			    	else
			    	{
						
				    		// Get unnecessary permissions if this app were removed from the area
				    	var ids = areaIds.split(",");
			    		for(x in ids)
			    		{
			    			$.ajax({
							async:false,
							url:"getUnnecessaryPermissions.php",
							type:'POST',
							data:
							{
								'areaId' : ids[x],
								'appId' : appId
							}, 
							success:function (response)
							{
								var response = JSON.parse(response);
								var permissionTableHtml = '<table style="width: 100%; max-width: 100%; word-break: break-word;"><tr><th style="text-align: center;">Short-name</th><th style="text-align: center;">Long-name</th></tr><tr><th style="text-align: center;" colspan="2">Description</th></tr>';
								var ps = response.permissions;
								for (var permission in ps)
								{
									permissionTableHtml += '<tr id="permission_'+ps[permission]['permissionId']+'">';
									permissionTableHtml += '<td>'+ps[permission]['shortName']+'</td>';
									permissionTableHtml += '<td>'+ps[permission]['longName']+'</td></tr>';
									permissionTableHtml += '<tr><td colspan="2">'+ps[permission]['description']+'</td>';
									permissionTableHtml += '</tr>';
								}
								if (!ps.length)
								{
									permissionTableHtml += '<tr><td colspan="2">No permissions to be removed.</td></tr>';
								}
								permissionTableHtml += '</table>';
								$('#permissionsToRemove_'+ids[x]+'_'+appId).html(permissionTableHtml);
								
								// Get employees that have those permissions here
								$.ajax({
								async:false,
								url:"getEmployeesWithAreaPermissions.php",
								type:'POST',
								data:
								{
									'areaId': ids[x],
									'permissions': ps
								},
								success: function(response)
								{
									var employees = [];
									if (response)
									{
										var response = JSON.parse(response);
										employees = response.employees;
									}
									var employeeTableHtml = '<table style="width: 100%; max-width: 100%; word-break: break-word;"><tr><th style="text-align: center;">Last-name</th><th style="text-align: center;">First-name</th><th style="text-align: center;">NetID</th></tr>';
									for (var i in employees)
									{
										employeeTableHtml += '<tr><td>'+employees[i].lastName+'</td><td>'+employees[i].firstName+'</td><td>'+employees[i].netID+'</td></tr>';
									}
									if (!employees.length)
									{
										employeeTableHtml += '<tr><td colspan="3">No employees will be affected.</td></tr>';
									}
									employeeTableHtml += '</table>';
									$('#employees_'+ids[x]+'_'+appId).html(employeeTableHtml);
								}
								});
							}
							});
						}
				    }
				}
				else
				{
					notify('First select an area in order to add/remove apps.', {'duration': 8000});
				}
				
			});
			
			// Mark the row as selected if the current area has this app
			
			
			if (parseInt(oData['selected']))
			{
				$row.addClass('selected');
			}
			
			// Add db Id to row
			$row.attr('id', oData['appId']);
			
			// Add the area Id as an attribute of the row
			for(x in areaInfo)
			{
				$row.attr('data-area', areaInfo[x]['areaId']);
			}
		},
		"sAjaxSource": "getApp.php",
		"fnServerParams": function(aoData)
		{
			//parameter string		
			var ids="";
			var areaIds="";
			for(x in areaInfo)
			{
				for (var key in areaInfo[x])
				{
					if(key=="ID")
						ids+=areaInfo[x]["ID"]+",";
					else if(key=="areaId")
						areaIds+=areaInfo[x]["areaId"]+",";
						
				}
			}
			ids = ids.slice(0, ids.length-1);
			areaIds = areaIds.slice(0, areaIds.length-1);
			if(ids.length>0)
				aoData.push({ 'name': "ID", 'value': ids });
			if(areaIds.length>0)
			aoData.push({ 'name': "areaId", 'value': areaIds });
			
		},
		"sServerMethod": "POST",
		"sAjaxDataProp": 'apps',
		"bAutoWidth": false,
		"aoColumns":
		[
			{'mData': 'appName', 'sWidth': '10%'},
			{'mData': 'description', 'sWidth': '50%'},
			{'mData': 'filePath', 'sWidth': '30%'},
			{'mData': 'internal', 'sWidth': '10%', 'mRender': function(data, type, full) { return renderAppCheckBox(data, type, full, 'internal'); } }
		]
	});
}

function renderAppCheckBox(data, type, full, dbField)
{
	if (type == 'display')
	{
		var checked = '';
		if (parseInt(data) == 1)
		{
			checked = ' checked="checked"';
		}
		return $('<input type="checkbox" disabled="disabled"'+checked+' />').prop('outerHTML');
	}
	return data;
}

function loadMe()
{
	// Load data for table
	$dataTable = $('#areaTable').dataTable(
	{
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bProcessing": true,
		"sAjaxSource": "getArea.php",// Read table data from objects acquired from getArea.php
		"sAjaxDataProp": 'areas',
		"fnCreatedRow": function(nRow, oData, iDataIndex)
		{
			$(nRow).click(function()
			{
				var $row = $(this);
				if ($row.hasClass('selected'))
				{
					//$('.selected').removeClass('selected');
					$row.removeClass('selected');	
					var newArray = new Array();
					for(x in areaInfo)
					{
						if(areaInfo[x]["ID"]!=oData["ID"])
						{
							
							newArray.push(areaInfo[x]);
						}
					}
					areaInfo = newArray;
					loadAncillaryAreaInfo();
				}
				else
				{
					//$('.selected').removeClass('selected');
					$row.addClass('selected');
					areaInfo.push(oData);
					loadAncillaryAreaInfo($row);
				}
			})
		},//formats each column (each key with same name of each object) according to specifications below
		"aoColumns":
		[
			{"mData": 'ID', "bVisible": false}, // ID
			{"mData": 'area', "fnCreatedCell": function(nTd, sData, full, iRow, iCol){ textCreated(nTd, sData, full, iRow, iCol, 'area'); }}, // Area
			{"mData": 'longName', "fnCreatedCell": function(nTd, sData, full, iRow, iCol){ textCreated(nTd, sData, full, iRow, iCol, 'longName'); }}, // Long Name
			{"mData": 'startDay', "fnCreatedCell": dayCreated, "mRender": function(data, type, full) { return renderDays(data, type, full, 'start'); }}, // Start Day
			{"mData": 'startTime', "fnCreatedCell": timeCreated, "mRender": function(data, type, full) { return renderTime(data, type, full, 'start'); }}, // Start Time
			{"mData": 'endDay', "fnCreatedCell": dayCreated, "mRender": function(data, type, full) { return renderDays(data, type, full, 'end'); }}, // End Day
			{"mData": 'endTime', "fnCreatedCell": timeCreated, "mRender": function(data, type, full) { return renderTime(data, type, full, 'end'); }}, // End Time
			{"mData": 'hourSize', "fnCreatedCell": rangeCreated, "mRender": renderRange}, // Shift Snap
			{"mData": 'homePage', "fnCreatedCell": function(nTd, sData, full, iRow, iCol){ textCreated(nTd, sData, full, iRow, iCol, 'homePage'); }}, // Home Page
			{"mData": 'postSchedulesByDefault', "fnCreatedCell": checkboxCreated, "mRender": function (data, type, full) { return renderCheckbox(data, type, full, 'postSchedulesByDefault'); }}, // Post by Default?
			{"mData": 'canEmployeesEditWeeklySchedule', "fnCreatedCell": checkboxCreated, "mRender": function (data, type, full) { return renderCheckbox(data, type, full, 'canEmployeesEditWeeklySchedule'); }}, // Self-Schedule Weekly?
			{"bSortable": false, "mData": null, "mRender": renderDelete}  // Delete?
		]
	});
	
	loadAncillaryAreaInfo();
	
	$('#addButton').button().click(function ()
	{
		addArea();
	});
}
