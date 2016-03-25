<?php

namespace TMT\model;

/**
 * The model class for interacting with custom group data fields
 */
class UserGroupData extends Model {

	/**
	 * The user's netId
	 */
 	protected $user;

	/**
	 * int The group number
	 */
	protected $group;

	/**
	 * array An associative array with key names as group data
	 *   fields and values as the user's information
	 */
	protected $data;

	/**
	 * Basic constructor to initialize data in the model
	 *
	 * @param $user  string The user's netId
	 * @param $group int    The user's group number
	 * @param $data  array  The associative array of data for this user
	 */
	public function __construct($user, $group, $data) {
		$this->user  = $user;
		$this->group = (int)$group;
		$this->data  = $data;
	}

	/**
	 * Gets the value of the given field for the user
	 *
	 * @param string The name of the field to get
	 *
	 * @throws \TMT\exception\CustomGroupDataException if the field doesn't exist
	 * 
	 * @return string The user's value for the given field
	 */
	public function getField($field) {
		if(!isset($this->data[$field])) {
			throw new \TMT\exception\CustomGroupDataException(4);
		}
		return $this->data[$field];
	}

	/**
	 * Sets the value for the given field
	 *
	 * @throws \TMT\exception\CustomGroupDataException if the field doesn't exist
	 * 
	 * @param string The name of the field to modify
	 * @param string The new value to set for the field
	 */
	public function editField($field, $value) {
		if(!isset($this->data[$field])) {
			throw new \TMT\exception\CustomGroupDataException(4);
		}
		$this->data[$field] = $value;
	}

	/**
	 * Returns an array of the user's data
	 *
	 * @return associative array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Returns the user's netId
	 *
	 * @return string
	 */
	public function getNetId() {
		return $this->user;
	}

	/**
	 * Returns the user's group 
	 *
	 * @return int
	 */
	public function getGroup() {
		return $this->group;
	}
}
?>
