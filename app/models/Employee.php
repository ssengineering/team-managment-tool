<?php

namespace TMT\model;

/**
 * The model class for the employee table
 */
class Employee extends Model {

	/**
	 * string The user's guid
	 */
	public $guid;

	/**
	 * string The user's netID
	 */
	public $netID;

	/** * int The user's active status
	 *   -1 Terminated
	 *    0 Inactive
	 *    1 Active
	 */
	public $active;

	/**
	 * int area id
	 */
	public $area;

	/**
	 * string User's first name
	 */
	public $firstName;

	/**
	 * string User's last name
	 */
	public $lastName;

	/**
	 * string User's maiden name
	 */
	public $maidenName;

	/**
	 * string User's phone number
	 */
	public $phone;

	/**
	 * string User's email
	 */
	public $email;

	/**
	 * string User's birthday
	 */
	public $birthday;

	/**
	 * string Languages that the user speaks
	 */
	public $languages;

	/**
	 * string User's hometown
	 */
	public $hometown;

	/**
	 * string User's major
	 */
	public $major;

	/**
	 * string User's mission or study abroad place
	 */
	public $missionOrStudyAbroad;

	/**
	 * string User's date of graduation
	 */
	public $graduationDate;

	/**
	 * int User's position
	 */
	public $position;

	/**
	 * string User's shift
	 */
	public $shift;

	/**
	 * string User's supervisor
	 */
	public $supervisor;

	/**
	 * string User's hireDate
	 */
	public $hireDate;

	/**
	 * string User's certificationLevel
	 */
	public $certificationLevel;

	/**
	 * int User's international
	 *   0 not international
	 *   1 international
	 */
	public $international;

	/**
	 * string User's byuIDnumber
	 */
	public $byuIDnumber;

	/**
	 * int User's fullTime
	 *  0 non full-time
	 *  1 full time
	 */
	public $fullTime;

	/**
	 * Constructor for the model which takes an object such as would
	 *  be returned by a query to the database through the PDO connector.
	 *  This allows the result from a query to be passed directly to this
	 *  constructor. 
	 */
	public function __construct($employee = null) {
		if($employee == null)
			return;
		$this->guid                 = isset($employee->guid) ? $employee->guid : null;
		$this->netID                = isset($employee->netID) ? $employee->netID : null;
		$this->active               = isset($employee->active) ? (int)$employee->active : null;
		$this->area                 = isset($employee->area) ? (int)$employee->area : null;
		$this->firstName            = isset($employee->firstName) ? $employee->firstName : null;
		$this->lastName             = isset($employee->lastName) ? $employee->lastName : null;
		$this->maidenName           = isset($employee->maidenName) ? $employee->maidenName : null;
		$this->phone                = isset($employee->phone) ? $employee->phone : null;
		$this->email                = isset($employee->email) ? $employee->email : null;
		$this->birthday             = isset($employee->birthday) ? $employee->birthday : null;
		$this->languages            = isset($employee->languages) ? $employee->languages : null;
		$this->hometown             = isset($employee->hometown) ? $employee->hometown : null;
		$this->major                = isset($employee->major) ? $employee->major : null;
		$this->missionOrStudyAbroad = isset($employee->missionOrStudyAbroad) ? $employee->missionOrStudyAbroad : null;
		$this->graduationDate       = isset($employee->graduationDate) ? $employee->graduationDate : null;
		$this->position             = isset($employee->position) ? (int)$employee->position : null;
		$this->shift                = isset($employee->shift) ? $employee->shift : null;
		$this->supervisor           = isset($employee->supervisor) ? $employee->supervisor : null;
		$this->hireDate             = isset($employee->hireDate) ? $employee->hireDate : null;
		$this->certificationLevel   = isset($employee->certificationLevel) ? $employee->certificationLevel : null;
		$this->international        = isset($employee->international) ? (int)$employee->international : null;
		$this->byuIDnumber          = isset($employee->byuIDnumber) ? $employee->byuIDnumber : null;
		$this->fullTime             = isset($employee->fullTime) ? (int)$employee->fullTime : null;
	}
}
?>
