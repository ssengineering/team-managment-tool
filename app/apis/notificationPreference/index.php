<?php

namespace TMT\api\notificationPreference;

class index extends \TMT\APIController {
    private $notificationPreferenceAcc;

    public function __construct() {
        parent::__construct();
        $this->requireAuthentication();
    }

    public function get($params) {
        $notificationPreferenceAcc = $this->getAccessor("NotificationPreferences");

        if (count($params['url']) < 3) {
            if (isset($params['request']['type']) && isset($params['request']['area'])) {
                $results = $notificationPreferenceAcc->getRecipients($params['request']['type'], $params['request']['area']);
            } else if (isset($params['request']['netId']) && isset($params['request']['area'])) {
                $results = $notificationPreferenceAcc->getUserPreferences($params['request']['netId'], $params['request']['area']);
            } else {
                $this->error("'type' and 'area' or 'netId' and 'area' must be specified", 400);
                return;
            }

            $this->respond($results);
            return;
        }
    }

    public function post($params) {
        $notificationPreferenceAcc = $this->getAccessor("NotificationPreferences");

        $preference = $this->getModel("NotificationPreference");
        if (!isset($params['request']['netId'])) {
            $this->error("'netId' must be specified", 400);
            return;
        } else {
            $preference->netId = $params['request']['netId'];
        }

        if (!isset($params['request']['type'])) {
            $this->error("'type' must be specified", 400);
            return;
        } else {
            $preference->type = $params['request']['type'];
        }

        if (!isset($params['request']['method'])) {
            $this->error("'method' must be specified", 400);
            return;
        } else {
            $preference->method = $params['request']['method'];
        }

        if (!isset($params['request']['area'])) {
            $this->error("'area' must be specified", 400);
            return;
        } else {
            $preference->area = $params['request']['area'];
        }

        $notificationPreferenceAcc->add($preference);
        $this->respond("success");
    }

    public function delete($params) {
        $notificationPreferenceAcc = $this->getAccessor("NotificationPreferences");

        if (count($params['url']) < 3) {
            $this->error("'netId' must be specified", 400);
            return;
        } else if (count($params['url']) < 4) {
            $this->error("'type' must be specified", 400);
            return;
        } else if (count($params['url']) < 5) {
            $this->error("'area' must be specified", 400);
            return;
        }

        $notificationPreferenceAcc->delete($params['url'][2], $params['url'][3], $params['url'][4]);
        $this->respond("success");
    }
}

?>
