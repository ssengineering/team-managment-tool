<?php
require("../includes/includeMeBlank.php");
require("../includes/email.php");

if ($env < 2)
{
	$to = 'OPS Development <'.getenv("DEV_EMAIL_ADDRESS").'>';
}
else
{
	$to = getenv("BOSS_EMAILS");
}
$subject = 'Student Supervisor Message'; 
$message = wordwrap($_GET['message'].' ~'.getEmployeeNameByNetId($netID), 70);
$email = (object)array(
	"recipients" => $to,
	"subject"    => $subject,
	"message"    => $message,
);
if(sendEmail($email))
{
	echo 'Message sent successfully!';
}
else 
{
	echo 'ERROR: Unable to send message.';
}
 
?>
