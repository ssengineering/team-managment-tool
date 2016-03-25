<?php

require($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php'); // Used to get netid of current user
header('Content-Type: application/json');
if (!can("access", "6a3b-c9e9-4283-8275-3c632dfc20d9")){ // supervisorDash resource
	echo "Failure";
	exit();
}

// DECRYPT PASSWORD
$password = "";
if(isset($_SESSION['servNowPass']))
{
	$password = passDecrypt(urldecode($_SESSION['servNowPass'])); 
}

$req = array();
$req['sys_id'] = $_REQUEST['sys_id'];
$req['short_description'] = $_REQUEST['short_description'];
$req['u_completion_rating'] = $_REQUEST['rating'];
if(isset($_REQUEST['start'])){
	$req['work_start'] = $_REQUEST['start'];
}
if(isset($_REQUEST['end'])){
	$req['work_end'] = $_REQUEST['end'];
}
if(isset($_REQUEST['comments'])){
	$req['u_task_work_log'] = $_REQUEST['comments'];
}
if(isset($_REQUEST['down_time'])){
	$req['u_actual_down_time'] = $_REQUEST['down_time'];
}

// Cannot currently change the state directly
if(isset($_REQUEST['state'])){
	$req['state'] = $_REQUEST['state'];
}

$url = getenv("SERVICE_NOW_STAGE_URL")."/change_request.do?WSDL";

if( $env == 2 ){
	$url = getenv("SERVICE_NOW_URL")."/change_request.do?WSDL";
} 

$ServiceNowClient = new SoapClient($url, array('trace' => 1, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 'login' => $netID, 'password' => $password));

$result = $ServiceNowClient->update($req);

if(isset($result->sys_id))
{
	echo json_encode(true);
}
else
{
	echo json_encode(false);
}

?>
