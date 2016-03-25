<?php

namespace TMT\model;

/**
 * The model class for the majorIncidentManagers table
 */
class Mim extends Model {

  /**
   * The guid of the mim entry
   */
  public $guid;

  /**
   * string The MIM's Net ID
   */
  public $netID;
  
  /**
   * string The MIM's first name 
   */
  public $firstName;
  
  /**
   * string The MIM's last name 
   */
  public $lastName;

  /**
   * Constructor for the model which takes an object such as would
   *  be returned by a query to the database through the PDO connector.
   *  This allows the result from a query to be passed directly to this
   *  constructor. 
   */
  public function __construct($mim = null) {
    if($mim == null)
		return;
	if (is_array($mim))
		$mim = (object) $mim;

	$this->guid              = isset($mim->guid)      ? $mim->guid      : null;
    $this->netID             = isset($mim->netID)     ? $mim->netID     : null;
    $this->firstName         = isset($mim->firstName) ? $mim->firstName : null;
    $this->lastName          = isset($mim->lastName)  ? $mim->lastName  : null;
  }
}

?>
