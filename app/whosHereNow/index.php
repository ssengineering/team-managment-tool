<?php require_once('../includes/includeme.php'); ?>

<style type='text/css'>
	.schedule, .hourType {
		margin: auto;
		padding:7px;
		text-align:center;
		font-size:90%;
	}
	td.schedule {
	    width: 200px;
	}
	.hourType {
		width: 100px;
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
	.info, #data {
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
</style>

<script type=text/javascript>
	<?php
		echo "var hourSize = ".$hourSize.";";
	?>

	window.onload=pullSchedule;
	var date = new Date();
	
	function pullSchedule() {
		 $("#datepicker").datepicker({showOn: 'button', buttonImageOnly: true, buttonImage: '../../includes/templates/images/calendarImg/cal.gif',
			 onSelect: function(dateText,inst){
							updateDate(dateText);
					}
			});
		

		//../includes/templates/images/calendarImg/cal.gif
		
		var time = date.getHours();
		if (date.getMinutes() > 0) time += Math.floor((date.getMinutes() / 60) / hourSize) * hourSize;
		
		<?php if (can("update","1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/) echo 'var showUnposted = document.getElementById("showNonPosted").checked ? 1 : 0;'; ?>
		
		var page = "returnWhosHereNow.php?date=" + dateToText(date) + "&time=" + time + "&values=" + getSelected("types") + "&employees=" + getSelected("names") <?php if (can("update","1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/) echo ' + "&unposted=" + showUnposted'; ?>;
		var cb = function (result) {
			document.getElementById("data").innerHTML=result;
		}
		
		callPhpPage(page,cb);
		
		document.getElementById("date").innerHTML=dateToText(date) + " " + hourToTime(time);
		document.getElementById('blah').value=dateToText(date);
	}
	function prevHour() {
		date.setMinutes(date.getMinutes() - hourSize * 60);
		
		pullSchedule();
	}
	function nextHour() {
		date.setMinutes(date.getMinutes() + hourSize * 60);
		
		pullSchedule();
	}
	function currHour() {
		date = new Date();
		
		pullSchedule();
	}
	function updateDate(d1) {
    date = new Date(d1);
		time = new Date();
		date.setHours(time.getHours());
		date.setMinutes(time.getMinutes());
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
					
					var members = JSON.parse(result);
					
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

<div class="info" align="center">
	<h1>Who's Here Now <div id="date"></div></h1>
	<table>
		<tr>
			<td><input type=hidden id="blah">
					<span style="cursor:pointer;"><input type=hidden id='datepicker' /></span>
				<?php //calendarCallback("blah","updateDate"); ?>
			</td>
			<td>
				<li class="ui-state-default ui-corner-all" onClick="toggleVisibility();"><span class="ui-icon ui-icon-wrench"></span></li>
			</td>
		</tr>
	</table>
    
	<a href="javascript:void" class='innerLink' onClick="prevHour(); return false;">Previous</a>
	<a href="javascript:void" class='innerLink' onClick="currHour(); return false;">Current</a>
	<a href="javascript:void" class='innerLink' onClick="nextHour(); return false;">Next</a>
</div>

<div id="tools" title="Who's Here Now Tools">
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
	<?php if (can("update","1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/) echo '<label><input type="checkbox" id="showNonPosted" onChange="pullSchedule();"> Show Non-Posted Schedules</label>'; ?>
</div>

<div id="data"></div>


<?php 
require_once('../includes/includeAtEnd.php'); 
?>
