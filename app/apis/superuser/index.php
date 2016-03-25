<?php

namespace TMT\api\superuser;

/**
 * API gateway/proxy to permissions microservice for the route /api/superuser
 */
class index extends \TMT\APIController {

	private $url;
	
	public function __construct(){
		parent::__construct();
		$this->url = getEnv('PERMISSIONS_URL');
	}

		
	public function get($params) {
		$this->requireAuthentication();

		echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/superuser"));
	}

	public function post($params) {
		$this->requireAuthentication();

		// Permission checks are within the microservice
		echo json_encode($this->sendAuthenticatedRequest("POST", $this->url."/superuser", $params['request']));
	}

	public function put($params) {
		$this->requireAuthentication();

		// Permission checks are within the microservice
		echo json_encode($this->sendAuthenticatedRequest("PUT", $this->url."/superuser/".$params['url'][2], $params['request']));
	}

	public function delete($params) {
		$this->requireAuthentication();

		// Permission checks are within the microservice
		echo json_encode($this->sendAuthenticatedRequest("DELETE", $this->url."/superuser/".$params['url'][2]));
	}
}
?>
