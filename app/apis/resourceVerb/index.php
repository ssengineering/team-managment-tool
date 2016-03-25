<?php 
namespace TMT\api\resourceVerb;

/**
 * The api proxy/gateway class for resourceVerbs
 *
 * Handles requests for the route /api/verbs and directs them to the
 * resources microservice, then returns the result.
 */
class index extends \TMT\APIController {
	
	private $url;

	public function __construct(){
		parent::__construct();
		$this->url = getEnv('RESOURCES_URL');
	}

	/**
	 * Handles requests to route GET/api/verbs/:guid
	 */
	public function get($params){
		$this->requireAuthentication();
		echo json_encode($this->sendAuthenticatedRequest("GET", $this->url."/verbs/".$params['url'][2]));
	}

	/**
	 * Handles requests to route POST/api/verbs
	 */
	public function post($params){
		$this->requireAuthentication();
		if(!$this->isSuperuser()) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("POST", $this->url."/verbs", $params['request']));
	}	
	
	/**
	 * Handles requests to route PUT/api/verbs/:guid
	 */
	public function put($params){
		$this->requireAuthentication();
		if(!$this->isSuperuser()) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("PUT", $this->url."/verbs/".$params['url'][2],$params['request']));
	}

	/**
	 * Handles requests to route DELETE/api/verbs/:guid
	 */
	public function delete($params){
		$this->requireAuthentication();
		if(!$this->isSuperuser()) {
			$this->error("You do not have permission to perform this action", 403);
			return;
		}

		echo json_encode($this->sendAuthenticatedRequest("DELETE", $this->url."/verbs/".$params['url'][2]));
	}
}
