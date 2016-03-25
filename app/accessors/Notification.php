<?php

namespace TMT\accessor;

class Notification extends MysqlAccessor {

    /**
     * Retrieves a notification by guid
     *
     * @param $guid string The notification guid
     *
     * @return \TMT\model\Notfication
     */
    public function get($guid) {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE guid=:guid");
        $stmt->execute(array(':guid' => $guid));
        if ($notification = $stmt->fetch()) {
            return new \TMT\model\Notification($notification);
        }

        return new \TMT\model\Notification();
    }

    /**
     * Insert a new notification
     *
     * @param $notification \TMT\model\Notification the notification to be inserted
     *
     * @return \TMT\model\Notification The notification that was just created
     */
    public function create($notification) {
        $guid = $this->newGuid();
        $stmt = $this->pdo->prepare("INSERT INTO notifications (message, type, area, guid) VALUES (:message, :type, :area, :guid)");
        $stmt->execute(array(':message' => $notification->message, ':type' => $notification->type, ':area' => $notification->area, ':guid' => $guid));
        return $this->get($guid);
    }

    /**
     * Searches the notifications table for notifications that match the search parameters
     *
     * @param $params associative array An array where the keys are column names and values are search parameters for that column
     *   For the following columns, the search function will accept similar matches:
     *     message
     *   For the following columns, the search function will only accept exact matches:
     *     type, area, timestamp
     *
     * @return array(\TMT\model\Notification) An array of Notification objects that match the search criteria
     */
    public function search($params = null) {
        //SELECT * FROM notifications WHERE (a AND b AND c)
        $queryString = "SELECT * FROM notifications WHERE (";
        if (count($params) < 1 || $params == null) {
            return array();
        }

        if (array_key_exists('message', $params)) {
            $queryString .= "message LIKE :message AND ";
            $queryParams[':message'] = '%' . $params['message'] . '%';
        }

        if (array_key_exists('type', $params)) {
            $queryString .= "type=:type AND ";
            $queryParams[':type'] = $params['type'];
        }

        if (array_key_exists('area', $params)) {
            $queryString .= "area=:area AND ";
            $queryParams[':area'] = $params['area'];
        }

        if (array_key_exists('startDate', $params)) {
            $queryString .= "timestamp >= :startDate AND ";
            $queryParams[':startDate'] = $params['startDate'];
        }

        if (array_key_exists('endDate', $params)) {
            $queryString .= "timestamp <= :endDate AND ";
            $queryParams[':endDate'] = $params['endDate'];
        }

        $queryString = substr($queryString, 0, -5);
        $queryString .= ")";

        $stmt = $this->pdo->prepare($queryString);
        $stmt->execute($queryParams);
        $notifications = array();
        while ($notification = $stmt->fetch()) {
            $notifications[] = new \TMT\model\Notification($notification);
        }

        return $notifications;
    }
}
