<?php

namespace TMT\model;

/**
 * The model class for the position table
 */
class Position extends Model {

	/**
	 * The position guid
	 */
	public $guid;

    /**
     * int The user's position Id
     */
    public $positionId;

    /**
     * The name of the position
     */
    public $positionName;

    /**
     * A human-readable description of the position
     */
    public $positionDescription;

    /**
     * An int describing the area of employment
     */
    public $employeeArea;

    /**
     * A bool stating whether the position is deleted
     */
    public $deleted;

    /**
     * Constructor for the model which takes an object such as would
     *  be returned by a query to the database through the PDO connector.
     *  This allows the result from a query to be passed directly to this
     *  constructor. 
     */
    public function __construct($position = null) {
        if ($position == null)
            return;

		if (is_array($position))
			$position = (object) $position;

		$this->guid                = isset($position->guid) ? $position->guid : null;
        $this->positionId          = isset($position->positionId) ? (int)$position->positionId : null;
        $this->positionName        = isset($position->positionName) ? $position->positionName : null;
        $this->positionDescription = isset($position->positionDescription) ? $position->positionDescription : null;
        $this->employeeArea        = isset($position->employeeArea) ? (int)$position->employeeArea : null;
        $this->deleted             = isset($position->deleted) ? (int)$position->deleted : null;
    }
}
