<?php 
require('../includes/includeme.php');
?>

<?php
if(can("read", "86755385-4a09-45ce-81b9-049b660210df")){//performanceSummary resource
	//summaryAdmin.php		
//page layout
/******************************************
| Name | Missed Punches | Absences | Tardies | Commendable Performances | Policy Reminders | Quizzes | Meeting Requests | Performance Reviewed |


*******************************************/
?>
<script type='text/javascript'>
	function updateSummary(month,sortBy){
		var year = document.getElementById('year').value;
		var cb = function(result){
			document.getElementById('results').innerHTML = result;
			sorttable.makeSortable(document.getElementById('summary'));
		}
		
		//alert(month);
		callPhpPage("printSummaryAdmin.php?month="+month+"&year="+year+"&sortBy="+sortBy,cb);
			//ajax call to a print summary file that prints summary out.

	}

</script>

<h1 style='text-align:center'>Performance Summary</h1>
<h3 style='text-align:center'>Select the month to display the performance statistics for that month</h3>
<br/>
<div id='input' class='header' style='text-align:center; margin-bottom:20px;'>
	<select name='year' id='year' onchange="updateSummary($('#showMonth').val(), 'employee')" >
		<?php 
			$year = date("Y");
			for ($i = 0; $i < 5; $i++)
			{
				echo "<option value='".$year."'>".$year."</option>";
				$year--;
			}
		?>
	</select>
	<select name='showMonth' id='showMonth' onchange='updateSummary(this.value,"employee")'>
		<option>Select Month</option>
		<?php monthSelect(); ?>
	</select>
</div>
<div id='results'>

</div>
<?php 
} else {
	echo "<h1>You are not Authorized to View this page</h1>";
}
	
require('../includes/includeAtEnd.php');
?>
