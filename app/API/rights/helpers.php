<?php //helpers.php

/**
 * Gets all the rights for a level
 *
 * @param $level The level number
 * @param $area  The area
 * @param $netID The netID of the employee
 *
 * @return array(associative array(ID,name,description,rightType('EMAIL','BASIC'),level,status(0 = not requested, 1 = requested, 2 = has right, 3 = revoked) ))
 */
function getRightsByLevel($level, $area, $netID)
{
	global $db;
	try {
		$rightsQuery = $db->prepare("SELECT * FROM employeeRights WHERE rightLevel=:level AND area=:area ORDER BY rightName ASC");
		$rightsQuery->execute(array(':level' => $level, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$rightsArray = array();
	while($right = $rightsQuery->fetch(PDO::FETCH_ASSOC)) {
		$status = checkRightStatus($right['ID'], $netID);
		$rightsArray[] = array('ID' => $right['ID'], 'name' => $right['rightName'], 'description' => $right['description'], 'type' => $right['rightType'], 'level' => $right['rightLevel'], 'status' => $status);
	}
	return $rightsArray;
}

/**
 * Gets the right's status for the given employee and right
 *
 * @param $rightID The id of the right
 * @param $netID   The employee's netID
 *
 * @return associative array: (status(0 = not requested, 1 = requested, 2 = has right, 3 = revoked),
 *		requestedBy(netID of the person who requested the right for the employee), requestedDate, 
 *		updatedBy(netID of the person who granted the right for the employee), updatedDate, 
 *		removedBy(netID of the person who revoked the right for the employee), removedDate)
 */
function checkRightStatus($rightID, $netID)
{
	global $db;
	try {
		$statusQuery = $db->prepare("SELECT * FROM employeeRightsStatus WHERE netID=:netID AND rightID=:rightID");
		$statusQuery->execute(array(':netID' => $netID, ':rightID' => $rightID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$right = $statusQuery->fetch(PDO::FETCH_ASSOC);
	$status = 0;
	if($right == "") { //If right has not been requested
		$status = 0;	
	} else if($right['rightStatus'] == 1) { //If right has been requested
		$status = 1;
	} else if($right['rightStatus'] == 2) { //If user has right
		$status = 2;
	} else { //If the user has previously had the right revoked
		$status = 3;
	}
	return array('status' => $status, 'requestedBy' => $right['requestedBy'], 'requestedDate' => $right['requestedDate'], 'updatedBy' => $right['updatedBy'], 'updatedDate' => $right['updatedDate'],
				 'removedBy' => $right['removedBy'], 'removedDate' => $right['removedDate']);
}

/**
 * Checks whether an employee has all the rights for a given level
 *
 * @param $rightLevel The level number
 * @param $netID      The employee's netID
 * @param $area       The employee's area
 * 
 * @return true if employee has all the rights, false if not
 */
function checkAllRightStatus($rightLevel, $netID, $area)
{
	global $db;
	try {
		$rightsQuery = $db->prepare("SELECT * FROM employeeRights WHERE rightLevel=:level AND area=:area");
		$rightsQuery->execute(array(':level' => $rightLevel, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($right = $rightsQuery->fetch(PDO::FETCH_ASSOC)) {
		try {
			$statusQuery = $db->prepare("SELECT * FROM employeeRightsStatus WHERE netID=:netID AND rightID=:right");
			$statusQuery->execute(array(':netID' => $netID, ':right' => $right['ID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$status = $statusQuery->fetch();
		if(!$status) {
			return false;
		} else if($status->rightStatus != 2) {
			return false;
		}
	}
	return true;
}

/**
 * Creates a status for the employee and the right in the database
 *
 * @param $right    The id of the right whose status is being created
 * @param $employee The employee's netID
 * @param $manager  The manager's netID
 * @param $type     The right's type ('EMAIL', 'BASIC')
 */
function createRightStatus($right,$employee,$manager,$type)
{
	global $db;
	$date = date('Y-m-d');			
	if($type == "EMAIL") {
		try {
			$insertQuery = $db->prepare("INSERT INTO employeeRightsStatus (netID,rightID,rightStatus,requestedBy,requestedDate,guid) VALUES (:employee,:right,'1',:manager,:day,:guid) ON DUPLICATE KEY UPDATE requestedBy=:manager1,requestedDate=:day1,rightStatus='1'");
			$insertQuery->execute(array(':employee' => $employee, ':right' => $right, ':manager' => $manager, ':day' => $date, ':guid' => newGuid(), ':manager1' => $manager, ':day1' => $date));
		} catch(PDOException $e) {
			exit("error in query");
		}
	} else if($type == "BASIC") {
		try {
			$insertQuery = $db->prepare("INSERT INTO employeeRightsStatus (netID,rightID,rightStatus,updatedBy,updatedDate,guid) VALUES (:employee,:right,'2',:manager,:day,:guid) ON DUPLICATE KEY UPDATE updatedBy=:manager1,updatedDate=:day1,rightStatus='2'");
			$insertQuery->execute(array(':employee' => $employee, ':right' => $right, ':manager' => $manager, ':day' => $date, ':guid' => newGuid(), ':manager1' => $manager, ':day1' => $date));
		} catch(PDOException $e) {
			exit("error in query");
		}
	} else {
		return;
	}
}

/**
 * Updates an employee's right status in the database
 *
 * @param $right       The id of the right whose status is being updated
 * @param $employee    The employee's netID
 * @param $manager     The manager's netID
 * @param $howToUpdate The way to update the status('confirm', 'terminate', 'activate')
 */
function updateRightStatus($right,$employee,$manager,$howToUpdate)
{
	global $db;
	$date = date('Y-m-d');
	if($howToUpdate == "confirm") {
		try {
			$confirmQuery = $db->prepare("UPDATE employeeRightsStatus SET rightStatus='2',updatedBy=:manager,updatedDate=:day WHERE rightID=:right AND netID=:employee");
			$confirmQuery->execute(array(':manager' => $manager, ':day' => $date, ':right' => $right, ':employee' => $employee));
		} catch(PDOException $e) {
			exit("error in query");
		}
	} else if($howToUpdate == "terminate") {
		try {
			$terminateQuery = $db->prepare("UPDATE employeeRightsStatus SET rightStatus='3',removedBy=:manager,removedDate=:day WHERE rightID=:right AND netID=:employee");
			$terminateQuery->execute(array(':manager' => $manager, ':day' => $date, ':right' => $right, ':employee' => $employee));
		} catch(PDOException $e) {
			exit("error in query");
		}
	} else if($howToUpdate == "activate") {
		try {
			$deleteQuery = $db->prepare("DELETE FROM employeeRightsStatus WHERE rightID=:right AND netID=:employee");
			$deleteQuery->execute(array(':right' => $right, ':employee' => $employee));
		} catch(PDOException $e) {
			exit("error in query");
		}
	} else {
		return;
	}
}

/**
 * Sends an email
 *
 * @param $rightID  The id of the right the email is about
 * @param $whatKind The type of email being sent('activation', 'termination')
 * @param $employee The employee's netID
 * @param $manager  The manager's netID
 * @param $env      The environment being worked in (0 = dev, 1 = stg, 2 = prod)
 */
function sendEmail($rightID,$whatKind,$employee,$manager,$env)
{
	global $db;
	try {
		$emailQuery = $db->prepare("SELECT * FROM employeeRightsEmails WHERE rightID=:right");
		$emailQuery->execute(array(':right' => $rightID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$emailInfo = $emailQuery->fetch(PDO::FETCH_ASSOC);
	$employeeIDNumber = getEmployeeByuIdByNetId($employee); 
	if($env == 2) {
		if($whatKind == "activation") {
			$body = $emailInfo['add_body']."\n\nName: ".nameByNetId($employee)."\n\nUser: ".$employee."\n\nBYU ID: ".$employeeIDNumber."\n\n";
			mail($emailInfo['address'],$emailInfo['add_title'],$body,"From:".$manager."\r\ncc:".$emailInfo['cc']);

		} else if($whatKind == "termination") {
			$body = $emailInfo['del_body']."\n\nName: ".nameByNetId($employee)."\n\nUser: ".$employee."\n\nBYU ID: ".$employeeIDNumber."\n\n";
			mail($emailInfo['address'],$emailInfo['del_title'],$body,"From:".$manager."\r\ncc:".$emailInfo['cc']);
			
		} else {
			return;
		}
	} else {
		if($whatKind == "activation") {
			$body = $emailInfo['address']."\n".$emailInfo['cc']."\n".$emailInfo['add_body']."\n\nName: ".nameByNetId($employee)."\n\nUser: ".$employee."\n\nBYU ID: ".$employeeIDNumber."\n\n";
			if (mail(getenv("DEV_EMAIL_ADDRESS"),$emailInfo['add_title'],$body,"From:".$manager."\r\ncc:".getenv("DEV_EMAIL_ADDRESS"))) {
				echo "Sent Activation email";
			} else {
				echo "Failed to send activation email.";
			}
		} else if($whatKind == "termination") {
			$body = $emailInfo['address']."\n".$emailInfo['cc']."\n".$emailInfo['del_body']."\n\nName: ".nameByNetId($employee)."\n\nUser: ".$employee."\n\nBYU ID: ".$employeeIDNumber."\n\n";
			if (mail(getenv("DEV_EMAIL_ADDRESS"),$emailInfo['del_title'],$body,"From:".$manager."\r\ncc:".getenv("DEV_EMAIL_ADDRESS"))) {
				echo "Sent Termination email";
			} else {
				echo "Failed to send termination email.";
			}
		} else {
			echo "Complete and utter failure.";
			return;
		}		
	}
}

/**
 * Identifies the employee's certification level and updates the employee's certification level in the database
 *
 * @param $employee The employee's netID
 * @param $area     The current area
 */
function checkCertification($employee, $area)
{
	global $db;
	$certificationLevel = "New Hire";
	try {
		$levelsQuery = $db->prepare("SELECT * FROM employeeRightsLevels WHERE area=:area ORDER BY level ASC");
		$levelsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $levelsQuery->fetch()) {
		if(checkAllRightStatus($cur->level,$employee,$area)) {
			$certificationLevel = $cur->name;
		} else {
			break;
		}
	}
	updateCertLevel($employee,$certificationLevel);
}

/**
 * Updates the employee's certification level in the database
 *
 * @param $employee  The employee's netID
 * @param $certLevel The certification level (string) the employee receives
 */
function updateCertLevel($employee,$certLevel)
{
	global $db;
	try {
		$updateQuery = $db->prepare("UPDATE employee SET certificationLevel=:certLevel WHERE netID=:employee");
		$updateQuery->execute(array(':certLevel' => $certLevel, ':employee' => $employee));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

?>
