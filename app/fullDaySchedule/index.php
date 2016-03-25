<?php 
require_once('../includes/includeme.php');
?>

<style type='text/css'>
	/* #main-header {
		width: 100%;
		position: fixed;
	}
	#content, #footer-bottom {
		position: relative;
		top: 168px;
		overflow: auto;
	} */
	.schedule, .schedule_time {
		margin:auto;
		border-collapse:collapse;
		border-style:solid;
		border-width:1px;
		padding:7px;
		text-align:center;
	}
	.schedule_time {
		background-color: #e0e0e0;
	}
	td.schedule {
	    /* background-color:white; */
	}
	.info {
		width:50%;
		margin:auto;
		text-align:center;
		padding-bottom:10px;
	}
	.innerLink{
		display:inline;
		margin:auto;
		text-align:center;
		font-size:120%;
		color:#023962;
		padding-left: 25px;
		padding-right: 25px;
	}
	.innerLink:hover{
		color:#f8ad50;
		cursor:pointer;
	}
	.data {
	    padding-bottom:20px;
	}
	#tools {
	    display: none;
	}
	li {
		margin: 0px;
		position: relative;
		cursor: pointer;
		float: left;
		list-style: none;
	}
	.schedule tbody tr:nth-child(2n) {
		background-color: #E7E7E7;
	}
	.schedule tbody tr:hover {
		background-color: #CECECE;
	}
</style>

<script type='text/javascript'>  
	function print() {
	
} 
	window.onload = function () {
		pullSchedule();
		
	};
    
    var date = new Date();

	function pullSchedule() {
		
		<?php if (can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/) echo 'var showUnposted = document.getElementById("showNonPosted").checked ? 1 : 0;'; ?>
		 $("#datepicker").datepicker({showOn: 'button', buttonImageOnly: true, buttonImage: '../../includes/templates/images/calendarImg/cal.gif',
			 onSelect: function(dateText,inst){
							updateDate(dateText);
					}
			});		
	

		var page = ("returnSchedule.php?date=" + dateToText(date) + "&values=" + getSelected("types") + "&employees=" + getSelected("names"))<?php if (can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/) echo ' + "&unposted=" + showUnposted'; ?>;
		var cb = function(result) {
            document.getElementById('data').innerHTML=result;
			$("#sched").stickyTableHeaders();
		}
		
		callPhpPage(page, cb);
		document.getElementById('date').innerHTML=dateToPrettyText(date);
		document.getElementById('blah').value=dateToText(date);
	}
	
	function printSchedule() {
		<?php if (can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/) echo 'var showUnposted = document.getElementById("showNonPosted").checked ? 1 : 0;'; ?>
		
		var url = ("printSchedule.php?date=" + dateToText(date) + "&values=" + getSelected("types") + "&employees=" + getSelected("names"))<?php if (can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/) echo ' + "&unposted=" + showUnposted'; ?>;
		window.open(url);

	}
	
	function previousDay(){
	    date.setDate(date.getDate() - 1);
	    pullSchedule();
	}
	function nextDay() {
	    date.setDate(date.getDate() + 1);
	    pullSchedule();
	}
	function currentDay() {
	    date = new Date();
	    pullSchedule();
    }
    function updateDate(d1) {
    	date = new Date(d1);
    	pullSchedule();
    }
    function getSelected(nam) {
        var values = new Array(); 
        var list = document.getElementById(nam);
        for (var i = 0; i < list.length; i++) { 
            if (list.options[i].selected) { 
                values.push(list.options[i].value);
            } 
        }
        return values;
    }
    function toggleVisibility() {		
		$(function() {
			$( "#tools" ).dialog({ resizable: true, width: 450 });
		});
    }
    
   	function updateTeamsList() {
		var teamId = document.getElementById("teams").value
		
		if (teamId == '') {
			var tm = document.getElementById("names");
			for (var j = 0; j < tm.options.length; j++) {
				tm.options[j].selected = "selected";
			}
			
			pullSchedule();
			
			return;
		}

		var teamMembers = '/teams/ajax/pullTeamMembersArray.php?id='+teamId;
		
		var updateTeamList = function(result){
					var tm = document.getElementById("names");
				//	alert(result);
					var members = JSON.parse(result);
				//	alert(members);
					for (var j = 0; j < tm.options.length; j++) {
						tm.options[j].selected = "";
					}
					
					for (var i = 0; i < members.length; i++){
						var netId = members[i].netID;
						for (var j = 0; j < tm.options.length; j++) {
							if (tm.options[j].value == netId) tm.options[j].selected = "selected";
						}
					}
					
					pullSchedule();
				};

		callPhpPage(teamMembers,updateTeamList);
	
	}

</script>
<div class='info'>
	<div align="center">
		<?php if($area == 4){ ?>
		<h1>Daily Schedule for <div id="date"></div></h1>
		<?php } else { ?>
		<h1>Full Day Schedule for <div id="date"></div></h1>
		<?php } ?>

		<table><tr>
			<td><input type=hidden id="blah">
				<span style="cursor:pointer;"><input type=hidden id='datepicker' /></span>
				<?php //calendarCallback("blah","updateDate"); ?>
			</td>
			<td>
				<li class="ui-state-default ui-corner-all" onClick="toggleVisibility();"><span class="ui-icon ui-icon-wrench"></span></li>
			</td>
		</tr></table>
	</div>
	
	<input type="button" value="View Printable Schedule"onclick="printSchedule()"></button><br  />
	<a href="javascript:void" class='innerLink' onClick="previousDay()">Previous Day</a>
	<a href="javascript:void" class='innerLink' onClick="currentDay()">Current Day</a>
	<a href="javascript:void" class='innerLink' onClick="nextDay()">Next Day</a>
</div>


<div id="tools" title="Full Week Schedule Tools">
	<table>
		<tr>
			<th><label for="hourList">Shift Types</label></th>
			<th><label for="nameList">Employees</label></th>
		</tr>
		<tr>
			<td><?php echo hourTypeListBox("types", "hourList", "pullSchedule();", 10, true); ?></td>
			<td>
				<select id='teams' onchange='updateTeamsList()' >
					<option value='' selected>All Employees</option>
					<?php pullTeams($area); ?>
				</select>
				<?php echo employeeNameListBox("names", "nameList", "pullSchedule();", 9, true); ?>
			</td>
		</tr>
	</table>
	<?php if (can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/) echo '<label><input type="checkbox" id="showNonPosted" onChange="pullSchedule();"> Show Non-Posted Schedules</label>'; ?>
</div>

<div id="data" class="data" align="center"></div>

<?php 
require_once('../includes/includeAtEnd.php'); 
?>
<script type='text/javascript' src="stickytableheaders.js"></script>
