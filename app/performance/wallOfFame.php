<?php //wallOFFame.php This is basically just a log that prints out all of the commendable performances that have been made public.

include('../includes/includeme.php');

function printFamousPeople(){
	global $area, $db;
	try {
		$commendableQuery = $db->prepare("SELECT * FROM `reportCommendable` WHERE `public`='1' AND `area`=:area AND `timeStamp` > (NOW() - INTERVAL 3 WEEK) ORDER BY timeStamp DESC");
		$commendableQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	if ($first = $commendableQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<div class='employee'>";
		echo "<img class='employeeImage' src='".getenv("BYU_PI_PHOTO")."?n={$first['employee']}'>";
		echo "<div class='employeeName'>".nameByNetId($first['employee'])."</div>";
		echo "<div class='date'>".$first['date']."</div>";
		echo "<div class='comments'>".$first['reason']."</div>";
		echo "</div>";
		
		while($cur = $commendableQuery->fetch(PDO::FETCH_ASSOC)){
			echo "<div class='employee'>";
			echo "<img class='employeeImage' src='".getenv("BYU_PI_PHOTO")."?n={$cur['employee']}'>";
			echo "<div class='employeeName'>".nameByNetId($cur['employee'])."</div>";
			echo "<div class='date'>".$cur['date']."</div>";
			echo "<div class='comments'>".$cur['reason']."</div>";
			echo "</div>";
		}

		echo "</table>";

	}
	else
	{
		echo "<h2 align='center'>No Commendables awarded in the last week.</h2>";
	}
}

?>
<link rel='stylesheet' type='text/css' href='wallOfFame.css'>

<div id='header'>
	<img id='headerImage' src='wallOfFame.png'>
</div>

<div align='center'>

<?php printFamousPeople(); ?>

</div>

<?php
require('../includes/includeAtEnd.php');
?>
