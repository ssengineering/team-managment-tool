<?php //printTeamLeaders.php this responds to an ajax call
require('../includes/includeMeBlank.php');
require('teamingFunctions.php');

echo "<table id='teamLeadersTable' class='sortable'>
		<tr><th class='sorttable_numeric'>Name</th><th>Leader</th></tr>";	
    pullTeamsSelect($area);
echo	"</table>";
echo '<input type="button" value="Delete Team" onclick="deleteTeam()" />';
    echo "<select name='teams' id='teams'>";
     teamsSelect($area);
    echo '</select>';
?>
