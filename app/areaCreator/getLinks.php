<?php
require_once("../includes/includeMeBlank.php");

	if (isset($_POST['areaId']))
	{
		try {
			$linkQuery = $db->prepare("SELECT * FROM `link` WHERE `area` = :area ORDER BY `sortOrder`");
			$success = $linkQuery->execute(array(':area' => $_POST['areaId']));
		} catch(PDOException $e) {
			$success = false;
		}
		if ($success)
		{
			$links = array();
			while ($link = $linkQuery->fetch(PDO::FETCH_ASSOC))
			{
				$links[] = $link;
			}
			echo json_encode(array('status'=>"OK", 'query'=>'', 'links'=>$links));
		}
		else
		{
			echo json_encode(array('status'=>"FAIL", 'query'=>'', 'error'=>"error in query"));
		}
	}
?>