<?php
//muteTask.php
require('../includes/includeMeBlank.php');

//--------------------GET data-------------------------------
	$taskId = $_GET['id']; //this is the task ID.
	$mutedComments = strip_tags($_GET['comment']); //this is the text from the comment field on the page
	$dateDue = $_GET['date'];

//------------GET TASK TO BE COMPLETED------------------
	try {
		$tasksQuery = $db->prepare("SELECT * FROM routineTasks WHERE ID=:taskId");
		$tasksQuery->execute(array(':taskId' => $taskId));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$task = $tasksQuery->fetch(PDO::FETCH_ASSOC);


//------------SET VARIABLES---------------------
	$title = $task['title'];
	$timeDue = $task['timeDue'];
	$area = $task['area'];
	$muted = '1';
	$mutedBy = nameByNetId($netID);
	$timeMuted = date('G:i');
	$dateMuted = date('Y-m-d');
//------------------------------------------------

//queries the database and then add or updates an entry to the TaskLog
	try {
		$insertQuery = $db->prepare("INSERT INTO routineTaskLog (title,taskId,timeDue,dateDue,area,muted,mutedBy,timeMuted,dateMuted,mutedComments,guid) VALUES (:title,:taskId,:timeDue,:dateDue,:area,:muted,:by,:timeMuted,:dateMuted,:comments,:guid)");
		$insertQuery->execute(array(':title' => $title, ':taskId' => $taskId, ':timeDue' => $timeDue, ':dateDue' => $dateDue, ':area' => $area, ':muted' => $muted, ':by' => $mutedBy, ':timeMuted' => $timeMuted, ':dateMuted' => $dateMuted, ':comments' => $mutedComments, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}	
?>

