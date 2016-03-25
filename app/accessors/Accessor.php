<?php

namespace TMT\accessor;

/**
 * Base Accessor class
 *
 * All tmt accessors will be extended from this class
 */
class Accessor {

	/**
	 * An object that creates new Guids
	 */
	private $guidCreator;

	/**
	 * The default constructor for all accessors
	 */
	public function __construct() {
		$this->guidCreator = new GuidCreator();
	}

	/**
	 * Generates a new guid by calling out to the guid-generator micro-service, or
	 *   if it can't be hit, generates one on it's own
	 *
	 * @return A new v4 guid
	 */
	public function newGuid() {
		return $this->guidCreator->newGuid();
	}

	public function setGuidCreator($gc) {
		$this->guidCreator = $gc;
	}
}
?>
