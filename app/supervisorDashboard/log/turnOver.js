window.onload = function()
{
	$('#startDate,#endDate').datepicker({'dateFormat': 'yy-mm-dd',showOn: 'both',buttonImage: '/includes/libs/img/cal.gif',buttonImageOnly: true});
	$('#submitQuery').button().click(function()
	{
		// Do stuff here on click
		var requestUrl = '/API/turnOverNotes/'+$('#submittedBy').val()+'/'+$('#ownedBy').val()+'/'+$('#startDate').val()+'/'+$('#endDate').val()+'/'+$('#cleared').val()+'/'+$('#noteText').val()+'/'+$('#closingComment').val()+'/';
		$.ajax(requestUrl, {'dataType': 'json', 'success': function(response)
		{
			if (response.status == 'OK')
			{
				if (response.data.length)
				{
					var th = '<table id="tableNotes"><tr><th class="smallHeader">Submitted-by</th><th class="smallHeader">Time-submitted</th><th>Text</th><th class="smallHeader">Closed-by</th><th class="smallHeader">Time-closed</th><th>Closing-comment</th></tr>';
					var tr = '';
					var notes = response.data;
					for (var i in response.data)
					{
						var owner = '';
						var timeOwned = '';
						if (notes[i]['cleared'] == 1)
						{
							timeOwned = notes[i]['timeCleared'];
							owner = notes[i]['cLastName']+', '+notes[i]['cFirstName'];
						}
						else
						{
							owner = 'N/A';
							timeOwned = 'N/A';
						}
						tr += '<tr><td>'+notes[i]['sLastName']+', '+notes[i]['sFirstName']+'</td><td>'
							+notes[i]['timeSubmitted']+'</td><td>'
							+notes[i]['note']+'</td><td>'
							+owner+'</td><td>'
							+timeOwned+'</td><td>'
							+notes[i]['closingComment']+'</td></tr>';
					}
					var tableEnd = '</table>';
					$('#noteLog').html(th+tr+tableEnd);
				}
				else
				{
					$('#noteLog').html('<h3>No entries matched the parameters given.</h3>');
				}
			}
			else
			{
				notify(response.error, {'status': 'failure', 'clickToDismiss': true});
			}
		}});
	});

	// Run an initial default search
	$("#submitQuery").click();
}
