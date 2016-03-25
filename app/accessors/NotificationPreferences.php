<?php

namespace TMT\accessor;

class NotificationPreferences extends MysqlAccessor {

	/**
	 * Retrieve all users that should receive a notification
	 *  of the given type in the specified area
	 *
	 * @param $type string The notification type guid
	 * @param $area string The area guid
	 *
	 * @return array(\TMT\model\NotificationPreference)
	 */
	public function getRecipients($type, $area) {
		$stmt = $this->pdo->prepare("SELECT notificationPreferences.*, employee.email FROM notificationPreferences
			JOIN employee ON notificationPreferences.netId=employee.netID WHERE type=:type AND notificationPreferences.area=:area");
		$stmt->execute(array(':type' => $type, ':area' => $area));
		$preferences = array();
		while($preference = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$preferences[] = new \TMT\model\NotificationPreference($preference);
		}
		return $preferences;
	}

	/**
	 * Retrieve all of a user's notification preferences
	 *
	 * @param $netId string The user's netId
	 * @param $area  string (optional) Restrict results by area
	 *
	 * @return array(\TMT\model\NotificationPreference)
	 */
	public function getUserPreferences($netId, $area = null) {
		$query = "SELECT * FROM notificationPreferences WHERE netId=:netId";
		$params = array(':netId' => $netId);
		if($area != null) {
			$query .= " AND area=:area";
			$params[':area'] = $area;
		}

		$stmt = $this->pdo->prepare($query);
		$stmt->execute($params);
		$preferences = array();
		while($preference = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$preferences[] = new \TMT\model\NotificationPreference($preference);
		}
		return $preferences;
	}
	
	/**
	 * Retrieve a user's notification for a specific type and area
	 *
	 * @param $netId string The user's netId
	 * @param $area  string Restrict results by area
	 * @param $type string Restrict results by message type
	 *
	 * @return array(\TMT\model\NotificationPreference)
	 */
	public function getOneUserPreference($netId, $area, $type) {
		$query = "SELECT * FROM notificationPreferences WHERE netId=:netId AND area=:area AND type=:type";
		$params = array(':netId' => $netId, ':area' => $area, ':type' => $type);

		$stmt = $this->pdo->prepare($query);
		$stmt->execute($params);
		$preferences = array();
		if($preference = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$preferences = new \TMT\model\NotificationPreference($preference);
		}
		if($preferences->method == null) {
			return null;
		}
		return $preferences;
	}
	/**
	 * Add an entry to notification preferences (subscribe)
	 *
	 * @param \TMT\model\NotificationPreference
	 *  Note: email does not need to be set
	 */
	public function add($preference) {
		$stmt = $this->pdo->prepare("INSERT INTO notificationPreferences (netId, type, method, area) VALUES(:netId, :type, :method, :area)");
		$stmt->execute(array(':netId' => $preference->netId, ':type' => $preference->type, ':method' => $preference->method, ':area' => $preference->area));
	}

	/**
	 * Delete an entry from notification preferences (unsubscribe from a notification)
	 *
	 * @param $netId string The netId of the user
	 * @param $type  string The notification type guid
	 * @param $area  string The area guid
	 */
	public function delete($netId, $type, $area) {
		$stmt = $this->pdo->prepare("DELETE FROM notificationPreferences WHERE netId=:netId AND type=:type AND area=:area");
		$stmt->execute(array(':netId' => $netId, ':type' => $type, ':area' => $area));
	}

	/**
	 * Delete all of a user's notification preferences. This
	 *   should be used when he/she is terminated so that
	 *   they do not receive any more notifications.
	 *
	 * @param $netId string The user's netId
	 */
	public function deleteAll($netId) {
		$stmt = $this->pdo->prepare("DELETE FROM notificationPreferences WHERE netId=:netId");
		$stmt->execute(array(':netId' => $netId));
	}
}
