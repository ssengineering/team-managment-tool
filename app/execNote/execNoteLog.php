<?php //index.php This is the supervisor report Log. It pulls in the info from submitted reports
require('../includes/includeme.php');
require('execNoteFunctions.php');
function printIcs(){
	global $netID;
	echo "<select id='ics' name='ics' style='width: 100%;'>";
	echo "<option value='[ Any ]' selected>Anyone</option>";
	getICList('');
	echo "</select>";
}

?>
<script language="JavaScript" src="/includes/libs/tcal.js"></script>
<link rel="stylesheet" type="text/css" href="/includes/libs/tcal.css" />
<link rel="stylesheet" type="text/css" href="execNoteLog.css" />
<script type='text/javascript' >

window.onload = function() {
		printLog();
	}

function printLog(){
	var ticket = encodeURIComponent($("#searchTicket").val());
	var startDate = encodeURIComponent($("#startDate").val());
	var endDate = encodeURIComponent($("#endDate").val());
	var description = encodeURIComponent($("#searchDescription").val());
	var ic = encodeURIComponent($("#ics").val());
	var page = "printExecNoteLog.php?searchTicket="+ticket+"&startDate="+startDate+"&endDate="+endDate+"&ic="+ic+"&description="+description;
	
	var cb = function(result)
		{
			document.getElementById("results").innerHTML = result;
			$('.execNote').each( function (index) {
				var height = $(this).children('.subject').css('height');
				$(this).children().css('height', height);
			});
			
			// IF ONLY ONE RESULT SHOW EXEC NOTE HISTORY
			if ($('.execNoteContainer').length < 3)
			{
				$('.priority.divTd').each( function(index)
				{
					var str = $(this).html().replace('▹', '▾');
					$(this).html(str);
				});
				$('.execNoteHistory').each( function(index) {
				if ($(this).is(':hidden'))
				{
					$(this).show();
					var textHeight = parseFloat($(this).children('.text').css('height'));
					var submitterHeight = parseFloat($(this).children('.submitter').css('height'));
					if (textHeight >= submitterHeight) height = textHeight;
					else height = submitterHeight;
					$(this).children().css('height', height+'px');
				}
				else $(this).hide();
				});
			}
		};

	callPhpPage(page,cb);
}

function viewExecNote(id){
	$('#'+id).before($('#execNoteTh'));
	$('.execNoteHistory:not(.history'+id+')').hide();
	$('.execNote').each( function(index) {
	var clearStr = $(this).children('.priority').html().replace('▾', '▹');
		$(this).children('.priority').html(clearStr);
	});
	var str = $('#execNote'+id).children('.priority').html().replace('▹', '▾');
	$('#execNote'+id).children('.priority').html(str);
	$('.history'+id).each( function(index) {
	if ($(this).is(':hidden'))
	{
		$(this).show();
		var textHeight = parseFloat($(this).children('.text').css('height'));
		var submitterHeight = parseFloat($(this).children('.submitter').css('height'));
		if (textHeight >= submitterHeight) height = textHeight;
		else height = submitterHeight;
		$(this).children().css('height', height+'px');
	}
	else 
	{
		$(this).hide();
		$('.execNote').each( function(index) {
			var clearStr = $(this).children('.priority').html().replace('▾', '▹');
			$(this).children('.priority').html(clearStr);
		});
		$('#results').prepend($('#execNoteTh'));
	}
	});
}
</script>
<div>
<h1>Executive Notification Log</h1>
<h3>Search</h3>
<table style="width: 100%;">
	<tr>
		<th style="width: 16%">Ticket</th>
		<th style="width: 12%">Start Date</th>
		<th style="width: 12%">End Date</th>
		<th style="width: 37%">Subject/Description</th>
		<th style="width: 23%">Touched By</th>
	</tr>
	<tr>
		<td><input type="text" id="searchTicket" name="searchTicket" style="width: 100%;"></input></td>
		<td><input type="text" value="<?php echo date('Y-m-d', strtotime('-1 month')); ?>" name="startDate" id="startDate" size=10 class='tcal' /></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="endDate" id="endDate" size=10 class='tcal' /></td>
		<td><input type="text" id="searchDescription" name="searchDescription" style="width: 100%;"></input></td>
		<td><?php printIcs(); ?></td>
	</tr>
</table>
<input type='button' id='submit' value="Submit Search" onclick='printLog()' />
</div>
<br/>
<br/>
<div id='results'>
</div> 
<?php require('../includes/includeAtEnd.php');
?>
