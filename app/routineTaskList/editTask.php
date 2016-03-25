<?php
/*
This file calls the appropriate page to edit a routine task.
*/

require('../includes/includeMeSimple.php');
?>
<!---------------------Functions--------------------->
<?php
//-------------------------Initial Query-----------------------
	$taskID = $_GET['taskId'];
	try {
		$tasksQuery = $db->prepare("SELECT * FROM routineTasks WHERE ID=:taskId");
		$tasksQuery->execute(array(':taskId' => $taskID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$task = $tasksQuery->fetch(PDO::FETCH_ASSOC);
	
	if($task['day'] == NULL){
		include("editRepeated.php?ID='".$taskID."'");
	}else{
		include("editOneShot.php?ID='".$taskID."'");
	}

