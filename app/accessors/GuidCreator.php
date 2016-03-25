<?php

namespace TMT\accessor;

/**
 * A class for generating guids
 */
class GuidCreator {

	/**
	 * The default constructor
	 */
	public function __construct() {}

	/**
	 * Generates a new guid by calling out to the guid-generator micro-service, or
	 *   if it can't be hit, generates one on it's own
	 *
	 * @return A new v4 guid
	 */
	public function newGuid() {
		$url = getEnv('GUID_URL');
		if($url == "") {
			$url = "http://tmt-guid.byu.edu";
		}

		// Start building curl options
		$curl_options = array();
		$curl_options[CURLOPT_URL] = $url.'/guid';
		$curl_options[CURLOPT_RETURNTRANSFER] = true;

		// Create handle and set options
		$curl_handle = curl_init();
		$success = curl_setopt_array($curl_handle, $curl_options);

		// Check failure to properly prepare curl handle
		if(!$success) {
			curl_close($curl_handle);
			return $this->newGuidLocal();
		}

		// Execute request
		$response = curl_exec($curl_handle);

		// Check for errors
		$http_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
		$err_num   = curl_errno($curl_handle);

		// Failed to hit micro-service
		if(($http_code < 200 || $http_code >= 400) || $err_num != 0) {
			curl_close($curl_handle);
			return $this->newGuidLocal();
		}

		// Parse response and return
		$response = json_decode($response, false);
		$guid = $response->data;
		curl_close($curl_handle);
		return $guid;
	}

	/**
	 * Generates a new guid on the TMT side rather than getting
	 *   one from a micro-service.
	 *
	 * @return A new v4 guid
	 */
	public function newGuidLocal() {
		$generator = new \Icecave\Druid\UuidVersion4Generator;
		$uuid = $generator->generate()->string();
		return $uuid;
	}
}
?>
