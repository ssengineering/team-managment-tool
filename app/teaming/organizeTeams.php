<?php //organizeTeams.php this will alow users to select which teams a user appears on.
ini_set('display_errors', '1');
require('../includes/includeme.php');
require('teamingFunctions.php');

if(!can("update", "28e60394-f719-4225-85ad-fa542ab6a8df"))//teams resource
{
	echo "<h2>You are not authorized to view this page. Contact your supervisor if you believe you reached this in error.</h2>";
	require('../includes/includeAtEnd.php');
	return;
}

if(isset($_POST['submit']))
{	
	global $area;
	$nameCount = 1;
	
	while(isset($_POST['empNetID_'.$nameCount]))
	{
		if($_POST['currentTeamId_'.$nameCount] != '')
		{
			try {
				$membersQuery = $db->prepare("SELECT * FROM `teamMembers` WHERE `area` = :area ORDER BY `netID` ASC");
				$membersQuery->execute(array(':area' => $area));
			} catch(PDOException $e) {
				exit("error in query");
			}
			while($teamMembers = $membersQuery->fetch(PDO::FETCH_ASSOC))
			{	// The two if statements below make sure we are getting the right employee and update the correct record if the employee is part of multiple teams.  			 
				if($_POST['empNetID_'.$nameCount]==$teamMembers['netID'])
				{
					if($teamMembers['teamID']==$_POST['currentTeamId_'.$nameCount])
					{
						// Makes sure that we are actually updating, if employee's team is the same, then we don't update anything in the database.
						if(($_POST['currentTeamId_'.$nameCount])!=($_POST['select_'.$nameCount]))
						{		
							// If an employee belongs to multiple teams, we make sure that we don't update to a team that he is already a part of.
							$teamCheck = noSameTeamIDToUpdate(($_POST['empNetID_'.$nameCount]), ($_POST['select_'.$nameCount]));
							if($teamCheck)
							{
								//In case employee belongs to multiple teams. So if we need to make sure not to update to a team the employee already belongs to.
								if($_POST['select_'.$nameCount] == '')
								{
									try {
										$deleteQuery = $db->prepare("DELETE FROM teamMembers WHERE netID = :netId AND `teamID` = :team");
										$deleteQuery->execute(array(':netId' => $_POST['empNetID_'.$nameCount], ':team' => $_POST['currentTeamId_'.$nameCount]));
									} catch(PDOException $e) {
										exit("error in query");
									}
									removeEmployeeFromTeaming($_POST['empNetID_'.$nameCount]);
								}
								else
								{
									try {
										$updateQuery = $db->prepare("UPDATE teamMembers SET `teamID` = :team WHERE netID = :netId AND `teamID`=:oldTeam");
										$updateQuery->execute(array(':team' => $_POST['select_'.$nameCount], ':netId' => $_POST['empNetID_'.$nameCount], ':oldTeam' => $_POST['currentTeamId_'.$nameCount]));
									} catch(PDOException $e) {
										exit("error in query");
									}
									// Update teaming
									updateEmployeeTeamingInfo($_POST['empNetID_'.$nameCount], $_POST['select_'.$nameCount], $area);
								}	
							}
						}
					}
				}
			}		
		}		
		else
		{
			if($_POST['select_'.$nameCount] != '') {
				try {
					$insertQuery = $db->prepare("INSERT INTO teamMembers (netID,teamID, isSupervisor, area, guid) VALUES (:netId,:team,'0',:area,:guid)");
					$insertQuery->execute(array(':netId' => $_POST['empNetID_'.$nameCount], ':team' => $_POST['select_'.$nameCount], ':area' => $area, ':guid' => newGuid()));
				} catch(PDOException $e) {
					exit("error in query");
				}
				updateEmployeeTeamingInfo($_POST['empNetID_'.$nameCount], $_POST['select_'.$nameCount], $area);
			}		
		}
		$nameCount++;
	}
}
?>
<script type="text/javascript">
window.onload = function()
{	
	$("select").change(function () 
	{
		var str = $(this).attr('value');
		var selectName = $(this).attr('name');
		// Get digit out of the name
		var selectNameDigitsOnly = "";
			var selectDigitsOnlyArr = selectName.match(/[\d\.]+/g);
		  	for (i=0; i<selectDigitsOnlyArr.length; i++)
		  	{
				selectNameDigitsOnly=selectNameDigitsOnly+selectDigitsOnlyArr[i];
			}

		var request = $.ajax({
				  url: "getFutureTeamLead.php",
				  type: "GET",
				  data: {id : str},
				  async: true,
				  cache: false,
				  success: function(data){
					$('td#futureTeamLeader_'+selectNameDigitsOnly).html(data)
				  }
				});

				request.done(function(msg) {
				});

				request.fail(function(jqXHR, textStatus) {
				  alert( "Request failed: " + textStatus );
				});
	});
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
	<h1>Manage Team Members</h1>
	<h4>INSTRUCTIONS: Select a new team for the desired employee.  <br /> Then click the 'Submit Changes' button at the bottom of the page. </h4>
	<form id='teams' name='teams' method='post'>
	
	<table class='sortable'>
	<tr>
		<th>Employee</th><th>Current Team Lead</th><th>Select New Team</th><th>Future Team Lead</th>
	</tr>
	<?php printTeamOrganizer($area); ?>
	</table>
    <br />
	<input type='submit' id='submit' name='submit' value="Submit Changes" />
	</form>

</div>
<?php
require('../includes/includeAtEnd.php');
?>
