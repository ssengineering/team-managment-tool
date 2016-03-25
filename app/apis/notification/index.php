<?php

namespace TMT\api\notification;

class index extends \TMT\APIController {

    public function __construct() {
        parent::__construct();
        $this->requireAuthentication();
    }

    public function get($params) {
        $notifAcc = $this->getAccessor("Notification");
        if (count($params['url']) < 3) {
            $array = [];

            if (isset($params['request']['message'])) {
                $array['message'] = $params['request']['message'];
            }

            if (isset($params['request']['type'])) {
                $array['type'] = $params['request']['type'];
            }

            if (isset($params['request']['area'])) {
                $array['area'] = $params['request']['area'];
            }

            if (isset($params['request']['startDate'])) {
                $array['startDate'] = $params['request']['startDate'];
            }

            if (isset($params['request']['endDate'])) {
                $array['endDate'] = $params['request']['endDate'];
            }

            if (count($array) == 0) {
                $this->error("Some search parameters must be set ('message', 'type', 'area', 'startDate', or 'endDate')", 400);
                return;
            }

            $results = $notifAcc->search($array);
            $this->respond($results);
            return;
        } else {
            if (isset($params['url'][2])) {
                $results = $notifAcc->get($params['url'][2]);

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
}

?>
