<?php

namespace TMT\api\mim;

class index extends \TMT\APIController
{
	private $mimAcc;

	public function __construct()
	{
		parent::__construct();
		$this->requireAuthentication();
		$this->mimAcc = new \TMT\accessor\MimAccessor();
	}

	public function get($params)
	{   
		if (count($params['url']) < 3){
			$results = $this->mimAcc->getAll();
			$this->respond($results);
			return;
		} else { 
			$results = $this->mimAcc->get($params['url'][2]);
			$this->respond($results);
			return;
		}
	}   

	public function post($params)
	{
		if (!$this->can("update", "8ebcf9bb-3f88-4632-945c-9ef58e1c8cd6")){ // mim resource
			$this->error("You do not have permissions to edit the list of Major Incident Managers", 403);
			return;
		}
		$mim = new \TMT\model\Mim();
		if (count($params['url']) < 3){
			if (!isset($params['request']['netID'])){
				$this->error("'netID' must be specified", 400);
				return;
			} else {
				$mim->netID = $params['request']['netID'];
			}
		} else { 
			$mim->netID = $params['url'][2];
		}
		$existing = $this->mimAcc->get($mim->netID);
		if ($existing->netID){
			$this->error("$existing->firstName $existing->lastName is already a MIM", 400);
			return;
		}
		$results = $this->mimAcc->insert($mim);
		$this->respond($results);
	} 

	public function delete($params)
	{
		if (!$this->can("update", "8ebcf9bb-3f88-4632-945c-9ef58e1c8cd6")){ // mim resource
			$this->error("You do not have permissions to edit the list of Major Incident Managers", 403);
			return;
		}
		$mim = new \TMT\model\Mim();
		if (count($params['url']) < 3){
			$this->error("'netID' must be specified", 400);
			return;
		}
		$mim->netID = $params['url'][2];
		$results = $this->mimAcc->delete($mim);
		$this->respond($results);
	}
}

?>
