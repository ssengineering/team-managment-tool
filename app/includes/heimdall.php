<?php

	require_once('includeMeBlank.php');

	function checkVisibility($app)
	{
		global $netID, $area, $config;

		$request = curl_init();
		
		curl_setopt($request, CURLOPT_URL, "http://heimdall.byu.edu/isVisible/".$app."?user=".$netID."&environment=".$config['environment']."&area=".$area);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

		$result = json_decode(curl_exec($request));

		if (isset($result->visible))
		{
			if ($result->visible === 1)
				return true;
			else 
				return false;
		}

		return false;
	}
?>
