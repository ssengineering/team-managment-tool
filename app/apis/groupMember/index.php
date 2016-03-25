<?php

namespace TMT\api\groupMember;

/**
 * API gateway/proxy to permissions microservice for the route /api/groupMembers
 *
 * Handles requests for the route /api/groupMembers
 */
class index extends \TMT\APIController {

	private $url;

	public function __construct(){
		parent::__construct();
		$this->url = getEnv('PERMISSIONS_URL');
	}

	public function get($params) {
		$this->requireAuthentication();
		if(!$this->can("organize", "d27d2880-79b6-4945-bf7d-c813d70c393a")) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		if(isset($params['url'][2])) {
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/groupMembers/".$params['url'][2]));
			return;
		}
		echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/groupMembers?".http_build_query($params['request'])));
	}
	
	public function post($params) {
		$this->requireAuthentication();
		if(!$this->can("organize", "d27d2880-79b6-4945-bf7d-c813d70c393a")) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("POST", $this->url."/groupMembers", $params['request']));
	}

	public function delete($params) {
		$this->requireAuthentication();
		if(!$this->can("organize", "d27d2880-79b6-4945-bf7d-c813d70c393a")) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		if(isset($params['url'][3])) {
			echo json_encode($this->sendAuthenticatedRequest("DELETE", $this->url."/groupMembers/".$params['url'][2]."/".$params['url'][3]));
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("DELETE", $this->url."/groupMembers/".$params['url'][2]));
	}
}
