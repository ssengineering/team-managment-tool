<?php

namespace TMT\model;

/**
 * The model class for the notifications
 */
class Notification extends Model {

	/**
	 * The guid of the notification
	 */
	public $guid;

	/**
	 * date The notifications's timestamp
	 */
	public $timestamp;

	/**
	 * string The notifications's content
	 */
	public $message;

	/**
	 * string The notifications's type
	 */
	public $type;

	/**
	 * string The notifications's area
	 */
	public $area;

	/**
	 * The constructor to populate the model
	 */
	public function __construct($notification = null) {
		if ($notification == null)
			return;
		if (is_array($notification))
			$notification = (object) $notification;

		$this->guid      = isset($notification->guid)      ? $notification->guid      : null;
		$this->timestamp = isset($notification->timestamp) ? $notification->timestamp : null;
		$this->type      = isset($notification->type)      ? $notification->type      : null;
		$this->area      = isset($notification->area)      ? $notification->area      : null;
		$this->message   = isset($notification->message)   ? $notification->message   : null;
	}
}
