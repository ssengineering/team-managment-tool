<?php
require('../includes/includeme.php');

$employee = '';
$timeStamp = '';
$searchType = 0; // 0=search based on email link query. 1 = search based in date parameters. 
$semesterStartDate='';
$semesterEndDate='';
if(isset($_GET['employee']) || isset($_GET['timeSubmitted']))
{
	$employee = $_GET['employee'];
	$timeStamp = $_GET['timeSubmitted'];
	$timeStamp = rawurldecode($timeStamp);
	$searchType = 0;
}
else 
{	//textual representation of the semester name
	$semesterName = getSemesterName(date("Y-m-d"));
	$curPeriod = getSemester(date("Y-m-d"));
	
	try {
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE semester = :period AND `area` = :area");
		$semestersQuery->execute(array(':period' => $curPeriod, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$period = $semestersQuery->fetch(PDO::FETCH_ASSOC);
	$semesterStartDate = $period['startDate'];
	$semesterEndDate = $period['endDate'];
	$searchType = 1;
}
// Check for permissions if you are viewing someone else's ticket reviews. 
if($employee!='' && ($employee!=$netID))
{
	if(!can("access", "dc200fff-46a6-4744-92aa-76fd017049a5"))//ticketReview resource
	{
		echo "<h2>You are not authorized to view this page. Contact your supervisor if you believe this is in error.</h2>";
		require('../includes/includeAtEnd.php');
		return;
	}
}
?>

<style type="text/css">
.center
{
	margin:auto;
	width:100%;
	text-align:center;
}
.tableCenter
{ 
margin-left: auto;
margin-right: auto;
}
</style>

<script type="text/javascript">
var toggleSearchDisplay = 1;
var searchType = parseInt("<?php echo $searchType; ?>");

// Display Search
function showSearch()
{
	if(toggleSearchDisplay)
	{
		document.getElementById("basicSearch").style.display="table";
		toggleSearchDisplay = 0;
	}
	else
	{
		document.getElementById("basicSearch").style.display="none";
		toggleSearchDisplay = 1;
	}
}

window.onload = function()
{

	var initialStartDate = $('#ticketDate1').datepicker({dateFormat:"yy-mm-dd", onSelect: function() { submitSearch(); }});
	var initialEndDate = $('#ticketDate2').datepicker({dateFormat:"yy-mm-dd", onSelect: function() { submitSearch(); } });

	submitSearch(); 
}

function submitSearch()
{
	var page = '';
	
	//search based on email timestamp sent to user.
	var employee = "<?php echo $employee; ?>";
	var timeStamp = "<?php echo urlencode($timeStamp); ?>";
	//Search based on default semester start and end date
	var semesterStartDate = "<?php echo $semesterStartDate; ?>";
	var semesterEndDate = "<?php echo $semesterEndDate; ?>";
	//search based on user selecting specific dates.
	var employeeLoggedIn ="<?php echo $netID; ?>";
	var startDate = document.getElementById("ticketDate1").value;
	var endDate = document.getElementById("ticketDate2").value;
	
	// Specific email date stamp in the email
	if(searchType==0)	
	{
		document.getElementById("basicSearch").style.display="none";
		toggleSearchDisplay = 1;
		document.getElementById("ticketDate1").value = semesterStartDate;  // set yyyy-mm-dd date format in search boxes
		document.getElementById("ticketDate2").value = semesterEndDate;
		page = "logAjax/printLogIndividual.php?employee="+employee+"&timeStamp="+timeStamp;
		searchType = 2;
	}
	// Default dates for the semester	
	else if(searchType==1)  
	{
		$('#directions').html('<h4 class="center">DIRECTIONS: The following ticket reviews were submitted for the semester so far.<br >  If you have any questions talk to your supervisor. Change the date range to see other tickets.<h4>')
		document.getElementById("ticketDate1").value = semesterStartDate;  // set semester date in search boxes
		document.getElementById("ticketDate2").value = semesterEndDate;
		page = "logAjax/printLogIndividual.php?startDate="+semesterStartDate+"&endDate="+semesterEndDate+"&employee="+employeeLoggedIn;
		searchType = 2;	
	}
	//user selects specific dates.
	else if (searchType==2)
	{
		page = "logAjax/printLogIndividual.php?startDate="+startDate+"&endDate="+endDate+"&employee="+employeeLoggedIn;
	}
	var cb = function(result){ document.getElementById("results").innerHTML = result; }
	
	callPhpPage(page,cb);
}

</script>

<h1 class='center'>Individual Ticket Review</h1><br />
<div id='directions'> <h4 class='center'>DIRECTIONS: The following ticket reviews were submitted.<br />  If you have any questions talk to your supervisor. To see previous tickets click on 
<a href="#" onClick="showSearch();">" Search."</a><h4> </div> 

<TABLE BORDER="1" id='basicSearch' style="display:table;" class="tableCenter"> 
	<TR>
		<TH>Start Date</TH><TH>End Date</TH>	
	</TR>
	<TR>
		<TD><INPUT TYPE="TEXT" NAME="ticketDate1" id="ticketDate1" SIZE="7" PLACEHOLDER="YYYY-MM-DD" value="<?php echo date('Y-m-d',strtotime('first day of this month')); ?>" ></TD>
		<TD><INPUT TYPE="TEXT" NAME="ticketDate2" id="ticketDate2" SIZE="7" PLACEHOLDER="YYYY-MM-DD" value="" ></TD>
	</TR>
</TABLE>

<h4 class='center'>The following reviews were submitted for you: </h4>

<div id='results'>

</div>

<?php
require('../includes/includeAtEnd.php');
?>
