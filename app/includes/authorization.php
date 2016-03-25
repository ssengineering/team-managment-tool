<?php

// Composer Autoloader
require dirname(__FILE__)."/../../vendor/autoload.php";

/**
 * Send an authenticated request to one of the TMT micro-services
 *
 * @param $method string The HTTP method to use ("GET", "POST", "PUT", "DELETE")
 * @param $url    string The url to make the request to
 * @param $data   array  Any data to pass in POST data (GET data should be included in the $url)
 *
 * @return The response: an array created by json-decoding the response body
 */
function sendAuthenticatedRequest($method, $url, $data = array()) {
	global $netID;
	global $db;
	global $areaGuid;
	

	// Find private key
	$dir = getenv("KEYS_DIRECTORY");
	$dir = ($dir != "") ? $dir : $_SERVER['DOCUMENT_ROOT']."/keys";
	// Get file name for private key
	$privateKey = getenv("PRIVATE_KEY_FILE");
	$privateKey = ($privateKey != "") ? $privateKey : $_SERVER['DOCUMENT_ROOT']."/keys/key.pem";

	// Create JWT
	$signer = new \Lcobucci\JWT\Signer\Rsa\Sha256();
	$keychain = new \Lcobucci\JWT\Signer\Keychain();
	$builder = new \Lcobucci\JWT\Builder();
	$token = $builder->setIssuer(getenv("PROD_URL"))
		->setIssuedAt(time())
		->setNotBefore(time()-1)
		->setExpiration(time()+120) // 2 minutes
		->set("employee", $netID) // store netId in the token
		->set("area", $areaGuid) // store area id in the token
		->sign($signer, $keychain->getPrivateKey(file_get_contents($privateKey))) // Private key should be in file named "key.pem"!
		->getToken();

	// Start building options
	$curl_options = array();
	switch($method) {
	case "POST":
		$curl_options[CURLOPT_POST] = true;
		$curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
		break;
	case "PUT":
		$curl_options[CURLOPT_CUSTOMREQUEST] = "PUT";
		$curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
		break;
	case "DELETE":
		$curl_options[CURLOPT_CUSTOMREQUEST] = "DELETE";
		break;
	case "GET":
	default:
		$curl_options[CURLOPT_HTTPGET] = true;
	}
	$curl_options[CURLOPT_URL] = $url;
	$curl_options[CURLOPT_RETURNTRANSFER] = true;
	$curl_options[CURLOPT_SSL_VERIFYPEER] = false;
	$curl_options[CURLOPT_SSL_VERIFYHOST] = false;
	$curl_options[CURLOPT_HTTPHEADER] = array("Authorization: ".$token->__toString());


	// Set options and execute curl
	$curl_handle = curl_init();
	$options_set = curl_setopt_array($curl_handle, $curl_options);
	$response = curl_exec($curl_handle);
	return json_decode($response, true);
}
