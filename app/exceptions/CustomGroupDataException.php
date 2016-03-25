<?php

namespace TMT\exception;

/**
 * Used for throwing exceptions relating to
 *   the CustomGroupData accessor class.
 *
 * Usage:
 *   This exception takes the standard exception parameters
 *     It can be left all default for standard Exception functionality,
 *     however, it is recommended to supply one of the following error codes:
 *       1: Group already exists (for trying to create a new one)
 *       2: Group does not exist (for trying to access one)
 *       3: Field does already exists (when trying to add a field to the model)
 *       4: Field does not exist (when trying to access a field in the model)
 *       5: User exists, but not with the given group
 *       6: Creating a user without a group
 *       7: User already exists
 *       8: User does not exist in the database at all
 *
 *   Using one of these codes the message will automatically generate,
 *      but if a different message is desired, that can be supplied as the second parameter
 */
class CustomGroupDataException extends \Exception {

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
				$message = ($message == "") ? "Group already exists" : $message;
				break;
			case 2:
				$message = ($message == "") ? "Group does not exist" : $message;
				break;
			case 3:
				$message = ($message == "") ? "Field already exists" : $message;
				break;
			case 4:
				$message = ($message == "") ? "Field does not exist" : $message;
				break;
			case 5:
				$message = ($message == "") ? "No user with the given group and netId exists" : $message;
				break;
			case 6:
				$message = ($message == "") ? "Cannot create user without a group" : $message;
				break;
			case 7:
				$message = ($message == "") ? "User already exists" : $message;
				break;
			case 8:
				$message = ($message == "") ? "User does not exist" : $message;
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
