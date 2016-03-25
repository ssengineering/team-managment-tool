<?php

namespace TMT\app;

/**
 * The controller for the quicklinks app
 */
class quicklinks extends \TMT\App {

	public function index($params) {
		$this->forceAuthentication();

		$this->render("quicklinks/index");
	}
}
