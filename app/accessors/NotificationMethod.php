<?php

namespace TMT\accessor;

class NotificationMethod extends MysqlAccessor {

	/**
	 * Retrieve all notification methods
	 *
	 * @return array(string) A list of notification types
	 */
	public function getAll() {
		$stmt = $this->pdo->prepare("SELECT name FROM notificationMethods");
		$stmt->execute();
		$methods = array();
		while($method = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$methods[] = $method->name;
		}
		return $methods;
	}

	/**
	 * Add a new notification method
	 *
	 * @param $name string The name of the new notification method
	 */
	public function add($name) {
		$stmt = $this->pdo->prepare("INSERT INTO notificationMethods (name) VALUES(:name)");
		$stmt->execute(array(':name' => $name));
	}

	/**
	 * Removes a notification method from the list
	 *
	 * @param $name The name of the notification method
	 */
	public function delete($name) {
		$stmt = $this->pdo->prepare("DELETE FROM notificationMethods WHERE name=:name");
		$stmt->execute(array(':name' => $name));
	}
}
?>
