<?php

namespace TMT\api\group;

/**
 * The api router class for groups
 *
 * Handles requests for the route /api/group
 */
class index extends \TMT\APIController {

	/**
	 * API gateway/proxy to permissions microservice for the route /api/group
	 */
	private $url;

	public function __construct(){
		parent::__construct();
		$this->url = getEnv('PERMISSIONS_URL');
	}

	public function get($params) {
		$this->requireAuthentication();

		// /groups/:netId/:area
		if(isset($params['url'][3])) {
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/groups/".$params['url'][2]."/".$params['url'][3]));
			return;
		}

		// /groups/:guid
		if(isset($params['url'][2])) {
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/groups/".$params['url'][2]));
			return;
		}

		// /groups?area=:area
		echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/groups?".http_build_query($params['request'])));
	}

	
	public function post($params) {
		$this->requireAuthentication();
		if(!$this->can("organize", "d27d2880-79b6-4945-bf7d-c813d70c393a")) {
			$this->error("You are not authorized to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("POST", $this->url."/groups", $params['request']));
	}
	
	public function put($params) {
		$this->requireAuthentication();
		if(!$this->can("organize", "d27d2880-79b6-4945-bf7d-c813d70c393a")) {
			$this->error("You are not authorized to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("PUT", $this->url."/groups/".$params['url'][2],($params['request'])));	

	}

	public function delete($params) {
		$this->requireAuthentication();
		if(!$this->can("organize", "d27d2880-79b6-4945-bf7d-c813d70c393a")) {
			$this->error("You are not authorized to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("DELETE", $this->url."/groups/".$params['url'][2]));
	}
}
