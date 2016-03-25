<?php //This page is for viewing teams
require('../includes/includeme.php');

//prints out the table of team leads and members for team.php
function displayTeamsTable($area){
	global $db;
	try {
		$teamsQuery = $db->prepare("SELECT * FROM teams WHERE area=:area ORDER BY name");
		$teamsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($curTeam = $teamsQuery->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";		
        echo "<td>".$curTeam['name']."</td>";
        echo "<td>".nameByNetId($curTeam['lead'])."</td>";
        echo "<td>".$curTeam['email']."</td>";
		try {
			$membersQuery = $db->prepare("SELECT * FROM `teamMembers` JOIN `employee` ON `teamMembers`.`netID`=`employee`.`netID` WHERE `teamMembers`.`teamID` = :id ORDER BY `employee`.`firstName`");
			$membersQuery->execute(array(':id' => $curTeam['ID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo "<td>";
		while($cur = $membersQuery->fetch(PDO::FETCH_ASSOC)) {
			echo nameByNetId($cur['netID']);
            if($cur['isSupervisor']){
                echo " (Supervisor)";
            }
			echo "<br/>";
		}
		echo "</td></tr>";
	}

}

?>

<h1 align='center'>Teams</h1>
<div align='center'>
<table>
	<tr><th>Team Name</th><th>Lead</th><th>Email</th><th>Team Members</th></tr>
	<?php displayTeamsTable($area); ?>
</table>
</div>

<?php 
require('../includes/includeAtEnd.php');
?>
