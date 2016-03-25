<?php //API/rights/index.php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');
require_once("helpers.php");

/**
 * This is the API for the rights checklist. Calls can be made to the following URls
 *		/API/rights/create
 *		/API/rights/edit/[id]
 *			id is any integer that represents the ID of the right being affected
 *		/API/rights/delete/[id]
 *			id is any integer that represents the ID of the right being deleted
 *		/API/rights/print/[type]
 *			type: one of the following strings: 'main', 'manager', or 'edit'
 *		/API/rights/terminate
 *		/API/rights/request
 *		/API/rights/revoke
 *		/API/rights/confirm
 *		/API/rights/email
 *
 * See the corresponding functions for descriptions of parameters and what they do.
 */
// Process request
$URI = $_SERVER['REQUEST_URI'];
$url = explode('/', $URI);
$params = array();
foreach($url as $parameter) {
	if(!($parameter == "API" || $parameter == "rights" || $parameter == "")) {
		$params[] = $parameter;
	}
}

//test for proper input, and on bad input set $function = "" so that no action is taken
//store the function name in $function, and the parameter, if any, in $param 
$function = "";
$param = "";
if(isset($params[0])) {
	$function = $params[0];
	if(isset($params[1])) {
		if(!($function == "edit" || $function == "delete" || $function == "print")) {
			$function == "";
		} else if($function == "edit" || $function == "delete") {
			if(!is_numeric($params[1])) {
				$function = "";
			} else {
				$param = $params[1];
			}
		} else if($function == "print") {
			if($params[1] == "main" || $params[1] == "manager" || $params[1] == "edit") {
				$param = $params[1];
			} else {
				$function = "";
			}
		}
	}
}

//execute the corresponding function, or do nothing on bad input
switch($function) {
	case "create":
		createRight($_POST);
		break;
	case "edit":
		editRight($param, $_POST);
		break;
	case "delete":
		deleteRight($param);
		break;
	case "print":
		printRights($param, $_POST);
		break;
	case "terminate":
		terminateRight($_POST);
		break;
	case "request":
		requestRights($_POST);
		break;
	case "revoke":
		revokeRights($_POST);
		break;
	case "confirm":
		confirmRights($_POST);
		break;
	case "email":
		emailAll($_POST);
		break;
	default:
		break;
}

/**
 * Adds a new right into the database, along with the email if it is an email type right.
 *
 * @param $data associative array containing: name,descr,rightType,rightLevel,area, and if type email: to,cc,addTitle,addBody,delTitle,delBody
 */
function createRight($data)
{
	global $db;
	try {
		$query = $db->prepare("INSERT INTO employeeRights (rightName,description,rightType,rightLevel,area,guid) 
							   VALUES (:name,:descr,:type,:level,:area,:guid) ON DUPLICATE KEY UPDATE 
							   rightName=:name1, description=:descr1, rightType=:type1, rightLevel=:level1, area=:area1");
		$query->execute(array(':name'   => addSlashes($data['name']),  ':descr'  => addSlashes($data['descr']), 
							  ':type'   => $data['rightType'],         ':level'  => $data['rightLevel'],
							  ':area'   => $data['area'],              ':guid'   => newGuid(),
							  ':name1'  => addSlashes($data['name']),
		 					  ':descr1' => addSlashes($data['descr']), ':type1'  => $data['rightType'],
		 					  ':level1' => $data['rightLevel'],        ':area1'  => $data['area']));
	} catch(PDOException $e) {
		exit("error in query");
	}

	//get the id of the new right to use in the email insertion
	$newRightId = $db->lastInsertId();

	//If the type is an Email then we need to add or update the email data in the employeeRightsEmails table.
	if($data['rightType'] == "EMAIL") {
		try {
			$query = $db->prepare("INSERT INTO employeeRightsEmails (rightID,address,cc,add_title,add_body,del_title,del_body,guid) 
								   VALUES (:id,:to,:cc,:addTitle,:addBody,:delTitle,:delBody,:guid) ON DUPLICATE KEY UPDATE 
								   address=:to1, cc=:cc1, add_title=:addTitle1, add_body=:addBody1, del_title=:delTitle1, del_body=:delBody1");
			$query->execute(array(':id'       => $newRightId,      ':to'        => $data['to'], 
								  ':cc'       => $data['cc'],      ':addTitle'  => $data['addTitle'],
								  ':addBody'  => $data['addBody'], ':delTitle'  => $data['delTitle'], 
								  ':delBody'  => $data['delBody'], ':guid'      => newGuid(),
								  ':to1'      => $data['to'],
								  ':cc1'      => $data['cc'],      ':addTitle1' => $data['addTitle'],
								  ':addBody1' => $data['addBody'], ':delTitle1' => $data['delTitle'],
								  ':delBody1' => $data['delBody']));
		} catch(PDOException $e) {
			exit("error in query");
		}	
	}
	echo json_encode(array('id' => $newRightId));
}

/**
 * Updates database to reflect the changes made on the edit rights dialog.
 * It also edits the email entry if it is an email type right
 *
 * @param $id   int The id of the right to edit 
 * @param $data associative array containing: name,descr,rightType,rightLevel, and if type email: to,cc,addTitle,addBody,delTitle,delBody
 */
function editRight($id, $data)
{
	global $db;
	try {
		$emailTypeQuery = $db->prepare("SELECT rightType FROM employeeRights WHERE ID=:id");
		$emailTypeQuery->execute(array(':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$originalType = "";
	if ($row = $emailTypeQuery->fetch()) {
		$originalType = $row->rightType;
	}

	try {
		$rightsQuery = $db->prepare("UPDATE employeeRights SET rightName=:name, description=:descr, rightType=:type, rightLevel=:level WHERE ID=:id");
		$rightsQuery->execute(array(':name' => $data['name'],      ':descr' => $data['descr'],
									':type' => $data['rightType'], ':level' => $data['rightLevel'], ':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}

	if($data['rightType'] == "EMAIL" && $originalType == "EMAIL") {
		//update the email entry
		try {
			$updateQuery = $db->prepare("UPDATE employeeRightsEmails SET address=:to,cc=:cc,add_title=:addTitle,add_body=:addBody,del_title=:delTitle,del_body=:delBody WHERE rightID=:id");
			$updateQuery->execute(array(':to'       => $data['to'],       ':cc'      => $data['cc'],
								  		':addTitle' => $data['addTitle'], ':addBody' => $data['addBody'], 
									    ':delTitle' => $data['delTitle'], ':delBody' => $data['delBody'], ':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}

	} else if($data['rightType'] == "BASIC" && $originalType == "EMAIL"){
		//remove all email entries in case the right is changed from email to basic
		try {
			$deleteQuery = $db->prepare("DELETE FROM employeeRightsEmails WHERE rightID=:id");
			$deleteQuery->execute(array(':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
	} else if($data['rightType'] == "EMAIL" && $originalType == "BASIC") {
		//insert email if changed from basic type to email type
		try {
			$insertQuery = $db->prepare("INSERT INTO employeeRightsEmails (rightID,address,cc,add_title,add_body,del_title,del_body,guid) 
										 VALUES (:id,:to,:cc,:addTitle,:addBody,:delTitle,:delBody,:guid)");
			$insertQuery->execute(array(':id'      => $id,              ':to'       => $data['to'], 
										':cc'      => $data['cc'],      ':addTitle' => $data['addTitle'],
										':addBody' => $data['addBody'], ':delTitle' => $data['delTitle'], 
										':delBody' => $data['delBody'], ':guid'     => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}

/**
 * Removes a right from the database 
 *
 * @param $id int The id of the right to remove
 */
function deleteRight($id)
{
	global $db;
	try {
		$deleteQuery = $db->prepare("DELETE FROM employeeRights WHERE ID=:id");
		$deleteQuery->execute(array(':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

/**
 * Controls printing of all rights lists. The $type determines what it prints
 *
 * @param $type string 
 *		'main': 	sends information to print the list with the ability to request, confirm, and terminate rights
 *		'manager:'	sends information to print the rights manager list with the options to create, edit, and delete rights
 *						and also to create, edit, and delete levels.
 *		'edit':		send information to load into the edit rights dialog
 * @return a json encoded array containing the data necessary to load the pages
 *		for manager: array(levels), array(rights)
 *			level: name, level(level number)
 *			right: ID, name, description, type, level
 *		for main: name, certLevel, array(levels), array(int)
 *			level: name, level(level number)
 *			array(int) array(rights), status
 *				right: ID, name, description, type, level
 *				status: status(0 = all rights confirmed granted, 1 = no rights requested, 2 = not all rights confirmed),
 *					requestedBy, requestedDate, updatedBy, updatedDate, removedBy, removedDate
 *		for edit: associative array
 *			array: name, description, type, level
 *				If type 'EMAIL' it will also hold: to, cc, addTitle, addBody, delTitle, delBody
 */
function printRights($type, $data)
{
	global $db;
	if($type == "manager") {
		try {
			$levelsQuery = $db->prepare("SELECT * FROM employeeRightsLevels WHERE area=:area ORDER BY level ASC");
			$levelsQuery->execute(array(':area' => $data['area']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$levelsArray = array();
		$rightsArray = array();
		while($level = $levelsQuery->fetch(PDO::FETCH_ASSOC)) {
			$levelsArray[] = array('name' => $level['name'], 'level' => $level['level']);
			$rightsArray[] = getRightsByLevel($level['level'], $data['area'], $data['netId']); //Function in helpers.php
		}
		
		echo json_encode(array('levels' => $levelsArray, 'rights' => $rightsArray));

	} else if($type == "main") {
		try {
			$nameQuery = $db->prepare("SELECT firstName, lastName FROM employee WHERE netID=:netID");
			$nameQuery->execute(array(':netID' => $data['netId']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$nameResult = $nameQuery->fetch();
		$name = $nameResult->firstName." ".$nameResult->lastName;

		try {
			$certLevelQuery = $db->prepare("SELECT certificationLevel FROM employee WHERE netID=:netID");
			$certLevelQuery->execute(array(':netID' => $data['netId']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$certificationResult = $certLevelQuery->fetch();
		$certLevel = $certificationResult->certificationLevel;

		try {
			$parentQuery = $db->prepare("SELECT * FROM employeeRightsLevels WHERE area=:area ORDER BY level ASC");
			$parentQuery->execute(array(':area' => $data['area']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$levelsArray = array();
		$rightsArray = array();
		$statusesArray = array();
		while($level = $parentQuery->fetch(PDO::FETCH_ASSOC)) {
			$levelsArray[] = array('name' => $level['name'], 'level' => $level['level']);
			$rightsArray[] = getRightsByLevel($level['level'], $data['area'], $data['netId']); //Function in helpers.php
		}
		echo json_encode(array('name' => $name, 'certLevel' => $certLevel, 'levels' => $levelsArray, 'rights' => $rightsArray, 'status' => $statusesArray));
		
	} else if($type == "edit") {
		try {
			$rightsQuery = $db->prepare("SELECT * FROM employeeRights WHERE ID=:id");
			$rightsQuery->execute(array(':id' => $data['id']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($right = $rightsQuery->fetch(PDO::FETCH_ASSOC)) {
			$rightArray                = array();
			$rightArray['name']        = $right['rightName'];
			$rightArray['description'] = $right['description'];
			$rightArray['type']        = $right['rightType'];
			$rightArray['level']       = $right['rightLevel'];
			if($rightArray['type'] == "EMAIL") {
				try {
					$emailQuery = $db->prepare("SELECT * FROM employeeRightsEmails WHERE rightID=:right");
					$emailQuery->execute(array(':right' => $data['id']));
				} catch(PDOException $e) {
					exit("error in query");
				}
				if($email = $emailQuery->fetch(PDO::FETCH_ASSOC)) {
					$rightArray['to']       = $email['address'];
					$rightArray['cc']       = $email['cc'];
					$rightArray['addTitle'] = $email['add_title'];
					$rightArray['addBody']  = $email['add_body'];
					$rightArray['delTitle'] = $email['del_title'];
					$rightArray['delBody']  = $email['del_body'];
				} else {
					return;
				}
			}
			echo json_encode($rightArray);
		} else {
			return;
		}
	}
}

/**
 * Confirms that rights are granted
 * @param $data associative array containing: right,employee,manager,area
 */
function confirmRights($data)
{
	global $db;
	updateRightStatus($data['right'],$data['employee'],$data['manager'],"confirm");
	if($data['area'] == '2') {
		checkCertification($data['employee']); //Function in helpers.php
	}
}

/**
 * Revokes a certain right for the employee
 *
 * @param $data associative array containing: right,employee,noEmail,manager,env
 */
function terminateRight($data)
{
	global $db;
	try {
		$typeQuery = $db->prepare("SELECT rightType FROM employeeRights WHERE ID=:right");
		$typeQuery->execute(array(':right' => $data['right']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$tCheck = $typeQuery->fetch();
	$type = $tCheck->rightType;
	if($type == "EMAIL") {		
		if ($data['noEmail'] == "false") {
			sendEmail($data['right'],"termination",$data['employee'],getEmployeeEmailByNetId($data['manager']),$data['env']); //Function in helpers.php
		}
	}
	updateRightStatus($data['right'],$data['employee'],$data['manager'],"terminate"); //Function in helpers.php
}

/**
 * Confirms that a right has been granted for basic rights, and sends an activation email for email type rights
 *
 * @param $data associative array containing: right,noEmail,employee,manager,env,area
 */
function requestRights($data)
{
	global $db;
	try {
		$typeQuery = $db->prepare("SELECT rightType FROM employeeRights WHERE ID=:right");
		$typeQuery->execute(array(':right' => $data['right']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$tCheck = $typeQuery->fetch();
	$type = $tCheck->rightType;
	if($type == "EMAIL") {
		if ($data['noEmail'] == "false") {
			sendEmail($data['right'],"activation",$data['employee'],getEmployeeEmailByNetId($data['manager']),$data['env']); //Function in helpers.php
		}
	} else {
		if($data['area'] == '2') {
			checkCertification($data['employee']); //Function in helpers.php
		}
	}
	createRightStatus($data['right'],$data['employee'],$data['manager'],$type); //Function in helpers.php
}

/**
 * Sends all termination emails for an employee for a certain level
 *
 * @param $data associative array containing: level,area,noEmail,employee,manager,env
 */
function revokeRights($data) 
{
	global $db;
	try {
		$rightsQuery = $db->prepare("SELECT * FROM employeeRights WHERE rightLevel=:level AND area=:area AND rightType='EMAIL'");
		$rightsQuery->execute(array(':level' => $data['level'], ':area' => $data['area']));
	} catch(PDOException $e) {
		exit("error in query");
	}

	while($right = $rightsQuery->fetch(PDO::FETCH_ASSOC)){
		if ($data['noEmail'] == "false") {
			sendEmail($right['ID'],"termination",$data['employee'],getEmployeeEmailByNetId($data['manager']),$data['env']); //Function in helpers.php
		}
		updateRightStatus($right['ID'],$data['employee'],$data['manager'],"terminate"); //Function in helpers.php
	}
}

/**
 * Sends all activation emails for an employee in a given level
 *
 * @param $data associative array containing: level,area,noEmail,employee,manager,env
 */
function emailAll($data)
{
	global $db;
	try {
		$rightsQuery = $db->prepare("SELECT * FROM employeeRights WHERE rightLevel=:level AND area=:area AND rightType='EMAIL'");
		$rightsQuery->execute(array(':level' => $data['level'], ':area' => $data['area']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($right = $rightsQuery->fetch(PDO::FETCH_ASSOC)){
		createRightStatus($right['ID'],$data['employee'],$data['manager'],"EMAIL"); //Function in helpers.php
		if ($data['noEmail'] == "false") {
			sendEmail($right['ID'],"activation",$data['employee'],getEmployeeEmailByNetId($data['manager']),$data['env']); //Function in helpers.php
		}
	}
}

?>
