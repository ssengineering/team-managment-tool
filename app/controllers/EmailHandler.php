<?php

namespace TMT\controller;

/**
 * The controller that handles sending emails
 *
 * Usage:
 *   In order to properly use this Controller, first build
 *   an email model appropriately. Then pass the model to
 *   this class' constructor. Then call sendEmail()
 */
class EmailHandler extends \TMT\Controller {

	/**
	 * @var \TMT\model\Email
	 */
	private $email;

	/**
	 * Constructor that receives an email and processes it
	 *
	 * @param $email \TMT\model\Email An email model that will be sent
	 *
	 * @throws catchable fatal error if parameter given is not an email model
	 * @throws \TMT\exception\EmailException if the given email model is not formed properly
	 */
	public function __construct(\TMT\model\Email $email) {
		$this->email = $email;
		$this->processEmail();
	}

	/**
	 * Processes the email to prepare it for sending
	 *   and check that all required fields are present
	 *
 	 * @throws \TMT\exception\EmailException when the members of $this->email
	 *    are not given correctly or 1 or more required fields are missing
	 */
	private function processEmail() {
		// If any necessary piece was not given, don't try to send the email
		if($this->email->recipients == null || $this->email->subject == null || $this->email->message == null)
			throw new \TMT\exception\EmailException(1);


		// Check that a recipient exists in array or string form
		if(!(is_string($this->email->recipients) || is_array($this->email->recipients)))
			throw new \TMT\exception\EmailException(2, "Recipients are not specified in array or string format");

		// if cc and bcc are given, make sure they are in string or array form
		if(!is_string($this->email->cc) && !is_array($this->email->cc) && $this->email->cc != null)
			throw new \TMT\exception\EmailException(2, "cc recipients given, but not in string or array format");
		if(!is_string($this->email->bcc) && !is_array($this->email->bcc) && $this->email->bcc != null)
			throw new \TMT\exception\EmailException(2, "bcc recipients given, but not in string or array format");


		// If there is an array of recipients/cc/bcc instead of just one, turn it into a comma delimited email string
		$this->email->recipients = is_array($this->email->recipients) ? implode(", ", $this->email->recipients) : $this->email->recipients;

		// If cc is set
		if($this->email->cc != null)
			$this->email->cc  = is_array($this->email->cc)  ? implode(",", $this->email->cc)  : $this->email->cc;

		// If bcc is set
		if($this->email->bcc != null)
			$this->email->bcc = is_array($this->email->bcc) ? implode(",", $this->email->bcc) : $this->email->bcc;
	}

	/**
	 * Sends the email specified in the constructor
	 *
	 * @return bool True if the email was sent, false if not
	 *
	 * NOTE: Just because this function returns true does not guarantee
	 *   that it will reach the intended recipient, see php documentation
	 *   for the mail function for further information
	 */
	public function sendEmail($test_address = null) {
		if($test_address == null)
			$test_address = getenv("DEV_EMAIL_ADDRESS");

		$env = $this->getEnvironment();
		if ($env != "PROD") {
			$this->email->message = "ENVIRONMENT: $env<br>".
				"TO: ".$this->email->recipients."<br>".
				"CC: ".$this->email->cc."<br>".
				"BCC: ".$this->email->bcc."<br>".
				"MESSAGE:<br><br>".
				$this->email->message;
			$this->email->recipients = $test_address;
			$this->email->cc = null;
			$this->email->bcc = null;
		}
		// Generate headers
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n";
		$headers .= "From: ".getenv("NO_REPLY_ADDRESS")."\r\n";

		// Add CC and BCC headers as needed
		if($this->email->cc != null)
			$headers .= "Cc: ".$this->email->cc."\r\n";
		if($this->email->bcc != null)
			$headers .= "Bcc: ".$this->email->bcc."\r\n";

		// Send email
		return mail(
			$this->email->recipients,
			$this->email->subject,
			$this->email->message,
			$headers
		);
	}

	/**
	 * Returns the email object container by this controller
	 *
	 * @return \TMT\model\Email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Change the email object
	 *
	 * NOTE: This is identical in functionality to the constructor
	 *
	 * @param \TMT\model\Email The new email information to use
	 *
	 * @throws catchable fatal error if parameter given is not an email model
	 * @throws \TMT\exception\EmailException if the given email model is not formed properly
	 */
	public function setEmail(\TMT\model\Email $email) {
		$this->email = $email;
		$this->processEmail();
	}
}
