<?php

namespace TMT\accessor;

class NotificationEmail extends MysqlAccessor {

	/**
	 * Retrieve all notification methods
	 *
	 * @param $guid string The guid of the type to get
	 * @param $area string The guid of the area
	 *
	 * @return array(\TMT\model\NotificationEmail)
	 */
	public function getByType($type, $area) {
		$stmt = $this->pdo->prepare("SELECT * FROM notificationEmails WHERE type=:type AND area=:area");
		$stmt->execute(array(':type' => $type, ':area' => $area));
		$emails = array();
		while($email = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$emails[] = new \TMT\model\NotificationEmail($email);
		}
		return $emails;
	}

	/**
	 * Retrieve all notification methods
	 *
	 * @param $area string The guid of the area
	 *
	 * @return array(\TMT\model\NotificationEmail)
	 */
	public function getByArea($area) {
		$stmt = $this->pdo->prepare("SELECT * FROM notificationEmails WHERE area=:area");
		$stmt->execute(array(':area' => $area));
		$emails = array();
		while($email = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$emails[] = new \TMT\model\NotificationEmail($email);
		}
		return $emails;
	}

	/**
	 * Retrieve a notification type by guid
	 *
	 * @param $guid string The guid of the email
	 *
	 * @return \TMT\model\NotificationEmail the notification type information with the given guid
	 */
	public function get($guid) {
		$stmt = $this->pdo->prepare("SELECT * FROM notificationEmails WHERE guid=:guid");
		$stmt->execute(array(':guid' => $guid));
		if($email = $stmt->fetch(\PDO::FETCH_OBJ))
			return new \TMT\model\NotificationEmail($email);

		return new \TMT\model\NotificationEmail();
	}

	/**
	 * Add a new notification email (subscribe an email address to a notification)
	 *
	 * @param \TMT\model\NotificationEmail
	 *  Note: The guid doesn't matter, a new one is generated anyway
	 */
	public function add($email) {
		$stmt = $this->pdo->prepare("INSERT INTO notificationEmails (guid, email, type, area) VALUES (:guid, :email, :type, :area)");
		$stmt->execute(array(':guid' => $this->newGuid(), ':email' => $email->email, ':type' => $email->type, ':area' => $email->area));
	}

	/**
	 * Deletes a notification email (unsubscribe the email from the notification)
	 *
	 * @param $guid string The guid of the email to delete
	 */
	public function delete($guid) {
		$stmt = $this->pdo->prepare("DELETE FROM notificationEmails WHERE guid=:guid");
		$stmt->execute(array(':guid' => $guid));
	}
}
