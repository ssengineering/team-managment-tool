<?php

namespace TMT\api\employee;

/**
 * API controller for the /api/employee/area route
*/
class area extends \TMT\APIController {

	/**
	 * Retrieve all employees from the given area
	 * GET /api/employee/area/:area?active=x&defaultOnly=true&areas[]=1
	 *
	 * The main route is /api/employee/area. The rest is optional, although
	 *   if all options are omitted, it won't be very useful
	 *
	 * Either :area or areas[] get data must be set, both can be used in the
	 *   same request and it will be processed as if it were all passed in
	 *   through an array in the get data.
	 * active = -1/0/1 for terminated/inactive/active to filter out
	 *   search results by activity status
	 * defaultOnly = true/false true to get only employees defaulted to the given area
	 *   or false to get all employees with access to the given area(s). Defaults to true.
	 * customData = true/false true to get the custom data fields for the area (note, this
	 *   is only allowed if one area is specified. (Defaults to false)
	 *
	 * Examples:
	 *   GET /api/employee/area/3
	 *   Retrieve all employees in area 3
	 *
	 *   GET /api/employee/area?areas[]=3&areas[]=4 OR GET /api/employee/area/3?areas[]=4
	 *   Retrieve all employees defaulted to area 3 or 4
	 *
	 *   GET /api/employee/area?areas[]=2&defaultOnly=false
	 *   Retrieve all employees who have access to group 2
	 *
	 *   GET /api/employee/area?areas[]=2&areas[]=3&defaultOnly=false&active=1
	 *   Retrieve all active employees who have access to groups 2 or 3
	 *
	 *   GET /api/employee/area/1?customData=true
	 *   Retrieve all employees from group 1 with their custom data for group 1
	 *
	 * returns:
	 * {
	 *    status: OK/ERROR,
	 *    data: [
	 *      {
	 *        netID: "",
	 *        active: -1/0/1,
	 *        area: int,
	 *        firstName: "",
	 *        lastName: "",
	 *        maidenName: "",
	 *        phone: "",
	 *        email: "",
	 *        chqId: "",
	 *        birthday: "",
	 *        languages: "",
	 *        hometown: "",
	 *        major: "",
	 *        mission: "",
	 *        graduation: "",
	 *        position: int,
	 *        shift: "",
	 *        supervisor: "",
	 *        hireDate: "",
	 *        certification: "",
	 *        international: 0/1,
	 *        byuId: "",
	 *        fullTime: 0/1
	 *      }
	 *    ]
	 * }
	 */
	public function get($params) {
		$this->requireAuthentication();

		// Parse areas
		$area  = (isset($params['url'][3])) ? $params['url'][3] : null;
		$areas = (isset($params['request']['areas'])) ? $params['request']['areas'] : null;
		if($area === null && $areas === null) {
			$areas = array($this->user['area']);
		} else if($area !== null && $areas !== null) {
			$areas[] = $area;
			$areas = \array_unique($areas);
		} else if($area !== null && $areas === null) {
			$areas = array($area);
		}

		// Parse active and defaultOnly
		$active      = (isset($params['request']['active']))      ? (int)$params['request']['active']                                        : null;
		$defaultOnly = (isset($params['request']['defaultOnly'])) ? \filter_var($params['request']['defaultOnly'], \FILTER_VALIDATE_BOOLEAN) : true;
		$customData  = (isset($params['request']['customData']))  ? \filter_var($params['request']['customData'], \FILTER_VALIDATE_BOOLEAN)  : false;

		$employeeAccessor = new \TMT\accessor\Employee();
		$areaAccessor     = new \TMT\accessor\AreaAccessor();
		$employees = $employeeAccessor->getByArea($areas, $defaultOnly, $active);
		$userAreas = $areaAccessor->getAll($this->user['netId']);
		$results = array();

		for($i=0; $i < count($employees); $i++) {
			$employeeAreas = $areaAccessor->getAll($employees[$i]->netID);
			$overlap = false;
			foreach($employeeAreas as $eArea) {
				foreach($userAreas as $uArea) {
					if($uArea->ID == $eArea->ID) {
						$overlap = true;
						$results[] = $employees[$i];
						break;
					}
				}
				if($overlap)
					break;
			}
		}

		// If there is only one area specified and customData is true, add the fields to the employee
		if($customData && count($areas) == 1) {
			for($i=0; $i < count($results); $i++) {
				$data = $this->getCustomDataFields($results[$i]->netID, $areas[0]);
				foreach($data as $field => $value) {
					$results[$i]->{$field} = $value;
				}
			}
		}

		$this->respond($results);
	}

	/**
	 * Retrieves each of the custom data fields for an employee
	 *
	 * @param $netId string The netId of the employee
	 * @param $area  int    The area to get custom data fields for
	 *
	 * @return associative array with $field => $value
	 */
	private function getCustomDataFields($netId, $area) {
		// Get Employees
		$customUserDataAcc = $this->getAccessor("UserGroupData");

		// Get custom data fields
		try {
			$userCustomData = $customUserDataAcc->get($netId, (int)$area);
			$dataArray = $userCustomData->getData();
		} catch(\TMT\exception\CustomGroupDataException $e) {
			// If the user does not have any data return an empty array
			$dataArray = array();
		}
		return $dataArray;
	}
}
?>
