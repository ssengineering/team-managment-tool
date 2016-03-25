<?php

namespace TMT\api\notificationMethod;

class index extends \TMT\APIController {

    public function __construct() {
        parent::__construct();
        $this->requireAuthentication();
    }

    public function get($params) {
        $notificationMethodAcc = $this->getAccessor("NotificationMethod");

        $results = $notificationMethodAcc->getAll();
        $this->respond($results);
        return;
    }

    public function post($params) {
        $notificationMethodAcc = $this->getAccessor("NotificationMethod");

        if (!isset($params['request']['name'])) {
            $this->error("'name' must be specified", 400);
            return;
        } else {
            try {
                $notificationMethodAcc->add($params['request']['name']);
            } catch (\PDOException $e) {
                if (strpos($e->getMessage(), "Duplicate entry")) {
                    $this->error("Method already exists!");
                    return;
                } else {
                    throw $e;
                }
            }

            $this->respond("Success");
        }
    }

    public function delete($params) {
        $notificationMethodAcc = $this->getAccessor("NotificationMethod");

        if (count($params['url']) < 3) {
            $this->error("'name' must be specified", 400);
            return;
        }

        $notificationMethodAcc->delete($params['url'][2]);
        $this->respond("Success");
    }
}

?>
