<?php 
require('../includes/includeMeBlank.php');
//This file pulls all periods that are currently defined for the curent area. It returns them in the form of a JSON array.

try {
	$semestersQuery = $db->prepare("SELECT * FROM `scheduleSemesters` WHERE area = :area AND `endDate` >= DATE_SUB(NOW(), INTERVAL 1 YEAR) ORDER BY `startDate` DESC");
	$semestersQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}

$periods = array();
while($cur = $semestersQuery->fetch(PDO::FETCH_ASSOC))
{
	$periods[] = $cur;
}
echo json_encode($periods);
?>
