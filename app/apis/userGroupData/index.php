<?php

namespace TMT\api\userGroupData;

/**
 * The api router class for UserGroupData
 *
 * Handles requests for the route /api/userGroupData/
 */
class index extends \TMT\APIController {

	/**
	 * Get a user's information custom group information
	 * GET /api/userGroupData/:netId/:groupId
	 *
	 * NOTE: if the user does not exist, it will be
	 *   created. If it exists and does not
	 *   have an entry for the given group, an entry
	 *   for the group will be created with blank
	 *   data.
	 *
	 * returns: 
	 * {
	 *    Status: OK,
	 *    data: {
	 *       netId: "netId",
	 *       group: x,
	 *       fields: {
	 *          field: value,
	 *          field: value
	 *       }
	 *    }
	 * }
	 */
	public function get($params) {
		$this->requireAuthentication();
		$userAccessor = new \TMT\accessor\UserGroupData();
		$areaAccessor = new \TMT\accessor\AreaAccessor();

		$netId = isset($params['url'][2]) ?      $params['url'][2] : null;
		$group = isset($params['url'][3]) ? (int)$params['url'][3] : null;

		if($netId == null || $group == null) {
			$this->error("Invalid netId or group");
			return;
		}

		// Ensure both user and employee have rights to access the group
		if(!$areaAccessor->checkAreaRights($netId, $group) || !$areaAccessor->checkAreaRights($this->user['netId'], $group)) {
			$this->error("You do not have rights to access this employee's data");
			return;
		}

		try {
			$user = $userAccessor->get($netId, $group);
		} catch(\TMT\exception\CustomGroupDataException $e) {
			if($e->getCode() == 5) {
				// User does not have any data for this group in mongo, but the user exists
				try {
					$userAccessor->addGroup($netId, $group); // Grant access since access to group has already been verified
					$user = $userAccessor->get($netId, $group);
				} catch(\TMT\exception\CustomGroupDataException $e) {
					$this->error($e->getMessage());
					return;
				}
			} else if($e->getCode() == 8) {
				// User does not exist
				try {
					$userAccessor->create($netId, $group);
					$user = $userAccessor->get($netId, $group);
				} catch(\TMT\exception\CustomGroupDataException $e) {
					$this->error($e->getMessage());
					return;
				}
			} else {
				$this->error($e->getMessage());
				return;
			}
		}

		$this->respond($user);
	}

	/**
	 * Update a user's custom group information
	 * PUT /api/userGroupData/:netId/:group
	 *   requires put data to have any/all of the custom data fields with their new values
	 *
	 * Any data fields specified that don't exist will be ignored.
	 * Not all data fields have to be passed in if only some of them are
	 *   being updated, it won't affect the other data fields
	 *
	 * returns: 
	 * {
	 *    Status: OK,
	 *    data: success
	 * }
	 */
	public function put($params) {
		$this->requireAuthentication();
		$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");
		$userAccessor = new \TMT\accessor\UserGroupData();
		$areaAccessor = new \TMT\accessor\AreaAccessor();

		$netId = isset($params['url'][2]) ?      $params['url'][2] : null;
		$group = isset($params['url'][3]) ? (int)$params['url'][3] : null;
		$data = $params['request'];

		// In the request body, any key with spaces converts them to underscores.
		//   In order to account for both cases, keep the underscore version
		//   and create a version with spaces. (If one of the fields doesn't exist it gets ignored)
		foreach($data as $key => $value) {
			$newKey = str_replace("_", " ", $key);
			$data[$newKey] = $value;
		}

		if($netId == null || $group == null) {
			$this->error("Invalid netId or group");
			return;
		}

		// Ensure both user and employee have rights to access the group
		if(!$areaAccessor->checkAreaRights($netId, $group) || !$areaAccessor->checkAreaRights($this->user['netId'], $group)) {
			$this->error("You do not have rights to access this employee's data");
			return;
		}

		// Retrieve the user's previous data
		try {
			$user = $userAccessor->get($netId, $group);
		} catch(\TMT\exception\CustomGroupDataException $e) {
			// User does not have any data for this group in mongo, but the user exists
			if($e->getCode() == 5) {
				try {
					$userAccessor->addGroup($netId, $group); // Grant access since access to group has already been verified
					$user = $userAccessor->get($netId, $group);
				} catch(\TMT\exception\CustomGroupDataException $e) {
					$this->error($e->getMessage());
					return;
				}
			} else if($e->getCode() == 8) {
				// User does not exist
				try {
					$userAccessor->create($netId, $group);
					$user = $userAccessor->get($netId, $group);
				} catch(\TMT\exception\CustomGroupDataException $e) {
					$this->error($e->getMessage());
					return;
				}
			} else {
				$this->error($e->getMessage());
				return;
			}
		}

		// Update each field of the user's data
		foreach($data as $field => $value) {
			try {
				$user->editField($field, $value);
			} catch(\TMT\exception\CustomGroupDataException $e) {
				if($e->getCode() == 4) {
					continue; // Ignore if a field was passed in that doesn't exist
				} else {
					$this->error($e->getMessage());
					return;
				}
			}
		}
		$userAccessor->update($user);

		$this->respond("success");
	}

	/**
	 * Removes a user from a group
	 *   This should be used in conjunction with
	 *   revoking a user's access to an area, it
	 *   will remove all data for this user for
	 *   this group.
	 *
	 * NOTE: This will delete the embedded document
	 *         for the given user that holds the group
	 *         information, but if the api is called to
	 *         get the same user with the same group,
	 *         it will be recreated with empty data,
	 *         unless the user's rights to the group
	 *         have been revoked.
	 *
	 * DELETE /api/userGroupData/:netId/:group
	 *
	 * returns:
	 * {
	 *    status: OK,
	 *    data: success
	 * }
	 */
	public function delete($params) {
		$this->requireAuthentication();
		$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");
		$userAccessor = new \TMT\accessor\UserGroupData();
		$areaAccessor = new \TMT\accessor\AreaAccessor();

		$netId = isset($params['url'][2]) ?      $params['url'][2] : null;
		$group = isset($params['url'][3]) ? (int)$params['url'][3] : null;
		
		if($netId == null || $group == null) {
			$this->error("Invalid netId or group");
			return;
		}

		// Ensure both user and employee have rights to access the group
		if(!$areaAccessor->checkAreaRights($this->user['netId'], $group)) {
			$this->error("You do not have rights to access this employee's data");
			return;
		}

		$userAccessor->removeGroup($netId, $group);
		$this->respond("success");
	}
}
?>
