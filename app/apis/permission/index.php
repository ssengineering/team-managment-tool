<?php

namespace TMT\api\permission;

/**
 * API gateway/proxy to permissions microservice for the route /api/permission
 */
class index extends \TMT\APIController {

	private $url;
	
	public function __construct(){
		parent::__construct();
		$this->url = getEnv('PERMISSIONS_URL');
	}

		
	public function get($params) {
		$this->requireAuthentication();
		if (isset($params["url"][3])) {
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/permission/".$params["url"][2]."/".$params["url"][3]));
		} else {
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/permission?".http_build_query($params['request'])));
		}
	}

	public function post($params) {
		$this->requireAuthentication();

		// The permission checks are done from the microservice
		echo json_encode($this->sendAuthenticatedRequest("POST", $this->url."/permission", $params['request']));
	}

	public function delete($params) {
		$this->requireAuthentication();

		// The permission checks are done from the microservice
		echo json_encode($this->sendAuthenticatedRequest("DELETE", $this->url."/permission/".$params['url'][2]."/".$params['url'][3]."/".$params['url'][4]));
	}
}
?>
