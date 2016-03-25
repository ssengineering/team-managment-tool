<?php
// Ensure this is run from the correct directory
chdir(dirname(__FILE__));

// THIS IS BEING RUN BY A CRON JOB
require ('../includes/dbconnect.php');

//gets the environment argument passed by the cron job

if (isset($argv[1]))
{
	$environment = $argv[1];
}
else
{
	$environment = 0;
}
try {
	$routineTaskQuery = $db->prepare("SELECT * FROM `routineTaskLog` LEFT JOIN `routineTasks` ON `routineTaskLog`.`taskId`=`routineTasks`.`ID` WHERE `routineTasks`.`title` LIKE '%Security Task%'
		AND ((`routineTaskLog`.`dateDue`=CURDATE() AND `routineTasks`.`timeDue`<'7:00:00') OR (`routineTaskLog`.`dateDue`=Date_SUB(CURDATE(), INTERVAL 1 DAY)
		AND `routineTasks`.`timeDue`>='7:00:00')) ORDER BY `routineTaskLog`.`dateDue`, `routineTaskLog`.`timeDue` ASC");
	$routineTaskQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}
$subject = "Daily security email for " . date("F d");
$dayMessage = "<h2 style='text-align:center'>Daily security email for " . date("F d") . "</h2><br>";
$message = "";
$addedTitles = "";
$count = 0;
$group = '';
$table = "style='border:1px solid #bbbbbb;border-collapse:collapse;border-spacing:0;margin:0 0 9px;padding:5px'";
$th = "style='background:none repeat scroll 0 0 #e0e0e0;border-left:1px solid #c9c9c9;border-right:1px solid #c9c9c9;border:1px solid #bbbbbb;padding:5px'";
$td = "style='border:1px solid #bbbbbb;vertical-align:top;padding:5px'";
while ($row = $routineTaskQuery->fetch(PDO::FETCH_ASSOC))
{
	$count++;
	if (strstr($addedTitles, $row["title"]) === false && $count > 0)
	{
		$message .= "<table $table'>" . $group . "</table>";
	}
	if (strstr($addedTitles, $row["title"]) === false)
	{
		$message .= "<h3>" . $row["title"] . "</b></h3>";
		$addedTitles .= $row["title"] . " ";
		$group = "<tr><th $th>Completed By</th><th $th>Time Completed</th><th $th>Comments</th></tr>";
	}

	$group .= "<tr><td $td >" . $row["completedBy"] . "</td><td $td >" . date("g:i a", strtotime($row["timeCompleted"])) . "</td>";
	$group .= "<td $td >" . nl2br($row['comments']) . "</td></tr>";
}

if ($message == "")
{
	$message .= "<h3 style='text-align:center'>No security taks completed in the last 24 hours</h3>";
}
else
{
	$message .= "<table $table'>" . $group . "</table>";
}

$message = $dayMessage . $message;
echo $message;

$header  = "MIME-Version: 1.0\r\n";
$header .= "Content-type: text/html; charset=utf-8\r\n";
$header .= "From: ".getenv("OPS_EMAIL_ADDRESS")." \r\n";
if ($environment < 2)
{
	$email = getenv("DEV_EMAIL_ADDRESS");
}
else
{
	$email = getenv("SECURITY_EMAIL_ADDRESS");
}
mail($email, $subject, wordwrap($message, 70), $header);
?>
