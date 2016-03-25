<?php

namespace TMT\api\employee;

/**
 * API router for the route /api/employee
 */
class index extends \TMT\APIController {

	/**
	 * Retrieve a specific employee or search for employees based on given criteria
	 * GET /api/employee/:netId OR GET /api/employee?firstName=x&lastName=y&netId=z&fullTime=1&active=0&area=4
	 *
	 * If a netId is given, the get data is ignored and the employee who matches
	 *   the given netId will be returned
	 * If netId is omitted a search is performed. The following parameters can be given:
	 *   firstName string
	 *   lastName  string
	 *   netId     string
	 *   fullTime  0/1
 	 *   active    -1/0/1
	 *   area      (int)
	 *
	 * The parameters firstName, lastName and netId match any employee where the given
	 *   fields contain the supplied search string. (i.e. firstName=m matches any employee with m in their first name)
	 * The parameters fullTime, active, and area must be exact matches
	 *   (i.e. fullTime=1 will only retrieve fullTime employees)
	 *
	 * returns:
	 * {
	 *    Status: OK/failed,
	 *    data: {
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
	 *    }
	 * }
	 */
	public function get($params) {
		$this->requireAuthentication();

		$single = (isset($params['url'][2])) ? true : false;
		$employeeAccessor = new \TMT\accessor\Employee();
		$areaAccessor     = new \TMT\accessor\AreaAccessor();
		$userAreas = $areaAccessor->getAll($this->user['netId']);

		// Respond for single employee
		if($single) {
			$netId = $params['url'][2];
			$employeeAreas = $areaAccessor->getAll($netId);
			// Determine if both employees have rights to an area in common
			$overlap = false;
			foreach($employeeAreas as $eArea) {
				foreach($userAreas as $uArea) {
					if($uArea->ID === $eArea->ID) {
						$overlap = true;
						break;
					}
				}
				if($overlap)
					break;
			}
			if(!$overlap) {
				$this->error("You do not have rights to see this employee's data");
				return;
			}
			$employee = $employeeAccessor->get($netId);
			$this->respond($employee);
			return;
		}

		// Respond if it is a search
		$search = $params['request'];
		unset($search['url']);
		$employees = $employeeAccessor->search($search);
		$results   = array();

		// Filter results to only return employees who have access to an area in common with the user
		for($i=0; $i < count($employees); $i++) {
			$employeeAreas = $areaAccessor->getAll($employees[$i]->netID);
			$overlap = false;
			foreach($employeeAreas as $eArea) {
				foreach($userAreas as $uArea) {
					if($uArea->ID === $eArea->ID) {
						$overlap = true;
						$results[] = $employees[$i];
						break;
					}
				}
				if($overlap)
					break;
			}
		}

		$this->respond($results);
	}

	/**
	 * Insert an employee
	 * POST /api/employee/ (requires post data with fields to update and netId)
	 * 
	 * Params:
	 *  REQUIRED
	 *		'netID'                
	 *		'firstName'            
	 *		'lastName'             
	 *		'phone'                
	 *		'email'                
	 *		'byuIDnumber'          
	 *		'wage'          
	 * OPTIONAL
	 *		'area' -- defaults to the current session area 
	 *		'active' -- defaults to 1 (i.e. active) 
	 *		'maidenName'           
	 *		'birthday'             
	 *		'chqID'                
	 *		'languages'            
	 *		'hometown'             
	 *		'major'                
	 *		'missionOrStudyAbroad' 
	 *		'graduationDate'       
	 *		'shift'                
	 *		'position'             
	 *		'supervisor'           
	 *		'hireDate'             
	 *		'certificationLevel'   
	 *		'international'        
	 *		'fullTime'             
	 *
	 * This api accepts any of the fields in the employee table, and ignores others
	 *
	 * returns:
	 * {
	 *    status: OK/ERROR,
	 *    data: {**new employee model**}
	 * }
	 */
	public function post($params) {
		$this->requireAuthentication();

		// Check for netId
		if(!isset($params['request']['netID'])) {
			$this->error("No netID given, cannot save without netID");
			return;
		}

		$employeeAccessor = new \TMT\accessor\Employee();

		if ((!isset($params['request']['wage']) || !is_numeric($params['request']['wage'])) && $params['request']['fullTime'] != 1) {
			$this->error("Must include valid starting wage");
			return;
		}

		// Retrieve current employee object
		$model = $employeeAccessor->get($params['request']['netID']);
		if ($model->netID) {
			$this->error($model->netID." already exists as an employee", 400);
			return;
		}

		// Create an employee model with only required values as NULL
		$model = new \TMT\model\Employee((object) array(
			'netID'                => NULL,
			'active'               => 1,
			'area'                 => $this->user['area'],
			'firstName'            => NULL,
			'lastName'             => NULL,
			'maidenName'           => '',
			'phone'                => NULL,
			'email'                => NULL,
			'birthday'             => '',
			'languages'            => '',
			'hometown'             => '',
			'major'                => '',
			'missionOrStudyAbroad' => '',
			'graduationDate'       => '',
			'position'             => 0,
			'shift'                => '',
			'supervisor'           => '',
			'hireDate'             => '',
			'certificationLevel'   => '',
			'international'        => 0,
			'byuIDnumber'          => NULL,
			'fullTime'             => 0,
			'guid'                 => null
		));

		// Update fields accordingly
		foreach($params['request'] as $key => $value) {
			if(property_exists($model, $key)) {
				$model->$key = $value;
			}
		}
		$missing_keys = array();
		foreach($model as $key => $value) {
			if (is_null($value) && $key != "guid"){
				$missing_keys[] = $key;
			}
		}
		if (count($missing_keys)){
			$this->error("Missing the following required fields: ".implode(", ", $missing_keys), 400);
			return;
		}
		$this->user['area'] = $model->area;
		$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");

		$employeeAccessor->save($model);

		// If it is a part-time employee, enter wage information
		if(!$model->fullTime) {
			$starting_wage = new \TMT\model\Raise();
			$starting_wage->netID = $params['request']['netID'];
			$starting_wage->raise = number_format(round((float)$params['request']['wage'], 2), 2);
			$starting_wage->newWage = number_format(round((float)$params['request']['wage'], 2), 2);
			$starting_wage->submitter = $this->user['netId'];
			$starting_wage->date = isset($params['request']['hireDate']) ? $params['request']['hireDate'] : 
				date("Y-m-d 00:00:00");	
			$starting_wage->comments = 'Starting Wage';
			$starting_wage->isSubmitted = 1;

			$raiseAcc = new \TMT\accessor\EmployeeRaiseLog();
			$raiseAcc->insert($starting_wage);
		}
		$this->respond($model);
	}

	/**
	 * Update an existing employee
	 * PUT /api/employee/:netID
	 *
	 * Fields to be updated should be provided in the body of the request
	 *
	 * Returns a model of the updated employee
	 */
	public function put($params) {
		$this->requireAuthentication();

		if (count($params['url']) < 3) {
			$this->error("No netID provided", 400);
		}	

		$netID = $params['url'][2];
		$employeeAcc = new \TMT\accessor\Employee();
		$model = $employeeAcc->get($netID);

		$allow_self_update = array("phone", "email");
		$require_permission = false;

		foreach ($params['request'] as $key => $value) {
			if (property_exists($model, $key)){
				switch($key) {
					case "area":
						if(!$this->isSuperuser()) {
							$this->error($message, 403);
							exit();
						}
						$model->area = $value;
						break;
					case "netID":
						// Never update a netID
						continue;
						break;
					case "position":
						// Positions should only be updated from the default area
						if($model->area == $this->user['area']) {
							$model->$key = $value;
						}
						break;
					default:
						if (!in_array($key, $allow_self_update)){
							$require_permission = true;
						}
						$model->$key = $value;
				}
			}
		}
		if ($require_permission) {
			$this->forcePermission("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2");
		}
		$employeeAcc->save($model);
		$this->respond($model);
	}
}
?>
