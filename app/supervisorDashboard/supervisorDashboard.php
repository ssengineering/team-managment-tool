<?php
	require('../includes/includeme.php');
	require('../routineTaskList/routineTaskTable.php');
	require_once $_SERVER['DOCUMENT_ROOT']."/includes/heimdall.php";

if(isset($_GET['date'])){
	$date = $_GET['date'];
}else{
	$date = date('Y-m-d');
}

$login = '';
if (isset($_SESSION['servNowPass']))
{
	$login = $_SESSION['servNowPass'];
}
if (isset($_POST['loginPass']))
{
	$_SESSION['servNowPass'] = urlencode(passEncrypt($_POST['loginPass']));
	unset($_POST['loginPass']);
	$login = $_SESSION['servNowPass'];
}
if ($login == '')
    {
        echo '<br /><br /><div style="width: 55%; margin-left: auto; margin-right: auto; text-align: center;" class="infoRequest"><h1>In order to use the dashboard you must re-authenticate.</h1><p style="margin-left:10%;">Please enter your password.</p><form style="margin-left:10%;" method="post"><input name="loginPass" id="login" type="password" autofocus="autofocus" /> <input type="submit" value="Authenticate" /></form></div>';
	}

else
{
	$tom = date('d',strtotime($date))+1;
	$tomorrow = date('Y-m-').$tom;
	$yest = date('d',strtotime($date))-1;
	$yesterday = date('Y-m-').$yest;
?>

<link rel="stylesheet" type="text/css" href="supDash.css" />

<script src="./index.js"></script>

<?php
	if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource*/{
?>
		<script src="./supOnly.js"></script>
<?php
	}
?>

<script type="text/javascript">
	var netid = "<?php echo $netID; ?>";
	var env = <?php echo $env; ?>;
	//This loads everything initially
window.onload=function(){
	loadJs();
	//This is for the unscheduled RFC popup
	$("#startDateRFC").datepicker({dateFormat:"yy-mm-dd"});	
	$("#endDateRFC").datepicker({dateFormat:"yy-mm-dd"});
	$('#startTimeRFC').timeEntry({useMouseWheel: true,
		timeSteps: [1,5,1]});
	$('#endTimeRFC').timeEntry({useMouseWheel: true,
		timeSteps: [1,5,1]});

	$('#rfcSearch').keyup(searchRFCs);
	function refreshSupDash ()
	{
		supNotes();
		loadList();
		displayRfcs();
		whosHere(hour, "whoIs");
		<?php if ($login != '') echo 'loadHiPri();'; ?>
	}
		
	refreshSupDash ();

	//This will reload parts of the page automatically
	setInterval('supNotes()',600000);	
	setInterval('loadList()',60000);
	setInterval('displayRfcs()', 480000);
	setInterval('whosHere(hour, "whoIs")',300000);
	<?php if ($login != '') echo "setInterval('loadHiPri()', 480000);"; ?>
	setInterval('reminderCheck()', 300000);

	// CATCH ALT+G (SHOW/HIDE GROUPS), ALT+N (LEAVE A NOTE), ALT+E (MAKE AN ENTRY), ALT+R (REFRESH SUPDASH), AND ALT+S (DO A KB SEARCH)
	$(window).keydown(function(event)
	{
		if(event.altKey && event.keyCode == 69)
		{
			$('#reportEntry').focus();
			event.preventDefault(); 
		}
		if(event.altKey && event.keyCode == 71)
		{
			showGroups();
			event.preventDefault(); 
		}
		if(event.altKey && event.keyCode == 78)
		{
			leaveNote();
			event.preventDefault(); 
		}
		if(event.altKey && event.keyCode == 82)
		{
			refreshSupDash();
			event.preventDefault(); 
		}
		if(event.altKey && event.keyCode == 83)
		{
			$('#byuKb').focus();
			event.preventDefault(); 
		}
	});

	$('#footer-bottom').hide();
	windowResizing();
}

	var passy = <?php echo '"'.$login.'"'; ?>;

function windowResizing()
{
	if( !/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )
	{
		$(window).resize(function()
		{
			
			if($(window).width()<1024)
			{
				$(".myAllClass").width($(window).width()*0.95);
				//console.log("test1");
			}
			if($(window).width()>=1024)
			{
				$(".myAllClass").width("95%");
				//console.log("test2");
			}
		});
	}
}
// MARK A ROUTINE TASK AS COMPLETED
	function completeTask(id){
			if (window.XMLHttpRequest)
			  {// code for IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			  }
			else
			  {// code for IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			  }
			xmlhttp.onreadystatechange=function()
			  {
			  if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
				document.getElementById("dueTasks").innerHTML=xmlhttp.responseText;
				loadList();
				}
			  }
			var commentText = prompt("Enter Comments below","");
			if(commentText != null){
				xmlhttp.open("GET","../routineTaskList/completeTask.php?id="+ id+"&comment="+escape(commentText)+"&date="+'<?php echo $date;?>',true);
			xmlhttp.send();
			}
		}

// MUTE A ROUTINE TASK
		function muteTask(id){
			if (window.XMLHttpRequest)
			  {// code for IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			  }
			else
			  {// code for IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			  }
			xmlhttp.onreadystatechange=function()
			  {
			  if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
				document.getElementById("dueTasks").innerHTML=xmlhttp.responseText;			
				loadList();
				}
			  }
			var commentText = prompt("Enter Comments below","");
			if(commentText != null){
				xmlhttp.open("POST","../routineTaskList/muteTask.php?id=" + id+"&comment="+escape(commentText)+"&date="+'<?php echo $date;?>',true);
				xmlhttp.send();
			}
		}

// UPDATE WHO IS HERE NOW WITH THE USER SELECTED DATE
	function updateDate(text, d2)
	{
		d = new Date();
		ONE_DAY = 1000*60*60*24;
		diff = Math.ceil((d2.getTime()-d.getTime())/(ONE_DAY));
		hour = diff * 24;
		whosHere(hour, 'whoIs');
	}
	
</script>

<div class="myAllClass" style="width:95%;">

<div class="supDashTitle">
<?php
	print ((can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource*/ ? 'Supervisor' : 'Analyst');
?> Dashboard</div>
<div class="kbSearch">
	<div class="byuKbSearch"><div style="float: left; margin-left: 21px; width: 66px;">BYU KB</div><div style="width: auto; font-size: .75em; float: left; margin-left: 7px;"><input style="width: 100%;" id="byuKb" name="byuKb" placeholder="Search BYU KB" /></div></div>
	<div class="clearMe"></div>
</div>

<div class="clearMe"></div>

<div class="leftColumn">
<?php
	if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9")){/*supervisorDashboard resource*/
?>
<div class="supReport">
	<div class="supSubTitle">Report Entry</div>
	<div class="viewSupReport"><input id="viewSoFar" name="viewSoFar" type="button" value="View Reports So-Far" /></div>
	<div class="clearMe"></div>
	<div class="supReportForm">
		<div class="reportEntry"><textarea class="reportEntryTextArea" name="reportEntry" id="reportEntry"></textarea></div>
		<div class="reportGroups"><a href="#" onclick="showGroups()" class="reportGroupsLabel">Select Groups to be Informed</a>
		<div id="reportGroups" style="display: none;">
<?php

try {
	$groupsQuery = $db->prepare("SELECT * FROM `notificationGroup`");
	$groupsQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}
$found = false;
while ($group = $groupsQuery->fetch(PDO::FETCH_ASSOC)) {
	$found = true;
	echo '<div class="notificationGroup"><input type="checkbox" class="supReportEntryNotificationGroup" value="'.$group['groupName'].'" />'.$group['groupName'].'</div><br />';
}
if(!$found) {
	echo '<div>No Groups Found!</div>';
}
?>
		</div></div>
		<div class="submitReport"><input id="submitEntry" name="submitEntry" type="button" value="Submit" onclick="supReportEntry()" /></div>
	</div>
</div>

<?php
	}
?>
<div class="highPriority">
    <div class="supSubTitle">BYU High Priority Incidents</div>
<?php
	if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9")){/*supervisorDashboard resource*/
?>
    <div class="textBoss"><input type="button" value="Notify Christine" onclick="textBoss()" /></div>
<?php
	}
?>
    <div id="hiPriTickets" class="hiPriTickets"></div>
</div>

</div>

<div class="rightColumn">
<div class="supNotes">
	<div class="supSubTitle">Turn-over Notes</div>
<?php
	if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9")){/*supervisorDashboard resource*/
?>
	<div class="makeANote"><input type="button" value="Leave a Note" onclick="leaveNote()" /></div>
<?php
	}
?>
	<div class="clearMe"></div>
	<div class="supNoteLog" id="supNoteLog">
	</div>
</div>
<div class="supSubTitle">Routine-Tasks Due</div>
<div class="createOneShot"><input type="button" value="Create One-Shot" onclick="javascript:newwindow('../routineTaskList/createOneShot.php', 1025, 480)" /></div>
<div class="dueTasks" namd="dueTasks" id="dueTasks">

</div>


</div>
<div class="clearMe"></div>
<div class="rfcWrapper">
	<div class="supSubTitle" style="width: 30%;"><a href="javascript:newwindow('<?php if($env != 2) echo getenv("SERVICE_NOW_STAGE_URL"); else echo getenv("SERVICE_NOW_URL"); ?>/show_schedule.do?sysparm_type=change_calendar&sysparm_include_view=monthly,daily&sysparm_cancelable=true&sysparm_cancelable=true', 1325, 600);" style="color: #444444;">Requests For Change</a></div>
	<div class="viewSupReport" style="text-align: right; width: 63%;">
	<input id="rfcSearch" type="text" placeholder="Search by RFC Number" />
<?php
	if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource*/{
?>
		<input id="submitUnscheduledChange" name="submitUnscheduledChange" onclick="openRFCpopup()" type="button" value="Unscheduled Change" />
<?php
	}
?>
		<input id="viewOnCall" name="viewOnCall" type="button" value="Eng On-Call" />	
	</div>
	<div class="clearMe"></div>
	<div class="rfcs" id="rfcs">
	</div>
</div>
<div class="clearMe"></div>
<div class="supSubTitle">Who's Here <input type=hidden id="blah"><?php calendarCallback("blah","updateDate"); ?></div>
<div id="supDisplayDate" class="viewSupReport" style="text-align: right; margin-top: .5em; margin-bottom: .5em;"></div>
<div class="clearMe"></div>
<div class="whoHere" id="whoIs">
</div>

<?php
	if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9")){/*supervisorDashboard resource*/
?>
<div id="spinner" class="spinner" style="display:none;">
	<p>Please wait while your report is sent.</p>
    <img id="img-spinner" src="supReportLoading.gif" alt="Loading"/>
<?php
	}
?>
</div>

<div class="clearMe"></div>

</div>

<?php
	if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource*/{
?>
<div id="unscheduledRFC" class="unscheduledRFC" style="display:none;">
	<table>
	    <tr><th>    
		    Ticket/ RFC #:
		</th><td>
            <input type="text" id='ticketNumRFC' name="ticketNumRFC" maxlength=200 size=40 value=""/>
        </td></tr><tr><th>    
		    Engineer Name:
		</th><td>
            <input type="text" id='nameRFC' name="nameRFC" maxlength=200 size=40 value=""/>
		</td></tr><tr><th>
		   Start Time - Date:
		</th><td>
			<input type="text" id='startTimeRFC' name="startTimeRFC" maxlength=200 size=15 value="<?php echo date('H:00A'); ?>"/>
			<input type="text" id='startDateRFC' name="startDateRFC"  size=15 value="<?php echo date('Y-m-d');?> "/>
        </td></tr><tr><th>
		    End Time - Date:
		</th><td>
		    <input type="text" id='endTimeRFC' name="endTimeRFC" maxlength=200 size=15 value="<?php echo date('H:00A'); ?>"/>
			<input type="text" id='endDateRFC' name="endDateRFC"  size=15 value="<?php echo date('Y-m-d');?> "/>
        </td></tr><tr><th>
		    Description of Change:
		</th><td>
            <textarea id='descRFC' name="descRFC" cols=40 rows=3></textarea>
        </td></tr><tr><th>
			Impact:
		</th><td>
		    <textarea id='impactRFC' name="impactRFC" cols=40 rows=3></textarea>
        </td></tr>
    </table>
</div>
<?php
	}
?>

<?php
	}
	require('../includes/includeAtEnd.php');
 ?>
