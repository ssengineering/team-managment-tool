<?php

function clean($string)
{
       	// Removes special chars.
	return preg_replace('/[^A-Za-z0-9\ ]/', '', $string);
}

function getUnapprovedWhiteboards($area)
{
	global $db;
	try {
		$unapprovedQuery = $db->prepare("SELECT `whiteboard`.*, `whiteboardAreas`.`areaId`, `whiteboardAreas`.`approved`, `whiteboardAreas`.`deleted`, `tag`.*, emp.* FROM `whiteboard`
			JOIN `whiteboardAreas` ON `whiteboard`.`messageId` = `whiteboardAreas`.`whiteboardId` JOIN `tag` ON `whiteboard`.`type` = `tag`.`typeId`
			JOIN (SELECT `netID`, `firstName`, `lastName` FROM `employee`) AS emp ON `whiteboard`.`ownerId` = emp.`netID` WHERE `areaId` = :area
			AND `whiteboardAreas`.`approved` = 0 AND `whiteboardAreas`.`deleted` = 0 ORDER BY `postDate` DESC");
		$unapprovedQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	$data = array();
	while ($unapprovedPost = $unapprovedQuery->fetch(PDO::FETCH_ASSOC))
	{
		$unapprovedPost['message'] = html_entity_decode($unapprovedPost['message']);
		$data[] = $unapprovedPost;
	}

	return array('query'=>"", 'error'=>"", 'data'=>$data);
}

function getWhiteboards($curUser, $area, $mandatory, $primaryTag, $kb, $postedBy, $start, $end, $text)
{
	global $db;
	// Build query parts based off of input variables
	$queryString = '';
	$queryParams = array();
	if ($mandatory != '')
	{
		$queryString .= " AND `mandatory` = :mandatory ";
		$queryParams[':mandatory'] = $mandatory;
	}
	if ($primaryTag != '')
	{
		$queryString .= " AND `type` = :primaryTag ";
		$queryParams[':primaryTag'] = $primaryTag;
	}
	if ($kb != '')
	{
		$queryString .= " AND `kb` = :kb ";
		$queryParams[':kb'] = $kb;
	}
	if ($postedBy != '')
	{
		// Break up posted by into words and find any whiteboards posted by either that netID or name
		$postedByNames = explode(' ', clean($postedBy));

		// Join all of the possible name searches
		$queryString .= " AND (emp.`netID` IN (:names) OR `firstName` IN (:names1) OR `lastName` IN (:names2)) ";
		$queryParams[':names']  = implode("','", $postedByNames);
		$queryParams[':names1'] = implode("','", $postedByNames);
		$queryParams[':names2'] = implode("','", $postedByNames);
	}
	// If no start or end is given, just return active posts
	if ($start == '' && $end == '')
	{
		$today = date('Y-m-d');
		$queryString .= " AND `postDate` <= :post AND `expireDate` > :expire ";
		$queryParams[':post']   = $today." 23:59:59";
		$queryParams[':expire'] = $today;
	}
	else
	{
		if ($start != '')
		{
			$queryString .= " AND (`postDate` >= :startPost OR `expireDate` >= :startExpire) ";
			$queryParams[':startPost']   = $start;
			$queryParams[':startExpire'] = $start;
		}
		if ($end != '')
		{
			$queryString .= " AND (`postDate` <= :endPost OR `expireDate` <= :endExpire) ";
			$queryParams[':endPost']   = $end;
			$queryParams[':endExpire'] = $end;
		}
	}
	if ($text != '')
	{
		$cleanedTextArray = explode(' ', clean($text));
		// I wish we had a newer version of MySQL so we could do Full-text Searches
		$queryString .= " AND (CONCAT(`title`, ' ', `message`) LIKE :word0 ";
		$queryParams[':word0'] = '%'.$cleanedTextArray[0].'%';
		for($i = 1; $i < count($cleanedTextArray); $i++) {
			$queryString .= " OR CONCAT(`title`, ' ', `message`) LIKE :word".$i." ";
			$queryParams[':word'.$i] = '%'.$cleanedTextArray[$i].'%';
		}
		$queryString .= ") ";
	}

	if ($curUser)
	{
		$hasReadSelect = ' IF(mandatory.`msgID`, 1, 0) as hasRead, ';
		$hasReadJoin = "LEFT JOIN `whiteboardMandatoryLog` AS mandatory ON mandatory.netID = :user AND `whiteboard`.`messageId` = mandatory.`msgID`";
		$queryParams[':user'] = $curUser;
	}
	else
	{
		$hasReadSelect = '';
		$hasReadJoin = "";
	}

	$query = "SELECT `whiteboard`.*, whiteAreas.*,".$hasReadSelect." `tag`.*, emp.* FROM `whiteboard` JOIN (SELECT `whiteboardAreas`.*, `employee`.`firstName` AS approverFirst, `employee`.`lastName`
		AS approverLast FROM `whiteboardAreas` LEFT JOIN `employee` ON `whiteboardAreas`.`approvedBy` = `employee`.`netID`) AS whiteAreas ON `whiteboard`.`messageId` = whiteAreas.`whiteboardId`
		".$hasReadJoin." JOIN `tag` ON `whiteboard`.`type` = `tag`.`typeId` JOIN (SELECT `netID`, `firstName`, `lastName` FROM `employee`) AS emp ON `whiteboard`.`ownerId` = emp.`netID`
		WHERE `areaId` = :area ".$queryString." AND whiteAreas.`approved` = 1 AND whiteAreas.`deleted` = 0 ORDER BY `postDate` DESC";
	$queryParams[':area'] = $area;
	try {
		$whiteboardQuery = $db->prepare($query);
		$whiteboardQuery->execute($queryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
	$whiteboardArray = array();
	while($row = $whiteboardQuery->fetch(PDO::FETCH_ASSOC))
	{
		$row['message'] = html_entity_decode($row['message']);
		$whiteboardArray[] = $row;
	}

	return array('query'=>$query, 'error'=>"", 'data'=>$whiteboardArray);
}

function getPeoples($msgId,$area)
{
	global $db;
	if($msgId == NULL)
	{
		return array();
	}

	/* This is to get a list of employees for the current area and determine whether or not the employee has read the whiteboard post specified. */
	try {
		$employeeQuery = $db->prepare("SELECT `employee`.*, IF(`msgId`, 1, 0) AS hasRead FROM employee LEFT JOIN (SELECT * FROM whiteboardMandatoryLog WHERE `msgID`=:message) AS ml ON `employee`.`netID` = ml.`netID` WHERE area=:area AND `active` = 1 ORDER BY IF(`msgId`, 1, 0) ASC, firstName ASC, lastName ASC");
		$employeeQuery->execute(array(':message' => $msgId, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	$peeps = array();
	while($people = $employeeQuery->fetch(PDO::FETCH_ASSOC))
	{
		$peeps[] = $people;
	}
	return array('query'=>$query2, 'error'=>"", 'data'=>$peeps);
}

?>
