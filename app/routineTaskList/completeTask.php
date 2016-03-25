<?php
//completeTask.php
require('../includes/includeMeBlank.php');

//-----------GET data------------------------------
	$taskId = $_REQUEST['id']; //this is the task ID.
	$comments = strip_tags($_REQUEST['comment']); //this is the text from the comment field on the page
	$dateDue = $_REQUEST['date'];
//------------TASK TO BE COMPLETED------------------
	try {
		$tasksQuery = $db->prepare("SELECT * FROM routineTasks WHERE ID=:id");
		$tasksQuery->execute(array(':id' => $_REQUEST['id']));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$task = $tasksQuery->fetch(PDO::FETCH_ASSOC);


//------------SET VARIABLES---------------------
	$title = $task['title'];
	$timeDue = $task['timeDue'];
	$area = $task['area'];
	$completed = '1';
	$timeCompleted = date('G:i');
	$dateCompleted = date('Y-m-d');
	$completedBy = nameByNetId($netID);
//------------------------------------------------

	//Query to test whether this task is in the log already ie. its been muted.
	try {
		$logQuery = $db->prepare("SELECT * FROM routineTaskLog WHERE taskId =:taskId AND (dateMuted IS NOT NULL AND dateCompleted IS NULL)");
		$logQuery->execute(array(':taskId' => $taskId));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$mutedTask = $logQuery->fetch(PDO::FETCH_ASSOC);

	$logID = $mutedTask['ID'];

//queries the database and then add or updates an entry to the TaskLog	
	try {
		$insertQuery = $db->prepare("INSERT INTO routineTaskLog (ID,title,taskId,timeDue,dateDue,area,completed,completedBy,timeCompleted,dateCompleted,comments,guid) VALUES (:id,:title,:taskId,:timeDue,:dateDue,:area,:completed,:by,:timeCompleted,:dateCompleted,:comments,:guid) ON DUPLICATE KEY UPDATE title=:title2,taskId=:taskId2,timeDue=:timeDue2,area=:area2,completed=:completed2,completedBy=:by2,timeCompleted=:timeCompleted2,dateCompleted=:dateCompleted2,comments=:comments2");
		$insertQuery->execute(array(':id' => $logID, ':title' => $title, ':taskId' => $taskId, ':timeDue' => $timeDue, ':dateDue' => $dateDue, ':area' => $area, ':completed' => $completed, ':by' => $completedBy, ':timeCompleted' => $timeCompleted, ':dateCompleted' => $dateCompleted, ':comments' => $comments, ':guid' => newGuid(), ':title2' => $title, ':taskId2' => $taskId, ':timeDue2' => $timeDue, ':area2' => $area, ':completed2' => $completed, ':by2' => $completedBy, ':timeCompleted2' => $timeCompleted, ':dateCompleted2' => $dateCompleted, ':comments2' => $comments));
	} catch(PDOException $e) {
		exit("error in query");
	}	
?>
