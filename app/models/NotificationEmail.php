<?php

namespace TMT\model;

/**
 * The model class for notification emails
 *   Represents an email for a non-TMT user
 *   attached to a certain type of notification
 */
class NotificationEmail extends Model {

	/**
	 * @var $guid
	 */
	public $guid;

	/**
	 * @var $email string The email address
	 */
	public $email;

	/**
	 * @var $type string The type guid
	 */
	public $type;

	/**
	 * @var $area string The area guid
	 */
	public $area;

	/**
	 * Constructor to populate the model
	 */
	public function __construct($email = null) {
		if($email == null)
			return;
		if(is_array($email))
			$email = (object) $email;

		$this->guid  = isset($email->guid)  ? $email->guid  : null;
		$this->email = isset($email->email) ? $email->email : null;
		$this->type  = isset($email->type)  ? $email->type  : null;
		$this->area  = isset($email->area)  ? $email->area  : null;
	}
}
