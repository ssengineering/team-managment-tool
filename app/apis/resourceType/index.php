<?php

namespace TMT\api\resourceType;

/**
 * The api proxy/gateway for the resourceType in the resources microservice
 *
 * Handles requests for the route /api/type, directs them to the microservice
 * and returns the result.
 */
class index extends \TMT\APIController {

	private $url;

	public function __construct(){
		parent::__construct();
		$this->url = getEnv('RESOURCES_URL');
	}

	/**
	 * Handles routes GET/api/type/:guid
	 */
	public function get($params){
		$this->requireAuthentication();
		if(!$this->isSuperuser()) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/type/".$params['url'][2]));
	}	

	/**
	 * Handles route POST/api/type
	 */
	public function post($params){
		$this->requireAuthentication();
		if(!$this->isSuperuser()) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("POST", $this->url."/type", $params['request']));
	}

}
