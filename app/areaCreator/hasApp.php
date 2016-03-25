<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['areaId']) && isset($_POST['appId']))
{
	$area = $_POST['areaId'];
	$numberOfAreas = explode(",", $area);
	$numberOfAreas = count($numberOfAreas);

	try {
		$appQuery = $db->prepare("SELECT employeeAreas.area as areaNames, appId FROM `link` LEFT JOIN (SELECT * FROM `employeeAreas` WHERE ID=:area) AS employeeAreas 
								  ON `link`.`area`=`employeeAreas`.`ID` WHERE link.appId=:app AND employeeAreas.area IS NOT NULL");
		$appQuery->execute(array(':area' => $_POST['areaId'], ':app' => $_POST['appId']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	if($app = $appQuery->fetch())
	{
		echo "true";
	}
	else
	{
		echo "false";
	}

}
	
?>