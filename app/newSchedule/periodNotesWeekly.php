<?php
require ('../includes/includeMeBlank.php');
if ( (isset($_POST['employee']) && $_POST['employee'] != '') && (isset($_POST['weekStart']) && $_POST['weekStart'] != '') )
{
	function getNotes($employee, $weekStart)
	{
	    global $area, $db;
		try {
			$notesQuery = $db->prepare("SELECT requestedHours, registeredHours, notes FROM scheduleNotes WHERE netID = :netId AND semester = (SELECT `ID` FROM `scheduleSemesters` WHERE `area` = :area AND :start >= `startDate` AND :end <= `endDate`)");
			$notesQuery->execute(array(':netId' => $employee, ':area' => $area, ':start' => $weekStart, ':end' => $weekStart));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo json_encode($notesQuery->fetch(PDO::FETCH_ASSOC));
	}
	getNotes($_POST['employee'], $_POST['weekStart']);
	return;
}
?>
