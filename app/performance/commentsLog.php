<?php //commendableLog.php
require('../includes/includeme.php');

$admin = can("read", "86755385-4a09-45ce-81b9-049b660210df");//performanceSummary resource

function printEmployees(){
	global $netID;
	global $admin;
	if($admin){
		echo "<select id='employees' name='employees' onchange>";
		employeeFillCurrentArea();
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
			var type = 'comment';
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
</script>
<div align='center'>
<h1>Performance Comments</h1>
Use the date fields below to help narrow your search.
By default the page will display all events.<br/><br/>
<?php if(can("read", "86755385-4a09-45ce-81b9-049b660210df")/* performanceSummary resource */){ ?>
<a href='comments.php' target=_blank>Submit a new Comment</a>
<?php } ?>
<br/><br/>
<form name="comments" method="post" id='comments'>
<table>
	<tr>
		<th>Employee</th>
		<th>Start Date</th>
		<th>End Date</th>
	</tr>
	<tr>
		<td><?php printEmployees(); ?></td>
		<td><input type="text" value="<?php echo date('Y-m-d', strtotime('-3 week')); ?>" name="startDate" id="startDate" size=10 /></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="endDate" id="endDate" size=10 /></td>
	</tr>
</table>
<input type='button' id='submit' value='Submit' onclick='printLog()' />
</form>
</div>
<br/>
<br/>
<div align='center' id='searchResults'>

</div>
<?php require('../includes/includeAtEnd.php');
?>
