function deletePermission(element)
{
	var permissionId = $(element).parent().parent().attr('id');
	$.post("deletePermission.php", {'id': permissionId}, function (response)
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

function ok()
{
	$.post("newPermission.php", {'shortName': $('#shortName').val(), 'longName': $('#longName').val(), 'description': $('#description').val()}, function (response)
	{
		response = JSON.parse(response);
		if (response['status'] == 'OK')
		{
			notify("Successfully added!", {'status': 'success'});
			aoDataId = $dataTable.fnAddData([$('#shortName').val(), $('#longName').val(), $('#description').val(), $("<span onmouseover=\"$(this).addClass('red')\" onmouseout=\"$(this).removeClass('red')\" onclick=\"deletePermission(this)\" class=\"delete ui-icon ui-icon-circle-close\"></span>").get(0).outerHTML]);
			var newRow = $dataTable.fnGetNodes(aoDataId);
			newRow.id = response['permissionId'];
			$(newRow).find('span.delete').parent().addClass('deleteTd');
		}
		else
		{
			notify("Failed to create permission!<br />Please try creating it again.", {'status': 'failure', 'clickToDismiss': true});
		}
		$('#addPermissionPopup input').val('');
	});
}

function cancel()
{
	$('#addPermissionPopup input').val('');
}

function addPermission()
{
	var $shiftPopup = $('#addPermissionPopup')
    .dialog({
        width: '350',
        height: 'auto',
        title: 'New Permission',
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

function loadMe()
{
	// Load tEditable jQuery plug-in
	$.getScript("tEditable.js");
	
	// Turn permissions table into a data table
	$dataTable = $('#permissionTable').dataTable(
	{
		"bJQueryUI": true,
		"iDisplayLength": 25
	});
	
	$('#addButton').button().click(function ()
	{
		addPermission();
	});
}
