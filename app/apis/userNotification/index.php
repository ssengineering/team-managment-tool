<?php

namespace TMT\api\userNotification;

class index extends \TMT\APIController {

    public function __construct() {
        parent::__construct();
        $this->requireAuthentication();
    }

    public function get($params) {
        $notifAcc = $this->getAccessor("UserNotification");
        if (isset($params['request']['guid'])) {
            $results = $notifAcc->getRecipients($params['request']['guid']);
        } else if (isset($params['request']['netId'])) {
            if (isset($params['request']['read'])) {
                $read = filter_var($params['request']['read'], FILTER_VALIDATE_BOOLEAN);
            } else {
                $read = true;
            }

            $results = $notifAcc->getUserNotifications($params['request']['netId'], $read);
        } else {
            $this->error("'guid' or 'netId' must be specified", 400);
            return;
        }

        $this->respond($results);
        return;
    }

    public function put($params) {
        $notifAcc = $this->getAccessor("UserNotification");
        if (count($params['url']) < 3) {
            $this->error("'netId' must be specified", 400);
            return;
        } else if (count($params['url']) < 4) {
            $this->error("'type' must be specified", 400);
            return;
        }

        $notifAcc->markRead($params['url'][2], $params['url'][3]);
        $this->respond("success");
    }

    public function delete($params) {
        $notifAcc = $this->getAccessor("UserNotification");
        if (count($params['url']) < 3) {
            $this->error("'netId' must be specified", 400);
            return;
        } else if (count($params['url']) < 4) {
            $this->error("'guid' must be specified", 400);
            return;
        }

        $notifAcc->delete($params['url'][2], $params['url'][3]);
        $this->respond("success");
    }
}

?>
