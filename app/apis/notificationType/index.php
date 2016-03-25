<?php

namespace TMT\api\notificationType;

class index extends \TMT\APIController {
    public function __construct() {
        parent::__construct();
        $this->requireAuthentication();
    }

    public function get($params) {
        $notificationTypeAcc = $this->getAccessor("NotificationType");

        if (count($params['url']) < 3) {
            $results = $notificationTypeAcc->getAll();
            $this->respond($results);
            return;
        } else {
            if (isset($params['url'][2])) {
                $results = $notificationTypeAcc->get($params['url'][2]);

                if ($results->guid == NULL) {
                    $this->error("A notification type with the specified 'guid' could not be found", 400);
                    return;
                }
            } else {
                $this->error("'guid' must be specified", 400);
                return;
            }

            $this->respond($results);
            return;
        }
    }

    public function post($params) {
        $notificationTypeAcc = $this->getAccessor("NotificationType");

        $notification = $this->getModel("NotificationType");
        if (!isset($params['request']['name'])) {
            $this->error("'name' must be specified", 400);
            return;
        } else {
            $notification->name = $params['request']['name'];
        }

        if (isset($params['request']['resource'])) {
            $notification->resource = $params['request']['resource'];
        }

        if (isset($params['request']['verb'])) {
            $notification->verb = $params['request']['verb'];
        }

        $results = $notificationTypeAcc->add($notification);
        $this->respond($results);
    }

    public function put($params) {
        $notificationTypeAcc = $this->getAccessor("NotificationType");

        $notification = $this->getModel("NotificationType");
        if (count($params['url']) < 3) {
            $this->error("'guid' must be specified", 400);
            return;
        } else {
            $notification->guid = $params['url'][2];
        }

        if (!isset($params['request']['name'])) {
            $this->error("'name' must be specified", 400);
            return;
        } else {
            $notification->name = $params['request']['name'];
        }

        if (isset($params['request']['resource'])) {
            $notification->resource = $params['request']['resource'];
        }

        if (isset($params['request']['verb'])) {
            $notification->verb = $params['request']['verb'];
        }

        $notificationTypeAcc->update($notification);
        $this->respond("Success");
    }

    public function delete($params) {
        $notificationTypeAcc = $this->getAccessor("NotificationType");

        if (count($params['url']) < 3) {
            $this->error("'guid' must be specified", 400);
            return;
        }

        $notificationTypeAcc->delete($params['url'][2]);
        $this->respond("Success");
    }
}

?>
