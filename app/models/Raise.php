<?php

namespace TMT\model;

/**
 * The model class for the employeeRaiseLog table
 */
class Raise extends Model {

	public $guid;

	public $index;

	public $netID;

	public $raise;

	public $newWage;

	public $submitter;

	public $date;

	public $comments;

	public $isSubmitted;

  /**
   * Constructor for the model which takes an object such as would
   *  be returned by a query to the database through the PDO connector.
   *  This allows the result from a query to be passed directly to this
   *  constructor. 
   */
  public function __construct($raise = null) {
    if($raise == null)
		return;
	if (is_array($raise))
		$raise = (object) $raise;

	$this->guid         = isset($raise->guid) ? $raise->guid : null;
	$this->index		= isset($raise->index) ? $raise->index : null;
	$this->netID		= isset($raise->netID) ? $raise->netID : null;
	$this->raise		= isset($raise->raise) ? $raise->raise : null;
	$this->newWage		= isset($raise->newWage) ? $raise->newWage : null;
	$this->submitter	= isset($raise->submitter) ? $raise->submitter : null;
	$this->date			= isset($raise->date) ? $raise->date : null;
	$this->comments		= isset($raise->comments) ? $raise->comments : null;
	$this->isSubmitted	= isset($raise->isSubmitted) ? $raise->isSubmitted : null;
  }
}

?>
