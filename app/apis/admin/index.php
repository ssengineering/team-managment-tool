<?php

namespace TMT\api\admin;

/**
 * The api proxy/gateway class for admin
 *
 * Handles requests for the route /api/admin
 */
class index extends \TMT\APIController {

	private $url;
	
	public function __construct(){
		parent::__construct();
		$this->url = getEnv('PERMISSIONS_URL');
	}

	public function get($params) {
		$this->requireAuthentication();

		if(isset($params["url"][3])) {
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/admin/".$params["url"][2]."/".$params["url"][3]));
		} else {
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/admin?".http_build_query($params['request'])));
		}
	}

	public function post($params) {
		$this->requireAuthentication();

		// Permission checks are done within the microservice
		echo json_encode($this->sendAuthenticatedRequest("POST", $this->url."/admin", $params['request']));
	}

	public function delete($params) {
		$this->requireAuthentication();

		// Permission checks are done within the microservice
		echo json_encode($this->sendAuthenticatedRequest("DELETE", $this->url."/admin/".$params['url'][2]."/".$params['url'][3]));
	}
}
?>
