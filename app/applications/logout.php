<?php

namespace TMT\app;

/**
 * The logout controller
 */
class logout extends \TMT\App {

	/**
	 * Logs the user out / destroys the CAS session
	 */
	public function index() {
		if(\phpCAS::checkAuthentication()) {
			\phpCAS::logout();
		}
		session_destroy();
		header("Location: /landing");
		return;
	}
}
