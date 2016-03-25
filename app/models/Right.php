<?php

namespace TMT\model;

/**
 * The model class for the `employeeRights` table
 */
class Right extends Model {

	// guid
	public $guid;

	// Unique ID
	public $ID;

	// Short name for right
	public $rightName;

	// Longer description of right
	public $description;

	// EMAIL or BASIC
	public $rightType;

	// Certification level for the right
	public $rightLevel;

	// id of area the right applies to
	public $area;


	/**
	 * Constructor for the model which takes an object such as would
	 *  be returned by a query to the database through the PDO connector.
	 *  This allows the result from a query to be passed directly to this
	 *  constructor. 
	 */
	public function __construct($right = null) {
		if($right == null)
			return;
		$right = (object) $right;

		$this->guid = isset($right->guid) ? $right->guid : null;
		$this->ID = isset($right->ID) ? $right->ID : null;
		$this->rightName = isset($right->rightName) ? $right->rightName : null;
		$this->description = isset($right->description) ? $right->description : null;
		$this->rightType = isset($right->rightType) ? $right->rightType : null;
		$this->rightLevel = isset($right->rightLevel) ? $right->rightLevel : null;
		$this->area = isset($right->area) ? $right->area : null;
	}
}

?>
