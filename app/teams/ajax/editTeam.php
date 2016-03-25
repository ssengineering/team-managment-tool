<?php //editTeam.php
//edits a team in the databse
require('../../includes/includeMeBlank.php');

$name = $_GET['name'];
$email = $_GET['email'];
$lead = $_GET['lead'];
$id = $_GET['id'];

try {
	$updateQuery = $db->prepare("UPDATE teams SET name = :name, lead = :lead, email = :email WHERE ID = :id");
	$updateQuery->execute(array(':name' => $name, ':lead' => $lead, ':email' => $email, ':id' => $id));
	$insertQuery = $db->prepare("INSERT INTO teamMembers (netID,teamID,area,isSupervisor,guid) VALUES (:lead,:id,:area,'1',:guid)");
	$insertQuery->execute(array(':lead' => $lead, ':id' => $id, ':area' => $area, ':guid' => newGuid()));
} catch(PDOException $e) {
	exit("error in query");
}
?>
