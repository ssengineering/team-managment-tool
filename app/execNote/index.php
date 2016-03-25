<?php //execNote.php this is the new version of the Executive Notification tool.
require('../includes/includeme.php');
require('execNoteFunctions.php');

?>

<h2 align='center'>Executive Notification</h2>
<div align='center'>
<input type='button' value="New" onclick="window.location.href='execNoteForm.php'" />
<input type='button' value="Update" onclick="window.location.href='loadOpen.php?type=Update'" />
<input type='button' value="Resolve" onclick="window.location.href='loadOpen.php?type=Resolve'" />
<input type='button' value="New/Resolve" onclick="window.location.href='execNoteForm.php?type=New/Resolve'" />
<input type='button' value="Re-Open" onclick="window.location.href='loadClosed.php?type=Re-open'" />
</div>

<?php 
require('../includes/includeAtEnd.php');
