<?php

namespace TMT\api\permission;

/**
 * API gateway/proxy to permissions microservice for the route /api/permission/groups
 */
class groups extends \TMT\APIController {

	private $url;
	
	public function __construct(){
		parent::__construct();
		$this->url = getEnv('PERMISSIONS_URL');
	}

		
	public function get($params) {
		$this->requireAuthentication();

		if (isset($params["url"][3])) {
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/permission/groups/".$params["url"][3]));
		} else {
			unset($params['request']['url']);
			echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/permission/groups?".http_build_query($params['request'])));
		}
	}
}
?>
