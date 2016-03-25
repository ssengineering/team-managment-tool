<?php

namespace TMT\app;

/**
 * The controller for the permissions management app
 */
class permissions extends \TMT\App {

	/**
	 * Renders the verb permission group page
	 */
	public function index($params) {
		$this->forceAuthentication();

		if($this->isAdmin() || $this->isSuperuser()) {
			$this->render("permissions/index");
		} else {
			$this->error("You must have admin rights to access this page", 403);
		}
	}
	
	/*
	 * Renders the main permission group page
	 */
	public function groups($params) {
		$this->forceAuthentication();

		$admin = $this->isAdmin() ? 1 : 0;
		if(!$admin)
			$admin = $this->isSuperuser() ? 1 : 0;

		if($admin || $this->can("organize", "d27d2880-79b6-4945-bf7d-c813d70c393a")) {
			$this->render("permissions/groups", array("admin" => $admin));
		} else {
			$this->error("You do not have permission to access this page", 403);
		}
	}

	/*
	 * Renders the explain permissions page
	 */
	public function explain($params) {
		$this->forceAuthentication();


		if($this->can("organize", "d27d2880-79b6-4945-bf7d-c813d70c393a")) {
			$this->render("permissions/explain");
		} else {
			$this->error("You do not have permission to access this page", 403);
		}
	}

	/**
	 * Render the admin crud app page
	 */
	public function admin($params) {
		$this->forceAuthentication();

		if($this->isAdmin() || $this->isSuperuser()) {
			$this->render("permissions/admin");
		} else {
			$this->error("You must have admin rights to access this page", 403);
		}
	}
}
