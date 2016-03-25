<?php

namespace TMT\api\position;

class index extends \TMT\APIController
{
	private $positionAcc;
	private $areaAcc;

	public function __construct()
	{
		parent::__construct();
		$this->requireAuthentication();
		$this->positionAcc = new \TMT\accessor\Position();
		$this->areaAcc = new \TMT\accessor\AreaAccessor();
	}

	/**
	 * GET /api/position -- return models for all positions in the current sessions area
	 * GET /api/position/:positionId -- return a model for the position with id :positionId
	 *  params:
	 *   NONE
	 */
	public function get($params)
	{   
		if (count($params['url']) < 3){
			$results = $this->positionAcc->getByArea($this->user['area']);
			$this->respond($results);
			return;
		} else { 
			$results = $this->positionAcc->get($params['url'][2]);
			// Hide results from areas the user does not have access to
			if ($this->areaAcc->checkAreaRights($this->user['netId'], $results->employeeArea)) {
				$this->respond($results);
				return;
			} else {
				$this->respond(new \TMT\model\Position());
			}
		}
	}   

	/**
	 * POST /api/position -- insert a position 
	 *  params:
	 *   positionName - REQUIRED string
	 *   positionDescription - REQUIRED string
	 *   employeeArea - REQUIRED int
	 */
	public function post($params)
	{
		if (isset($params['request']['employeeArea'])) {
			$this->user['area'] = $params['request']['employeeArea'];
		}
		else {
			$params['request']['employeeArea'] = $this->user['area'];
		}
		$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");
		$position = new \TMT\model\Position();
		$position->positionId = NULL;
		$position->deleted = 0;
		$position->employeeArea = $params['request']['employeeArea'];
		if ((!isset($params['request']['positionName'])) || 
			(!isset($params['request']['positionDescription']))) {
			$this->error("Must specify positionName and positionDescription", 400);
			return;
		}
		$position->positionName = $params['request']['positionName'];
		$position->positionDescription = $params['request']['positionDescription'];
		$results = $this->positionAcc->save($position);
		$this->respond($results);
	} 

	/**
	 * PUT /api/position/:positionId -- update a position with id :positionId
	 *  params:
	 *   positionName - OPTIONAL string
	 *   positionDescription - OPTIONAL string
	 *   employeeArea - OPTIONAL int
	 */
	public function put($params)
	{
		if (count($params['url']) < 3){
			$this->error('No positionId provided', 400);
			return;
		} 
		// In case the position's area is being updated, check that the user
		// has rights to edit permissions in the position's current area and its 
		// new area
		$position = $this->positionAcc->get($params['url'][2]);
		$this->user['area'] = $position->employeeArea;
		$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");
		if (isset($params['request']['employeeArea'])) {
			$this->user['area'] = $params['request']['employeeArea'];
			$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");
			$position->employeeArea = $params['request']['employeeArea'];
		}
		if (isset($params['request']['positionName'])) {
			$position->positionName = $params['request']['positionName'];
		}
		if (isset($params['request']['positionDescription'])) {
			$position->positionDescription = $params['request']['positionDescription'];
		}
		$results = $this->positionAcc->save($position);
		$this->respond($results);
	} 

	/**
	 * DELETE /api/position/:positionId -- delete a position with id :positionId
	 *  params:
	 *   NONE
	 */
	public function delete($params)
	{
		$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");
		if (count($params['url']) < 3){
			$this->error('No positionId provided', 400);
			return;
		}
		$result = $this->positionAcc->delete($params['url'][2]);
		$this->respond($result);
	}
}

?>
