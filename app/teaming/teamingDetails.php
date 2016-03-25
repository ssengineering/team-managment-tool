<?php require('../includes/includeme.php'); 
	require('teamingFunctions.php');

?>
<script>
window.onload = function(){ $( "input:text.datepicker" ).datepicker({dateFormat:"yy-mm-dd"}); };
	function runSearch() {
		var page = "getTeamingDetails.php?";
			page += "employee=" + document.getElementById("employee").value + "&";
			page += "leader=" + document.getElementById("leader").value + "&";
			page += "startDate=" + document.getElementById("startDate").value + "&";
			page += "endDate=" + document.getElementById("endDate").value + "&";
			page += "teamed=" + document.getElementById("teamed").value + "&";
			page += "timely=" + document.getElementById("timely").value;
	
		var cb = function(result) {
					document.getElementById("data").innerHTML = result;
				}
		
		callPhpPage(page, cb);
	}
</script>

<div align='center'>

<h1>Training Details</h1>

<table>
	<tr>
		<th>Employee</th>
		<th>Leader</th>
		<th>Start Date</th>
		<th>End Date</th>
		<th>Trained</th>
		<th>Timely</th>
	</tr>
	<tr>
		<td>
			<select name="employee" id="employee">
				<?php employeeFillCurrentArea(); ?>
			</select>
		</td>
		<td>
			<select name="leader" id="leader">
				<?php teamLeaderSelect($area, ''); ?>
			</select>
		</td>
		<td>
			<input type=text id='startDate' class='datepicker' size=10>
		</td>
		<td>
			<input type=text id='endDate' class='datepicker' size=10>
		</td>
		<td>
			<select id="teamed">
				<option value="TRUE">Yes</option>
				<option value="FALSE">No</option>
				<option value="" selected>Either</option>
			</select>
		</td>
		<td>
			<select id="timely">
				<option value="TRUE">Yes</option>
				<option value="FALSE">No</option>
				<option value="" selected>Either</option>
			</select>
		</td>
	</tr>
</table>

<input type=button value="Search" onClick="runSearch()">

<br /><br /><br />

<div id="data"></div>

</div>

<?php require('../includes/includeAtEnd.php'); ?>
