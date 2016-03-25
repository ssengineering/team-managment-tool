<?php

namespace TMT\model;

/**
 * The model class for interacting with custom group data fields
 */
class CustomGroupData extends Model {

	/**
	 * int The group number
	 */
	protected $group;

	/**
	 * array Array of field names for the group
	 */
	protected $fields;

	/**
	 * A basic constructor to fill the model data
	 *
 	 * @param $group  int The group number
	 * @param $fields array An array of the field names
	 */
	public function __construct($group, $fields) {
		$this->group  = (int)$group;
		$this->fields = $fields;
	}

	/**
	 * Adds a field with the given name if there is already a
	 * field with that name, an exception is thrown
	 *
	 * @throws \TMT\exception\CustomGroupDataException when field already exists
	 *
	 * @param string The name of the field to insert
	 */
	public function addField($field) {
		foreach($this->fields as $dataField) {
			if($dataField == $field) {
				throw new \TMT\exception\CustomGroupDataException(3, "Field ".$field." already exists");
				return;
			}
		}
		$data[] = $field;
	}

	/**
	 * Deletes the field with the given name if no
	 * such field exists it does nothing
	 *
	 * @param string The name of the field to delete
	 */
	public function deleteField($field) {
		for($i = 0; $i < count($this->fields); $i++) {
			if($this->fields[$i] == $field) {
				unset($this->fields[$i]);
				return;
			}
		}
	}

	/**
	 * Returns an array of all the names of the fields for the group
	 *
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Returns the id of the group
 	 *
	 * @return int
	 */
	public function getId() {
		return $this->group;
	}

	/**
	 * Determines whether a given field exists or not
	 *
	 * @param $field string A field name
	 *
	 * @return bool true if it exists, false otherwise
	 */
	public function fieldExists($field) {
		foreach($this->fields as $dataField) {
			if($dataField == $field) {
				return true;
			}
		}
		return false;
	}
}
?>
