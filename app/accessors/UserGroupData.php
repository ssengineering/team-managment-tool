<?php

namespace TMT\accessor;

/**
 * An accessor class to interact with the database
 *  for UserGroupData information
 */
class UserGroupData extends MongoAccessor {

	/**
	 * Retrieves the information for the user with the given netId
	 *
	 * @param $netId string The user's netId to get
	 * @param $group int    The user's current group
	 *
	 * @throws \TMT\exception\CustomGroupDataException when no user exists with the given netId in the given group
	 *
	 * @return \tmt\model\UserGroupData model with the given netId
	 */
	public function get($netId, $group) {
		$collection = $this->db->selectCollection("users");
		$result = $collection->findOne(
			array('netId' => $netId, 'groupData.group' => $group),
			array('groupData' => array(
				'$elemMatch' => array('group' => $group)
			))
		);
		if($result == null) {
			$result2 = $collection->findOne(array('netId' => $netId));
			if($result2 == null) {
				throw new \TMT\exception\CustomGroupDataException(8);
			} else {
				throw new \TMT\exception\CustomGroupDataException(5);
			}
		}
		return new \TMT\model\UserGroupData($netId, $group, $result['groupData'][0]['customDataFields']);
	}

	/**
	 * Creates a new user in the database
	 *
	 * @param $netId  string         The new user's netId
	 * @param $groups array(int)/int An array of all the groups the user belongs to, or integer for 1 group
	 *
	 * @throws \TMT\exception\CustomGroupDataException when the user already exists or a group does not, or no group given
	 */
	public function create($netId, $groups) {
		//validate input
		if(count($groups) < 1 && !is_numeric($groups))
			throw new \TMT\exception\CustomGroupDataException(6);
		$groups = (is_array($groups)) ? $groups : array($groups) ;

		//ensure user doesn't already exist
		$userCol = $this->db->selectCollection("users");
		$result = $userCol->findOne(array('netId' => $netId));
		if($result != null)
			throw new \TMT\exception\CustomGroupDataException(7);

		//Create custom data fields for user for each group
		$groupCol = $this->db->selectCollection("groups");
		$info = array();
		for($i=0; $i < count($groups); $i++) {
			$groupInfo = array('group' => $groups[$i], 'customDataFields' => array());
			$result = $groupCol->findOne(array('id' => $groups[$i]));
			if($result == null)
				throw new \TMT\exception\CustomGroupDataException(2);
			$fields = array();
			foreach($result['customDataFields'] as $field) {
				$fields[$field] = "";
			}
			$groupInfo['customDataFields'] = $fields;
			$info[] = $groupInfo;
		}

		$userCol->insert(array(
			"netId"      => $netId,
			"groupData"  => $info
		));
	}

	/**
	 * Adds a group to the given user
	 *
	 * @param $netId string The user's netId who is being modified
	 * @param $group int    The group to add to the user
	 *
	 * @throws \TMT\exception\CustomGroupDataException if user or group doesn't exist
	 */
	public function addGroup($netId, $group) {
		$groupCol = $this->db->selectCollection("groups");
		$userCol  = $this->db->selectCollection("users");

		//Ensure group exists
		$result = $groupCol->findOne(array('id' => $group));
		if($result == null)
			throw new \TMT\exception\CustomGroupDataException(2);

		//Ensure user exists
		$user = $userCol->findOne(array('netId' => $netId));
		if($user == null)
			throw new \TMT\exception\CustomGroupDataException(8, "User does not exist");
		$index = count($user['groupData']);//Get index of group in array for user to use on update
		$fields = array();
		for($i = 0; $i < count($result['customDataFields']); $i++) {
			$fields[$result['customDataFields'][$i]] = "";
		}

		$userCol->update(
			array("netId" => $netId),
			array('$set' => array(
				'groupData.'.$index => array(
					'group'            => $group,
					'customDataFields' => $fields
				)
			)),
			array('multiple' => false)
		);
	}

	/**
	 * Removes a group from the given user
	 *
	 * @param $netId string The user's netId who is being modified
	 * @param $group int    The group to remove from the user
	 */
	public function removeGroup($netId, $group) {
		$collection = $this->db->selectCollection("users");
		$collection->update(
			array("netId" => $netId),
			array('$pull' => array(
				'groupData' => array(
					'group' => $group
				)
			)),
			array('multiple' => true)
		);
	}

	/**
	 * Updates the user information in the database
	 *  If no user with the given netId and group exists nothing happens
	 *
	 * @param \tmt\model\UserGroupData A UserGroupData model to be updated in the database
	 */
	public function update($user) {
		$collection = $this->db->selectCollection("users");
		$collection->update(
			array(
				"netId"           => $user->getNetId(),
				"groupData.group" => $user->getGroup()
			),
			array('$set' => array(
					'groupData.$.customDataFields' => $user->getData()
				)
			),
			array('multiple' => false)
		);
	}

	/**
	 * Deletes the user from the database
	 *
	 * @param $netId string The user's netId
	 */
	public function remove($netId) {
		$col = $this->db->selectCollection("users");
		$col->remove(array('netId' => $netId));
	}
}
?>
