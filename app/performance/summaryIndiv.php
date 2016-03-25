<?php 
//summaryIndiv.php
//The summary performance report for an individual
require ('../includes/includeme.php');
$previousEmployeeSel = 0;
$prevSelEmployee = '';
$prevSelStart = '';
$prevSelEnd = '';

$admin = can("read", "86755385-4a09-45ce-81b9-049b660210df"); //performanceSummary resource
$month = date('F', strtotime("-1month"));

if (isset($_GET['employee']))
{
	$employee = $_GET['employee'];
}
else
{
	$employee = $netID;
}
function printTerminatedEmployees()
{
	global $employee;
	global $admin;
	global $area;
	global $db;
	$text ="<select id='terminatedEmployees' name='terminatedEmployees'><option value=''>Select Terminated Employee</option>";
	try {
		$terminationQuery = $db->prepare("SELECT * FROM `employeeTerminationDetails` LEFT JOIN `employee` ON `employeeTerminationDetails`.`netID`=`employee`.`netID` WHERE `employeeTerminationDetails`.`area`=:area ORDER BY `employee`.`lastName` ASC");
		$terminationQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($row = $terminationQuery->fetch(PDO::FETCH_ASSOC)) {
		$text.="<option value='" . $row['netID'] . "'>" . $row['lastName'] . ", " . $row['firstName'] . "</option>";
	}
	$text.= "</select>";
	return $text;
}

function printEmployees()
{
	global $employee;
	global $admin;
	global $area;
	if ($admin)
	{
		echo "<select id='employees' name='employees' onchange>
				<option value=''>Select Employee</option>";
		employeeFillSelected($employee, $area);
		echo "</select>";
	}
	else
	{
		echo nameByNetId($employee);
	}
}

function printEmployees2()
{
	global $employee;
	global $admin;
	global $area;
	global $db;
	$text="";
	if ($admin)
	{
		$text.= "<select id='employees' name='employees' onchange><option value=''>Select Employee</option>";
		try {
			$employeeQuery = $db->prepare("SELECT * FROM `employee` WHERE `active`=1 AND `area`=:area ORDER BY `lastName`");
			$employeeQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row=$employeeQuery->fetch(PDO::FETCH_ASSOC))
		{
			if($employee==$row['netID'])
			{
				$text.="<option value='$row[netID]' selected> $row[lastName], $row[firstName]</option>";
			}
			else 
			{	
				$text.="<option value='$row[netID]'> $row[lastName], $row[firstName]</option>";
			}
		}
		$employees = getEmployeesNotDefaultedToCurArea();
		
		foreach($employees as $curEmployee)
		{
			try {
				$employeeQuery = $db->prepare("SELECT firstName, lastName, netID FROM employee WHERE netID=:netId");
				$employeeQuery->execute(array(':netId' => $curEmployee));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$info = $employeeQuery->fetch(PDO::FETCH_ASSOC);
			if ($employee == $curEmployee) 
			{
				$text .= "<option value='".$info['netID']."' selected>*".$info['lastName'].", ".$info['firstName']."</option>";
			}
			else 
			{
				$text .= "<option value='".$info['netID']."'>*".$info['lastName'].", ".$info['firstName']."</option>";
			}
		}
		
		$text.= "</select>";
	}
	else
	{
		$text.= nameByNetId($employee);
	}
	return $text;
}
function checkReviewStatus($netID, $month)
{
	global $area, $db;
	$year = date('Y-01-01', strtotime("today"));
	try {
		$reviewedQuery = $db->prepare("SELECT * FROM reportPerformanceReviewed WHERE netID = :netId AND month = :month AND area = :area AND date > :year");
		$reviewedQuery->execute(array(':netId' => $netID, ':month' => $month, ':area' => $area, ':year' => $year));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if ($row = $reviewedQuery->fetch())
	{
		return false;
	}
	return true;
}
?>
<script type='text/javascript'>
						window.onload=function(){
		$("#startDate").datepicker({dateFormat: "yy-mm-dd"});
		$("#endDate").datepicker({dateFormat: "yy-mm-dd"});
		printLog();
		terminatedEvent();
	}
	function terminatedEvent()
	{
		if($("#terminatedCheckBox").length>0)
		{
			$("#terminatedCheckBox").click(function()
			{
				if($("#terminatedCheckBox").prop("checked")==true)
				{
					var html = "<?php echo printTerminatedEmployees(); ?>";
					$("#startDate").attr("disabled","disabled");
					$("#endDate").attr("disabled","disabled");
					$("#employees").replaceWith(html);
				}
				else
				{
					var html = "<?php echo printEmployees2(); ?>";
					console.log(html);
					$("#startDate").removeAttr("disabled");
					$("#endDate").removeAttr("disabled");
					$("#terminatedEmployees").replaceWith(html);
					printLog();
				}
			});	
		}
	}
	function printLog(){
		var terminated = 'false';
		if ($("#terminatedCheckBox").prop("checked") == true)
		{
			terminated = 'true';
			<?php
		if ($admin)
		{
			echo 'var employee = document.getElementById("terminatedEmployees").value;';
		}
		else
		{
			echo 'var employee = "' . $netID . '";';
		}
	?>
			
		}
		else
		{
			<?php
		if ($admin)
		{
			echo 'var employee = document.getElementById("employees").value;';
		}
		else
		{
			echo 'var employee = "' . $netID . '";';
		}
	?>
		}
		
		var type = 'admin';
		var start = document.getElementById("startDate").value;
		var end = document.getElementById("endDate").value;
		
		//check for previous selected user
			<?php
			//check for previous selected employee.
			if (isset($_GET['prevSelectedEmp']))
			{

				$prevSelEmployee = $_GET['prevSelectedEmp'];
				$prevSelStart = $_GET['prevStart'];
				$prevSelEnd = $_GET['prevEnd'];
				echo "var page = 'printLog.php?employee=" . $prevSelEmployee . "&start=" . $prevSelStart . "&end=" . $prevSelEnd . "&type='+type;";

			}
			else
			{

				echo "var page = 'printLog.php?employee='+employee+'&start='+start+'&end='+end+'&type='+type+'&terminated='+terminated;";
			}
			?>
																							var page = 'printLog.php?employee='+employee+'&start='+start+'&end='+end+'&type='+type+'&terminated='+terminated;
			//alert(page);
			var cb = function(result){ document.getElementById("searchResults").innerHTML = result; };

			callPhpPage(page,cb);
	}

	function editLog(id,type){
		var urlpass = "editLog.php?id="+id+"&type="+type;		

		window.open(urlpass,"Edit","status=1,width=1024,height=500,scrollbars=1");

	}
	
	function editCallLog(type, id, callNumber){
	//Add functionality to update this page once the user is finished with the popup window
	<?php
	if ($admin)
	{
		echo 'var originalEmp = document.getElementById("employees").value;';
	}
	else
	{
		echo 'var originalEmp = "' . $netID . '";';
	}
	?>
		var prevSelectedStart = document.getElementById("startDate").value;
		var prevSelectedEnd = document.getElementById("endDate").value;

		var urlpass = "editLog.php?id=" + id + "&type=" + type + "&call=" + callNumber + "&prevSelectedEmp=" + originalEmp + "&prevStartDate=" + prevSelectedStart + "&prevEndDate=" + prevSelectedEnd;

		window.open(urlpass, "Edit", "status=1,width=1024,height=500,scrollbars=1");

		}

		function deleteLog(id, type)
		{
			//var type = 'Absence';
			var page = 'deleteLog.php?type=' + type + '&id=' + id;

			var cb = function(result)
			{
				printLog();
			};

			callPhpPage(page, cb);
		}

		function newwindow(urlpass)
		{
			window.open(urlpass, "Call Summary", "status=1,width=1024,height=600,scrollbars=1");
		}

		function updateReviewed(month)
		{
			var page = 'updatedReview.php?month=' + month;

			var cb = function(result)
			{
			};

			callPhpPage(page, cb);

		}

</script>

<style type="text/css">
      .centerText
      {
            text-align: center;
      }
</style>
<div align='center'>
<h1>Employee Performance Summary</h1>
Use the date fields below to help narrow your search.
By default the page will display all events.
<form name="indiv" method="post" id='indiv'>
<table>
	<tr>
		<th>Employee</th>
		<th>Start Date</th>
		<th>End Date</th>
		<?php if ($admin) echo"<th>Terminated?</th>" ?>
	</tr>
	<tr>
		<td><?php printEmployees(); ?></td>
		<td><input type="text" value="<?php echo date('Y-m-d', strtotime('-1 month')); ?>" name="startDate" id="startDate" size=10 /></td>
		<td><input type="text" value="<?php echo date('Y-m-d'); ?>" name="endDate" id="endDate" size=10  /></td>
		<?php
			if ($admin)
				echo '</php><td><input type="checkbox" id="terminatedCheckBox"></td>';
 ?>
	</tr>
</table>
<input type='button' id='submit' value='Submit' onclick='printLog()' />
</form>
</div>
<br/>
<br/>
<div align='center' id='searchResults'>

</div>
<?php if(checkReviewStatus($netID,$month) && $area != 2){ ?>
<div align='center'>
<input type='button' name='reviewed' id='reviewed' onclick="updateReviewed('<?php echo $month; ?>')" value="I have Reviewed this month's Summary" />
</div>
<?php } ?>
<?php
require ('../includes/includeAtEnd.php');
?>
