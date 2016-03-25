<?php

namespace TMT\accessor;

class UserNotification extends MysqlAccessor {

    /**
     * Retrieve all onsite notifications that were sent out
     *   from the same notification
     *
     * @param $type string The notification type guid
     *
     * @return array(\TMT\model\UserNotification)
     */
    public function getRecipients($guid) {
        $stmt = $this->pdo->prepare("SELECT notifications.*, userNotifications.netId, userNotifications.read FROM notifications JOIN userNotifications
			ON notifications.guid=userNotifications.notificationGuid WHERE notifications.guid=:guid AND userNotifications.deleted=0");
        $stmt->execute(array(':guid' => $guid));
        $notifications = array();
        while ($notification = $stmt->fetch(\PDO::FETCH_OBJ)) {
            $notifications[] = new \TMT\model\UserNotification($notification);
        }
        return $notifications;
    }

    /**
     * Retrieve all of a user's notifications
     *   If read is set to false, only get unread notifications
     *
     * @param $netId string The user's netId
     * @param $area  bool   Whether or not to include notifications that have been read
     *
     * @return array(\TMT\model\UserNotification)
     */
    public function getUserNotifications($netId, $read = true) {
        $query = "SELECT notifications.*, userNotifications.netId, userNotifications.read FROM notifications JOIN userNotifications
			ON notifications.guid=userNotifications.notificationGuid WHERE userNotifications.netId=:netId AND userNotifications.deleted=0";
        if (!$read) {
            $query .= " AND userNotifications.read=0";
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array(':netId' => $netId));
        $notifications = array();
        while ($notification = $stmt->fetch(\PDO::FETCH_OBJ)) {
            $notifications[] = new \TMT\model\UserNotification($notification);
        }
        return $notifications;
    }

    /**
     * Add an on-site notification for a user
     *
     * @param \TMT\model\UserNotification
     *  Note: email does not need to be set
     */
    public function add($netId, $guid) {
        $stmt = $this->pdo->prepare("INSERT INTO userNotifications (netId, notificationGuid) VALUES(:netId, :guid)");
        $stmt->execute(array(':netId' => $netId, ':guid' => $guid));
    }

    /**
     * Marks a user's notification as read
     *
     * @param $netId string The netId of the user
     * @param $guid  string The notification type guid
     */
    public function markRead($netId, $guid) {
        $stmt = $this->pdo->prepare("UPDATE userNotifications SET `read`=1 WHERE netId=:netId AND notificationGuid=:guid");
        $stmt->execute(array(':netId' => $netId, ':guid' => $guid));
    }

    /**
     * Marks a user's notification as deleted
     *
     * @param $netId string The netId of the user
     * @param $guid  string The notification type guid
     */
    public function delete($netId, $guid) {
        $stmt = $this->pdo->prepare("UPDATE userNotifications SET deleted=1 WHERE netId=:netId AND notificationGuid=:guid");
        $stmt->execute(array(':netId' => $netId, ':guid' => $guid));
    }
}
