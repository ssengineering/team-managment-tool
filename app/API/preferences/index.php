<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/dbconnect.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/guid.php');
global $db;
// Get requested end-point (i.e. "$call")
$URI = $_SERVER['REQUEST_URI'];
$APIDirectory = '/API/preferences/';
$call = substr($URI, strlen($APIDirectory));

// Determine the type of interaction being attempted
$method = $_SERVER['REQUEST_METHOD'];

//Create variables with default values of NULL
$status = $error =  $query = $data = NULL;

// Explode $call to get our nice pretty variables
list($employee,$key, $value) = array_pad(explode('/', $call, 3), 3, NULL);

if ($employee)
{
	// Query database for preferences object
	try {
		$preferencesQuery = $db->prepare("SELECT `preferencesObject` FROM `empPreferences` WHERE `employee`=:employee");
		$success = $preferencesQuery->execute(array(':employee' => $employee));
	} catch(PDOException $e) {
		$success = false;
	}

	// If we were able to get the preferences properly carry out the desired method call, otherwise respond with an error
	if ($success)
	{
		$status = 'OK';
		$preferences = $preferencesQuery->fetch(PDO::FETCH_ASSOC);
		
		// If we have real results (i.e. an entry in the database for the employee) then use that
		if ($preferences)
		{
			$originalPreferencesObject = json_decode($preferences['preferencesObject']);
		}
		// Otherwise create a new array object for us to use in the mean-time
		else
		{
			$originalPreferencesObject = new stdClass();
		}
		$preferences = &$originalPreferencesObject;
	}
	else
	{
		$status = 'FAIL';
		$error = "An error occurred when accessing the database";
	}

	// Ensure we were successful in getting the $preferences variable
	if ($status === 'OK')
	{
		if ($key)
		{
			$keys = explode('.', $key);
		}
		else
		{
			$keys = array();
		}
		switch ($method)
		{
		case "GET":
			// Keep getting the next preference value for each key until we get to the final key:value pair we wanted
			foreach ($keys as $property)
			{
				if (isset($preferences->$property))
				{
					$preferences = $preferences->$property;
				}
				else
				{
					$preferences = new stdClass();
					break;
				}
			}
			$data = $preferences;
			break;
		// I just treat POST as a replacement for PUT, I also only support the use of three letter acronymns for HTTP Request-types (i.e. GET, PUT, DEl) and wish things had been done that way
		case "POST":
			// Update if exists else create
			foreach ($keys as $property)
			{
				if (isset($preferences->$property))
				{
					$preferences = &$preferences->$property;
				}
				else
				{
					// Create the sub-key, and set our $preferences as a pointer to our new array
					$preferences->$property = new stdClass();
					$preferences = &$preferences->$property;
				}
			}

			// Add key to $preferences with our new value if this is the last key/property
			$preferences = json_decode(urldecode($value));
			// Update the database with the new preferencesObject
			// It should also be updating the $_SESSION['preferences'] value already since we are modifiying things by reference
			$jsonPreferences = json_encode($originalPreferencesObject);

			try {
				$query = $db->prepare("INSERT INTO `empPreferences` (`employee`,`preferencesObject`,`guid`) VALUES (:employee,:preferences,:guid) ON DUPLICATE KEY UPDATE `preferencesObject`=:preferences1");
				$result = $query->execute(array(':employee' => $employee, ':preferences' => $jsonPreferences, ':guid' => newGuid(), ':preferences1' => $jsonPreferences));
			} catch(PDOException $e) {
				exit("error in query");
			}

			// Response should match whether or not we were able to successfully update the preferences in the database
			if ($result)
			{
				$data = array($key=>$preferences);
			}
			else
			{
				$status = 'FAIL';
				$error = "An error occurred when accessing the database";
			}
			break;
		default:
			$status = 'FAIL';
			$error = "$method method is un-handled by the API, feel free to implement it!";
			break;
		}
	}
	// Prepare our JSON response
	$response = array('status'=>$status, 'error'=>$error, 'data'=>$data);

	// Return our response
	echo json_encode($response);
}

?>
