<?php

namespace TMT\model;

/**
 * The model class for the position table
 */
class EmployeeTermination extends Model {

	/**
	 * The guid of the termination data
	 */
	public $guid;

    /**
     * A human-readable reason for the employee's termination
     */
    public $reasons;

    /**
     * A string discussing the attendance record of the employee
     */
    public $attendance;

    /**
     * A string discussing the attitude of the terminated employee
     */
    public $attitude;

    /**
     * A string discussing the performance of the terminated employee
     */
    public $performance;

    /**
     * A string with the netId of the employee
     */
    public $netID;

    /**
     * A string naming the submitter
     */
    public $submitter;

    /**
     * int The employee's area
     */
    public $area;

    /**
     * A bool stating whether the employee is rehirable
     */
    public $rehirable;

    /**
     * Constructor for the model which takes an object such as would
     *  be returned by a query to the database through the PDO connector.
     *  This allows the result from a query to be passed directly to this
     *  constructor. 
     */
    public function __construct($employee = null) {
        if ($employee == null)
            return;

		if(is_array($employee))
			$employee = (object)$employee;

		$this->guid               = isset($employee->guid) ? $employee->guid : null;
        $this->reasons            = isset($employee->reasons) ? $employee->reasons : null;
        $this->attendance         = isset($employee->attendance) ? $employee->attendance : null;
        $this->attitude           = isset($employee->attitude) ? $employee->attitude : null;
        $this->performance        = isset($employee->performance) ? $employee->performance : null;
        $this->netID              = isset($employee->netID) ? $employee->netID : null;
        $this->submitter          = isset($employee->submitter) ? $employee->submitter : null;
        $this->area               = isset($employee->area) ? (int)$employee->area : null;
        $this->rehirable          = isset($employee->rehirable) ? $employee->rehirable : null;
    }
}
