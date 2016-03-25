<?php
/*
*	Name: index.php
*	Application: Tag API
*	Site: psp.byu.edu
*
* Description: The tag system is supposed to give developers the ability to
* easily associate users with specific apps. I developed it for use with 
* the manager report scheduling app, but wanted it to be portable for other apps
* as well. 
* 
* The API has three methods: add, drop and search. Requests should be sent to
* /API/tags/add, /API/tags/drop or /API/tags/search. Params are described below.
*  
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');

// Process request
$URI = $_SERVER['REQUEST_URI'];

// Extract the method name from the URI
$method_and_params = explode('?',substr($URI, strlen('/API/tags/')));
$method = $method_and_params[0];

// Extract params from request and make API call
$params = $_REQUEST;
$response = tagAPICall($method, $params);

echo $response;


/*
 * Executes the API call.
 * 
 * @param $method
 * 	add, drop or search
 * @param $params
 * 	An array with the following keys: 
 * 		'netid' -- The Net ID of the user being tagged. Optional on search calls.
 * 		'area' -- The area the user should be tagged in. REQUIRED
 * 		'tag' -- The shortname of the tag in the `tags` table. REQUIRED
 * 		'long' -- **Optional** The long description for the tag. This allows 
 * 			for new tags to be created with a full description, simply by calling
 * 			the API. If the tag does not already exist and the 
 * @return
 * 	The response. Search results will be formatted as a JSON object.
 */ 
function tagAPICAll($method, $params){
	global $db;
	$def_error = "Error ";
	$required_method_keys = array("netid","area","tag");
	foreach ($required_method_keys as $value){
		if (!array_key_exists($value, $params)){
			if ($method == "search"){
				if ($value=="netid") continue;
			}
			return $def_error."Missing parameter ".$value;
		}
	}
	$result = "";
	$netid = "";
	if(isset($params['netid'])) {
		$netid = $params['netid'];
	}
	$area = $params['area'];
	$tag = $params['tag'];
	if ($method=="add"){
		$long = isset($params['long']) ? $params['long'] : 'DEFAULT'; 		
		try {
			$tagQuery = $db->prepare("CALL tagEmployee(:netID, :area, :tag, :long)");
			$tagQuery->execute(array(':netID' => $netid, ':area' => $area, ':tag' => $tag, ':long' => $long));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	else if ($method=="drop"){
		try {
			$tagQuery = $db->prepare("CALL untagEmployee(:netID, :area, :tag)");
			$tagQuery->execute(array(':netID' => $netid, ':area' => $area, ':tag' => $tag));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	else if ($method=="search"){
		try {
			$searchQuery = $db->prepare("CALL searchTags(:area, :tag)");
			$searchQuery->execute(array(':area' => $area, ':tag' => $tag));
		} catch(PDOException $e) {
			exit("error in query");
		}
		
		$list = array();
		while($row = $searchQuery->fetch(PDO::FETCH_ASSOC)) {
    		$list[] = $row['netID'];
		}
		
		$result = json_encode($list);
	}
	else {
		$result = $def_error."Invalid method";
	}
	return $result;
	
}
?>
