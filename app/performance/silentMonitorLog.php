<?php //silentMonitorLog.php
require('../includes/includeme.php');

// Get current user's available areas
$employeeAreas = getAreas();
$tempArea = $area;
// Set the current area variable to be temporarily interpreted as the area the silent monitor was originally done in
// This is undone at the bottom of this page
if(isset($_GET['arg']) && in_array($_GET['arg'], $employeeAreas))
{
	$area = $_GET['arg'];
}

// Ensure user has permissions for the app in the current area.
$admin = can("read", "86755385-4a09-45ce-81b9-049b660210df");//performanceSummary resource

if(isset($_GET['smID']))
{
	$smID = $_GET['smID'];
	try {
		$silentMonitorQuery = $db->prepare("SELECT * FROM `silentMonitor` WHERE `index` = :id");
		$silentMonitorQuery->execute(array(':id' => $smID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$silentMonitor = $silentMonitorQuery->fetch(PDO::FETCH_ASSOC);
	
	$employee = $silentMonitor['netID'];
	$date = $silentMonitor['submitDate'];
	
	if($admin || ($netID == $employee))
	{
		echo '<script type="text/javascript">
				function printLogFromEmail()
				{
					var page = "printLog.php?employee='.$employee.'&start='.$date.'&end='.$date.'&type=silentMonitor&smID='.$smID.'";
					var cb = function(result){ document.getElementById("searchResults").innerHTML = result; };
					callPhpPage(page,cb);
				}
				window.onLoad(printLogFromEmail());
			</script>';
	}
	else
	{
		echo '<script type="text/javascript">
				alert("You do not have the correct permissions to view other people\'s call logs.\nYou may only view your own Silent Monitor logs.");
			</script>';
	}
}
else
{
	$employee = $netID;
}

function printEmployees($employee){
	global $netID;
	global $admin;
	global $area;
	if($admin){
		echo "<select id='employees' name='employees' onchange>";
		echo employeeFillSelected($employee,$area);
		echo "</select>";
	} else {
		echo nameByNetId($netID);
	}
}

?>
<script type='text/javascript'>
	window.onload = function(){
		$("#startDate").datepicker({dateFormat: "yy-mm-dd"});
		$("#endDate").datepicker({dateFormat: "yy-mm-dd"});
	}
	

	function printLog(){
		<?php if($admin){ 
			echo 'var employee = document.getElementById("employees").value;';
		  } else {
				echo 'var employee = "'.$netID.'";';
			}?>
			var type = 'silentMonitor';
			var start = document.getElementById("startDate").value;
			var end = document.getElementById("endDate").value;
			var page = 'printLog.php?employee='+employee+'&start='+start+'&end='+end+'&type='+type;
			
			var cb = function(result){ document.getElementById("searchResults").innerHTML = result; };

			callPhpPage(page,cb);
	}

	function editLog(id,type){
		var urlpass = "editLog.php?id="+id+"&type="+type;		

		window.open(urlpass,"Edit","status=1,width=1024,height=500,scrollbars=1");

	}

	function deleteLog(id,type){
		var r = confirm("Are you sure you want to Delete this entry?");
			if(r == true){
				var page = 'deleteLog.php?&type='+type+'&id='+id;
			
				var cb = function(result){ printLog(); };

				callPhpPage(page,cb);
			}
	}
	
	function deleteCallLog(type, id, call)
	{
		var answer = confirm("Are you sure you want to delete this entry?");
		
		if(answer == true)
		{
			var page = 'deleteCallLog.php?&type='+type+'&id='+id+'&call='+call;
			
			var cb = function(result){ printLog(); };
			
			callPhpPage(page, cb);
		}	
	}
	
	function editCallLog(type, id, call)
	{
		var urlpass = "editLog.php?type="+type+"&id="+id+"&call="+call;		

		window.open(urlpass,"Edit","status=1,width=1024,height=400,scrollbars=1");
	}
	
	function newwindow(urlpass) {
		window.open(urlpass,"Call Summary","status=1,width=900,height=450,scrollbars=1");
	}
</script>
<div align='center'>
<h1>Silent Monitors</h1>
Use the date fields below to help narrow your search.
By default the page will display all events.<br/><br/>
<br/><br/>
<form name="silentMonitor" method="post" id='silentMonitor'>
<table>
	<tr>
		<th>Employee</th>
		<th>Start Date</th>
		<th>End Date</th>
	</tr>
	<tr>
		<td><?php printEmployees($employee); ?></td>
		<td><input type="text" value="<?php echo date('Y-m-d', strtotime('-3 week')); ?>" name="startDate" id="startDate" size=10 /></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="endDate" id="endDate" size=10 /></td>
	</tr>
</table>
<input type='button' id='submit' value='Submit' onclick='printLog()' />
</form>
</div>
<br/>
<div align='center' id='searchResults'>

</div>
<?php
// Undoing any temporary area changes that may have been made (if none were made this just sets the area equal to the area again--e.g. sets 2=2)
$area = $tempArea;
require('../includes/includeAtEnd.php');
?>
