<?php //preview.php shows the preview of the e-mail to be sent.
require('../includes/includeme.php');


var_dump($_POST);


function formEmail($postData){
		
	echo "Subject: ".getEmailType($postData['type'])."Executive Notification: ";
	echo $postData['subject'];
	echo "\nNotification Time: ".$postData['time'];
	echo "\nNotification Date: ".date('m/d/Y',strtotime($postData['date']));
	echo "\nParent Ticket: INC".$postData['parentTicket'];
	echo "\nPriority: ".$postData['priority'];
	echo "\nIncident Coordinator: ".nameByNetId($postData['ic']);
	echo "\nProblem Description: ".$postData['desc'];


}

function getEmailType($type){
	if($type == "New"){
		return "NEW -- ";
	}else if( $type == "Update"){
		return "UPDATE -- ";
	}else if($type == "Resolve"){
		return "RESOLVED -- ";
	}else if($type == "New/Resolve"){
		return "NEW/RESOLVED -- ";
	}else if($type == "Re-open"){
		return "RE-OPENED -- ";
	}


}
?>

<h2>Preview of Executive Notification Email</h2>
<a href='execNoteForm.php'>Return</a><br/>
<form method='post' action='sendEmail.php'>
<textarea id='email' name='email' cols=100 rows=10>
<?php formEmail($_POST); ?>
</textarea>
<br/>
<input type='submit' id='submit' name='submit' value="Send Email" />
</form>


<?php
require('../includes/includeAtEnd.php');
?>
