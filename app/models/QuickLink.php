<?php

namespace TMT\model;

class QuickLink extends Model {

	public $guid;

	public $name;

	public $netId;

	public $url;

	public function __construct($obj = null) {
		if($obj == null)
			return;
		if(is_array($obj))
			$obj = (object) $obj;

		$this->guid = isset($obj->guid) ? $obj->guid : null;
		$this->name = isset($obj->name) ? $obj->name : null;
		$this->netId = isset($obj->netId) ? $obj->netId : null;
		$this->url = isset($obj->url) ? $obj->url : null;
	}
}