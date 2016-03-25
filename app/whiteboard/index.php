<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeme.php');
require_once('logFunctions.php');

$isAdmin = can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936")/*whiteboard resource*/? '1':'0';
$isApprover = can("approve", "6db1ee4f-4d80-424d-a062-97dc4cc22936")/*whiteboard resource*/? '1':'0';
$isLogViewer = can("access", "6e5f124a-b6f7-4ee7-8fb9-cab94ae881ae")/*whiteboardMandatoryLog resource*/? '1':'0';
// Include admin stuff or dummy functions if permissions are not met.
if($isApprover == '1')
{
	echo '<script type="text/javascript" src="/whiteboard/approver.js"></script>';
}
else
{
	echo '<script type="text/javascript"> var getUnapprovedWhiteboards = function (){}; </script>';
}

// Include log viewing stuff or dummy functions if permissions are not met
if ($isLogViewer)
{
	echo '<script src="/whiteboard/logViewer.js" type="text/javascript"></script>';
}
else
{
	echo '<script type="text/javascript"> var loadHasReadList = function() {}; </script>';
}?>

<script>

	var curUser = <?php echo "{'netID':'${netID}', 'isAdmin': ${isAdmin}, 'isApprover': ${isApprover}, 'isLogViewer': ${isLogViewer}, 'area': '${area}'}"; ?>;

	window.onload = function()
	{
		getPreferences();
		$('#whiteboardSearch').button().click(searchWhiteboards);
		$('.datepick').datepicker({'dateFormat': 'yy-mm-dd'});
		getUnapprovedWhiteboards();
		searchWhiteboards();
		setInterval(searchWhiteboards, 60000);
		setInterval(getUnapprovedWhiteboards, 61000);
	}

	var preferences;
	// Get the current users preferences
	function getPreferences()
	{
		$.ajax('/API/preferences/'+curUser.netID, {'type': 'GET', 'dataType':'json', 'complete':function(response, status)
		{
			if (status == 'success')
			{
				var response = JSON.parse(response.responseText);
				if (!response.error)
				{
					preferences = response.data;
				}
			}
		}});
	}

	// Set preferences for user on key -- the value should not be stringified yet
	function savePreferences(key, value)
	{
		$.ajax('/API/preferences/'+curUser.netID+'/'+key+'/'+JSON.stringify(value), {'type': 'POST', 'dataType': 'json', 'complete': function(response)
		{
			response = JSON.parse(response.responseText);
			if (response.error)
			{
				notify("Failed to update preferences: "+response.error, {'status':'failure'});
			}
		}});
	}

	// First call is just to track when the first call to searchWhiteboards is made so that we can remove old whiteboard preferences that are for expired whiteboards
	var isFirstCall = true;
	var currentWhiteboards = {};
	// Get all whiteboards for the current area matching the search criteria
	function searchWhiteboards(forceRefresh)
	{
		if (forceRefresh)
		{
			for (post in currentWhiteboards)
			{
				$('#titleRow'+post).remove();
				$('#messageRow'+post).remove();
				delete currentWhiteboards[post];
			}
		}
		$.ajax('printWhiteboards.php', {'type': 'POST', 'dataType': 'json', 'data': $('#searchParameters>input,#searchParameters>select').serialize(), 'complete': function(response, requestStatus)
		{
			if (requestStatus == 'success')
			{
				var result = JSON.parse(response.responseText);
				if (!result.error)
				{
					var whiteboards = result.data;
					if (whiteboards.length)
					{
						$('#whiteboardsTable').show();
						$('#noWhiteboards').hide();

						var idArray = whiteboards.map(function (post) {return post.messageId});
						// For everything in currentWhiteboards that is not in our new list of whiteboards Remove it
						for (var post in currentWhiteboards)
						{
							if ($.inArray(post, idArray) == -1)
							{
								$('#titleRow'+post).remove();
								$('#messageRow'+post).remove();
								delete currentWhiteboards[post];
							}
						}

						// Now add the whiteboards that need to be added
						post = whiteboards.length;
						var lastPost;
						while (post--)
						{
							var id = whiteboards[post].messageId;
							// If we don't already have this whiteboard loaded render and add to currentWhiteboards
							if (!currentWhiteboards[id])
							{
								renderWhiteboard(whiteboards[post], lastPost, true);
								currentWhiteboards[id] = whiteboards[post];
							}
							lastPost = whiteboards[post].messageId;
						}
					}
					else
					{
						// No whiteboards matched the search parameters
						$('#whiteboardsTable').hide();
						$('#noWhiteboards').show();
					}

					// Remove expired whiteboards from preferences
					if (isFirstCall)
					{
						try
						{
							var collapsed = preferences[curUser.area].whiteboard.collapsed;
							var toBeRemoved = [];
							for (var i in collapsed)
							{
								// Remove the whiteboard from our preferences if it is no longer an active message, also remove it if the message has not been read.
								if ( !(collapsed[i] in currentWhiteboards) )
								{
									toBeRemoved.push(collapsed[i]);
								}
								else if ( currentWhiteboards[collapsed[i]].hasRead == "0" )
								{
									toBeRemoved.push(collapsed[i]);
									$('#messageDiv'+collapsed[i]).show();
								}
							}
							for (var i in toBeRemoved)
							{
								collapsed.splice(collapsed.indexOf(toBeRemoved[i]), 1);
							}
							savePreferences(curUser.area+'.whiteboard.collapsed', collapsed);
						}
						catch (e)
						{
							console.log(e);
						}
						isFirstCall = false;
					}
				}
			}
			else
			{
				console.log(textStatus, response);
			}
		}});
	}

	function hideMessageDiv(e)
	{
		var msg = e.data;
		if (msg.hasRead == '1')
		{
			var $content = $('#messageDiv'+msg.messageId);
			// We are checking for visibility before altering it so isCollapsed really should be the same as whether the element is currently visible
			var isCollapsed = $content.is(':visible');
			$content.slideToggle('fast');
			// Update preferences to mark as either collapsed or opened
			try
			{
				var updatedPreferences = preferences[curUser.area].whiteboard.collapsed;
			}
			catch (e)
			{
				var updatedPreferences = [];
				var keysArray = [curUser.area, 'whiteboard'];
				var subPreferences = preferences;
				for (var i = 0; i < keysArray.length; i++)
				{
					if (subPreferences[keysArray[i]] === undefined)
					{
						subPreferences[keysArray[i]] = {};
					}
					subPreferences = subPreferences[keysArray[i]];
				}
				preferences[curUser.area].whiteboard.collapsed = updatedPreferences;
			}

			if (isCollapsed)
			{
				updatedPreferences.push(msg.messageId);
			}
			else
			{
				updatedPreferences.splice(updatedPreferences.indexOf(msg.messageId), 1);
			}
			savePreferences(curUser.area+'.whiteboard.collapsed', updatedPreferences);
		}
		else
		{
			notify('Before collapsing this message you<br>must mark it as having been read.');
		}
	}

	// Render the whiteboard html and insert it in its correct location on the page
	function renderWhiteboard(msg, lastPost, isApproved)
	{
		//build html then add in "proper" place on page
		var titleRow = document.createElement("tr");
		titleRow.id = 'titleRow'+msg.messageId;
		var msgType = document.createElement("td");

		// Title should include whether or not this is a mandatory msg
		var msgTitle = document.createElement("td");
		var msgKb = document.createElement("td");
		var msgOwner = document.createElement("td");
		var msgPostDate = document.createElement("td");
		var msgExpireDate = document.createElement("td");
		var msgEdit = document.createElement("td");
		var msgDelete = document.createElement("td");

		// Now set text node for all td's
		msgType.textContent = msg.typeName;
		msgTitle.innerHTML = (msg.mandatory == '1'? '<b>MANDATORY:</b><br>':'')+'<a>'+msg.title+'</a>';
		if(msg.kb == '') {
			msgKb.innerHTML = 'None';
		} else {
			// Pad with zeros
			var zeros = "";
			var zeroDiff = 7 - msg.kb.length;
			while(zeroDiff > 0) {
				zeros += "0";
				zeroDiff--;
			}
			msgKb.innerHTML = '<a href="https://it.byu.edu/kb_view_customer.do?sysparm_article=KB'+zeros+msg.kb+'" target="_blank">'+msg.kb+'</a>';
		}
		msgOwner.textContent = msg.firstName + ' ' + msg.lastName;
		msgPostDate.textContent = msg.postDate.split(' ')[0];
		msgExpireDate.textContent = msg.expireDate;
		// If the owner of the whiteboard then allow editing and deleting, or if a whiteboard admin
		if (msg.ownerId == curUser.netID || curUser.isAdmin)
		{
			msgEdit.innerHTML = "<a onclick=\"newwindow('/whiteboard/editMessage.php?messageId="+msg.messageId+"')\">Edit</a>";
			msgDelete.innerHTML = '<a onclick="deleteMessage('+msg.messageId+')">Delete</a>'; 
		}

		// Set css junk
		titleRow.className = 'titleRow';
		msgType.style.backgroundColor = msg.color;
		msgType.className = 'msgType';
		msgTitle.className = 'msgTitle';
		msgTitle.style.cursor = 'pointer';
		msgKb.className = 'msgKb';
		msgOwner.className = 'msgPoster';
		msgPostDate.className = 'msgPostedDate';
		msgExpireDate.className = 'msgExpirationDate';
		msgEdit.className = 'msgEdit';
		msgDelete.className = 'msgDelete';

		// Now append all the above td's to the titleRow
		titleRow.appendChild(msgType);
		titleRow.appendChild(msgTitle);
		titleRow.appendChild(msgKb);
		titleRow.appendChild(msgOwner);
		titleRow.appendChild(msgPostDate);
		titleRow.appendChild(msgExpireDate);
		titleRow.appendChild(msgEdit);
		titleRow.appendChild(msgDelete);

		// Now build the message content
		var msgRow = document.createElement("tr");
		msgRow.id = 'messageRow'+msg.messageId;
		msgRow.className = 'msgRow';
		var msgContent = document.createElement("td");

		// TODO: Includes markRead button for area 2 only? (do the other areas care about marking as read and allowing people to minimize posts??)
		if (msg.hasRead === '0')
		{
			msgContent.innerHTML = '<input type="button" class="markAsRead" id="markAsRead'+msg.messageId+'" onclick="markRead('+msg.messageId+')" value="I have read this message">';
		}
		var msgDiv = document.createElement("div");
		msgDiv.id = 'messageDiv'+msg.messageId;
		msgDiv.className = 'messageDiv';
		// Needs to span all columns of the table
		$(msgContent).attr('colspan', 8);

		// Determine if we should show any approval information. If not just add an empty text node
		if (isApproved && msg.approvedOn)
		{
			// Give the approval information
			var approveElement = document.createElement('span');
			approveElement.textContent = "Approved by "+msg.approverFirst+' '+msg.approverLast+' on '+msg.approvedOn;
			approveElement.className = 'approvalSpan';
		}
		else if (!isApproved)
		{
			// Provide an option to approve the unapproved whiteboard
			var approveElement = document.createElement('input');
			approveElement.type = 'button';
			approveElement.value = "Approve";
			approveElement.className = 'approveButton';
		}
		else
		{
			// Do nothing (except make an empty text node to attach
			var approveElement = document.createTextNode('');
		}

		msgDiv.innerHTML = msg.message;
		msgContent.appendChild(msgDiv);
		var msgLog = document.createElement('div');
		msgLog.id = 'msgLogDiv'+msg.messageId;
		msgLog.className = 'msgLogDiv';
		msgContent.appendChild(msgLog);
		msgContent.appendChild(approveElement);
		// If the current user has permissions to view logs give them a button to click on if they want to see the logs
		if (curUser.isLogViewer)
		{
			var viewLog = document.createElement('a');
			viewLog.textContent = "View Log/Content";
			viewLog.className = "viewLogLink";
			viewLog.id = "viewLogLink"+msg.messageId;
			msgContent.appendChild(viewLog);
		}
		msgRow.appendChild(msgContent);

		// If we have a lastRow then insert after it, otherwise just append to the table
		if (lastPost)
		{
			$(titleRow).insertBefore('#titleRow'+lastPost);
			$(msgRow).insertAfter(titleRow);
		}
		else
		{
			var table = isApproved ? '#whiteboardsTable' : '#unapprovedTable';
			$(table).append(titleRow).append(msgRow);
		}

		// Set onclick functions
		$(msgTitle).click(msg,hideMessageDiv);
		if (!isApproved)
		{
			$(approveElement).button().click(msg, approveWhiteboard);
		}
		if (curUser.isLogViewer)
		{
			$(viewLog).click(msg, loadHasReadList);
		}

		// Hide msgRow if it has been flagged for hiding
		try
		{
			if ($.inArray(msg.messageId, preferences[curUser.area].whiteboard.collapsed) != -1)
			{
				// Hide msgRow
				$(msgDiv).hide();
			}
		}
		catch (e)
		{
			console.log(e);
		}
	}
	
	// Helper functions that were previously being used, and I decided to keep but alter to make things easier in my life
	function newwindow(urlpass)
	{
		window.open(urlpass, "Whiteboard Message", "status=1,width=1024,height=700,scrollbars=1");
	}
	function deleteMessage(id)
	{
		var r = confirm("Are you sure you want to Delete this Message?");
		if (r == true)
		{
			var request = $.ajax({url: "/whiteboard/deleteMessage.php", type: "POST", 'dataType': 'json', data: {'messageId': id}, cache: false});
			request.done(function(response)
			{
				// Delete the relevant html elements and remove from currentWhiteboards
				// Oh, yeah . . . and uh notify the user
				if (!response.error)
				{
					var msg = currentWhiteboards[id];
					if (msg)
					{
						delete currentWhiteboards[id];
						if (!Object.keys(currentWhiteboards).length)
						{
							searchWhiteboards();
						}
					}
					else
					{
						msg = unapprovedWhiteboards[id];
						delete unapprovedWhiteboards[id];
						if (!Object.keys(unapprovedWhiteboards).length)
						{
							getUnapprovedWhiteboards();
						}
					}
					$('#titleRow'+msg.messageId+',#messageRow'+msg.messageId).remove();
					notify('"' + msg.title + '"' + ' has been deleted.', {'status': 'success'});
				}
				else
				{
					notify('Unable to delete whiteboard. Please try again!', {'status': 'failure'});
				}
			});

			request.fail(function(jqXHR, textStatus)
			{
				notify("Request failed: " + textStatus, {'status': 'failure'});
			});
		}
	}
	function markRead(msgID)
	{
		var page = "/whiteboard/markRead.php?id=" + msgID;
		var cb = function(responseText)
		{
			currentWhiteboards[msgID].hasRead = "1";
			notify('View recorded.');
			$('#markAsRead'+msgID).remove();
		}
		callPhpPage(page, cb);
	}

</script>

<style>
a
{
	cursor: pointer;
}
#whiteboardsTable td,#unapprovedTable td
{
	padding: 10px;
	vertical-align: middle;
	border: 2px solid;
	border-color: #3F5678;
}
.msgType
{
	font-weight: bold;
	text-align: center;
	color: #ffffff;
	width: 12%;
}
.msgTitle
{
	width: 27%;
}
.msgKb
{
	text-align: center;
	width: 8%;
}
.msgPoster
{
	text-align: center;
	width: 10%;
}
.msgPostedDate
{
	text-align: center;
	width: 11%;
}
.msgExpirationDate
{
	text-align: center;
	width: 11%;
}
.msgEdit
{
	text-align: center;
	width: 6%;
}
.msgDelete
{
	text-align: center;
	width: 7%;
}
.messageDiv
{
	width: 100%;
}
.msgLogDiv
{
	width: 100%;
	display: none;
}
.msgLogDiv ul {
    list-style: none;
    margin:0;
    padding:0;
}
.msgLogDiv ul > li {
    display: inline-block;
    width: 100%;
}
.msgLogDiv div
{
	width: 100%;
	-webkit-column-count:6;
	-moz-column-count:6;
	-ms-column-count:6;
	-o-column-count:6;
	column-count:6;
	-webkit-column-gap:.25em;
	-moz-column-gap:.25em;
	-ms-column-gap:.25em;
	-o-column-gap:.25em;
	column-gap:.25em;
	columns:6;
}
.th
{
	text-align: center;
	background-color: #3F5678;
	color: white;
	border: 2px solid;
	border-color: #3F5678;
}
#buttons
{
	text-align:center;
	float:left;
	width:100%;
}
#info
{
	text-align:center;

}
viewed
{
	font-size:16px;
	color:#f8ad50;
}
#names
{
	text-align:center;
	font-size:16px;
	color:#8f100a;
	margin:auto;
}
#advancedSearchDiv
{
	margin: auto;
	text-align: center;
}
#searchParameters
{
	width: 80%;
	text-align: center;
	margin: auto;
	display: none;
}
#searchParameters>label
{
	font-weight: bold;
}
#text
{
	width: 33em;
	margin-bottom: .5em;
	margin-top: .5em;
}
#searchInstructions
{
	text-align: center;
	margin: auto;
	font-size: .75em;
}
.datepick
{
	width: 7em;
	margin-bottom: .5em;
}
#postedBy
{
	width: 16em;
	margin-bottom: .5em;
}
#kb
{
	width: 5em;
}
#whiteboardSearch,.approveButton
{
	margin-left: .5em;
	font-size: .85em !important;
}
.approvalSpan,.approveButton
{
	position: relative;
	bottom: 0;
	float: right;
}
.viewLogLink
{
	position: relative;
	bottom: 0;
	float: left;
}
#unapprovedDiv
{
	display: none;
}
.approvalTitle
{
	font-weight: bold;
	margin-left: .5em;
}
#noWhiteboards
{
	display: none;
	margin-left: auto;
	margin-right: auto;
	margin-top: 1em;
	font-weight: bold;
	font-size: 1.33em;
}
#whiteboardsTable,#unapprovedTable
{
	margin: auto;
	width: 100%;
	padding: 3;
	border: 1px solid;
	border-color: #3F5678;
	font-size: 14px;
	table-layout: fixed;
	word-wrap: break-word;
}
#unapprovedTable
{
	margin-top: .5em;
	margin-bottom: .5em;
}
#msgType
{
	width: 12%;
}
#msgTitle
{
	width: 27%;
}
#msgKb
{
	width: 8%;
}
#msgPoster
{
	width: 10%;
}
#msgPostedDate
{
	width: 11%;
}
#msgExpirationDate
{
	width: 11%;
}
#msgEdit
{
	width: 6%;
}
#msgDelete
{
	width: 7%;
}
</style>

<h1 align='center' style="margin: .2em;">Whiteboard</h1>

<div id="advancedSearchDiv">
	<a href="" id="advancedSearch" onclick="{$('#searchParameters').toggle(); event.preventDefault();}">Advanced Search</a>
</div>

<div id="searchParameters" align='center'>

	<label for="text">Key-words: </label>
	<input name="text" type="text" id="text" value="" class="text" placeholder="Finds whiteboards that contain any keywords listed (space separated)">
	<br>

	<label for="start">*Date-range Start: </label>
	<input name="start" type="text" id="start" value="" class="text datepick">
	
	<label for="end">*Date-range End: </label>
	<input name="end" type="text" id="end" value="" class="text datepick">
	
	<label for="postedBy">Posted-by: </label>
	<input name="postedBy" type="text" id="postedBy" value="" class="text" placeholder="Employee(s) name(s) or NetID(s)">
	<br>

	<label for="kb">KB: </label>
	<input name="kb" type="text" id="kb" value="" class="text">
	
	<label for="primaryTag">Type: </label>
	<select name="primaryTag" id="primaryTag">
		<option value="">Any</option>
	<?php
		try {
			$tagQuery = $db->prepare("SELECT * FROM `tag` WHERE `area` = :area ORDER BY `typeName`");
			$tagQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while ($tag = $tagQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo "<option value='${tag['typeId']}'>${tag['typeName']}</option>";
		}
	?>
	</select>
	
	<label for="mandatory">Mandatory: </label>
	<select name="mandatory" id="mandatory">
		<option value="">Any</option>
		<option value="0">No</option>
		<option value="1">Yes</option>
	</select>

	<input name="whiteboardSearch" type="button" id="whiteboardSearch" value="Search" class="text">
	<br>

	<span id="searchInstructions">*If both start and end date are left empty only active whiteboards are included in the search</span>

</div>

<img src="/whiteboard/button.png" onmouseover="this.src='/whiteboard/button1.png'" onmouseout="this.src='/whiteboard/button.png'" onclick="newwindow('/whiteboard/postmessage.php')">
<?php if($isAdmin == '1'){ ?>
<div style="float:right;"><a style="vertical-align: sub; vertical-align: -webkit-baseline-middle;" href="editMessageTypes.php">Edit Message Types</a></div>
<div id="unapprovedDiv">
<h2 id="unapprovedTitle" class="approvalTitle"> Unapproved </h2>
	<table id="unapprovedTable">
		<tr>
			<th class="th msgType">Type</th>
			<th class="th msgTitle">Title</th>
			<th class="th msgKb">KB</th>
			<th class="th msgPoster">Poster</th>
			<th class="th msgPostedDate">Posted Date</th>
			<th class="th msgExpirationDate">Expiration Date</th>
			<th class="th msgEdit">Edit</th>
			<th class="th msgDelete">Delete</th>
		</tr>
	</table>
<h2 id="approvedTitle" class="approvalTitle"> Approved </h2>
</div>
<?php } ?>

<div id='info'>
	<table id="noWhiteboards">
		<tr>
			<th>No whiteboards matched the search terms provided. Try broadening your search.</th>
		</tr>
	</table>
	<table id="whiteboardsTable">
		<tr>
			<th class="th" id="msgType">Type</th>
			<th class="th" id="msgTitle">Title</th>
			<th class="th" id="msgKb">KB</th>
			<th class="th" id="msgPoster">Poster</th>
			<th class="th" id="msgPostedDate">Posted Date</th>
			<th class="th" id="msgExpirationDate">Expiration Date</th>
			<th class="th" id="msgEdit">Edit</th>
			<th class="th" id="msgDelete">Delete</th>
		</tr>
	</table>
</div>

<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeAtEnd.php');
?>
