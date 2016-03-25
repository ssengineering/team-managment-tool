<?php
require ('../includes/includeMeBlank.php');
if ( (isset($_POST['hoursRequested']) && $_POST['hoursRequested'] != '') || (isset($_POST['hoursRegistered']) && $_POST['hoursRegistered'] != '') || (isset($_POST['note']) && $_POST['note'] != '') )
{
	function setNotes($employee, $semester, $requested, $registered, $notes)
	{
		global $db;
		try {
			$insertQuery = $db->prepare("INSERT INTO scheduleNotes (netID, semester, requestedHours, registeredHours, notes, guid) VALUES (:employee,:semester,:requested,:registered,:notes,:guid) ON DUPLICATE KEY UPDATE requestedHours=:requested1,registeredHours=:registered1,notes=:notes1");
			$success = $insertQuery->execute(array(':employee' => $employee, ':semester' => $semester, ':requested' => $requested, ':registered' => $registered, ':notes' => addSlashes($notes), ':guid' => newGuid(), ':requested1' => $requested, ':registered1' => $registered, ':notes1' => addSlashes($notes)));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo $success;
	}
	setNotes($_POST['employee'], $_POST['period'], $_POST['hoursRequested'], $_POST['hoursRegistered'], $_POST['note']);
	return;
}
else if ( (isset($_POST['employee']) && $_POST['employee'] != '') || (isset($_POST['period']) && $_POST['period'] != '') )
{
	function getNotes($employee, $semester)
	{
		global $db;
		try {
			$hoursQuery = $db->prepare("SELECT requestedHours, registeredHours, notes FROM scheduleNotes WHERE netID = :employee AND semester = :semester");
			$hoursQuery->execute(array(':employee' => $employee, ':semester' => $semester));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo json_encode($hoursQuery->fetch(PDO::FETCH_ASSOC));
	}
	getNotes($_POST['employee'], $_POST['period']);
	return;
}
?>
