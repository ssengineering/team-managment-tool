<?php

namespace TMT\api\getPiInformation;

/**
 * This api is an abstraction of the request we make
 *   to BYU's Personal Information to pull employee
 *   information when they are added to the TMT
 */
class index extends \TMT\APIController {

	/**
	 * Retrieves a user's information from BYU's Personal Information
	 *
	 * GET /api/personSummary/:netId
	 */
	public function get($params) {
		$this->requireAuthentication();
		$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");

		// Check for netId
		if(count($params['url']) < 3) {
			$this->error("No netId given");
			return;
		}
		$netId = $params['url'][2];

		// Request this url
		$url = getenv("BYU_PI_ENDPOINT")."/".$netId;

		// Start building options
		$curl_options = array();

		if(array_key_exists('BYU-Web-Session', $_COOKIE)) {
			$byu_web_session = $_COOKIE['BYU-Web-Session'];
			$curl_options[CURLOPT_COOKIE] = "BYU-Web-Session={$byu_web_session}";
		}

		$curl_options[CURLOPT_HTTPGET] = true;
		$curl_options[CURLOPT_URL] = $url;
		$curl_options[CURLOPT_RETURNTRANSFER] = true;


		// Set options and execute curl
		$curl_handle = curl_init();
		$options_set = curl_setopt_array($curl_handle, $curl_options);
		$response = curl_exec($curl_handle);
		$response = json_decode($response, true);
		$response = $response['Person Summary Service']['response'];

		// Check for errors
		$http_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
		if ($http_code != 200 || array_key_exists("identifier_error", $response)) {
			$this->error("Unable to retrieve information for ".$netId);
			return;
		}


		curl_close($curl_handle);
		$this->respond($response);
	}
}
