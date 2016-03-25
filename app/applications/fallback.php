<?php

namespace TMT\app;

/**
 * The fallback Controller
 *
 * This controller is only routed to in the case
 *   that the user tries to access a nonexistent page
 */
class fallback extends \TMT\App {

	/**
	 * Takes the user to the landing page if the user is not logged in
	 *   if they are logged in, it gives an error page
	 */
	public function index($params) {

		// Go to landing page if not authenticated
		if(!$this->authenticated) {
			header('Location: /landing');
			exit();
		}

		// Give an error page
		http_response_code(404);
		$this->render("helpers/notFound");
	}
}
