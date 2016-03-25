<?php //index.php This is the supervisor report Log. It pulls in the info from submitted reports
require('../includes/includeme.php');
//*************************
//add permission check here
if(!can("access", "c81d511e-6af0-4045-a53f-8e3c55ea3545"))/*supervisorLog resource*/{
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
<script type='text/javascript' >
window.onload = function(){ $( "input:text.datepicker" ).datepicker({dateFormat:"yy-mm-dd"}); };

function printLog(){
	var employee = document.getElementById("employees").value;
	var start = document.getElementById("startDate").value;
	var end = document.getElementById("endDate").value;
	var outage = document.getElementById("outageSearch").value;
	var problem = document.getElementById("problemSearch").value;
	var misc = document.getElementById("miscSearch").value;
	var page = 'printLog.php?employee='+employee+'&start='+start+'&end='+end+'&outage='+outage+'&problem='+problem+'&misc='+misc;
	
	var cb = function(result){ document.getElementById("results").innerHTML = result; };

	callPhpPage(page,cb);
}

function editLog(id,type){
	var urlpass = "../performance/editLog.php?id="+id+"&type="+type;		

	window.open(urlpass,"Edit","status=1,width=1024,height=500,scrollbars=1");

}

function deleteLog(id,type){
	var r = confirm("Are you sure you want to Delete this entry?");
		if(r == true){
			var page = '../performance/deleteLog.php?&type='+type+'&id='+id;
		
			var cb = function(result){ printLog(); };

			callPhpPage(page,cb);
		}
}

function editReport(id){
	var page = "editReport.php?id="+id;
	
	window.open(page,"Edit","status=1,width=1024,height=600,scrollbars=1")
	//var cb = function(result){ printLog();}
			
	//callPhpPage(page,cb);
}
</script>
<div>
<h1>Supervisor Report Log</h1>
<table>
	<tr>
		<th>Employee</th>
		<th>Start Date</th>
		<th>End Date</th>
		<th>Outages</th>
		<th>Shift Problems</th>
		<th>Misc</th>
	</tr>
	<tr>
		<td><?php printEmployees(); ?></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="startDate" id="startDate" size=10 class='datepicker' /></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="endDate" id="endDate" size=10 class='datepicker' /></td>
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
