<?php //teams.php All this does is display the teams
require('../includes/includeme.php');
require('teamingFunctions.php');
?>

<h1 align='center'>Training Teams</h1>
<div align='center'>
<table>
	<tr><th>Team Name</th><th>Team Lead</th><th>Team Members</th></tr>
	<?php displayTeamsTable($area); ?>
</table>
</div>

<?php require('../includes/includeAtEnd.php');
?>

