<?php

namespace TMT\accessor;

/**
 * A mock guid creator class
 *
 * This class can be used for unit testing in place
 * of a normal GuidCreator.
 */
class MockGuidCreator {

	/**
	 * A stored guid specified by setReturn()
	 */
	private $guid;

	/**
	 * The constructor. Identical functionality to setReturn
	 *
	 * @param $guid string A guid to return.
	 */
	public function __construct($guid = null) {
		$this->guid = $guid;
	}

	/**
	 * Returns a fake guid (not necessarily in guid format)
	 *
	 * @return string Whatever is set by setReturn()
	 */
	public function newGuid() {
		return $this->guid;
	}

	/**
	 * Returns a fake guid (not necessarily in guid format)
	 *
	 * @return string Whatever is set by setReturn()
	 */
	public function newGuidLocal() {
		return $this->guid;
	}

	/**
	 * Sets the guid to be returned
	 *
	 * @param $guid string
	 */
	public function setReturn($guid) {
		$this->guid = $guid;
	}
}
?>
