<?php //index.php
require ('../includes/includeme.php');
//This is the index for the wages app. It shows the raise history for employees and allows the submitting of new raises.

if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af"))/*wages resource*/{
//Sets the post data sent from employeeList as if the list was already clicked
if(isset($_POST['employeeNetId'])){
	$employeeNetId = $_POST['employeeNetId'];
} else if( isset($_GET['employee'])){
	$employeeNetId = $_GET['employee'];
}else{
	$employeeNetId = $netID;
}

?>
	<style type='text/css'>
	table{
	margin:auto;
	}
	.employeeList{
	float:left;
	width:30%;
	position: relative;
	height: 633px;
	margin: 10px 10px 10px 10px;
	}
	.info{
	margin:auto;	
	//width:65%;
	}
	.log{ 
	margin:auto;
	text-align:center; 
	font-size:120%; 
	//width:65%;
	}
	.employeeImg{
	display:block;
	margin-left:auto;
	margin-right:auto;
	width:150px;
	height:150px;
	}
	.header{
	font-size:200%;
	}
	</style>
	


<!--Create a text box when "Other" is selected from the dropdown menu-->
<script language="javascript" type="text/javascript">
var curEmployee = '<?php echo $employeeNetId; ?>';

window.onload = printRaiseLog(curEmployee);

//This is what switches the reasons dropdown to a text field
function toggleField(val)
{
	var select = document.getElementById('otherComment');
	var primary = document.getElementById('comments').value;
	if(val == 'Other'){
		select.style.display = 'block'; 
		select.style.width='190px';
		primary.style.display = 'inline';
		document.getElementById("other").innerHTML="Enter Reason:";
		document.getElementById("other").style.position="relative"
		document.getElementById("other").style.top="25px"
		document.getElementById("comments").style.border="20"
	}
	else{
		var page ="raiseAjax/raiseAjaxReason.php?comments="+primary;
	    var cb = function(raisematch){document.getElementById("raise").value = raisematch; };
		callPhpPage(page,cb);
		select.style.display = 'none';
		primary.style.display = 'inline';
		document.getElementById("comments").style.border="thick"
		document.getElementById("other").innerHTML="Reason:";
		document.getElementById("other").style.position="static"

}
}

//This prints the raise log for the passed in employee
function printRaiseLog(employee){
	var page = "wagesAjax/printRaises.php?employee="+employee;
	
	var cb = function(result){ document.getElementById("results").innerHTML = result; };

	callPhpPage(page,cb);
}

//This is the AJAX call that actually submits the raise.
function submitRaise(){
	var employeeName = '<?php echo nameByNetId($employeeNetId); ?>';
	var raise = document.getElementById("raise").value;
	var date = document.getElementById("date").value;
	var reason = document.getElementById("comments").value;
	if(reason == "Other"){
		reason = document.getElementById("otherComment").value;
	}
	var r = confirm("Are you sure you want to submit a raise for "+employeeName+" with amount "+raise+"?");
	if(r){
		var page = "wagesAjax/insertRaise.php?employee="+curEmployee+"&date="+date+"&reason="+reason+"&raise="+raise;
		
		var cb = function(result){ alert(result); printRaiseLog(curEmployee); };

		callPhpPage(page,cb);
	}
}

</script>

<?php 

//creates the popup confirmation box
echo '<script> 


function show_confirm()
{
	var r=confirm("Give '.$employeeNetId.' a raise of $" + document.getElementById(\'raise\').value + " ?");
	if (r==true)
		{
		alert("Raise is now Pending");
		return true;
		}
	else
		{
		alert("Request Cancelled");
		return false;
		}
}
</script>';
?>
<!-----------------------------------------------Start Search---------------------------------------------------------->
<div id="employeeList" class="employeeList">   
<?php 
// Here employeeList is called with "false". This sets it so that it will only show employees from the same area
// instead of all employees with access to the area
employeeList(false);
?>
</div>
<!-----------------------------------------------End Search------------------------------------------------------------>

<!-------------------------------------------Start Employee Info------------------------------------------------------->
<!----------------This section contains the table which holds the information for the selected employee---------------->
<div id="info" class="info" align="center">

<br><br>
<form name="wageForm" method="post" id='raiseForm'>

	<table style="text-align:center; font-size:120%; border-collapse:collapse;" border="1">	

		<tr>
		<td colspan=4><img class='employeeImg' src="<?php echo getenv("BYU_PI_PHOTO"); ?>?n=<?php if (isset($_POST['employeeNetId'])) echo $_POST['employeeNetId']; else echo $netID; ?>" alt="Picture Not Found" /> </td>
		</tr>
		
		<tr>
		<td>Name:</td>
		<td> 
			<?php 
			//pulls the full name of the selected employee
			if (isset($_POST['employeeNetId'])) $employeeNetId = $_POST['employeeNetId']; 
			else $employeeNetId = $netID;
				
			echo nameByNetId($employeeNetId);
			
			?> 
		</td>
		
		<td>Wage:</td>
		<td>
			<?php 
			global $area;
			//pulls the current wage from the database for the selected employee
			try {
				$wageQuery = $db->prepare("SELECT wage FROM employeeWages WHERE netID=:netId");
				$wageQuery->execute(array(':netId' => $employeeNetId));
				$areaQuery = $db->prepare("SELECT area FROM `employee` WHERE netId = :netId");
				$areaQuery->execute(array(':netId' => $employeeNetId));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$defaultArea = $areaQuery->fetch()->area;
			$result = $wageQuery->fetch(PDO::FETCH_ASSOC);
			if($defaultArea == $area)
				echo "$".$result['wage'];
			?> 
		</td> 
		</tr>
	
		<tr>
		<td>Date:</td>
		<!----------------If left blank the calendar will default to the current time stamp---------------->
		<td><input type="text" id='date' name="date" placeholder="MM-DD-YYYY" size="8"><?php calendar('wageForm','date') ?></td>
		
		<td>Raise: </td>
		<td> $
		<input type="text"  name="raise" id="raise" value=""  size="3">
		</td>
		</tr>


		<tr>
		<td id="other">Reason:</td>
		
		<td colspan=2>
		<select style='width:195px' name="comments" id="comments" onClick="toggleField(this.value);">
			
		<?php
			//Pulls the Description field options from employeeRaiseReasons
			$reasonFill = "";
			try {
				$reasonQuery = $db->prepare("SELECT reason FROM employeeRaiseReasons WHERE area=:area");
				$reasonQuery->execute(array(':area' => $area));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$reasonFill .= "<option value=''>Select Raise Reason</option>";
			while ($selReason = $reasonQuery->fetch(PDO::FETCH_ASSOC)) {
				$reasonFill .= "<option ' value='".$selReason['reason']."'>".$selReason['reason']."</option>";
			}
			$reasonFill .= "<option value='Other'>Other</option>";
			echo $reasonFill;
		?>
				
		</select>
		
		<input type="text" name="otherComment" id="otherComment" style="display: none;">
		</td><td><input type='button' name='editReasons' id='editReasons' value="Edit Reasons" onclick='window.location.href="editRaiseReasons.php"' /></td>
		</tr>				

	</table>
	<br>
	<input type='button' name='submit' id='submit' value='Submit' onclick='submitRaise();' />
	<br>
	<br>
	
	<input type='hidden' name='netID' value="<?php echo $employeeNetId; ?>">
</form> 		
</div>
<!----------------------------------------------End Employee Info--------------------------------------------------->

<!-------------------------------------------------Start Log-------------------------------------------------------->
<!-------------------Table holds the info for the raiselog. Previous raises pulled from the database------------------->
<div id="log" class="log">
	<header>Raise History for <?php echo nameByNetId($employeeNetId) ?></header>
	<div id='results'>
	
	</div>
</div>


<?php
} else {
	echo "You do not have permissions to view this application. If you feel this is in error contact your supervisor.";
}
require ('../includes/includeAtEnd.php');
?>
