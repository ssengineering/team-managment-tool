// Approve whiteboard
function approveWhiteboard(e)
{
	var msg = e.data;
	// Make ajax call to approve whiteboard then upon completion call getUnapprovedWhiteboards and searchWhiteboards to refresh our tables and notify user
	$.ajax('approveWhiteboard.php', {'type': 'POST', 'dataType': 'json', 'data': {'messageId':msg.messageId}, 'complete': function(response, requestStatus)
	{
		if (requestStatus = 'success')
		{
			var result = JSON.parse(response.responseText);
			if (!result.error)
			{
				getUnapprovedWhiteboards();
				searchWhiteboards();
				notify('Whiteboard post has been approved!', {'status': 'success'});
			}
			else
			{
				notify("Failed to approve whiteboard post! Please try again!", {'status': 'failure'});
			}
		}
		else
		{
			notify('Failed to connect to server! Whiteboard post was not approved!', {'status': 'failure'});
		}
	}});
}

// This is for handling the unapproved whiteboards
var unapprovedWhiteboards = {};
function getUnapprovedWhiteboards(forceRefresh)
{
	if (forceRefresh)
	{
		for (post in unapprovedWhiteboards)
		{
			$('#titleRow'+post).remove();
			$('#messageRow'+post).remove();
			delete unapprovedWhiteboards[post];
		}
	}
	$.ajax('printUnapprovedWhiteboards.php', {'type': 'POST', 'dataType': 'json', 'complete': function(response, requestStatus)
	{
		if (requestStatus == 'success')
		{
			var result = JSON.parse(response.responseText);
			if (!result.error)
			{
				var unapproved = result.data;
				$('#unapprovedDiv').show();

				var idArray = unapproved.map(function (post) {return post.messageId});
				// For everything in unapprovedWhiteboards that is not in our new list of whiteboards Remove it
				for (var post in unapprovedWhiteboards)
				{
					if ($.inArray(post, idArray) == -1)
					{
						$('#titleRow'+post).remove();
						$('#messageRow'+post).remove();
						delete unapprovedWhiteboards[post];
					}
				}

				// Now add the whiteboards that need to be added
				post = unapproved.length;
				var lastPost;
				while (post--)
				{
					var id = unapproved[post].messageId;
					// If we don't already have this whiteboard loaded render and add to unapprovedWhiteboards
					if (!unapprovedWhiteboards[id])
					{
						renderWhiteboard(unapproved[post], lastPost, false);
						unapprovedWhiteboards[id] = unapproved[post];
					}
					lastPost = unapproved[post].messageId;
				}
				if (!unapproved.length)
				{
					// No unapproved whiteboards so just hide everything related
					$('#unapprovedDiv').hide();
				}
			}
		}
		else
		{
			console.log(textStatus, response);
		}
	}});
}
