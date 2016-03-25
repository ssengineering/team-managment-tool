<?php

namespace TMT\app;

/**
 * The controller for the notifications app
 */
class notifications extends \TMT\App {

	/**
	 * Renders the notification inbox page
	 */
	public function index($params) {
		$this->forceAuthentication();

		$this->render("notifications/index");
	}

	/**
	 * Renders the notification types CRUD app page
	 */
	public function types($params) {
		$this->forceAuthentication();

		if($this->isSuperuser()) {
			$this->render("notifications/types");
		} else {
			$this->error("You do not have permission to access this page", 403);
		}
	}


	/**
	 * Renders the user notification preferences page
	 */
	public function preferences($params) {
		$this->forceAuthentication();

		$isAdmin = $this->isAdmin() ? 1 : 0;
		$data = array(
			"isAdmin" => $isAdmin
		);

		$this->render("notifications/preferences", $data);
	}
}
