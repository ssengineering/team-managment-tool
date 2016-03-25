<?php 
require("../includes/includeMeSimple.php");
//Pulls get data and opens a new window in order to fully view all information about a task

//----------------GET DATA-----------------
$messageId = $_GET['taskId'];

//----------------QUERY the Database to pull the task info-----------------------
try {
	$tasksQuery = $db->prepare("SELECT * FROM routineTasks WHERE ID = :id");
	$tasksQuery->execute(array(':id' => $_GET['taskId']));
} catch(PDOException $e) {
	exit("error in query");
}

$row = $tasksQuery->fetch(PDO::FETCH_ASSOC);
$title = $row['title'];
$owner = nameByNetId($row['creator']);
$editor = nameByNetId($row['editor']);
$message = $row['descr'];


?>
<style type="text/css">
.message {
	background-color:#D5E1DD;
	padding:10px 15px; 
	margin:12px;
}
.header{
	text-align:center;
	margin:12px;
}
</style>
<title>Task: <?php echo $title;?></title>

<div class="header" id="header"> 
<h2><?php echo $title;?></h2>
<p>Created by: <?php echo $owner;?></p>
<p>Edited by: <?php echo $editor;?></p>
</div>
<div class="message" id="message">
	<h2>Message:</h2>
	<?php echo $message;?>
</div>

