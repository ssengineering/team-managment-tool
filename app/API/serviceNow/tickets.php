<?php

$netID = getenv("SERVICE_NOW_USER");
$password = getenv("SERVICE_NOW_PASSWORD");

$query = $_GET['query'];

$ServiceNowClient = new SoapClient(getenv("SERVICE_NOW_URL")."/incident_list.do?WSDL", array('trace' => 1, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 'login' => $netID, 'password' => $password));

$result = $ServiceNowClient->getRecords(array('__encoded_query'=>$query));

if(isset($result->getRecordsResult))
{
	if(is_array($result->getRecordsResult))
	{
		foreach($result->getRecordsResult as $incident)
		{
			echo json_encode($incident);
		}
	}
	else
	{
		echo json_encode($result->getRecordsResult);
	}
}
else
{
	echo '{"description":"No matches."}';
}

?>
