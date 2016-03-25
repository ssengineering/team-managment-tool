function addApp()
{
	var $shiftPopup = $('#addAppPopup')
    .dialog({
        width: '350',
        height: 'auto',
        title: 'New App Reference',
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
	$.post("newApp.php", {'appName': $('#appName').val(), 'filePath': $('#filePath').val(), 'description': $('#description').val(), 'internal': $('#internal').prop('checked')? 1:0}, function (response)
	{
		response = JSON.parse(response);
		if (response['status'] == 'OK')
		{
			notify("Successfully added!", {'status': 'success'});
			var isChecked = $('#internal').prop('checked');
			if (isChecked)
			{
				isChecked = 'checked="checked"';
			}
			else
			{
				isChecked = '';
			}
			aoDataId = $dataTable.fnAddData([$('#appName').val(), $('#filePath').val(), $('#description').val(), $('<input onclick="internalToggle(this)" type="checkbox" name="internal" value="'+response['appId']+'" '+isChecked+' />').get(0).outerHTML, $("<span onmouseover=\"$(this).addClass('red')\" onmouseout=\"$(this).removeClass('red')\" onclick=\"deleteApp(this)\" class=\"delete ui-icon ui-icon-circle-close\"></span>").get(0).outerHTML]);
			var newRow = $dataTable.fnGetNodes(aoDataId);
			newRow.id = response['appId'];
			$(newRow).find('input[name=internal]').parent().addClass('internalTd');
			$(newRow).find('span.delete').parent().addClass('deleteTd');
		}
		else
		{
			notify("Failed to create permission!<br />Please try creating it again.", {'status': 'failure', 'clickToDismiss': true});
		}
		$('#addAppPopup input').val('');
	});
}

function cancel()
{
	$('#addAppPopup input').val('');
}

function deleteApp(element)
{
	var appId = $(element).parent().parent().attr('id');
	$.post("deleteApp.php", {'id': appId}, function (response)
	{
		if (response == 'OK')
		{
			notify("Successfully deleted!", {'status': 'success'});
		}
		else
		{
			notify("Failed to delete! Please refresh!", {'status': 'failure', 'clickToDismiss': true});
		}
	});
	$dataTable.fnDeleteRow($(element).parent().parent().get(0));
}

function internalToggle(element)
{
	var $element = $(element);
	$.post('editApp.php',
	{
		'id' : $element.attr('value'),
		'column' : 'internal',
		'value' : $element.prop('checked')?1:0
	}, function (response)
	{
		if (data == "OK")
		{
			notify("Updated Successfully!", {"status": "success"});
		}
		else
		{
			notify("Failed to update the server!<br /><div style=\"text-align: center;\">Please try again!</div>", {'status': 'failure', 'clickToDismiss': true});
		}
	});
	$element.prop('checked');
}

function loadMe()
{
	// Load tEditable jQuery plug-in
	$.getScript("tEditable.js");
	
	// Turn permissions table into a data table
	$dataTable = $('#appTable').dataTable(
	{
		"bJQueryUI": true,
		"iDisplayLength": 25
	});
	
	$('#addButton').button().click(function ()
	{
		addApp();
	});
}
