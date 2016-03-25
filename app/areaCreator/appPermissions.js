function addAssociation()
{
	var $shiftPopup = $('#addAssociationPopup')
    .dialog({
        width: '350',
        height: 'auto',
        title: 'New App Permission Association',
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
	$.post("newAppPermission.php", { 'appId': $('#appId').val(), 'permissionId': $('#permissionId').val() }, function (response)
	{
		response = JSON.parse(response);
		if (response['status'] == 'OK')
		{
			notify("Successfully added!", {'status': 'success'});
			$.post("getAppPermission.php", { 'id': response['appPermissionId'] }, function (response)
			{
				response = JSON.parse(response);
				if (response['status'] == 'OK')
				{
					var appPermission = response['appPermission'];
					aoDataId = $dataTable.fnAddData([appPermission['appName'], appPermission['filePath'], appPermission['appDescription'], appPermission['shortName'], appPermission['longName'], appPermission['permissionDescription'], $("<span onmouseover=\"$(this).addClass('red')\" onmouseout=\"$(this).removeClass('red')\" onclick=\"deleteAssociation(this)\" class=\"delete ui-icon ui-icon-circle-close\"></span>").get(0).outerHTML]);
					var newRow = $dataTable.fnGetNodes(aoDataId);
					newRow.id = appPermission['appPermissionId'];
					$(newRow).find('span.delete').parent().addClass('deleteTd');
				}
			});
		}
		else
		{
			notify("Failed to create association!<br />Please try creating it again.", {'status': 'failure', 'clickToDismiss': true});
		}
		$('#addAssociationPopup input').val('');
	});
}

function cancel()
{
	$('#addAssociationPopup input').val('');
}

function deleteAssociation(element)
{
	var appPermissionId = $(element).parent().parent().attr('id');
	$.post("deleteAppPermission.php", {'id': appPermissionId}, function (response)
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

function loadMe()
{
	// Load tEditable jQuery plug-in
	$.getScript("tEditable.js");
	
	// Turn permissions table into a data table
	$dataTable = $('#associationTable').dataTable(
	{
		"bJQueryUI": true,
		"iDisplayLength": 25
	});
	
	$('#addButton').button().click(function ()
	{
		addAssociation();
	});
}
