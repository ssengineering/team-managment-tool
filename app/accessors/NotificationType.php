<?php

namespace TMT\accessor;

class NotificationType extends MysqlAccessor {

	/**
	 * Retrieve all notification methods
	 *
	 * @return array(\TMT\model\NotificationType)
	 */
	public function getAll() {
		$stmt = $this->pdo->prepare("SELECT * FROM notificationTypes");
		$stmt->execute();
		$types = array();
		while($type = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$types[] = new \TMT\model\NotificationType($type);
		}
		return $types;
	}

	/**
	 * Retrieve a notification type by guid
	 *
	 * @param $guid string The guid of the type to get
	 *
	 * @return \TMT\model\NotificationType the notification type information with the given guid
	 */
	public function get($guid) {
		$stmt = $this->pdo->prepare("SELECT * FROM notificationTypes WHERE guid=:guid");
		$stmt->execute(array(':guid' => $guid));
		if($type = $stmt->fetch(\PDO::FETCH_OBJ))
			return new \TMT\model\NotificationType($type);

		return new \TMT\model\NotificationType();
	}

	/**
	 * Retrieve a notification type by name
	 *
	 * @param $name string The name of the type to get
	 *
	 * @return \TMT\model\NotificationType the notification type information with the given name
	 */
	public function getByName($name) {
		$stmt = $this->pdo->prepare("SELECT * FROM notificationTypes WHERE name=:name");
		$stmt->execute(array(':name' => $name));
		if($type = $stmt->fetch(\PDO::FETCH_OBJ))
			return new \TMT\model\NotificationType($type);

		return new \TMT\model\NotificationType();
	}

	/**
	 * Add a new notification type
	 *
	 * @param \TMT\model\NotificationType
	 *  Note: The guid doesn't matter, a new one is generated anyway
	 */
	public function add($type) {
		$stmt = $this->pdo->prepare("INSERT INTO notificationTypes (guid, name, resource, verb) VALUES(:guid, :name, :resource, :verb)");
		$stmt->execute(array(':guid' => $this->newGuid(), ':name' => $type->name, ':resource' => $type->resource, ':verb' => $type->verb));
	}

	/**
	 * Update a notification type
	 *
	 * @param \TMT\model\NotificationType
	 */
	public function update($type) {
		$stmt = $this->pdo->prepare("UPDATE notificationTypes SET name=:name, resource=:resource, verb=:verb WHERE guid=:guid");
		$stmt->execute(array(':name' => $type->name, ':resource' => $type->resource, ':verb' => $type->verb, ':guid' => $type->guid));
	}

	/**
	 * Deletes a notification type
	 *
	 * @param $guid string The guid of the type to delete
	 */
	public function delete($guid) {
		$stmt = $this->pdo->prepare("DELETE FROM notificationTypes WHERE guid=:guid");
		$stmt->execute(array(':guid' => $guid));
	}
}
