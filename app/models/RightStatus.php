<?php

namespace TMT\model;

/**
 * The model class for the `employeeRightsStatus` table
 */
class RightStatus extends Model {

	public $guid;

	// Unique ID
	public $ID;
	
	// Unique ID of right
	public $rightID;

	// Net ID of employee granted the right
	public $netID;

	// 1 - Employee's rights have been requested
	// 2 - Employee's rights have been confirmed
	// 3 - Employee's rights have been revoked
	public $rightStatus;

	// Manager who most requested the employee's right
	public $requestedBy;

	// Date right was requested
	public $requestedDate;

	// Manager who confirmed the granting of this right
	public $updatedBy;
	
	// Date right was confirmed
	public $updatedDate;

	// Manager who requested the right be revoked
	public $removedBy;
	
	// Date the right was requested to be revoked
	public $removedDate;


	/**
	 * Constructor for the model which takes an object such as would
	 *  be returned by a query to the database through the PDO connector.
	 *  This allows the result from a query to be passed directly to this
	 *  constructor. 
	 */
	public function __construct($rightStatus = null) {
		if($rightStatus == null)
			return;
		if (is_array($rightStatus))
			$rightStatus = (object) $rightStatus;

		$this->guid = isset($rightStatus->guid) ? $rightStatus->guid : null;
		$this->ID = isset($rightStatus->ID) ? $rightStatus->ID : null;
		$this->rightID = isset($rightStatus->rightID) ? $rightStatus->rightID : null;
		$this->netID = isset($rightStatus->netID) ? $rightStatus->netID : null;
		$this->rightStatus = isset($rightStatus->rightStatus) ? $rightStatus->rightStatus : null;
		$this->requestedBy = isset($rightStatus->requestedBy) ? $rightStatus->requestedBy : null;
		$this->requestedDate = isset($rightStatus->requestedDate) ? $rightStatus->requestedDate : null;
		$this->updatedBy = isset($rightStatus->updatedBy) ? $rightStatus->updatedBy : null;
		$this->updatedDate = isset($rightStatus->updatedDate) ? $rightStatus->updatedDate : null;
		$this->removedBy = isset($rightStatus->removedBy) ? $rightStatus->removedBy : null;
		$this->removedDate = isset($rightStatus->removedDate) ? $rightStatus->removedDate : null;
	}
}

?>
