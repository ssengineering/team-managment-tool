<?php require('../includes/includeme.php'); 
function pullMyTeams($area,$permission){
	global $netID, $db;
	if($permission){
		$teamsQueryString = "SELECT * FROM teams WHERE area = :area";
		$teamsQueryParams = array(':area' => $area);
	} else {
		$teamsQueryString = "SELECT * FROM teams WHERE `ID` IN (SELECT `teamID` AS `ID` FROM `teamMembers` WHERE `netID` = :netId AND isSupervisor = '1' AND area = :area) OR (area = :area1 AND lead = :netId1)";
		$teamsQueryParams = array(':netId' => $netID, ':area' => $area, ':area1' => $area, ':netId1' => $netID);
	}
	try {
		$teamsQuery = $db->prepare($teamsQueryString);
		$teamsQuery->execute($teamsQueryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $teamsQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value='".$cur['ID']."' id='".$cur['email']."_|".$cur['lead']."' >".$cur['name']."</option>";
	}
}

?>

<script>
	window.onload = function () {
		var sb = document.getElementById("all_employees");
		sb.remove(0);
		sortListbox(sb);
	};
	
	function addOption(selectBox, value, text)	{
		var opt = document.createElement("OPTION");
		opt.text = text;
		opt.value = value;
		selectBox.options.add(opt);
	}
	
	function removeOption(selectBox, value) {
		if (!selectBox) return;
		for (var i = 0; i < selectBox.options.length; i++) {
			if (selectBox[i].value == value)
				selectBox.remove(i);
		}
	}
	
	function removeAllOptions(selectBox) {
		if (!selectBox) return;
		selectBox.options.length = 0;
	}
	
	function changeText(selectBox, value, text) {
		if (!selectBox) return;
		for (var i = 0; i < selectBox.length; i++) {
			if (selectBox[i].value == value)
				selectBox[i].text = text;
		}		
	}
	
	function getText(selectBox, value) {
		if (!selectBox) return;
		for (var i = 0; i < selectBox.length; i++) {
			if (selectBox[i].value == value)
				return selectBox[i].text;
		}
	}
	
	function setSelectedValue(selectObj, value) {
		if (!selectObj) return;
		var selectLength = selectObj.length;
		if (selectLength == undefined)
			alert ("Argh!");
		for (var i = 0; i < selectLength; i++) {
			selectObj[i].selected = (value == selectObj[i].value ? '1' : '');
		}
	}
	
	function sortListbox(selectBox) {
		arrTexts = new Array(); 
		arrValues = new Array(); 
		arrOldTexts = new Array(); 

		for(var i = 0; i < selectBox.length; i++) { 
			arrTexts[i] = selectBox.options[i].text; 
			arrValues[i] = selectBox.options[i].value; 

			arrOldTexts[i] = selectBox.options[i].text; 
		} 

		arrTexts.sort(); 

		for(var i = 0; i < selectBox.length; i++) { 
			selectBox.options[i].text = arrTexts[i]; 
			for(var j = 0; j < selectBox.length; j++) { 
				if (arrTexts[i] == arrOldTexts[j]) { 
					selectBox.options[i].value = arrValues[j]; 
					j = selectBox.length; 
				} 
			} 
		} 
	}
	
	function addTeam(){
		document.getElementById("teamName").value = "";
		document.getElementById("teamEmail").value = "";
		setSelectedValue(document.getElementById("teamLead"),'');
		$( "#teamEditor" ).dialog({
			title: "Add Team",
			resizable: false,
			width: 350,
			modal: true,
			draggable: true,
			buttons: [{ text: "Add Team", click: function() { add(); } }]
		});
	}

	function add(){
		$("#teamEditor").dialog("close");
		var name = document.getElementById("teamName").value;
		var email = document.getElementById("teamEmail").value;
		var lead = document.getElementById("teamLead").value;
		var page = 'ajax/addTeam.php?name='+name+'&email='+email+'&lead='+lead;
				
		var cb = function(result){ 
				window.location.reload();
				};

		callPhpPage(page,cb);
	}

	function editTeam(){
		var sel = document.getElementById("teams");
		var stuff = sel.options[sel.selectedIndex].id
		var items = stuff.split("_|");
		document.getElementById("teamName").value = sel.options[sel.selectedIndex].text;
		document.getElementById("teamEmail").value = items[0];
		setSelectedValue(document.getElementById("teamLead"),items[1]);

	$( "#teamEditor" ).dialog({
			title: "Edit Team",
			resizable: false,
			width: 350,
			modal: true,
			draggable: true,
			buttons: [{ text: "Edit Team", click: function() { edit(); } }]
		});
	}

	function edit(){
		$("#teamEditor").dialog("close");
		var name = document.getElementById("teamName").value;
		var email = document.getElementById("teamEmail").value;
		var lead = document.getElementById("teamLead").value;
		var id = document.getElementById("teams").value;
		var page = 'ajax/editTeam.php?name='+name+'&email='+email+'&lead='+lead+'&id='+id;
				
		var cb = function(result){ 
				window.location.reload();
				};

		callPhpPage(page,cb);
	}

	function moveTo(tbFrom, tbTo) 
	{
		 var arrFrom = new Array(); var arrTo = new Array(); 
		 var arrLU = new Array();
		 var i;
		 for (i = 0; i < tbTo.options.length; i++) 
		 {
		  arrLU[tbTo.options[i].text] = tbTo.options[i].value;
		  arrTo[i] = tbTo.options[i].text;
		 }
		 var fLength = 0;
		 var tLength = arrTo.length;
		 for(i = 0; i < tbFrom.options.length; i++) 
		 {
		  arrLU[tbFrom.options[i].text] = tbFrom.options[i].value;
		  if (tbFrom.options[i].selected && tbFrom.options[i].value != "") 
		  {
		   arrTo[tLength] = tbFrom.options[i].text;
		   tLength++;
		  }
		  else 
		  {
		   arrFrom[fLength] = tbFrom.options[i].text;
		   fLength++;
		  }
		}

		tbFrom.length = 0;
		tbTo.length = 0;
		var ii;
		for(ii = 0; ii < arrFrom.length; ii++) 
		{
		  var no = new Option();
		  no.value = arrLU[arrFrom[ii]];
		  no.text = arrFrom[ii];
		  tbFrom[ii] = no;
		}

		for(ii = 0; ii < arrTo.length; ii++) 
		{
		 var no = new Option();
		 no.value = arrLU[arrTo[ii]];
		 no.text = arrTo[ii];
		 tbTo[ii] = no;
         changeMembership("add",arrLU[arrTo[ii]]);
		}
		sortListbox(tbFrom);
		sortListbox(tbTo);
	}

    function moveFrom(tbFrom, tbTo) 
	{
		 var arrFrom = new Array(); var arrTo = new Array(); 
		 var arrLU = new Array();
		 var i;
		 for (i = 0; i < tbTo.options.length; i++) 
		 {
		  arrLU[tbTo.options[i].text] = tbTo.options[i].value;
		  arrTo[i] = tbTo.options[i].text;
		 }
		 var fLength = 0;
		 var tLength = arrTo.length;
		 for(i = 0; i < tbFrom.options.length; i++) 
		 {
		  arrLU[tbFrom.options[i].text] = tbFrom.options[i].value;
		  if (tbFrom.options[i].selected && tbFrom.options[i].value != "") 
		  {
		   arrTo[tLength] = tbFrom.options[i].text;
		   tLength++;
		  }
		  else 
		  {
		   arrFrom[fLength] = tbFrom.options[i].text;
		   fLength++;
		  }
		}

		tbFrom.length = 0;
		tbTo.length = 0;
		var ii;
		for(ii = 0; ii < arrFrom.length; ii++) 
		{
		  var no = new Option();
		  no.value = arrLU[arrFrom[ii]];
		  no.text = arrFrom[ii];
		  tbFrom[ii] = no;
		}

		for(ii = 0; ii < arrTo.length; ii++) 
		{
		 var no = new Option();
		 no.value = arrLU[arrTo[ii]];
		 no.text = arrTo[ii];
		 tbTo[ii] = no;
         changeMembership("remove",arrLU[arrTo[ii]]);
		}
		updateTeamsList();
		sortListbox(tbFrom);
		sortListbox(tbTo);
	}
	
	function updateTeamsList(){
		var teamId = document.getElementById("teams").value
		
		removeAllOptions(document.getElementById("team_employees"));
		removeAllOptions(document.getElementById("all_employees"));
		
		if (teamId == '') return;

		var teamMembers = 'ajax/pullTeamMembersArray.php?id='+teamId;
		var nonTeamMembers = 'ajax/pullNonTeamMembers.php?id='+teamId;
		
		var updateLeftList = function(result){
					var tm = document.getElementById("team_employees");
					
					var members = JSON.parse(result);
					
					for (var i = 0; i < members.length; i++){
						var name = members[i].name;
						if(members[i].isSupervisor == 1){
							name+= " (Supervisor)";
						}
						addOption(tm, members[i].netID,name);
					}	
					sortListbox(tm)
				};
				
		var updateRightList = function(result){
					var ae = document.getElementById("all_employees");
					
					var employees = JSON.parse(result);
					
					for (var i = 0; i < employees.length; i++){
						addOption(ae, employees[i].netID, employees[i].name);
					}
					
					sortListbox(ae)
				};

		callPhpPage(teamMembers,updateLeftList);
		callPhpPage(nonTeamMembers,updateRightList);
	
	}
	
	function changeMembership(type,netid){
		var teamId = document.getElementById("teams").value;
		var page = 'ajax/updateMembership.php?team='+teamId+'&employee='+netid+'&type='+type;
		
		var cb = function(result){
				};

		callPhpPage(page,cb);
	
	}

    function deleteTeam(){
        var teamId = document.getElementById("teams").value;
        var page = 'ajax/deleteTeam.php?team='+teamId;

        var cb = function(result){
                    window.location.reload(); 
                };
        var i = confirm("Are you sure you want to delete "+document.getElementById("teams").value+"?");
        if(i){        
            callPhpPage(page,cb); 
        }
    }
    
    function toggleSupervisor(){
        var teamId = document.getElementById("teams").value;
        var selected = getSelected("team_employees");
        var page = 'ajax/toggleSupervisor.php?team='+teamId+'&netid='+selected;
		
		var cb = function(result){
				updateTeamsList();
				};

		var i = confirm("Are you sure you want to toggle Supervisor Status for: "+selected+"?");
        if(i){        
            callPhpPage(page,cb); 
        }
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
</script>
<h2 align='center'>Teams</h2>
<div id="options" align='center'>
<?php if(can("update","28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource*/{?>
<input type='button' value="Add New Team" onclick='addTeam()' />
<?php } ?>
<select id='teams' onchange='updateTeamsList()' >
<option value=''>Select Team</option>
<?php pullMyTeams($area,can("update","28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource*/; ?>
</select>
<input type='button' value="Edit Team" onclick='editTeam()' />
<?php if(can("update","28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource*/{?>
<input type='button' value="Delete Team" onclick='deleteTeam()' />
<?php } ?>
</div>
<div id='teamEditor' style='display:none;'>
	<h2>Team</h2>
	<table>
	<tr>
		<th>Name</th><td><input type='text' id='teamName' /></td>
	</tr><tr>
		<th>Email</th><td><input type='text' id='teamEmail' /></td>
	</tr><tr>
		<th>Lead</th><td><select id='teamLead'><?php employeeFillCurrentArea(); ?></select></td>
	<tr>
	</table>
</div>
<br/>
<br/>
<div id='listBoxes' align='center'>
<form name="combo_box">
<table>
<thead><tr><th>Available Employees</th><th></th><th>Team Members</th></tr></thead>
<tr><td>
<select name="all_employees" id="all_employees" size=23 style="width:200px" multiple>
</select>
</td>
<td align="center" style="vertical-align:middle;">
<input type="button" onClick="moveTo(this.form.all_employees,this.form.team_employees)" 
value="->"><br />
<input type="button" onClick="moveFrom(this.form.team_employees,this.form.all_employees)" 
value="<-">
</td>
<td>
<input type='button' value="Toggle Supervisor Status" onclick='toggleSupervisor()' />
<br/>
<select multiple size=22  id="team_employees" name="team_employees" style="width:200px"> 
</select>
</td></tr></table>
</form>
</div>
<?php require('../includes/includeAtEnd.php'); ?>
