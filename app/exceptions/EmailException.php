<?php

namespace TMT\exception;

/**
 * Used for throwing exceptions relating to
 *   the Email model and EmailHandler Controller classes.
 *
 * Usage:
 *   This exception takes the standard exception parameters
 *     It can be left all default for standard Exception functionality,
 *     however, it is recommended to supply one of the following error codes:
 *       1: Information necessary to send the email is missing (such as recipient(s))
 *       2: The email object has invalidly typed members, i.e. int for recipient
 *
 *   Using one of these codes the message will automatically generate,
 *      but if a different message is desired, that can be supplied as the second parameter
 */
class EmailException extends \Exception {

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
				$message = ($message == "") ? "Missing information necessary to send email" : $message;
				break;
			case 2:
				$message = ($message == "") ? "Bad parameter type given" : $message;
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
