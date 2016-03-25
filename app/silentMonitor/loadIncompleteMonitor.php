<?php //loadIncompleteMonitor.php
//this page will display all of the save but not completed monitors in a list and allow users to choose one of them to load.

require('../includes/includeme.php');

function printAvailableMonitors(){
	global $db;
	try {
		$incompleteQuery = $db->prepare("SELECT * FROM silentMonitor WHERE completed ='0' AND `deleted` = '0' ORDER BY submitDate ASC");
		$incompleteQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	while($cur = $incompleteQuery->fetch(PDO::FETCH_ASSOC)) {
		echo '<input type="radio" name="id" id="id" value="'.$cur['index'].'" />Started on: '.date("l, M j, Y",strtotime($cur['submitDate'])).', for '.nameByNetId($cur['netID']).'  <a href="deleteMonitor.php?id='.$cur['index'].'">Delete</a><br />';
	}
}
?>

<h2>Load A Silent Monitor</h2>
<form  method=get id='loadSilentMonitor' name='loadSilentMonitor' action='index.php' >
<div>
<?php printAvailableMonitors(); ?>
<input type='submit' id='submit' value="Load" />
</form>
<input type='button' id='cancel' value='Cancel' onclick='window.location.href="index.php"' />
</div>


<?php 
	require('../includes/includeAtEnd.php');
?>
