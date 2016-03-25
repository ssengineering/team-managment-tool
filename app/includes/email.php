<?php

/**
 * This is an adaptation of the new EmailHandler class
 *   to be used with the old framework
 *
 * Sends an email from the no reply address specified by the environment variable NO_REPLY_ADDRESS
 *
 * @param stdObject A stdObject with the following fields defined:
 *   recipients: string/array(string) an email address or array of them
 *   subject:    string The subject of the email
 *   message:    string The message to send in the email
 *   cc:         string/array(string) an email address or array of them to cc in to the email
 *   bcc:        string/array(string) an email address or array of them to bcc in to the email
 *
 * @return bool  true if email sent, false otherwise
 */
function sendEmail($email) {
	// If any necessary piece was not given, don't try to send the email
	if($email->recipients == null || $email->subject == null || $email->message == null)
		return false;

	// Test if cc and bcc are set
	$email->cc  = isset($email->cc)  ? $email->cc  : null;
	$email->bcc = isset($email->bcc) ? $email->bcc : null;

	// If there is an array of recipients/cc/bcc instead of just one, turn it into a comma delimited email string
	$email->recipients = is_array($email->recipients) ? implode(", ", $email->recipients) : $email->recipients;

	// If cc is set
	if($email->cc != null)
		$email->cc  = is_array($email->cc)  ? implode(",", $email->cc)  : $email->cc;

	// If bcc is set
	if($email->bcc != null)
		$email->bcc = is_array($email->bcc) ? implode(",", $email->bcc) : $email->bcc;


	// Generate headers
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=utf-8\r\n";
	$headers .= "From: ".getenv("NO_REPLY_ADDRESS")."\r\n";

	// Add CC and BCC headers as needed
	if($email->cc != null)
		$headers .= "Cc: ".$email->cc."\r\n";
	if($email->bcc != null)
		$headers .= "Bcc: ".$email->bcc."\r\n";

	// Send email
	return mail(
		$email->recipients,
		$email->subject,
		$email->message,
		$headers
	);
}
