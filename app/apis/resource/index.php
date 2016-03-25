<?php

namespace TMT\api\resource;

/**
 * The api proxy/gateway class for resources
 *
 * Handles requests for the route /api/resources
 */
class index extends \TMT\APIController {

	private $url;
	
	public function __construct(){
		parent::__construct();
		$this->url = getEnv('RESOURCES_URL');
	}
	
	/**
	 * Handles routes GET/api/resources/:guid and GET/api/resources
	 */	
	public function get($params){
		$this->requireAuthentication();

		//If there is a value in the second index of the url, send a GET request using that value.
		if(isset($params['url'][2])){
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/resources/".$params['url'][2]));
			return;
		}

		//If there is no value in the second index of the url, send a different GET request.
		echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/resources"));
	}

	/**
	 * Handles route POST/api/resources
	 */
	public function post($params){
		$this->requireAuthentication();
		if(!$this->isSuperuser()) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("POST", $this->url."/resources", $params['request']));
	}

	/**
	 * Handles route PUT/api/resources/:guid
	 */
	public function put($params){
		$this->requireAuthentication();
		if(!$this->isSuperuser()) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("PUT", $this->url."/resources/".$params['url'][2],$params['request']));
	}

	/**
	 * Handles route DELETE/api/resources/:guid
	 */
	public function delete($params){
		$this->requireAuthentication();
		if(!$this->isSuperuser()) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("DELETE", $this->url."/resources/".$params['url'][2]));
	}

	
}
