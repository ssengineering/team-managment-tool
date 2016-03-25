<?php

include_once('loadConfig.php');
include_once('dbconnect.php');
include_once('CAS/CAS.php');

session_start();

// Breakout for the testing suite 
if (getenv('ENVIRONMENT') == 'TESTING') {
	$area = 1;
	$env = 0;
	$netID = 'TESTING_USER';
	return true;
}

if (isset($_REQUEST['logout']))
{
	if ($_SESSION['ldap'])
	{
		session_destroy();
		echo '<META HTTP-EQUIV="Refresh" Content="0; URL=../landing.php">';
		exit;
	}
	else
	{
		// Initialize CAS so that we can logout
		phpCAS::client(CAS_VERSION_2_0, 'cas.byu.edu', 443, 'cas', false);
		phpCAS::setNoCasServerValidation();
		phpCAS::forceAuthentication();

		session_destroy();
		phpCAS::logout();
	}
}

if (isset($_SESSION['ldap']))
{
	$netID = $_SESSION['user'];
	$_SESSION['ldap'] = true;
	$auth = 1;
}
else 
{
	// If the last time we checked the user's authentication was less than 3 seconds ago
	if (isset($_SESSION['lastAuthCheck']) && time()-$_SESSION['lastAuthCheck'] < 3)
	{
		$netID = $_SESSION['netId'];
		$auth = 1;
	}
	else
	{
		
		// initialize phpCAS
		phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas', false);
		$_SESSION['newCheckTime'] = time();
		
		// no SSL validation for the CAS server
		phpCAS::setNoCasServerValidation();

		$auth = phpCAS::checkAuthentication();

		if(!$auth)
		{
			echo '<META HTTP-EQUIV="Refresh" Content="0; URL=../landing.php?request='.urlencode($_SERVER['REQUEST_URI']).'">';
			exit;
		}	
		else
		{
			$netID = phpCAS::getUser();
			$_SESSION['netId'] = $netID;
			$_SESSION['lastAuthCheck'] = time();
		}

	}
	
}

if(isset($_REQUEST['login']))
{
	phpCAS::forceAuthentication();
}

try {
	$query = $db->prepare("SELECT netID, active, area FROM employee WHERE netID = :netID");
	$query->execute(array(":netID" => $netID));
} catch(PDOException $e) {
	exit("error in query");
}

$results = $query->fetch();

/* Sets up the variable $area to hold the current user's default area. 
 * If cookie has an area in it (due to user viewing a different area than
 * default) set area to that area
 * Else, set area to the user's default area.
 */ 
if(isset($_COOKIE['area'])){
	$area = $_COOKIE['area'];
}else{
	$area = $results->area;
}

$areaGuid = "";
try {
	$query2 = $db->prepare("SELECT guid FROM employeeAreas WHERE ID=:area");
	$query2->execute(array(":area" => $area));
	$areaGuid = $query2->fetch()->guid;
} catch(PDOException $e) {
	exit("error in query");
}

if(isset($_COOKIE['environment'])){
	$env = $_COOKIE['environment'];
}else{
	$env = $config['environment'];
}

if ($results->active == 1){
	return true;
}else{
	header("Location: notAuthorized.php");
	return false;
}
?>

