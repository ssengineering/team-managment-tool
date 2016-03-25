<?php

namespace TMT\accessor;

class AreaAccessor extends MysqlAccessor {

	/**
	 * Retrieves an area by id
	 *
	 * @param $ID area's ID
	 *
	 * @return object An Area model object
	 */
	public function get($ID) {
		$query = $this->pdo->prepare("SELECT * FROM `employeeAreas` WHERE `ID`=:ID");
		$query->execute(array(':ID' => $ID));
		if($area = $query->fetch(\PDO::FETCH_OBJ)) {
			return new \TMT\model\Area($area);
		} else {
			return new \TMT\model\Area();
		}
	}

	/**
	 * Retrieves an area by short name
	 *
	 * @param $name area's short name
	 *
	 * @return object An Area model object
	 */
	public function getByShortName($name) {
		$query = $this->pdo->prepare("SELECT * FROM `employeeAreas` WHERE `area`=:name");
		$query->execute(array(':name' => $name));
		if($area = $query->fetch(\PDO::FETCH_OBJ)) {
			return new \TMT\model\Area($area);
		} else {
			return new \TMT\model\Area();
		}
	}

	/**
	 * Retrieves a list of all areas.
	 *
	 * @param $netID if set, restricts the list to areas the user has access to
	 *
	 * @return array(object) An array of Area model objects
	 */
	public function getAll($netID = null) {
		if ($netID === null) {
			$query = "SELECT * FROM `employeeAreas`";
			$params = array();
		} else {
			$query = "SELECT `employeeAreas`.* FROM `employeeAreas` RIGHT JOIN 
				((SELECT `employee`.`area` FROM `employee` WHERE `netID`=:netID)  
				UNION DISTINCT 
				(SELECT `employeeAreaPermissions`.`area` FROM `employeeAreaPermissions` WHERE `netID`=:netID)) 
				AS `tmp` ON `employeeAreas`.`ID`=`tmp`.`area` ORDER BY `employeeAreas`.`ID` ASC";
			$params = array(":netID" => $netID);
		}

		$stmt = $this->pdo->prepare($query);
		$stmt->execute($params);
		$areas = array();
		while($area = $stmt->fetch()) {
			$areas[] = new \TMT\model\Area($area);
		}
		return $areas;
	}
	
	/**
	 *	Check if a user has rights to an area
	 *
	 *	@param $netID 	string 	the user's netID
	 *	@param $area 	int		the area to check
	 *
	 *	@return true if the user has rights to the area, false otherwise
	 */
	public function checkAreaRights($netID, $area) {
		$areas = $this->getAll($netID);
		foreach ($areas as $area_obj) {
			if ($area_obj->ID == $area)
				return true;
		}
		return false;
	}

	/**
	 * Insert or update an area in the database
	 *
	 * @param $area the Area model to be inserted/updated. If the ID field is set, an update will
	 *  be run -- else an insert is run
	 *
	 * @return the Mim that was inserted/updated
	 */
	public function save($area) {
		if ($area->ID === NULL) {
			$query = $this->pdo->prepare("INSERT INTO `employeeAreas`(`area`, `longName`, `startDay`, 
				`endDay`, `startTime`, `endTime`, `hourSize`, `homePage`, `postSchedulesByDefault`, 
				`canEmployeesEditWeeklySchedule`, `guid`) VALUES (:area,:longName,:startDay,:endDay,:startTime,
				:endTime,:hourSize,:homePage,:postSchedulesByDefault,:canEmployeesEditWeeklySchedule,:guid)");
			$query->execute(array(
				":area" => $area->area,
				":longName" => $area->longName,
				":startDay" => $area->startDay,
				":endDay" => $area->endDay,
				":startTime" => $area->startTime,
				":endTime" => $area->endTime,
				":hourSize" => $area->hourSize,
				":homePage" => $area->homePage,
				":postSchedulesByDefault" => $area->postSchedulesByDefault,
				":canEmployeesEditWeeklySchedule" => $area->canEmployeesEditWeeklySchedule,
				":guid" => $this->newGuid()
			));
			return $this->get($this->pdo->lastInsertId());
		} else {
			$query = $this->pdo->prepare("UPDATE `employeeAreas` SET `area`=:area,`longName`=:longName, 
				`startDay`=:startDay,`endDay`=:endDay,`startTime`=:startTime,`endTime`=:endTime,
				`hourSize`=:hourSize,`homePage`=:homePage,`postSchedulesByDefault`=:postSchedulesByDefault, 
				`canEmployeesEditWeeklySchedule`=:canEmployeesEditWeeklySchedule WHERE ID=:ID");
			$query->execute(array(
				":ID" => $area->ID,
				":area" => $area->area,
				":longName" => $area->longName,
				":startDay" => $area->startDay,
				":endDay" => $area->endDay,
				":startTime" => $area->startTime,
				":endTime" => $area->endTime,
				":hourSize" => $area->hourSize,
				":homePage" => $area->homePage,
				":postSchedulesByDefault" => $area->postSchedulesByDefault,
				":canEmployeesEditWeeklySchedule" => $area->canEmployeesEditWeeklySchedule
			));
			return $this->get($area->ID);
		}
	}

}
?>
