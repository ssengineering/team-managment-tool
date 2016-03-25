<?php //addTeam.php
//adds a team to the database
require('../../includes/includeMeBlank.php');

$name = $_GET['name'];
$email = $_GET['email'];
$lead = $_GET['lead'];

try {
	$insertQuery = $db->prepare("INSERT INTO teams (name,lead,email,area,guid) VALUES (:name,:lead,:email,:area,:guid)");
	$insertQuery->execute(array(':name' => $name, ':lead' => $lead, ':email' => $email, ':area' => $area, ':guid' => newGuid()));
	$id = $db->lastInsertId();
	$insertMemberQuery = $db->prepare("INSERT INTO teamMembers (netID,teamID,area,isSupervisor,guid) VALUES (:lead,:id,:area,'1',:guid)");
	$insertMemberQuery->execute(array(':lead' => $lead, ':id' => $id, ':area' => $area, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}
?>
