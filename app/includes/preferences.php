<?php
function getCurrentUserPreferences()
{
	global $env;
	global $netID;

	// Build the URL for the preferences of the current employee
	$url = 'https://'.$_SERVER['HTTP_HOST']."/API/preferences/${netID}";

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// In dev environment do not verify security certificates
	if ($env == 0)
	{
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	}
	
	$ret = NULL;
	// Get the response from the API, ensure it was successful, parse JSON, and set $_SESSION variable
	$response = curl_exec($ch);
	if ($response)
	{
		$response = json_decode($response);
		if ($response && $response->status == 'OK')
		{
			$ret = $response->data;
		}
	}

	curl_close($ch);
	return $ret;
}

$_SESSION['preferences'] = getCurrentUserPreferences();
?>
