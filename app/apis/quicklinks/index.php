<?php

namespace TMT\api\quicklinks;

/**
 * Api router for the route /api/quicklinks
 */
class index extends \TMT\APIController {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->requireAuthentication();
	}

	/**
	 * Get Individual: GET /api/quicklinks/:guid
	 * getByUser: GET /api/quicklinks?netId=:netId
	 */
	public function get($params) {
		$accessor = $this->getAccessor("Quicklinks");
		// GET /api/quicklinks/:guid
		if(isset($params['url'][2])) {
			$result = $accessor->get($params['url'][2]);
			$this->respond($result);
			return;
		}
		// GET /api/quicklinks?netId=:netId
		if(isset($params['request']["netId"])) {
			$results = $accessor->getByUser($params['request']['netId']);
			$this->respond($results);
			return;
		}
		$this->error("Invalid request", 400);
	}

	/**
	 * POST /api/quicklinks
	 */
	public function post($params) {
		$accessor = $this->getAccessor("Quicklinks");
		if(!isset($params['request']['name']) || !isset($params['request']['netId']) || !isset($params['request']['url'])) {
			$this->error("Not all parameters were specified", 400);
			return;
		}
		if($this->user['netId'] != $params['request']['netId']) {
			$this->error("Cannot add quicklinks for another user!");
			return;
		}
		$model = new \TMT\model\QuickLink((object)$params['request']);
		$result = $accessor->add($model);
		$this->respond($result);
	}

	/**
	 * PUT /api/quicklinks/:guid
	 */
	public function put($params) {
		$accessor = $this->getAccessor("Quicklinks");
		if(!isset($params['url'][2])) {
			$this->error("Not all necessary values were specified");
			return;
		}
		if(!isset($params['request']['name']) || !isset($params['request']['url'])) {
			$this->error("Not all updatable values were specified");
			return;
		}
		if($this->user['netId'] != $params['request']['netId']) {
			$this->error("Cannot change quicklinks for another user!");
			return;
		}

		$model = new \TMT\model\QuickLink((object)$params['request']);
		$model->guid = $params['url'][2];
		$result = $accessor->update($model);
		$this->respond($result);
	}

	/**
	 * DELETE /api/quicklinks/:guid
	 */
	public function delete($params) {
		$accessor = $this->getAccessor("Quicklinks");
		if(!isset($params['url'][2])) {
			$this->error("Not all necessary values were specified");
			return;
		}

		$accessor->delete($params['url'][2]);
		$this->respond("success");
	}
}
