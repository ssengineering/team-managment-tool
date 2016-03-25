<?php

namespace TMT\api\customData;

/**
 * The api router class for CustomData which controls
 *   the fields each group keeps track of.
 *
 * Handles requests for the route /api/customData/
 */
class index extends \TMT\APIController {

	/**
	 * Get group information, create the group if it doesn't exist
	 * GET /api/customData/:groupId
	 *
	 * returns: 
	 * {
	 *    Status: OK/failed,
	 *    data: {
	 *      id: x,
	 *      fields: [
	 *         "field1",
	 *         "field2"
	 *      ]
	 *    }
	 * }
	*/
	public function get($params) {
		$this->requireAuthentication();

		// Get group id, if none given, default to current group
		$groupId = isset($params['url'][2]) ? (int)$params['url'][2] : $this->user['area'];
		$groupAccessor = new \TMT\accessor\CustomGroupData();
		$areaAccessor  = new \TMT\accessor\AreaAccessor();

		if(!$areaAccessor->checkAreaRights($this->user['netId'], $groupId)) {
			$this->error("You do not have rights to this group");
			return;
		}

		try {
			$group = $groupAccessor->get($groupId);
		} catch(\TMT\exception\CustomGroupDataException $e) {
			if($e->getCode() === 2) { // Group does not exist
				try {
					$groupAccessor->create(array(), $groupId);
					$group = $groupAccessor->get($groupId);
				} catch(\TMT\exception\CustomGroupDataException $e2) {
					$this->error($e2->getMessage());
					return;
				}
			} else {
				$this->error($e->getMessage());
				return;
			}
		}

		$this->respond($group);
	}

	/**
	 * Add a new field to the group
	 *
	 * POST /api/customData/:groupId (requires field in POST data)
	 *
	 * returns: 
	 * {
	 *    status: OK/ERROR,
	 *    data: "success"
	 * }
	 */
	public function post($params) {
		$this->requireAuthentication();
		$this->forcePermission("update", "db9b1290-c9dd-4bad-b3f5-0374dd5ec2a7");
		$groupAccessor = new \TMT\accessor\CustomGroupData();

		// Parse id and fields
		if(!isset($params['url'][2]) || !is_numeric($params['url'][2])) {
			$this->error("group id is invalid");
			return;
		}
		if(!isset($params['request']['field'])) {
			$this->error("Field name was not specified");
			return;
		}
		$id    = (int)$params['url'][2];
		$field = $params['request']['field'];

		// Add field and respond
		$groupAccessor->addField($id, $field);
		$this->respond("success");
	}

	/**
	 * Rename a field
	 *
	 * PUT /api/customData/:groupId (requires 'old' and 'field' in PUT data)
	 *   The data is included in the body because they may occasionally
	 *   contain spaces which are filtered out in the URL by the router
	 *
	 * returns: 
	 * {
	 *    status: OK/ERROR,
	 *    data: "success"
	 * }
	 */
	public function put($params) {
		$this->requireAuthentication();
		$this->forcePermission("update", "db9b1290-c9dd-4bad-b3f5-0374dd5ec2a7");
		$groupAccessor = new \TMT\accessor\CustomGroupData();

		// Parse id and fields
		if(!isset($params['request']['old']) || !is_numeric($params['url'][2])) {
			$this->error("No group id given, or id is invalid");
			return;
		}
		if(!isset($params['request']['field'])) {
			$this->error("The new name for the field was not specified");
		}
		$id       = (int)$params['url'][2];
		$oldField = $params['request']['old'];
		$newField = $params['request']['field'];

		// Rename the field and respond
		$groupAccessor->renameField($id, $oldField, $newField);
		$this->respond("success");
	}

	/**
	 * Delete a field from the group
	 *
	 * DELETE /api/customData/:groupId (requires 'field' in request data)
	 *   The data is included in the body because they may occasionally
	 *   contain spaces which are filtered out in the URL by the router
	 *
	 * returns: 
	 * {
	 *    status: OK/ERROR,
	 *    data: "success"
	 * }
	 */
	public function delete($params) {
		$this->requireAuthentication();
		$this->forcePermission("update", "db9b1290-c9dd-4bad-b3f5-0374dd5ec2a7");
		$groupAccessor = new \TMT\accessor\CustomGroupData();

		// Parse id and fields
		if(!is_numeric($params['url'][2])) {
			$this->error("group id is invalid");
			return;
		}
		if(!isset($params['request']['field'])) {
			$this->error("The new name for the field was not specified");
		}
		$id    = (int)$params['url'][2];
		$field = $params['request']['field'];

		// Remove field and respond
		$groupAccessor->removeField($id, $field);
		$this->respond("success");
	}
}
?>
