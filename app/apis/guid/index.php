<?php

namespace TMT\api\guid;

/**
 * The api proxy/gateway class for guid
 *
 * Handles requests for the route /api/guid
 */
class index extends \TMT\APIController {

	private $url;

	//Constructor: this makes the url dynamic between dev and prod environments,
	//rather than hard coding in the prod address only.

	public function __construct(){
		parent::__construct();
		$this->url = getEnv('GUID_URL');
		if($this->url == "")
		{
			$this->url = "http://tmt-guid.byu.edu";
		}
	}

	/**
	 * Handles request to GET/api/guid
	 */
	public function get($params){
		$this->requireAuthentication();
		echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/guid"));
	}
}

