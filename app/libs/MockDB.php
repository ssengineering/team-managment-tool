<?php

namespace TMT;

class MockDB {

	private $executeData;
	private $currentExecute;

	private $queryStrings;
	private $currentPrepare;

	private $returnData;
	private $currentReturn;

	public function __construct() {
		$this->executeData = array();
		$this->currentExecute = 0;
		$this->queryStrings = array();
		$this->currentPrepare = 0;
		$this->returnData = null;
		$this->currentReturn = 0;
	}

	public function expectPrepare($query) {
		$this->queryStrings[] = $query;
	}

	public function expectExecute($data = null) {
		$this->executeData[] = $data;
	}

	public function &prepare($query) {
		if($this->currentPrepare < count($this->queryStrings)) {
			if($this->queryStrings[$this->currentPrepare] != $query) {
				throw new \Exception("Prepare was expected, but query did not match. Expected \"".$this->queryStrings[$this->currentPrepare].
					"\" but got \"".$query."\"");
			} else {
				$this->currentPrepare++;
			}
		} else {
			throw new \Exception("Unexpected prepare with parameter \"".$query."\"");
		}
		return $this;
	}

	public function execute($data = null) {
		if($this->currentExecute < count($this->executeData)) {
			if($this->executeData[$this->currentExecute] != $data) {
				throw new \Exception("Data given for the query does not match the expected data. Expected ".print_r($this->executeData[$this->currentExecute], true)." but got ".print_r($data, true));
			} else {
				$this->currentExecute++;
			}
		} else {
			throw new \Exception("Unexpected execute was called with the following data: ".print_r($data, true));
		}
		return true;
	}

	public function setReturnData($data) {
		$this->returnData = $data;
	}

	public function fetch($style = null) {
		if($this->currentReturn < count($this->returnData)) {
			return $this->returnData[$this->currentReturn++];
		} else {
			return false;
		}
	}

	public function verify() {
		if($this->currentPrepare != count($this->queryStrings))
			throw new \Exception("Prepare was called ".$this->currentPrepare." times. ".count($this->queryStrings)." were expected.");
		if($this->currentExecute != count($this->executeData))
			throw new \Exception("Execute was called ".$this->currentExecute." times. ".count($this->executeData)." were expected.");
	}
}
