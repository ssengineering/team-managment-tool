<?php
require('../includes/includeMeBlank.php');
if(isset($_POST['previous']))
{
	$viewedDate=$_POST['date'];
	//gets start date of current period in view and returns row that is immediatlly before
	try {
		$endQuery = $db->prepare("SELECT DISTINCT `endDate` FROM `scheduleSemesters` WHERE `endDate`<:day AND `area`=:area ORDER BY `endDate` DESC LIMIT 1");
		$endQuery->execute(array(':day' => $viewedDate, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $endQuery->fetch(PDO::FETCH_NUM))
	{
		echo $row[0];
	}
}
if(isset($_POST['next']))
{
	$viewedDate=$_POST['date'];
	try {
		$startQuery = $db->prepare("SELECT `startDate` FROM `scheduleSemesters` WHERE `startDate` > :day AND `area`= :area ORDER BY `startDate` ASC LIMIT 1");
		$startQuery->execute(array(':day' => $viewedDate, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $startQuery->fetch(PDO::FETCH_NUM))
	{
		echo $row[0];
	}
}
?>
