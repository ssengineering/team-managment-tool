<?php

namespace TMT\model;

/**
 * The model class for notification types
 */
class NotificationPreference extends Model {

	/**
	 * @var $type string The notification type guid
	 */
	public $type;

	/**
	 * @var $area string The area guid
	 */
	public $area;

	/**
	 * @var $netId string The netId of the user who's receiving the notification
	 */
	public $netId;

	/**
	 * @var $method string The notification method
	 */
	public $method;

	/**
	 * @var $email string The user's email address
	 */
	public $email;

	/**
	 * Constructor to populate the model
	 */
	public function __construct($preference = null) {
		if($preference == null)
			return;
		if(is_array($preference))
			$preference = (object) $preference;

		$this->type     = isset($preference->type)     ? $preference->type     : null;
		$this->area     = isset($preference->area)     ? $preference->area     : null;
		$this->netId    = isset($preference->netId)    ? $preference->netId    : null;
		$this->method   = isset($preference->method)   ? $preference->method   : null;
		$this->email    = isset($preference->email)    ? $preference->email    : null;
	}
}
