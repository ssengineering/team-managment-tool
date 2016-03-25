<?php

namespace TMT\api\permission;

/**
 * API gateway/proxy to permissions microservice for the route /api/permission/verbs
 */
class verbs extends \TMT\APIController {

	private $url;
	
	public function __construct(){
		parent::__construct();
		$this->url = getEnv('PERMISSIONS_URL');
	}

		
	public function get($params) {
		$this->requireAuthentication();

		echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/permission/verbs/".$params["url"][3]."/".$params["url"][4]));
	}
}
?>
