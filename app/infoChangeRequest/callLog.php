<?php //callLog.php
require('../includes/includeme.php');

?>
<script>
function printCallLog()
{
	var cb = function(result){
		document.getElementById('log').innerHTML = result;
	}
	var start = document.getElementById('startDate').value;
	var end = document.getElementById('endDate').value;

	callPhpPage("printCallLog.php?start="+start+"&end="+end,cb);
	
}
</script>
<div id='title' align='center'>
<h1>Unusual Call Comments</h1>
</div>
<form name="silentMonitor" method="post" id='silentMonitor'>
<center><table>
	<tr>
		<th>Start Date</th>
		<th>End Date</th>
	</tr>
	<tr>
		<td><input type="text" value="<?php echo date('Y-m-d', strtotime('-3 week')); ?>" name="startDate" id="startDate" size=10 onChange="isValidDate(this);" onKeyPress="return disableEnterKey(event);" /><?php calendarNoForm("startDate"); ?></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="endDate" id="endDate" size=10 onChange="isValidDate(this);" onKeyPress="return disableEnterKey(event);" /><?php calendarNoForm("endDate"); ?></td>
	</tr>
</table>
<input type='button' id='submit' value='Submit' onclick='printCallLog()' />
</form><br/><br/>
</center>
<form method='post'>
<div align='center' id='log' name='log'>
</div>
</form>
<?php 
require('../includes/includeAtEnd.php');
?>
