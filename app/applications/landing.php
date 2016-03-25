<?php

namespace TMT\app;

/**
 * The landing page Controller
 */
class landing extends \TMT\App {

	/**
	 * Renders the landing page
	 */
	public function index($params) {

		// If the user is authenticated, redirect to the main page
		if($this->authenticated) {
			header("Location: /");
			return;
		}

		$this->render("helpers/landing");
	}
}
