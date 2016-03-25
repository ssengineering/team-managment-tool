<?php

namespace TMT\app;

/**
 * The Login Controller
 */
class login extends \TMT\App {

	/**
	 * Override parent constructor to not initialize
	 */
	public function __construct() {}

	/**
	 * Forces the user to authenticate through CAS then redirects to the homepage
	 */
	public function index() {
		$this->initialize();
		$this->forceAuthentication();
		header('Location: /');
	}

	/**
	 * Logs the user in to the TMT through LDAP
	 *
	 * Expects the netId and password fields to be set in the request
	 */
	public function ldap($params) {
		session_start();
		$netId    = isset($params['request']['netId'])    ? $params['request']['netId']    : null;
		$password = isset($params['request']['password']) ? $params['request']['password'] : null;
		if($netId == null || $password == null) {
			session_destroy();
			header('Location: /landing');
			exit();
		}

		$link = \ldap_connect(getenv("LDAP_URL"),389);
		if ($link) {
			\ldap_set_option($link, LDAP_OPT_SIZELIMIT, 2);
			$authenticated = @\ldap_bind($link, 'uid='.$netId.',ou=People,o=byu.edu', $password);
			if ($authenticated) {
				$_SESSION['user'] = $netId;
				$_SESSION['ldap'] = true;
				header('Location: /');
				exit();
			} else {
				session_destroy();
				$this->user['netId'] = null;
				$this->render("helpers/loginFail");
				exit();
			}
		} else {
			// Unable to connect to LDAP, return to landing page
			session_destroy();
			header('Location: /landing');
			exit();
		}
	}
}
