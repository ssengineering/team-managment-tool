<?php //index.php This is the supervisor report Log. It pulls in the info from submitted reports
require('../includes/includeme.php');
//*************************
//add permission check here
if($area!=6){
	echo "<h2>You are not authorized to view this page. Contact your supervisor if you believe this is in error.</h2>";
	require('../includes/includeAtEnd.php');
	return;
}
//***********************


function printEmployees(){
		echo "<select id='employees' name='employees' onchange>";
		employeeFillCurrentArea();
		echo "</select>";
}

?>
<script language="JavaScript" src="/includes/libs/tcal.js"></script>
<link rel="stylesheet" type="text/css" href="/includes/libs/tcal.css" />
<script type='text/javascript' >
function printLog(){
	var employee = document.getElementById("employees").value;
	var start = document.getElementById("startDate").value;
	var end = document.getElementById("endDate").value;
	var securityProblems = document.getElementById("outageSearch").value;
	var shiftProblem = document.getElementById("problemSearch").value;
	var misc = document.getElementById("miscSearch").value;
	var page = 'printSecurityLog.php?employee='+employee+'&start='+start+'&end='+end+'&securityProblems='+securityProblems+'&shiftProblem='+shiftProblem+'&misc='+misc;
	
	var cb = function(result){ document.getElementById("results").innerHTML = result; };

	callPhpPage(page,cb);
}

</script>
<div>
<h1>Supervisor Report Log</h1>
<table>
	<tr>
		<th>Employee</th>
		<th>Start Date</th>
		<th>End Date</th>
		<th>Security Problems</th>
		<th>Shift Problems</th>
		<th>Misc</th>
	</tr>
	<tr>
		<td><?php printEmployees(); ?></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="startDate" id="startDate" size=10 class='tcal' /></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="endDate" id="endDate" size=10 class='tcal' /></td>
		<td><textarea id="outageSearch" cols='15'></textarea></td>
		<td><textarea id="problemSearch" cols='15'></textarea></td>
		<td><textarea id="miscSearch" cols='15'></textarea ></td>
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