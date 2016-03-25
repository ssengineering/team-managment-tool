<?php

namespace TMT\accessor;

class Employee extends MysqlAccessor {

	/**
	 * Retrieves an employee by netId
	 *   if no employee has the given netId then an empty object is returned
	 *
	 * @param $netId string The employee's netId
	 *
	 * @return object An employee model object
	 */
	public function get($netId) {
		$query = $this->pdo->prepare("SELECT * FROM employee WHERE netID=:netId");
		$query->execute(array(':netId' => $netId));
		if($employee = $query->fetch()) {
			return new \TMT\model\Employee($employee);
		} else {
			return new \TMT\model\Employee();
		}
	}

	/**
	 * Retrieves an array of employees in a given area
	 *
	 * @param $area        int/array(int) The area number, or array of area numbers
	 * @param $defaultOnly bool If true, only get employees whose default area has been specified,
	 *                          If false, get all employees that have access to the area
	 * @param $active      int The active status of the employees. Defaults to active (1)
	 *
	 * @return array(object) An array of employee model objects
	 */
	public function getByArea($area, $defaultOnly = true, $active = null) {
		$queryString = "SELECT DISTINCT employee.* FROM employee LEFT JOIN employeeAreaPermissions ON employeeAreaPermissions.netID=employee.netID WHERE";
		$queryParams = array();

		if(is_array($area)) {
			$inClause = "";
			for($i=0; $i < count($area); $i++) {
				$inClause .= ":area".$i;
				$queryParams[':area'.$i] = $area[$i];
				if($i != count($area)-1)
					$inClause .= ',';
			}
			$queryString .= " (employee.area IN (".$inClause.")";
		} else {
			$queryString .= " (employee.area=:area";
			$queryParams[':area'] = $area;
		}

		// Get all employees with access to the area
		if(!$defaultOnly) {
			if(is_array($area)) {
				$inClause = "";
				for($i=0; $i < count($area); $i++) {
					$inClause .= ":access".$i;
					$queryParams[':access'.$i] = $area[$i];
					if($i != count($area)-1)
						$inClause .= ',';
				}
				$queryString .= " OR employeeAreaPermissions.area IN (".$inClause."))";
			} else {
				$queryString .= " OR employeeAreaPermissions.area=:areaPerm)";
				$queryParams[':areaPerm'] = $area;
			}
		} else {
			$queryString .= ")";
		}

		// Retrieve only employees with the given activity status
		if($active !== null) {
			$queryString .= " AND employee.active=:active";
			$queryParams[':active'] = $active;
		}

		$query = $this->pdo->prepare($queryString);
		$query->execute($queryParams);
		$employees = array();
		while($employee = $query->fetch()) {
			$employees[] = new \TMT\model\Employee($employee);
		}
		return $employees;
	}

	/**
	 * Searches the employee table for employees who match the search parameters
	 *
	 * @param $params associative array An array where the keys are column names and values are search parameters for that column
	 *   For the following columns, the search function will match values that are like them:
	 *     netId, firstName, lastName
	 *   For these values the search function will only accept exact matches:
	 *     active, area, fullTime, position
	 *
	 * @return array(object) An array of Employee objects that match the search criteria
	 */
	public function search($params = array()) {
		//SELECT * FROM employee WHERE (x OR y OR z) AND (a AND b AND c)
		$queryString = "SELECT * FROM employee WHERE (";
		if(count($params) < 1)
			return array();
		$condition1 = false;
		if(array_key_exists('netId', $params)) {
			$queryString .= "LOWER(netID) LIKE :netId OR ";
			$queryParams[':netId'] = "%".strtolower($params['netId'])."%";
			$condition1 = true;
		}
		if(array_key_exists('firstName', $params)) {
			$queryString .= "LOWER(firstName) LIKE :firstName OR ";
			$queryParams[':firstName'] = "%".strtolower($params['firstName'])."%";
			$condition1 = true;
		}
		if(array_key_exists('lastName', $params)) {
			$queryString .= "LOWER(lastName) LIKE :lastName OR ";
			$queryParams[':lastName'] = "%".strtolower($params['lastName'])."%";
			$condition1 = true;
		}
		if($condition1) {
			$queryString = substr($queryString, 0, -4);
			$queryString .= ") AND (";
		}

		$condition2 = false;
		if(array_key_exists('active', $params)) {
			$queryString .= "active=:active AND ";
			$queryParams[':active'] = $params['active'];
			$condition2 = true;
		}
		if(array_key_exists('area', $params)) {
			$queryString .= "area=:area AND ";
			$queryParams[':area'] = $params['area'];
			$condition2 = true;
		}
		if(array_key_exists('fullTime', $params)) {
			$queryString .= "fullTime=:fullTime AND ";
			$queryParams[':fullTime'] = $params['fullTime'];
			$condition2 = true;
		}
		if(array_key_exists('position', $params)) {
			$queryString .= "position=:position AND ";
			$queryParams[':position'] = $params['position'];
			$condition2 = true;
		}

		if(!$condition1 && !$condition2)
			return array();

		if($condition2) {
			$queryString = substr($queryString, 0, -5);
			$queryString .= ")";
		} else {
			$queryString = substr($queryString, 0, -6);
		}

		$query = $this->pdo->prepare($queryString);
		$query->execute($queryParams);
		$employees = array();
		while($employee = $query->fetch()) {
			$employees[] = new \TMT\model\Employee($employee);
		}
		return $employees;
	}

	/**
	 * Handles both inserting and updating entries into the employee table
	 *   It will insert if there is already an entry with the given netId,
	 *   otherwise, it will update the user.
	 *
	 * @param object An employee model object
	 */
	public function save($employee) {
		//test if this is a new employee or an already existing one
		$query = $this->pdo->prepare("SELECT COUNT(netID) FROM employee WHERE netID=:netId");
		$query->execute(array(':netId' => $employee->netID));
		if($query->fetch(\PDO::FETCH_NUM)[0] > 0) {
			//Employee was found, update.
			$query2 = $this->pdo->prepare("UPDATE employee SET active=:active, area=:area, firstName=:first, lastName=:last, maidenName=:maiden, phone=:phone, email=:email,
				birthday=:birthday, languages=:languages, hometown=:hometown, major=:major, missionOrStudyAbroad=:mission, graduationDate=:graduation, position=:position, shift=:shift,
				supervisor=:supervisor, hireDate=:hireDate, certificationLevel=:cert, international=:international, byuIDnumber=:byuId, fullTime=:fullTime WHERE netID=:netId");
			$query2->execute(array(
				':active'        => $employee->active,
				':area'          => $employee->area,
				':first'         => $employee->firstName,
				':last'          => $employee->lastName,
				':maiden'        => $employee->maidenName,
				':phone'         => $employee->phone,
				':email'         => $employee->email,
				':birthday'      => $employee->birthday,
				':languages'     => $employee->languages,
				':hometown'      => $employee->hometown,
				':major'         => $employee->major,
				':mission'       => $employee->missionOrStudyAbroad,
				':graduation'    => $employee->graduationDate,
				':position'      => $employee->position,
				':shift'         => $employee->shift,
				':supervisor'    => $employee->supervisor,
				':hireDate'      => $employee->hireDate,
				':cert'          => $employee->certificationLevel,
				':international' => $employee->international,
				':byuId'         => $employee->byuIDnumber,
				':fullTime'      => $employee->fullTime,
				':netId'         => $employee->netID
			));
		} else {
			//No employee found with this netId, insert.
			$query2 = $this->pdo->prepare("INSERT INTO employee (netID,active,area,firstName,lastName,maidenName,phone,email,birthday,languages,hometown,major,
				missionOrStudyAbroad,graduationDate,position,shift,supervisor,hireDate,certificationLevel,international,byuIDnumber,fullTime,guid) VALUES
				(:netId,:active,:area,:first,:last,:maiden,:phone,:email,:birthday,:languages,:hometown,:major,:mission,:graduation,:position,:shift,:supervisor,:hireDate,:cert,:international,:byuId,:fullTime,:guid)");
			$query2->execute(array(
				':netId'         => $employee->netID,
				':active'        => $employee->active,
				':area'          => $employee->area,
				':first'         => $employee->firstName,
				':last'          => $employee->lastName,
				':maiden'        => $employee->maidenName,
				':phone'         => $employee->phone,
				':email'         => $employee->email,
				':birthday'      => $employee->birthday,
				':languages'     => $employee->languages,
				':hometown'      => $employee->hometown,
				':major'         => $employee->major,
				':mission'       => $employee->missionOrStudyAbroad,
				':graduation'    => $employee->graduationDate,
				':position'      => $employee->position,
				':shift'         => $employee->shift,
				':supervisor'    => $employee->supervisor,
				':hireDate'      => $employee->hireDate,
				':cert'          => $employee->certificationLevel,
				':international' => $employee->international,
				':byuId'         => $employee->byuIDnumber,
				':fullTime'      => $employee->fullTime,
				':guid'          => $this->newGuid()
			));
		}
	}
}
?>
