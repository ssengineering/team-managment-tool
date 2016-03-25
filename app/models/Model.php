<?php

namespace TMT\model;

/**
 * Base model class
 *
 * Models for the TMT will extend from this class
 */
class Model implements \JsonSerializable{

	/**
	 * The default constructor for models
	 */
	public function __construct() {}

	/**
	 * Customize the output when a model is passed in to json_encode.
	 * 	By default, this returns an associative array of all non-static member variables in the model. If
	 * 	other behavior is desired, this function should be overwritten by the model class.
	 */
	public function jsonSerialize() {
		return get_object_vars($this);
	}
}
?>
