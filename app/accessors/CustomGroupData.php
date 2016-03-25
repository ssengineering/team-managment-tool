<?php

namespace TMT\accessor;

/**
 * An accessor class to interact with the database
 *  for CustomGroupData information
 */
class CustomGroupData extends MongoAccessor {

	/**
	 * Retrieves the information for the group with the given id number
	 *
	 * @param $id int The group id
	 *
	 * @throws \TMT\exception\CustomGroupDataException when no group exists with the given id
	 *
	 * @return \tmt\model\CustomGroupData model with the given id number
	 */
	public function get($id) {
		$collection = $this->db->selectCollection("groups");
		$result = $collection->findOne(array('id' => $id));
		if($result == null)
			throw new \TMT\exception\CustomGroupDataException(2);
		return new \TMT\model\CustomGroupData($id, $result['customDataFields']);
	}

	/**
	 * Creates a new group in the database with the given fields
	 *
	 * @param $fields array(string) The array of field names for this group
	 * @param $id int               The id of the group, defaults to current highest id + 1
	 *
	 * @throws \TMT\exception\CustomGroupDataException when group already exists
	 *
	 * @return int The id of the new group
	 */
	public function create($fields = array(), $id = 0) {
		$groupId = $id;
		$collection = $this->db->selectCollection("groups");
		if($id == 0) {
			//get the next id in case one isn't provided
			$cursor = $collection->find(array(), array('id'));
			$cursor->sort(array('id' => -1))->limit(1);
			if($cursor->count() < 1) {
				$groupId = 1;
			} else {
				$result = $cursor->next();
				$groupId = $result['id'];
				$groupId++;
			}
		} else {
			//ensure that no group exists with the given id already
			$group = $collection->findOne(array('id' => $groupId));
			if($group != null)
				throw new \TMT\exception\CustomGroupDataException(1);
		}
		$collection->insert(array(
			"id"                => $groupId,
			"customDataFields"  => $fields
		));
		return $groupId;
	}

	/**
	 * Deletes a group from the database and removes the group from
	 *   all users that have that group
	 *
	 * @param $groupId int The id of the group to delete
	 */
	public function remove($groupId) {
		$collection = $this->db->selectCollection("groups");
		$collection->remove(array('id' => $groupId));

		//Remove group from each user
		$collection2 = $this->db->selectCollection("users");
		$collection2->update(
			array(),
			array('$pull' => array(
				'groupData' => array(
					'group' => $groupId
				)
			)),
			array('multiple' => true)
		);
	}

	/**
	 * Adds a field to the given group.
	 *   If the field already exists no change takes place.
	 *   This will also add the field to all users with the group.
	 * 
	 * @param $group int    The group id
	 * @param $field string The field's name
	 */
	public function addField($group, $field) {

		//Add field to the group
		$coll2 = $this->db->selectCollection("groups");
		try {
			$coll2->update(
				array('id' => $group),
				array('$addToSet' => array('customDataFields' => $field)),
				array('multiple' => false)
			);
		} catch(\MongoWriteConcernException $e) {
			// field 'customDataFields' is null, set it properly
			$coll2->update(
				array('id' => $group),
				array('$set' => array('customDataFields' => array($field))),
				array('multiple' => false)
			);
		}

		//Add field to all users with the group
		$coll = $this->db->selectCollection("users");
		try {
			$coll->update(
				array('groupData.group' => $group),
				array('$set' => array('groupData.$.customDataFields.'.$field => "")),
				array('multiple' => true)
			);
		} catch(\MongoWriteConcernException $e) {
			// The array hasn't been defined yet. Create it
			$coll->update(
				array('groupData.group' => $group),
				array('$set' => array('groupData.$.customDataFields' => array($field => ""))),
				array('multiple' => true)
			);
		}
	}

	/**
	 * Removes a field from the given group
	 *   If the field doesn't exist no change takes place.
	 *   Also, this will remove the field from all users' group data.
	 * 
	 * @param $group int    The group id
	 * @param $field string The field's name
	 */
	public function removeField($group, $field) {
		//Remove field from all users with the group
		$coll = $this->db->selectCollection("users");
		$coll->update(
			array('groupData.group' => $group),
			array('$unset' => array('groupData.$.customDataFields.'.$field => "")),
			array('multiple' => true)
		);

		//Remove field from group
		$coll2 = $this->db->selectCollection("groups");
		$coll2->update(
			array('id' => $group),
			array('$pull' => array('customDataFields' => $field)),
			array('multiple' => false)
		);
	}

	/**
	 * Renames a field for a given group and cascades the changes
	 *   to all users in the group
	 *
	 * @param $group   int The group id
	 * @param $oldName string The previous name of the field
	 * @param $newName string The new name for the field
	 */
	public function renameField($group, $oldName, $newName) {
		//Rename field for all users
		$coll = $this->db->selectCollection("users");
		$cursor = $coll->find(
			array('groupData.group' => $group),
			array('groupData.$' => 1, 'netId' => 1)
		);
		foreach($cursor as $user) {
			$val = $user['groupData'][0]['customDataFields'][$oldName];
			$coll->update(
				array('netId' => $user['netId'], 'groupData.group' => $group),
				array('$unset' => array('groupData.$.customDataFields.'.$oldName => "")),
				array('multiple' => false)
			);
			$coll->update(
				array('netId' => $user['netId'], 'groupData.group' => $group),
				array('$set' => array('groupData.$.customDataFields.'.$newName => $val)),
				array('multiple' => false)
			);
		}

		//Remove field from array
		$coll2 = $this->db->selectCollection("groups");
		$coll2->update(
			array('id' => $group),
			array('$pull' => array('customDataFields' => $oldName)),
			array('multiple' => false)
		);

		//Add new field name
		$coll2->update(
			array('id' => $group),
			array('$push' => array('customDataFields' => $newName)),
			array('multiple' => false)
		);
	}
}
?>
