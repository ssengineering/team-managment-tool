<?php

namespace TMT\model;

/**
 * The email model class
 */
class Email extends Model {

	/**
	 * @var $recipients string/array(string)
	 *    a string/array(string) which are email addresses who will
	 *    receive the email
	 */
	public $recipients;

	/**
	 * @var $subject string The subject of the email
	 */
	public $subject;

	/**
	 * @var $message string The message to send in the email
	 */
	public $message;

	/**
	 * @var $cc string/array(string)
	 *    a string/array(string) which are email addresses who
	 *    will be carbon copied into the email
	 */
	public $cc;

	/**
	 * @var $cc string/array(string)
	 *    a string/array(string) which are email addresses who
	 *    will be blind carbon copied into the email
	 */
	public $bcc;

	/**
	 * Constructor to populate the email
	 *   model
	 */
	public function __construct($email = null) {
		if($email == null)
			return;
		$email = (object) $email;
		$this->recipients = isset($email->recipients) ? $email->recipients : null;
		$this->subject    = isset($email->subject)    ? $email->subject    : null;
		$this->message    = isset($email->message)    ? $email->message    : null;
		$this->cc         = isset($email->cc)         ? $email->cc         : null;
		$this->bcc        = isset($email->bcc)        ? $email->bcc        : null;
	}
}
