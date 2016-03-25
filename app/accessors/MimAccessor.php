<?php

namespace TMT\accessor;

class MimAccessor extends MysqlAccessor {

	/**
	 * Retrieves a MIM by netId
	 *	 if the owner of the given netID is not a MIM, then an empty object is returned
	 *
	 * @param $netID string The mim's netId
	 *
	 * @return object A Mim model object
	 */
	public function get($netID) {
		$query = $this->pdo->prepare("SELECT `majorIncidentManagers`.*, `employee`.`firstName`, 
			`employee`.`lastName` FROM `majorIncidentManagers` LEFT JOIN `employee` 
			ON `employee`.`netID`=`majorIncidentManagers`.`netID` WHERE `employee`.`active`=1 
			AND `majorIncidentManagers`.`netID`=:netID");
		$query->execute(array(':netID' => $netID));
		if($mim = $query->fetch(\PDO::FETCH_OBJ)) {
			return new \TMT\model\Mim($mim);
		} else {
			return new \TMT\model\Mim();
		}
	}

	/**
	 * Retrieves an array of MIMs
	 *
	 * @param $area int The area number
	 * @param $active int The active status of the employees. Defaults to active (1)
	 *
	 * @return array(object) An array of employee model objects
	 */
	public function getAll() {
		// Automatically ignore inactive or terminated MIMs
		$query = $this->pdo->prepare("SELECT `majorIncidentManagers`.*, `employee`.`firstName`, 
			`employee`.`lastName` FROM `majorIncidentManagers` LEFT JOIN `employee` 
			ON `employee`.`netID`=`majorIncidentManagers`.`netID` WHERE `employee`.`active`=1
			ORDER BY `employee`.`firstName`");
		$query->execute();
		$mims = array();
		while($mim = $query->fetch()) {
			$mims[] = new \TMT\model\Mim($mim);
		}
		return $mims;
	}

	/**
	 * Get a list of employees that have access to the areas passed in.
	 *   This function will be useful for populating a fuzzy search on the CRUD app for MIMs
	 *
	 * @param $areas Array of areas to search for employees
	 *
	 * @return Array of Mim objects representing employees with access to the areas provided
	 */
	public function getPossible($areas) {
		if (!count($areas))
			return [];
		$areaQueryString = [];
		$queryParams = [];
		foreach ($areas as $index => $area) {
			$areaQueryString[] = ":area$index";
			$queryParams[":area$index"] = $area;

		}
		$areaQueryString = \implode(', ', $areaQueryString);
		$queryString = "SELECT DISTINCT `netID`, `firstName`, `lastName` FROM (
			SELECT `employee`.`netID`, `employee`.`firstName`, `employee`.`lastName`, `employee`.`area`,
				`employee`.`active`
		     	FROM `employee` LEFT JOIN `employeeAreaPermissions` ON 
		    	`employee`.`netID`=`employeeAreaPermissions`.`netID`
	     	UNION 
			SELECT `employee`.`netID`, `employee`.`firstName`, `employee`.`lastName`, 
					`employeeAreaPermissions`.`area`, `employee`.`active`
    			FROM `employee` RIGHT JOIN `employeeAreaPermissions` ON 
     			`employee`.`netID`=`employeeAreaPermissions`.`netID`
   			) AS `tmp`
			WHERE `area` IN ($areaQueryString) AND `active`=1
			ORDER BY `firstName`";
		$query = $this->pdo->prepare($queryString);
		$query->execute($queryParams);
		$potential_mims = [];
		while($potential_mim = $query->fetch()) {
			$potential_mims[] = new \TMT\model\Mim($potential_mim);
		}
		return $potential_mims;
	}

	/**
	 * Insert a new MIM into the database
	 *
	 * @param $mim the Mim model to be inserted
	 *
	 * @return the Mim that was inserted
	 */
	public function insert($mim) {
		$query = $this->pdo->prepare("INSERT INTO `majorIncidentManagers` (`netID`, `guid`) VALUES (:netID,:guid)");
		$query->execute(array(':netID' => $mim->netID, ':guid' => $this->newGuid()));
		return $this->get($mim->netID);
	}

	/**
	 * Deletes a MIM from the database
	 *
	 * @param $mim the Mim model to be inserted
	 *
	 * @return an empty Mim model
	 */
	public function delete($mim) {
		$query = $this->pdo->prepare("DELETE FROM `majorIncidentManagers` WHERE `netID`=:netID");
		$query->execute(array('netID' => $mim->netID));
		return $this->get($mim->netID);
	}

}
?>
