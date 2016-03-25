<?php
require("../includes/includeMeBlank.php");
try {
	$notesQuery = $db->prepare("SELECT * FROM `supNotes` WHERE `cleared`=0");
	$notesQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}
$supNoteString = 'Select * from `supNotes` Where `cleared`=0';
	$noteCounter = 0;
	$response = '';
	while ($supNote = $notesQuery->fetch(PDO::FETCH_ASSOC))
	{
		$dateStamp = date("j M",strtotime($supNote['timeSubmitted']));
		if(can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource*/{
			echo "<div style='width: 100%;' id='note".$noteCounter."'><div style='float: left; width: 86%; margin: 0 0 0 2%; padding: 0px 0px 5px 0px;'>".$dateStamp." - ".$supNote['note']."</div><div style='float: left; width: 12%; margin: 0; padding: 0;'><input type='button' value='Close' onclick='clearNote(".$supNote['noteId'].")' /></div></div>";
		} else {
			echo "<div style='width: 100%;' id='note".$noteCounter."'><div style='float: left; width: 86%; margin: 0 0 0 2%; padding: 0px 0px 5px 0px;'>".$dateStamp." - ".$supNote['note']."</div><div style='float: left; width: 12%; margin: 0; padding: 0;'></div></div>";
		}
		$noteCounter++;
	}
	if ($noteCounter == 0)
	{
		echo '<br /><div style="width: 100%; text-align: center; font-weight: bold; margin-bottom: 8px; font-size: 90%;"><table style="margin-left: auto; margin-right: auto;"><tr><th style="height: 2em; vertical-align: middle;">  There are no Turn-over Notes.  </th></tr></table></div>';
	}
?>
