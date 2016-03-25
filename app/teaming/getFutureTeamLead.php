<?php 

//getFutureTeamLead.php
//Returns the name of the team leader based on the ID of the team.

require('../includes/includeMeBlank.php');
if(can("update", "28e60394-f719-4225-85ad-fa542ab6a8df"))/*teams resource*/{

	$id = $_GET['id'];

	if($id === '')
	{
		echo 'None';
	}
	else
	{
		try {
			$teamsQuery = $db->prepare("SELECT * FROM teams WHERE ID = :id");
			$teamsQuery->execute(array(':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$cur = $teamsQuery->fetch(PDO::FETCH_ASSOC);
		echo nameByNetId($cur['lead']);
	}
}
?>
