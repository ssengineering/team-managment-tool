<?php
require('../includes/includeme.php');

$permission = can("use", "8c2901f9-27f6-45a1-b0e9-bbe53a6af189");//silentMonitor resource

if(!$permission)
{
	echo "<h2>You do not have permission to view this page.</h2>";

	require('../includes/includeAtEnd.php');
	
	return;
}

// This php function gets the information of that employee that is selected from the list.
	if (isset($_POST['employeeNetId'])) $employeeNetId = $_POST['employeeNetId'];
	elseif(isset($_POST['employeeReviewedNetID'])) $employeeNetId = $_POST['employeeReviewedNetID'];
	else $employeeNetId = $netID;
	try {
		$employeeQuery = $db->prepare("SELECT * FROM employee WHERE `netID` = :netId LIMIT 1");
		$employeeQuery->execute(array(':netId' => $employeeNetId));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$employeeReviewed = $employeeQuery->fetch(PDO::FETCH_ASSOC);
	
	//textual representation of the semester name
	$semesterName = getSemesterName(date("Y-m-d"));
	$curPeriod = getSemester(date("Y-m-d"));
	
	try {
		$periodQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE semester=:period AND `area`=:area");
		$periodQuery->execute(array(':period' => $curPeriod, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$period = $periodQuery->fetch(PDO::FETCH_ASSOC);
	$start = $period['startDate'];
	$end = $period['endDate'];
?>
<link rel="stylesheet" type="text/css" href="statsCSS.css" />
<style type="text/css">
.tableCenter
{ 
margin-left: auto;
margin-right: auto;
width:0px;
}
</style>
<h1 class='center'>Silent Monitor Statistics</h1><br />
<h4 class='center'>DIRECTIONS: Choose an employee to see his/her stats. </h4>

<script type="text/javascript">
window.onload = function()
{
	// Ajax to call stats
	var individualArrayStats ='';
	var totalStatsArray='';
	var empNetID = "<?php echo $employeeNetId; ?>";
	var area = "<?php echo $area; ?>";
	var startDate = "<?php echo $start;?>";
	var endDate =  "<?php echo $end;?>";

	var startDateRange = $('#ticketDate1').datepicker({dateFormat:"yy-mm-dd", onSelect: function() { getStatsBasedOnDateRange(); }});
	var endDateRange = $('#ticketDate2').datepicker({dateFormat:"yy-mm-dd", onSelect: function() { getStatsBasedOnDateRange(); } });

	function getStatsBasedOnDateRange()
	{
		
		if($('#ticketDate1').val() !='' && $('#ticketDate2').val() != '')
		{
			startDate = $('#ticketDate1').val();
			endDate = $('#ticketDate2').val();			
			getIndividualStats();
			getTotalStats();
		}
		
	}	

	function getIndividualStats()
	{
		//Get individual statistics
		var getIndividualStats = $.ajax({
						  url: "logAjax/individualStats.php",
						  type: "GET",
						  data: {area : area, startDate:startDate, endDate:endDate, empNetID:empNetID},
						  async: true,
						  cache: false,
						  success: function(data){
						      individualArrayStats = jQuery.parseJSON(data);
						      var semesterName = individualArrayStats.semesterName;
						      displaySemesterName(semesterName);
						      individualStatsDynamic(individualArrayStats);
						  }
						});

						getIndividualStats.done(function(msg) {
						//  alert("Success");
						});

						getIndividualStats.fail(function(jqXHR, textStatus) {
						  alert( "Request failed: " + textStatus );
						});


	}

	function getTotalStats()
	{
		//Get total statistics
		var getTotalStats = $.ajax({
						  url: "logAjax/allStats.php",
						  type: "GET",
						  data: {area : area, startDate:startDate, endDate:endDate, empNetID:empNetID},
						  async: true,
						  cache: false,
						  success: function(data){
						      totalStatsArray = jQuery.parseJSON(data);
						      totalEmpStats(totalStatsArray);
						  }
						});

						getTotalStats.done(function(msg) {
						//  alert("Success");
						});

						getTotalStats.fail(function(jqXHR, textStatus) {
						  alert( "Request failed: " + textStatus );
						});

	
	}

	function displaySemesterName(semesterName)
	{
		$("#semesterName").html(semesterName);
	}

	displaySemesterName();
	getIndividualStats();
	getTotalStats();
}

function individualStatsDynamic(individualArrayStats)
{
	var one =parseInt(individualArrayStats.one);
	var two = parseInt(individualArrayStats.two);
	var three= parseInt(individualArrayStats.three);
	var four= parseInt(individualArrayStats.four);
	var five = parseInt(individualArrayStats.five);
	var six= parseInt(individualArrayStats.six);
	var seven= parseInt(individualArrayStats.seven);
	var eight = parseInt(individualArrayStats.eight);
	var nine= parseInt(individualArrayStats.nine);
	var total= parseInt(individualArrayStats.total);
	
	
	$( "#one" ).progressbar({ value: one})
						 .children('.ui-progressbar-value').html(one + '%').css("display", "block");

	$( "#two" ).progressbar({ value: two })
						   .children('.ui-progressbar-value').html(two + '%').css("display", "block");

    $( "#three" ).progressbar({ value: three})
						  .children('.ui-progressbar-value').html(three + '%').css("display", "block");

	$( "#four" ).progressbar({ value: four })
							.children('.ui-progressbar-value').html(four + '%').css("display", "block");

	$( "#five" ).progressbar({value: five })
						.children('.ui-progressbar-value').html(five + '%').css("display", "block");

	$( "#six" ).progressbar({ value: six})
						.children('.ui-progressbar-value').html(six + '%').css("display", "block");

	$( "#seven" ).progressbar({ value: seven})
						 .children('.ui-progressbar-value').html(seven + '%').css("display", "block");

	$( "#eight" ).progressbar({ value: eight})
						.children('.ui-progressbar-value').html(eight + '%').css("display", "block");

	$( "#nine" ).progressbar({ value: nine})
					   .children('.ui-progressbar-value').html(nine + '%').css("display", "block");

	$( "#total" ).progressbar({ value: total})
					   .children('.ui-progressbar-value').html(total + '%').css("display", "block");
}	

function totalEmpStats(totalStatsArray)
{
	var one =parseInt( totalStatsArray.one);
	var two = parseInt( totalStatsArray.two);
	var three= parseInt( totalStatsArray.three);
	var four= parseInt( totalStatsArray.four);
	var five = parseInt( totalStatsArray.five);
	var six= parseInt( totalStatsArray.six);
	var seven= parseInt( totalStatsArray.seven);
	var eight = parseInt( totalStatsArray.eight);
	var nine= parseInt( totalStatsArray.nine);
	var total= parseInt( totalStatsArray.total);
	
	$( "#totalOne" ).progressbar({ value: one})
						 .children('.ui-progressbar-value').html(one + '%').css("display", "block");

	$( "#totalTwo" ).progressbar({ value: two })
						   .children('.ui-progressbar-value').html(two + '%').css("display", "block");

    $( "#totalThree" ).progressbar({ value: three})
						  .children('.ui-progressbar-value').html(three + '%').css("display", "block");

	$( "#totalFour" ).progressbar({ value: four })
							.children('.ui-progressbar-value').html(four + '%').css("display", "block");

	$( "#totalFive" ).progressbar({value: five })
						.children('.ui-progressbar-value').html(five + '%').css("display", "block");

	$( "#totalSix" ).progressbar({ value: six})
						.children('.ui-progressbar-value').html(six + '%').css("display", "block");

	$( "#totalSeven" ).progressbar({ value: seven})
						 .children('.ui-progressbar-value').html(seven + '%').css("display", "block");

	$( "#totalEight" ).progressbar({ value: eight})
						.children('.ui-progressbar-value').html(eight + '%').css("display", "block");

	$( "#totalNine" ).progressbar({ value: nine})
					   .children('.ui-progressbar-value').html(nine + '%').css("display", "block");

	$( "#totalTotal" ).progressbar({ value: total})
					   .children('.ui-progressbar-value').html(total + '%').css("display", "block");
}

</script>


<div id = "searchAndEmployeeInfo">
			<div id = "empSearch">
				<!-------------------Funtion to perfom the search of employees. Also it displays the list dynamically-------->	
					<?php employeeList(); ?>
				<!-------------------------------------End of "menuPos" div----------------------------------------------------->
			</div>
			<div id = "contactInfo"> <h3>Employee Information:</h3>
				    <div id="picturePos"> 
					<?php //This block of PHP code gets the employees picture. It checks the server first and then goes through the routeY webservice
					try {
						$imageQuery = $db->prepare("SELECT * FROM images WHERE netID = :netId");
						$imageQuery->execute(array(':netId' => $employeeNetId));
					} catch(PDOException $e) {
						exit("error in query");
					}
					if (file_exists("../imageUpload/images/${employeeNetId}.jpg")){
						echo "<img src=\"../imageUpload/images/${employeeNetId}.jpg\"  alt='No image' height='145' width='145' />";
					}
					else if(!($row = $imageQuery->fetch())) {
						echo "<img src='".getenv("BYU_PI_PHOTO")."?n=".$employeeNetId."' alt='Employee Picture' height='145' width='145' />";
					}
					?>
					</div> 
					<div id ="firstNamePos"><h3> First Name: </h3></div> <div id="fNameOutput"><h3><center><?php echo $employeeReviewed['firstName']; ?></center></h3></div>
					<div id ="lastNamePos"><h3>Last Name: </h3>   </div> <div id="lNameOutput"><h3><center><?php echo $employeeReviewed['lastName']; ?></center></h3>   </div>
					<div id ="netIdPos"><h3>NetId:</h3>   </div>  <div id="netIdOutput"> <h3><center><?php echo $employeeReviewed['netID']; ?></center></h3>  </div>
			</div>
</div>
<br /><br />
<div class="center">
	<h2 id="semesterName"></h2> 
	
	<TABLE BORDER="1" id='basicSearch' class="tableCenter"> 
	<TR>
		<TH>Start Date</TH><TH>End Date</TH>	
	</TR>
	<TR>
		<TD><INPUT TYPE="TEXT" NAME="ticketDate1" id="ticketDate1" SIZE="7" PLACEHOLDER="YYYY-MM-DD" value="" ></TD>
		<TD><INPUT TYPE="TEXT" NAME="ticketDate2" id="ticketDate2" SIZE="7" PLACEHOLDER="YYYY-MM-DD" value="" ></TD>
	</TR>
	</TABLE>	
</div><br />
<div id="empIndividualStats" class="individualStats">
<b> Individual Statistics: </b> 
<table>
	<tr>
		<th>Category </th><th>Percentage</th>
	</tr>
	<tr>
		<td>Verified Contact Info:</td>
		<td><div id="one" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td>Used Appropriate Words and Phrases:</td>
		<td><div id="two" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td>Customer Service:</td>
		<td><div id="three" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td>Professional:</td>
		<td><div id="four" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td>Confident:</td>
		<td><div id="five" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td>No Awkward Silences:</td>
		<td><div id="six" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td>Hold Etiquette:</td>
		<td><div id="seven" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td>Gave Correct Information:</td>
		<td><div id="eight" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td>Ended the call appropriately:</td>
		<td><div id="nine" style="height:20px;"></div> </td>
	</tr>
	<tr>
		<td><b>Total:</b></td>
		<td><div id="total" style="height:20px;"></div> </td>
	</tr>
</table>
</div>
<div id="empTotalStats" style="width:350px;float:right">
<b> All Statistics: </b>
	<table>
		<tr>
			<th>Category </th><th>Percentage</th>
		</tr>
		<tr>
			<td>Verified Contact Info:</td>
			<td><div id="totalOne" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td>Used Appropriate Words and Phrases:</td>
			<td><div id="totalTwo" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td>Customer Service:</td>
			<td><div id="totalThree" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td>Professional:</td>
			<td><div id="totalFour" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td>Confident:</td>
			<td><div id="totalFive" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td>No Awkward Silences:</td>
			<td><div id="totalSix" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td>Hold Etiquette:</td>
			<td><div id="totalSeven" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td>Gave Correct Information:</td>
			<td><div id="totalEight" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td>Ended the call appropriately:</td>
			<td><div id="totalNine" style="height:20px;"></div> </td>
		</tr>
		<tr>
			<td><b>Total:</b></td>
			<td><div id="totalTotal" style="height:20px;"></div> </td>
		</tr>
	</table>
</div>


<?php
require('../includes/includeAtEnd.php');
?>
