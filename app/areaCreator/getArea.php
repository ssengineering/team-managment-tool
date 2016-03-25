<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['id']))
{
	try {
		$areaQuery = $db->prepare("SELECT * FROM `employeeAreas` WHERE `ID` = :id LIMIT 1");
		$success   = $areaQuery->execute(array(':id' => $_POST['id']));
	} catch(PDOException $e) {
		$success = false;
	}
}
else
{
	try {
		$areaQuery = $db->prepare("SELECT * FROM `employeeAreas` ORDER BY `ID` ASC");
		$success   = $areaQuery->execute();
	} catch(PDOException $e) {
		$success = false;
	}
}
$response = array();
while ($success && $area = $areaQuery->fetch(PDO::FETCH_ASSOC))
{
	$response[] = $area;
}
echo json_encode(array('areas'=>$response));
?>