<?php

namespace TMT\api\notificationEmail;

class index extends \TMT\APIController {

    public function __construct() {
        parent::__construct();
        $this->requireAuthentication();
    }

    public function get($params) {
        $emailAcc = $this->getAccessor("NotificationEmail");
        if (count($params['url']) < 3) {
            if (isset($params['request']['type']) && isset($params['request']['area'])) {
                $results = $emailAcc->getByType($params['request']['type'], $params['request']['area']);
            } else if (isset($params['request']['area'])) {
                $results = $emailAcc->getByArea($params['request']['area']);
            } else {
                $this->error("'type' and 'area' or just 'area' must be specified", 400);
                return;
            }

            $this->respond($results);
            return;
        } else {
            if (isset($params['url'][2])) {
                $results = $emailAcc->get($params['url'][2]);

                if ($results->guid == NULL) {
                    $this->error("A notification method with the specified 'guid' could not be found", 400);
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
        $emailAcc = $this->getAccessor("NotificationEmail");
        $email = $this->getModel("NotificationEmail");
        if (!isset($params['request']['email'])) {
            $this->error("'email' must be specified", 400);
            return;
        } else {
            $email->email = $params['request']['email'];
        }

        if (!isset($params['request']['type'])) {
            $this->error("'type' must be specified", 400);
            return;
        } else {
            $email->type = $params['request']['type'];
        }

        if (!isset($params['request']['area'])) {
            $this->error("'area' must be specified", 400);
            return;
        } else {
            $email->area = $params['request']['area'];
        }

        $emailAcc->add($email);
        $this->respond("success");
    }

    public function delete($params) {
        $emailAcc = $this->getAccessor("NotificationEmail");
        if (count($params['url']) < 3) {
            $this->error("'guid' must be specified", 400);
            return;
        }

        $emailAcc->delete($params['url'][2]);
        $this->respond("success");
    }
}

?>
