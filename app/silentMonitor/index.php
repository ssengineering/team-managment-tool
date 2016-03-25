<?php //index for silent monitor
require('../includes/includeme.php');
include('printSilentMonitor.php');


if (can("use", "8c2901f9-27f6-45a1-b0e9-bbe53a6af189"))//silentMonitor resource
{
$loadID = '';
$curEmployee = '';
$curDate = '';
$overallComments = '';
if(isset($_GET['id'])){
	$loadID = $_GET['id'];
	try {
		$silentMonitorQuery = $db->prepare("SELECT * FROM silentMonitor WHERE `index` = :id");
		$silentMonitorQuery->execute(array(':id' => $loadID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$cur = $silentMonitorQuery->fetch(PDO::FETCH_ASSOC);
	$curEmployee = $cur['netID'];
	$overallComments = $cur['overallComment'];
}

?>
<script language="JavaScript" src="/includes/libs/tcal.js"></script>
<link rel="stylesheet" type="text/css" href="/includes/libs/tcal.css" />
<style type="text/css">
table,td,th{
	white-space: pre-line;
	vertical-align: middle;
}
.invisibleTable{
	vertical-align:top;
	border:none;
	background: none repeat scroll 0 0;
}
th.invisibleTable{
	font-size:18px;
}
</style>
<script type="text/javascript">
	<?php if($loadID != ''){ echo 'var queryString = "?id='.$loadID.'"'; }else{ echo 'var queryString = ""'; } ?>;
	
	var submitted = false;
	
	var page = 'loadCalls.php' + queryString;
			
	var cb = function(result){ document.getElementById("callLog").innerHTML = result; f_tcalInit();};

	callPhpPage(page,cb);

	setTimeout("autosave()", 20000);
	
	window.onbeforeunload = leavePage;
	
	function autosave()
	{
		var totalCalls = $('.callForm').length;
		
		if(document.silentMonitor.employee.value != "")
		{
			
			if($('#autosaveID').val() == "0")
			{
				var page = 'loadCalls.php?autosave=1&employee=' + document.silentMonitor.employee.value;

				var cb = function(result)
				{
					$('#autosaveID').val(result);
					$('#dex').val(result);
				}

				callPhpPage(page,cb);
				
				setTimeout("autosave()", 1000);
			}
			else
			{
				for(var i = 1; i <= totalCalls; i++)
				{
					//if($('[name="comments[' + i + ']"]').val() != "")
					//{
						var tempDex = 'dex=' + $('#autosaveID').val();
						var tempEmployee = 'employee=' + document.silentMonitor.employee.value;
						var tempSaveMonitor = 'saveMonitor=1';
						var tempOverallComments = 'overallComments=' + encodeURIComponent(document.silentMonitor.overallComments.value);
						var tempComments = 'comments[' + i + ']=' + encodeURIComponent($('[name="comments[' + i + ']"]').val());		
						var tempCallStartDate = 'startDate[' + i + ']=' + $('[name="startDate[' + i + ']"]').val()
						var tempRating = 'rating[' + i + ']=' + $('[name="rating[' + i + ']"]').filter(":checked").val();
						var tempSelect = "";
						var tempCallNumber = 'callNumber=' + i;
			
						var numberOfCriteria = $('.dropDown' + i).length;
			
						for(var j = 1; j <= numberOfCriteria; j++)
						{
							tempSelect += "select[" + i + "][" + j + "]=" + $('[name="select[' + i + '][' + j + ']"]').val() + "&";
						}
			
						var formData = '?' + tempDex + '&' + tempEmployee + '&' + tempSaveMonitor + '&' + tempOverallComments + '&' + tempCallNumber + '&' + tempComments + '&' + tempCallStartDate + '&' + tempSelect + tempRating;
				
						var page = 'submitMonitor.php' + formData;

						var cb = function(result)
						{
							//document.getElementById("callLog").innerHTML = result;
						}

						callPhpPage(page,cb);
					//}
				}
				
				setTimeout("autosave()", 20000);
			}
		}
		else
		{
			alert("Please select an employee if you want autosave to function.");
			setTimeout("autosave()", 20000);
		}
	}
	
	function setDates(result,dateObj){
		for(var i = 1; i<=10; i++){
			document.getElementById('startDate['+i+']').value = result;
		}

	}

	function addCall(numberOfCalls)
	{
		for( var h = 0; h < numberOfCalls; h++)
		{
			var totalCalls = $('.callForm').length;
			var totalCriteria = $('.dropDown' + totalCalls).length;
			var newClone = $('#callForm' + totalCalls).clone().attr('id', 'callForm' + (totalCalls + 1));		

			newClone.find('.callLabel').attr('onClick','$(\"#'+(totalCalls + 1)+'\").slideToggle(\"medium\")');
			newClone.find('.callLabel').html('Call '+(totalCalls + 1)+'+');
			newClone.find('#' + totalCalls).attr('id', totalCalls + 1);
			newClone.find('.comment').attr('name', 'comments[' + (totalCalls + 1) + ']');
			newClone.find('.comment').val('');
			for(var i = 1; i <= totalCriteria; i++)
			{
				var oldName = document.getElementById('select' + totalCalls + '_' +  i).getAttribute('name');
				var newName = oldName.replace('[' + totalCalls + '][' + i + ']', '[' + (totalCalls + 1) + '][' + i + ']');
				newClone.find('#select' + totalCalls + '_' + i).attr('name', newName);
				newClone.find('#select' + totalCalls + '_' + i).attr('id', 'select' + (totalCalls + 1) +'_' + i);
			}
			newClone.find('.dropDown' + totalCalls).attr('class', 'dropDown' + (totalCalls + 1));
			newClone.find('.tcal').attr('name', 'startDate[' + (totalCalls + 1) + ']');
			newClone.find('.tcal').attr('id', 'startDate[' + (totalCalls + 1) + ']');
			newClone.find('.tcal').val('');
			newClone.find('label[for="rating'+totalCalls+'_1"]').attr('for', 'rating' + (totalCalls + 1) + '_1');
			newClone.find('label[for="rating'+totalCalls+'_2"]').attr('for', 'rating' + (totalCalls + 1) + '_2');
			newClone.find('label[for="rating'+totalCalls+'_3"]').attr('for', 'rating' + (totalCalls + 1) + '_3');
			newClone.find('label[for="rating'+totalCalls+'_4"]').attr('for', 'rating' + (totalCalls + 1) + '_4');
			newClone.find('label[for="rating'+totalCalls+'_5"]').attr('for', 'rating' + (totalCalls + 1) + '_5');
			newClone.find('#rating' + totalCalls + '_1').attr({name: 'rating[' + (totalCalls + 1) + ']', id: 'rating' + (totalCalls + 1) + '_1'});
			newClone.find('#rating' + totalCalls + '_2').attr({name: 'rating[' + (totalCalls + 1) + ']', id: 'rating' + (totalCalls + 1) + '_2'});
			newClone.find('#rating' + totalCalls + '_3').attr({name: 'rating[' + (totalCalls + 1) + ']', id: 'rating' + (totalCalls + 1) + '_3'});
			newClone.find('#rating' + totalCalls + '_4').attr({name: 'rating[' + (totalCalls + 1) + ']', id: 'rating' + (totalCalls + 1) + '_4'});
			newClone.find('#rating' + totalCalls + '_5').attr({name: 'rating[' + (totalCalls + 1) + ']', id: 'rating' + (totalCalls + 1) + '_5'});

			$(newClone).appendTo('#callLog');
		
			f_tcalInit();
		
			// enable the "delete call" button
			$('#deleteCallButton').removeAttr('disabled');
		}
	}

	function removeCall()
	{
		var totalCalls = $('.callForm').length;
		$('#callForm' + totalCalls).remove();
		// if only one element remains, disable the "remove" button
        if ((totalCalls-1) == 1)
             $('#deleteCallButton').attr('disabled','disabled');
	}
	
	function leavePage()
	{
		if(!submitted)
		{	
			return 'Please stay on this page to save your work, or continue without saving.';
		}
	}
	
	function validateForm()
	{
		var employee = document.forms["silentMonitor"]["employee"].value;
		
		if(employee == null || employee == "")
		{
			alert("Please select an employee for the Silent Monitor.");
			return false;
		}
		else
		{
			submitted = true;
		}
		
		<?php
		if(isset($_GET['id']))
		{
			echo "$('#autosaveDex').val(document.silentMonitor.autosaveID.value);";
		}
		?>
		
		return true;
	}
</script>
<div id='title' align='center'>
<h1>Silent Monitoring</h1>
Please indicate whether or not the employee performed the tasks correctly.<br/>
<b>Yes</b>: The task was performed correctly.<br/>
<b>Partial</b>: The task was performed partially correctly.<br/>
<b>No</b>: The task was not performed adequately.<br/>

</div>
	<input type='button' id='loadIncomplete' value="Load Incomplete Monitor" onclick="window.location.href='loadIncompleteMonitor.php'" />
	<input type='button' id='editCriteria' value="Edit Criteria" onClick="window.location.href='editCriteria.php'" />
	<form enctype="multipart/form-data" method="post" id='silentMonitor' name='silentMonitor' action='submitMonitor.php' onSubmit='return validateForm()'>
		<h3>Employee: 
		<select name='employee' id='employee'>
			<?php 
					if(isset($_GET['id'])){
						echo employeeFillSelected($curEmployee,$area);
					}else{
						echo employeeFillCurrentArea();
					}	
			 ?>
		</select><br/>
		<input type='text' id="startDate" name="startDate" style ='display:none;' />
		<?php 
		if(isset($_GET['id']))
		{
			echo "<input type='hidden' id='dex' name='dex' value='".$loadID."' />";
			echo "<input type='hidden' id='autosaveID' name='autosaveID' value='".$loadID."'>";
		}
		else
		{
			echo "<input type='hidden' id='dex' name='dex' value='0' />";
			echo "<input type='hidden' id='autosaveID' name='autosaveID' value='0'>";
		}
			
			//calendarCallback("startDate",'setDates');
		?>
	
	<br/>*Please Note, Today's date will be recorded automatically in the database as the submit date.<br/>
	</h3>
	<div name='callLog' id='callLog' >
	
	</div>
	<input type='button' id='addCallButton' value='Add Call' onClick='addCall(1)' />
	<input type='button' id='deleteCallButton' disabled="disabled" value='Delete Call' onClick='removeCall()' />
	<label for='massAdd'>Mass add calls: </label>
	<select name='massAdd' id='massAdd'>
		<option name='massAddOne' value='1'>1</option>
		<option name='massAddTwo' value='2'>2</option>
		<option name='massAddThree' value='3'>3</option>
		<option name='massAddFour' value='4'>4</option>
		<option name='massAddFive' value='5'>5</option>
		<option name='massAddSix' value='6'>6</option>
		<option name='massAddSeven' value='7'>7</option>
		<option name='massAddEight' value='8'>8</option>
		<option name='massAddNine' value='9' selected='selected'>9</option>
		<option name='massAddTen' value='10'>10</option>
	</select>
	<input type='button' id='massAddCalls' value='Load' onClick='addCall(document.getElementById("massAdd").value)' />
	<br />
	<br />
	<div>
	<table>
		<tr>
			<th>Overall Comments</th>
		</tr>
		<tr>
			<td><textarea cols='60' rows='4' name='overallComments' id='overallComments'><?php echo $overallComments; ?></textarea></td>
		</tr>
	</table>
	</div>
	Choose the MP3 audio file:
	<br>
		<input type="file" id="audioFile" name="audioFile" accept="audio/mp3"/>
		<br>
		<br>
	<input type='checkbox' id='saveMonitor' name='saveMonitor' value='1' />Save Current Monitor?
	<input type='submit' id='submit' value="Submit" />

	</form>
<?php 
}
else 
{
	echo "<h1 style='text-align: center;'>You are not authorized to view this site.<br /><br />If you believe this is an error please contact your manager and request permissions.<br /><br />If you are the manager please contact the OIT Network OPS Development Team to resolve the issue. <br /><br />Thank You!</h1>";
}
	require('../includes/includeAtEnd.php');
?>
