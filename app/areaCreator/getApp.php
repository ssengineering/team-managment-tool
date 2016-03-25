<?php
require_once("../includes/includeMeBlank.php");

	$whereClause = '';
	$area = '0';
	$numberOfAreas="0";
	$queryParams = array();

	if (isset($_POST['id']))
	{
		$whereClause = ' WHERE `appId` IN (:id)';
		$queryParams[':id'] = $_POST['id'];
	}
	if (isset($_POST['areaId']))
	{
		$area = $_POST['areaId'];
		$numberOfAreas = explode(",", $area);
		$numberOfAreas = count($numberOfAreas);
	}
	$queryParams[':area']     = $area;
	$queryParams[':numAreas'] = $numberOfAreas;
	// I do a little bit of MySQL magic here. Normally in SQL I can't just do a group by to remove "duplicate" entries. I do it here to return only one tuple for each pairing of area and app 
	//ORIGINAL QUERY "SELECT `app`.*, IFNULL(`area`,0) AS selected FROM `app` LEFT JOIN (SELECT `area`, `appId` FROM `link` WHERE `area` IN (${area}) GROUP BY `appId`) AS temp ON temp.`appId` = `app`.`appId`".$whereClause;
	
	//New query selects intersection instead of union, it will get all rows of areas selected that have the same appIds
	try {
		$appQuery = $db->prepare("SELECT `app`.*, IFNULL(`area`,0) AS selected FROM `app` LEFT JOIN (SELECT `area`, `appId` FROM `link` WHERE `area` IN (:area) 
								  GROUP BY `appId` HAVING COUNT(DISTINCT `area`) = :numAreas) AS temp ON temp.`appId` = `app`.`appId`".$whereClause);
		$success = $appQuery->execute($queryParams);
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success)
	{
		$apps = array();
		while ($app = $appQuery->fetch(PDO::FETCH_ASSOC))
		{
			$apps[] = $app;
		}
		echo json_encode(array('status'=>"OK", 'query'=>'', 'apps'=>$apps));
	}
	else
	{
		echo json_encode(array('status'=>"FAIL", 'query'=>'', 'error'=>"error in query"));
	}
?>
