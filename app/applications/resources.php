<?php

namespace TMT\app;

/**
 * The controller for the resources app
 */
class resources extends \TMT\App {

	/**
	 * Renders the main employee page
	 */
	public function index($params) {
		$this->forceAuthentication();

		if($this->isSuperuser()) {
			$this->render("resources/index");
		} else {
			$this->error("You do not have permission to access this page", 403);
		}
	}
}
