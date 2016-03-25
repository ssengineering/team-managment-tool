<?php
	require('../includes/includeme.php');
?>
<script>
	window.onload = function(){
		$("#dateStart").datepicker({dateFormat: "yy-mm-dd"});
		$("#dateEnd").datepicker({dateFormat: "yy-mm-dd"});
		$("input:button").button();
		$("input:submit").button();
		$.getScript("../includes/table2CSV.js");
		$("#rfcTable").dataTable(
				{
					"bJQueryUI": true,
					"bPaginate": false
				}									
				).fnDraw();
	}


function csvExport()
{
	csvData = $('#rfcTable').table2CSV({delivery:'value'});
	var $csvForm = $('<form target="download" method="post" action="exportCsv.php" style="display: none;"></form>');
	$('#content').append($csvForm);
	var $data = $('<textarea name="csvData"></textarea>');
	$data.html(csvData);
	$csvForm.html($data);
	$csvForm.submit();
	$csvForm.remove();
	//window.open('exportCsv.php?csvData='+encodeURIComponent(csvData));
}

</script>

<h2>Select a Start and End date</h2>
<!-------------------------------------------------search-------------------------------------------------------------------------------------->
<form name='search' method='post' style='float:left;'>
	<font size='3'>Start Date:</font>
	<input type='text' name='dateStart' id='dateStart' size='10' value="<?php echo date('Y-m-d') ?>" >
	
	<font size='3'>End Date:</font>
	<input type='text' name='dateEnd' id='dateEnd' size='10' value="<?php echo date('Y-m-d') ?>" >
	<input type='submit' name='search' value='Search' />
</form></br></br><hr />
<!-------------------------------------------<div> results------------------------------------------------------------------------------------->
<div id='results' name='results'><font size="3">
<?php	
	if(isset($_POST['search'])){		
		
		echo "<h2>Unapproved RFC's</h2>";
		echo "<table id='rfcTable' class='rfcTable' name='rfcTable' width='100%'><thead>";
		echo '<tr><th>Ticket/RFC #</th><th>Engineer</th><th>Start</th><th>End</th><th>Description</th><th>Impact</th></tr></thead><tbody>';
		try {
			$rfcQuery = $db->prepare("SELECT * FROM `unscheduledRFC` WHERE `startDate` >= :start and `startDate` <= :end");
			$rfcQuery->execute(array(':start' => $_POST['dateStart'], ':end' => $_POST['dateEnd']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row = $rfcQuery->fetch(PDO::FETCH_ASSOC))
		{
			echo '<tr><td>'.$row['ticketNumber'].'</td><td>'.$row['engineerName'].'</td><td>'.$row['startTime'].' '.$row['startDate'].'</td><td>'.$row['endTime'].' '.$row['endDate'].'</td><td>'.$row['description'].'</td><td>'.$row['impact'].'</td></tr>';
		}
		echo '</tbody></table>';
		echo "<br/></font><input id='exportBtn' value='Export to Spreadsheet' type='button' onclick='csvExport();' />";
	}
?>
</div>
<!--------------------------------------------------------------------------------------------------------------------------------------->

<?php 
require('../includes/includeAtEnd.php'); ?>
