<?php //loadOpen.php
//this page will display all of the currently open Executive Notifications to load.

require('../includes/includeme.php');


function printOpenNotes(){
	global $db;
	try {
		$notificationQuery = $db->prepare("SELECT * FROM executiveNotification WHERE status ='0' ORDER BY startDate,startTime ASC");
		$notificationQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	while($cur = $notificationQuery->fetch(PDO::FETCH_ASSOC)){
		echo '<input type="radio" name="id" id="id" value="'.$cur['ID'].'" />Started on: '.date("l, M j, Y",strtotime($cur['startDate'])).', for ticket '.$cur['ticketNum'].', Subject: '.$cur['subject'].'  <a href="deleteNote.php?id='.$cur['ID'].'">Delete</a><br />';
	}
}
?>

<h2>Load an Open Executive Notification</h2>
<form  method=get id='loadExecNote' name='loadExecNote' action='execNoteForm.php' >
<?php if(isset($_GET['type'])){
	echo "<input type='hidden' name='type' id='type' value='".$_GET['type']."'/>";
	}
?>
<div>
<?php printOpenNotes(); ?>
<input type='submit' id='submit' value="Load" />
</form>
<input type='button' id='cancel' value='Cancel' onclick='window.location.href="index.php"' />
</div>


<?php 
	require('../includes/includeAtEnd.php');
?>
