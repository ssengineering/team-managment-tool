<?php

namespace TMT\api\position;

class area extends \TMT\APIController
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
	 * GET /api/position/area/:area -- return models for all positions with employeeArea :area
	 *  params:
	 *   NONE
	 *
	 * If the user does not have rights to the area, an empty array is returned
	 */
	public function get($params)
	{
		if (count($params['url']) < 4){
			$this->error('No area specified', 400);
			return;
		} else {
			$area = $params['url'][3];
			if (!$this->areaAcc->checkAreaRights($this->user['netId'], $area)) {
				$this->respond([]);
				return;
			}
			$results = $this->positionAcc->getByArea($params['url'][3]);
			$this->respond($results);
			return;
		}
	}   

}

?>
