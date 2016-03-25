<?php

namespace TMT\model;

/**
 * The model class for notification types
 */
class NotificationType extends Model {

	/**
	 * @var $guid
	 */
	public $guid;

	/**
	 * @var $name string The name of the notification type
	 */
	public $name;

	/**
	 * @var $resource string The resource guid required to access this notification
	 */
	public $resource;

	/**
	 * @var $verb string The action required to access this notification
	 */
	public $verb;

	/**
	 * Constructor to populate the model
	 */
	public function __construct($type = null) {
		if($type == null)
			return;
		if(is_array($type))
			$type = (object) $type;

		$this->guid     = isset($type->guid)     ? $type->guid     : null;
		$this->name     = isset($type->name)     ? $type->name     : null;
		$this->resource = isset($type->resource) ? $type->resource : null;
		$this->verb     = isset($type->verb)     ? $type->verb     : null;
	}
}
