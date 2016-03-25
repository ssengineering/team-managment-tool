<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');

/**
 * This is the API for the rights checklist. Calls can be made to the following URls
 *		/API/rightsLevels/create
 *		/API/rightsLevels/edit
 *		/API/rightsLevels/delete/[id]
 *			id is any integer that represents the ID of the level being deleted
 *		/API/rightsLevels/print
 *
 * See the corresponding functions for descriptions of parameters and what they do.
 */

// Process request
$URI = $_SERVER['REQUEST_URI'];
$url = explode('/', $URI);

$params = array();
foreach($url as $parameter) {
	if(!($parameter == "API" || $parameter == "rightsLevels" || $parameter == "")) {
		$params[] = $parameter;
	}
}

//test for proper input
//store the function name in $function and the parameter, if any, in $param
//on bad input set $function = "" in order to do nothing
$function = "";
$param = "";
if(isset($params[0])) {
	$function = $params[0];
	if(isset($params[1])) {
		if($function == "delete" && is_numeric($params[1])) {
			$param = $params[1];
		} else {
			$function = "";
		}
	}
}

//execute the corresponding function
switch($function) {
	case "create":
		createLevel($_POST);
		break;
	case "edit":
		editLevel($_POST);
		break;
	case "delete":
		deleteLevel($param, $_POST);
		break;
	case "print":
		printLevels($_POST);
		break;
	default:
		break;
}

/**
 * Creates a new level in the database
 */
function createLevel($data)
{
	global $db;
	try {
		$highestLevelQuery = $db->prepare("SELECT level FROM employeeRightsLevels WHERE area = :area ORDER BY level DESC LIMIT 1");
		$highestLevelQuery->execute(array(':area' => $data['area']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $highestLevelQuery->fetch();
	$highestLevel = $result->level;
	try {
		$insertQuery = $db->prepare("INSERT INTO employeeRightsLevels (name,level,area,guid) VALUES ('',:highestLevel,:area,:guid)");
		$insertQuery->execute(array(':highestLevel' => $highestLevel+1, ':area' => $data['area'], ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

/**
 * Updates a level in the database
 */
function editLevel($data)
{
	global $db;
	try {
		$levelQuery = $db->prepare("SELECT level FROM employeeRightsLevels WHERE area = :area ORDER BY level DESC LIMIT 1");
		$levelQuery->execute(array(':area' => $data['area']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $levelQuery->fetch();
	$highestLevel = $result->level;

	for($i = 1; $i <= $highestLevel; $i++){
		try {
			$updateQuery = $db->prepare("UPDATE employeeRightsLevels SET name=:name WHERE area=:area AND level=:level");
			$updateQuery->execute(array(':name' => $data[$i], ':area' => $data['area'], ':level' => $i));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}

/**
 * Deletes a level from the database
 */
function deleteLevel($id, $data)
{
	global $db;
	try {
		$query = $db->prepare("DELETE FROM employeeRightsLevels WHERE level=:level AND area=:area");
		$query->execute(array(':level' => $id, ':area' => $data['area']));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

/**
 * Sends information necessary to print the edit levels dialog menu
 *
 * Echoes a json encoded array: array(levels, names)
 * 		levels: array(int)    An array of level numbers
 *		names : array(string) An array of level names
 */
function printLevels($data)
{
	global $db;
	$returnData = array();
	try {
		$levelsQuery = $db->prepare("SELECT * FROM employeeRightsLevels WHERE area=:area ORDER BY level ASC");
		$levelsQuery->execute(array(':area' => $data['area']));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	$levels = array();
	$names = array();
	while($right = $levelsQuery->fetch()){
		$levels[] = $right->level;
		$names[]  = $right->name;
	}

	echo json_encode(array("levels" => $levels, "names" => $names));
}

?>
