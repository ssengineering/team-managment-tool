<?php

namespace TMT\model;

/**
 * The model class for user notifications
 */
class UserNotification extends Model {

	/**
	 * @var $guid string The notification guid
	 */
	public $guid;

	/**
	 * @var $timestamp string The time the notification was sent
	 */
	public $timestamp;

	/**
	 * @var $type string The notification type guid
	 */
	public $type;

	/**
	 * @var $area string The area guid
	 */
	public $area;

	/**
	 * @var $message string The message
	 */
	public $message;

	/**
	 * @var $netId string The netId of the user who's receiving the notification
	 */
	public $netId;

	/**
	 * @var $read bool True if the notification has been read, false otherwise
	 */
	public $read;

	/**
	 * Constructor to populate the model
	 */
	public function __construct($notification = null) {
		if($notification == null)
			return;
		if(is_array($notification))
			$notification = (object) $notification;

		$this->guid      = isset($notification->guid)      ? $notification->guid      : null;
		$this->timestamp = isset($notification->timestamp) ? $notification->timestamp : null;
		$this->message   = isset($notification->message)   ? $notification->message   : null;
		$this->type      = isset($notification->type)      ? $notification->type      : null;
		$this->area      = isset($notification->area)      ? $notification->area      : null;
		$this->netId     = isset($notification->netId)     ? $notification->netId     : null;
		$this->read      = isset($notification->read)      ? $notification->read      : false;
	}
}
