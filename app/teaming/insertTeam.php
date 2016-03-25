<?php //insertTeam.php used to insert a new team into the current area.
require('../includes/includeMeBlank.php');
if(can("update", "28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource*/{
	try {
		$insertQuery = $db->prepare("INSERT INTO teams (name, lead, area, email, guid) VALUES (:name, :leader, :area, :email, :guid)");
		$insertQuery->execute(array(':name' => $_POST['createTeamName'], ':leader' => $_POST['createTeamLeader'], ':area' => $area, ':email' => '', ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}
?>
