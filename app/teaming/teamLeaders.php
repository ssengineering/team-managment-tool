<?php //teamLeaders.php
//application for displaying and editing teams and their leaders
require('../includes/includeme.php'); 
require('teamingFunctions.php');

if(!can("update", "28e60394-f719-4225-85ad-fa542ab6a8df"))//teams resource
{
	echo "<h2>You are not authorized to view this page. Contact your supervisor if you believe you reached this in error.</h2>";
	require('../includes/includeAtEnd.php');
	return;
}

try {
	$teamsQuery = $db->prepare("SELECT * FROM teams WHERE area = :area");
	$teamsQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}
if(isset($_POST['submit'])){
    while($team = $teamsQuery->fetch(PDO::FETCH_ASSOC)) {
		try {
			$updateQuery = $db->prepare("UPDATE teams SET area=:area, name=:name, lead=:lead WHERE ID=:id");
			$updateQuery->execute(array(':area' => $area, ':name' => $_POST[$team['ID'].'name'], ':lead' => $_POST[$team['ID'].'lead'], ':id' => $team['ID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
?>
<script type='text/javascript'>
window.onload = function()
{
	printTeams();
	
	$("#createTeamDialog").dialog({
		autoOpen: false,
		close: function(){
			$('#createTeamName').val('');
		},
		buttons:
		{
			"Create": function()
			{
				if($("#createTeamName").val() !== "")
				{
					$.post("insertTeam.php", $("#createTeam").serialize(), printTeams);
					$(this).dialog("close");
				}
				else
				{
					alert("Error: You must enter a team name.");
				}
			},
			Cancel: function()
			{
				$(this).dialog("close");
			}
		}
	});
}

function printTeams(){
			var page = 'printTeamLeaders.php';
			
			var cb = function(result)
			{ 
				document.getElementById("results").innerHTML = result;
				sorttable.makeSortable(document.getElementById("teamLeadersTable"));
			};

			callPhpPage(page,cb);
	}


function deleteTeam(){
	var id = document.getElementById('teams').value;
	var r = confirm("Are you sure you want to Delete this team?");
	if(r == true){
		var page = 'deleteTeam.php?id='+id;
	
		var cb = function(result){ printTeams(); };

		callPhpPage(page,cb);
	}
}

</script>

<style type="text/css">
.center
{
margin:auto;
width:100%;
text-align:center;
}

table
{ 
margin-left: auto;
margin-right: auto;
}

</style>

<div class='center'>
<h1>Training Team Leaders</h1>
<h4>INSTRUCTIONS: After editing a team name or changing a team leader <br/>click "Submit Changes" BEFORE doing anything else or your changes will not be saved.</h4>
</div>
<form name='addTeam' method='post'>
	<div class='center'>
    <input type='button' name='newTeam' value="Insert New Team" onclick='$("#createTeamDialog").dialog("open")' />
	
    <input type='submit' class='button' name='submit' value="Submit Changes" /> 
     
	</div>
	<div name='results' id='results' class='center'>
	
	</div>
	<br/>
</form>

<div id="createTeamDialog" title = "Create New Team">
	<form id = "createTeam" name = "createTeam" method = "post" action = "insertTeam.php">
		<label for = "createTeamName">Team Name: </label>
		<input type = "text" id = "createTeamName" name = "createTeamName">
		
		<label for = "createTeamLeader">Team Leader: </lable>
		<select name = "createTeamLeader"><?php teamLeaderSelect($area, '');  ?></select>
	</form>
</div>

<?php require('../includes/includeAtEnd.php'); ?>
