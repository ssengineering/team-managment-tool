<?php

namespace TMT\app;

/**
 * The controller for the employeeList app
 */
class employeeList extends \TMT\App {

	/**
	 * Renders the main employee page
	 */
	public function index($params) {
		$this->forceAuthentication();

		// Gather data
		$data = array(
			"terminatedPerm"  => $this->can("read", "59b0f789-6bb6-414d-a860-ca61fdcf372f") ? 1 : 0, //terminatedEmployees resource
			"editPerm"        => $this->can("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2") ? 1 : 0, // employee resource
			"piPhotoUrl"      => getenv("BYU_PI_PHOTO")
		);

		$this->render("employeeList/index", $data);
	}

	public function table($params) {
		$this->forceAuthentication();

		// Get custom group data fields
		$customAcc = $this->getAccessor("CustomGroupData");
		try {
			$group = $customAcc->get((int)$this->user['area']);
			$fields = $group->getFields();
		} catch(\TMT\exception\CustomGroupDataException $e) {
			// If the group doesn't exist
			$fields = array();
		}

		$data = array(
			"terminatedPermission" => $this->can("read", "59b0f789-6bb6-414d-a860-ca61fdcf372f") ? 1 : 0, //terminatedEmployees resource
			"customDataFields"     => $fields,
			"piPhotoUrl"           => getenv("BYU_PI_PHOTO")
		);

		$this->render("employeeList/table", $data);
	}
}
