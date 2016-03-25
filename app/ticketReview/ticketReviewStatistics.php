<?php
require('../includes/includeme.php');
require('ticketReviewFunctions.php');

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
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE semester = :period AND `area` = :area");
		$semestersQuery->execute(array(':period' => $curPeriod, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$period = $semestersQuery->fetch(PDO::FETCH_ASSOC);
	$start = $period['startDate'];
	$end = $period['endDate'];

?>
<link rel="stylesheet" type="text/css" href="ticketStyle.css" />
<link rel="stylesheet" type="text/css" href="ticketReviewStats.css" />
<style type="text/css">
.tableCenter
{ 
margin-left: auto;
margin-right: auto;
width:0px;
}
</style>
<h1 class='center'>Ticket Review Statistics</h1><br />
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
	var requestor =parseInt(individualArrayStats.requestor);
	var contactInfo = parseInt(individualArrayStats.contactInfo);
	var serviceCat= parseInt(individualArrayStats.ssc);
	var ticketSource= parseInt(individualArrayStats.ticketSource);
	var priority = parseInt(individualArrayStats.priority);
	var kbOrSource= parseInt(individualArrayStats.kbOrSource);
	var workOrder= parseInt(individualArrayStats.workOrderNumber);
	var templates = parseInt(individualArrayStats.templates);
	var trouble= parseInt(individualArrayStats.troubleshooting);
	var closureCodes = parseInt(individualArrayStats.closureCodes);
	var pro = parseInt(individualArrayStats.professionalism);
	var indStatsAvg = parseInt(individualArrayStats.avgAllCategories); 
	var ticketsPerPeriod = parseInt(individualArrayStats.ticketsThisSemester) 
	
	$( "#requestorStat" ).progressbar({ value: requestor})
						 .children('.ui-progressbar-value').html(requestor + '%').css("display", "block");

	$( "#contactInfoStat" ).progressbar({ value: contactInfo })
						   .children('.ui-progressbar-value').html(contactInfo + '%').css("display", "block");

    	$( "#serviceCatStat" ).progressbar({ value: serviceCat})
						  .children('.ui-progressbar-value').html(serviceCat + '%').css("display", "block");

	$( "#ticketSourceStat" ).progressbar({ value: ticketSource })
							.children('.ui-progressbar-value').html(ticketSource + '%').css("display", "block");

	$( "#priorityStat" ).progressbar({value: priority })
						.children('.ui-progressbar-value').html(priority + '%').css("display", "block");

	$( "#kbSourceStat" ).progressbar({ value: kbOrSource})
						.children('.ui-progressbar-value').html(kbOrSource + '%').css("display", "block");

	$( "#workOrderStat" ).progressbar({ value: workOrder})
						 .children('.ui-progressbar-value').html(workOrder + '%').css("display", "block");

	$( "#templateStat" ).progressbar({ value: templates})
						.children('.ui-progressbar-value').html(templates + '%').css("display", "block");

	$( "#troubleStat" ).progressbar({ value: trouble})
					   .children('.ui-progressbar-value').html(trouble + '%').css("display", "block");
	
	$( "#closureCodesStat" ).progressbar({ value: closureCodes})
							.children('.ui-progressbar-value').html(closureCodes + '%').css("display", "block");
	
	$( "#proStat" ).progressbar({ value: pro})
				   .children('.ui-progressbar-value').html(pro + '%').css("display", "block");

	$( "#totalAvgInd").progressbar({ value: indStatsAvg})
				   .children('.ui-progressbar-value').html(indStatsAvg + '%').css({"display":'block',"background":'#B24926'});
	
	$( "#ticketsPerPeriodInd").html(ticketsPerPeriod);

}	

function totalEmpStats(totalStatsArray)
{
	var requestor =parseInt(totalStatsArray.requestor);
	var contactInfo = parseInt(totalStatsArray.contactInfo);
	var serviceCat= parseInt(totalStatsArray.ssc);
	var ticketSource= parseInt(totalStatsArray.ticketSource);
	var priority = parseInt(totalStatsArray.priority);
	var kbOrSource= parseInt(totalStatsArray.kbOrSource);
	var workOrder= parseInt(totalStatsArray.workOrderNumber);
	var templates = parseInt(totalStatsArray.templates);
	var trouble= parseInt(totalStatsArray.troubleshooting);
	var closureCodes = parseInt(totalStatsArray.closureCodes);
	var pro = parseInt(totalStatsArray.professionalism); 
	var totalStatsAvg = parseInt(totalStatsArray.avgAllCategories); 
	var ticketsPerPeriod = parseInt(totalStatsArray.ticketsThisSemester);
	
	$( "#totalRequestorStat" ).progressbar({ value: requestor})
						 .children('.ui-progressbar-value').html(requestor + '%').css("display", "block");

	$( "#totalContactInfoStat" ).progressbar({ value: contactInfo })
						   .children('.ui-progressbar-value').html(contactInfo + '%').css("display", "block");

    	$( "#totalServiceCatStat" ).progressbar({ value: serviceCat})
						  .children('.ui-progressbar-value').html(serviceCat + '%').css("display", "block");

	$( "#totalTicketSourceStat" ).progressbar({ value: ticketSource })
							.children('.ui-progressbar-value').html(ticketSource + '%').css("display", "block");

	$( "#totalPriorityStat" ).progressbar({value: priority })
						.children('.ui-progressbar-value').html(priority + '%').css("display", "block");

	$( "#totalkbSourceStat" ).progressbar({ value: kbOrSource})
						.children('.ui-progressbar-value').html(kbOrSource + '%').css("display", "block");

	$( "#totalWorkOrderStat" ).progressbar({ value: workOrder})
						 .children('.ui-progressbar-value').html(workOrder + '%').css("display", "block");

	$( "#totalTemplateStat" ).progressbar({ value: templates})
						.children('.ui-progressbar-value').html(templates + '%').css("display", "block");

	$( "#totalTroubleStat" ).progressbar({ value: trouble})
					   .children('.ui-progressbar-value').html(trouble + '%').css("display", "block");
	
	$( "#totalClosureCodesStat" ).progressbar({ value: closureCodes})
							.children('.ui-progressbar-value').html(closureCodes + '%').css("display", "block");
	
	$( "#totalProStat" ).progressbar({ value: pro})
				   .children('.ui-progressbar-value').html(pro + '%').css("display", "block");

	$( "#totalAvgAll").progressbar({ value: totalStatsAvg})
				   .children('.ui-progressbar-value').html(totalStatsAvg + '%').css({"display":'block',"background":'#B24926'});

	$( "#ticketsPerPeriodAll").html(ticketsPerPeriod);
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
						$pictureQuery = $db->prepare("SELECT * FROM images WHERE netID = :netId");
						$pictureQuery->execute(array(':netId' => $employeeNetId));
					} catch(PDOException $e) {
						exit("error in query");
					}
					if (file_exists("../imageUpload/images/${employeeNetId}.jpg")){
						echo "<img src=\"../imageUpload/images/${employeeNetId}.jpg\"  alt='No image' height='145' width='145' />";
					}
					else if(!($row = $pictureQuery->fetch(PDO::FETCH_ASSOC))) {
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
		<td>Requestor:</td>
		<td><div id="requestorStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Contact Info:</td>
		<td><div id="contactInfoStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Service Category:</td>
		<td><div id="serviceCatStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Ticket Source:</td>
		<td><div id="ticketSourceStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Priority:</td>
		<td><div id="priorityStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>KB Source:</td>
		<td><div id="kbSourceStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Work Order:</td>
		<td><div id="workOrderStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Template:</td>
		<td><div id="templateStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Trouble:</td>
		<td><div id="troubleStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Closure Codes:</td>
		<td><div id="closureCodesStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Professionalism:</td>
		<td><div id="proStat" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td style="font-weight:bold;">Total Avg:</td>
		<td><div id="totalAvgInd" style="height:18px;"></div> </td>
	</tr>
	<tr>
		<td>Tickets per Period</td>
		<td><div id="ticketsPerPeriodInd" style="height:18px;text-align:center;"></div> </td>
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
			<td>Requestor:</td>
			<td><div id="totalRequestorStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Contact Info:</td>
			<td><div id="totalContactInfoStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Service Category:</td>
			<td><div id="totalServiceCatStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Ticket Source:</td>
			<td><div id="totalTicketSourceStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Priority:</td>
			<td><div id="totalPriorityStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>KB Source:</td>
			<td><div id="totalkbSourceStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Work Order:</td>
			<td><div id="totalWorkOrderStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Template:</td>
			<td><div id="totalTemplateStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Trouble:</td>
			<td><div id="totalTroubleStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Closure Codes:</td>
			<td><div id="totalClosureCodesStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Professionalism:</td>
			<td><div id="totalProStat" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td style="font-weight:bold;">Total Avg:</td>
			<td><div id="totalAvgAll" style="height:18px;"></div> </td>
		</tr>
		<tr>
			<td>Tickets per Period</td>
			<td><div id="ticketsPerPeriodAll" style="height:18px;text-align:center;"></div> </td>
		</tr>
	</table>
</div>
<div style="width:90%;float:left;margin-left:70px;">
	NOTE: A category(ies) may show 100% if: a) No ticket reviews were made for the selected period on the selected employee.  In other words, the employee has not had reviews for
			the selected period.  b) The "Work Order" or "Template" fields may show 100% if in the reviews they have N/A in every review for the 
selected period. 
</div>

<?php
require('../includes/includeAtEnd.php');
?>
