<?php

namespace TMT\exception;

/**
 * Used for throwing exceptions relating to
 *   the Permission accessor class.
 *
 * Usage:
 *   This exception takes the standard exception parameters
 *     It can be left all default for standard Exception functionality,
 *     however, it is recommended to supply one of the following error codes:
 *       1: One or more parameters are incorrect/improper
 *       2: Area does not have permission
 *       3: The user does not have permission
 *
 *   Using one of these codes the message will automatically generate,
 *      but if a different message is desired, that can be supplied as the second parameter
 */
class PermissionException extends \Exception {

	/**
	 * Constructor
	 *
	 * @param $code     int       The exception code to use as defined above
	 * @param $message  string    A message to display to the user, defaults to a message related to the error code if supplied
	 * @param $previous Exception A previously thrown exception used by the base Exception class
	 */
	public function __construct($code = 0, $message = "", \Exception $previous = null) {

		switch($code) {
			case 1:
				$message = ($message == "") ? "Bad parameter(s)" : $message;
				break;
			case 2:
				$message = ($message == "") ? "Area does not have permission" : $message;
				break;
			case 3:
				$message = ($message == "") ? "User does not have permission" : $message;
				break;
			case 0:
			default:
				$message = ($message == "") ? "An error occurred" : $message;
		}

		// Call parent constructor
		parent::__construct($message, $code, $previous);
	}
}
?>
