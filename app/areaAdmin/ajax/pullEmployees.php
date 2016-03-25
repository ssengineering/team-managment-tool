<?php
require('../../includes/includeMeBlank.php');

$curArea = $_GET['area'];

try {
	$employeeQuery = $db->prepare("SELECT netID,CONCAT(`firstName`, ' ', `lastName`) AS `name` FROM employee WHERE area = :area AND `active`=1 ORDER BY firstName");
	$employeeQuery->execute(array(':area' => $curArea));
} catch(PDOException $e) {
	exit("error in query");
}
$employees = array();
while($cur = $employeeQuery->fetch(PDO::FETCH_ASSOC)){
	$employees[] = $cur;
}
	$employees = json_encode($employees);
	echo $employees;

?>
