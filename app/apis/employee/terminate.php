<?php

namespace TMT\api\employee;

/**
 * API controller for the /api/employee/terminate route
*/
class terminate extends \TMT\APIController {

    /**
     * Terminate an employee via their netId
     * POST /api/employee/terminate
     *
     * Examples:
     * POST /api/employee/terminate
     * Terminate an employee with the following data
     *
     * Required data (Submit as x-www-form-urlencoded):
     *      'reasons'
     *      'attendance'
     *      'attitude'
     *      'performance'
     *      'netID'
     *      'submitter'
     * int  'area'
     * bool 'rehirable'
     *
     * returns:
     * {
     *    status: OK,
     *    data: "Success"
     * }
     */

    public function get($params) {
        $this->requireAuthentication();

        if (!$params['url'][3]) {
            $this->error('Missing netID');
            return;
        }

        $terminationAccessor = $this->getAccessor("EmployeeTermination");

        $this->forcePermission("read", "59b0f789-6bb6-414d-a860-ca61fdcf372f");

        $this->respond($terminationAccessor->get($params['url'][3]));
    }

	/**
	 * Inserts termination details into the database
	 *
	 * POST /api/employee/terminate
	 *   in the body of the request the following fields must be passed in
     *      'reasons'
     *      'attendance'
     *      'attitude'
     *      'performance'
     *      'netID'
     * int  'area'
     * bool 'rehirable'
	 */
    public function post($params) {
        $this->requireAuthentication();

        $terminationData = (object) array(
            'reasons'            => isset($params['request']['reasons']) ? $params['request']['reasons'] : null,
            'attendance'         => isset($params['request']['attendance']) ? $params['request']['attendance'] : null,
            'attitude'           => isset($params['request']['attitude']) ? $params['request']['attitude'] : null,
            'performance'        => isset($params['request']['performance']) ? $params['request']['performance'] : null,
            'netID'              => isset($params['request']['netID']) ? $params['request']['netID'] : null,
            'submitter'          => $this->user['netId'],
            'area'               => $this->user['area'],
            'rehirable'          => isset($params['request']['rehirable']) ? $params['request']['rehirable'] : null
        );

        foreach ($terminationData as $key => $value) { // Check that all parameters were supplied
            if($value == null) {
                $this->error('Missing parameter '.$key);
                return;
            }
		}

		$employeeAcc = $this->getAccessor("Employee");
		$employee = $employeeAcc->get($params['request']['netID']);
		if ($employee->area != $this->user['area']) {
			$this->error("Employees can only be terminated from their default area");
			return;
		}
        
        $this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");

		// Begin termination process
		$response = array("permissions" => false, "notifications" => false, "rights" => false, "terminated" => false);
		// Revoke All Permissions
		try {
			$url = getenv("PERMISSIONS_URL");
			$res = $this->sendAuthenticatedRequest("DELETE", $url."/groupMembers/".$params['request']['netID']);
			if($res["status"] == "OK") {
				$response['permissions'] = true;
			} else {
				throw new \Exception("Unable to successfully revoke all user permissions");
			}
		} catch (\Exception $e) {
			$this->error($response, 500);
			return;
		}
		// Delete all notification preferences so as not to send any to terminated users
		try {
			$notifPref = $this->getAccessor("NotificationPreferences");
			$notifPref->deleteAll($params['request']['netID']);
			$response['notifications'] = true;
		} catch (\Exception $e) {
			$this->error($response, 500);
			return;
		}
		// Revoke all rights
		try {
			$rightHandler = $this->getController("RightHandler");
			$response['rights'] = $rightHandler->revokeAll($params['request']['netID'],$this->user['netId']);
		} catch (\Exception $e) {
			$this->error($response, 500);
			return;
		}
		// Terminate Employee
		try {
			$terminationModel = new \TMT\model\EmployeeTermination($terminationData);
			$terminationAccessor = $this->getAccessor("EmployeeTermination");
			$terminationAccessor->save($terminationModel);
			$response['terminated'] = true;
		} catch (\Exception $e) {
			$this->error($response, 500);
			return;
		}

		$this->respond($response);
		return;
    }

}
?>
