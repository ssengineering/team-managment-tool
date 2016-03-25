<?php

	function employeeFillDefaultAreaOnly(){
		global $area, $db;
		$employeeFiller = "";
		try {
			$query = $db->prepare("SELECT firstName, lastName, netID FROM employee WHERE area=:area AND active='1'  ORDER BY firstName");
			$query->execute(array(":area" => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$employeeFiller .= "<option value=''>Please Select an Employee</option>";
		while ($curEmployee = $query->fetch()) {
			$employeeFiller .= "<option value='".$curEmployee->netID."'>".$curEmployee->firstName." ".$curEmployee->lastName."</option>";
		}
		echo $employeeFiller;
	}

	function employeeFill($area){
		global $db;
		$employeeFiller = "";
		try {
			$query = $db->prepare("SELECT firstName, lastName, netID FROM employee WHERE area=:area AND active='1'  ORDER BY firstName");
			//A second query statment to get those who are not in the current area, so that we can sort them in the same list as the
			//those who are in the area, rather than having them all grouped at the bottom.
			$queryNotDef = $db->prepare("SELECT `employeeAreaPermissions`.`netID`, `employee`.`firstName`, `employee`.`lastName`, `employee`.`area` FROM `employeeAreaPermissions` LEFT JOIN `employee` ON 
				`employeeAreaPermissions`.`netID`=`employee`.`netID` WHERE `employeeAreaPermissions`.`area`=:area AND `employee`.`active`=1"); 
			$query->execute(array(":area" => $area));
			$queryNotDef->execute(array(":area"=> $area));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$employeeFiller .= "<option value=''>Please Select an Employee</option>";
		$employees = array();
		//Pushes the results of BOTH queries into the same array, so that we can then sort them all together.
		while($curEmployee = $query->fetch())
		{
			$employees[] = $curEmployee;
		}
		while($curEmployee = $queryNotDef->fetch())
		{
			$employees[] = $curEmployee;
		}
		//The function for comparison used by the method usort. This will sort all employees
		//alphabetically by first name.
		function compareFirstName($curEmployeeA, $curEmployeeB){
			return strcmp($curEmployeeA->firstName, $curEmployeeB->firstName);
		}
		usort($employees, "compareFirstName");
		//Print the employee list
		foreach($employees as $curEmployee) {
				$employeeFiller .= "<option value='".$curEmployee->netID."'>".$curEmployee->firstName." ".$curEmployee->lastName;
				if($curEmployee->area != $area){
					$employeeFiller .= "*";
				}
				$employeeFiller .= "</option>";
		}
		echo $employeeFiller;
	}


	function employeeFillCurrentArea(){
		global $db, $area;
		$employeeFiller = "";
		try {
			$query = $db->prepare("SELECT firstName, lastName, area, netID FROM employee WHERE area=:area AND active=1  ORDER BY firstName");
			//A second query statment to get those who are not in the current area, so that we can sort them in the same list as the
			//those who are in the area, rather than having them all grouped at the bottom.
			$queryNotDef = $db->prepare("SELECT `employeeAreaPermissions`.`netID`, `employee`.`firstName`, `employee`.`lastName`, `employee`.`area` FROM `employeeAreaPermissions` LEFT JOIN `employee` ON 
				`employeeAreaPermissions`.`netID`=`employee`.`netID` WHERE `employeeAreaPermissions`.`area`=:area AND `employee`.`active`=1"); 
			$query->execute(array(":area" => $area));
			$queryNotDef->execute(array(":area"=> $area));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$employeeFiller .= "<option value=''>Please Select an Employee</option>";
		$employees = array();
		//Pushes the results of BOTH queries into the same array, so that we can then sort them all together.
		while($curEmployee = $query->fetch())
		{
			$employees[] = $curEmployee;
		}
		while($curEmployee = $queryNotDef->fetch())
		{
			$employees[] = $curEmployee;
		}
		//The function for comparison used by the method usort. This will sort all employees
		//alphabetically by first name.
		function compareFirstName($curEmployeeA, $curEmployeeB){
			return strcmp($curEmployeeA->firstName, $curEmployeeB->firstName);
		}
		usort($employees, "compareFirstName");
		//Print the employee list
		foreach($employees as $curEmployee) {
				$employeeFiller .= "<option value='".$curEmployee->netID."'>".$curEmployee->firstName." ".$curEmployee->lastName;
				if($curEmployee->area != $area){
					$employeeFiller .= "*";
				}
				$employeeFiller .= "</option>";
		}
		echo $employeeFiller;
	}

	function employeeFillSelected($netID,$area){
		global $db, $area;
		$employeeFiller = "";
		try {
			$query = $db->prepare("SELECT firstName, lastName, area, netID FROM employee WHERE area=:area AND active=1  ORDER BY firstName");
			//A second query statment to get those who are not in the current area, so that we can sort them in the same list as the
			//those who are in the area, rather than having them all grouped at the bottom.
			$queryNotDef = $db->prepare("SELECT `employeeAreaPermissions`.`netID`, `employee`.`firstName`, `employee`.`lastName`, `employee`.`area` FROM `employeeAreaPermissions` LEFT JOIN `employee` ON 
				`employeeAreaPermissions`.`netID`=`employee`.`netID` WHERE `employeeAreaPermissions`.`area`=:area AND `employee`.`active`=1"); 
			$query->execute(array(":area" => $area));
			$queryNotDef->execute(array(":area" => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$employeeFiller .= "<option value=''>Please Select an Employee</option>";
		$employees = array();
		//Pushes the results of BOTH queries into the same array, so that we can then sort them all together.
		while($curEmployee = $query->fetch())
		{
			$employees[] = $curEmployee;
		}
		while($curEmployee = $queryNotDef->fetch())
		{
			$employees[] = $curEmployee;
		}
		//The function for comparison used by the method usort. This will sort all of the members of the array (which includes
		//those who are not defaulted to the current area), alphabetically by first name.
		function compareFirstName($curEmployeeA, $curEmployeeB){
			return strcmp($curEmployeeA->firstName, $curEmployeeB->firstName);
		}
		usort($employees, "compareFirstName");
		//Print the employee list
		foreach($employees as $curEmployee) {
				$employeeFiller .= "<option value='".$curEmployee->netID."'>".$curEmployee->firstName." ".$curEmployee->lastName;
				if($curEmployee->area != $area){
					$employeeFiller .= '*';		
				}
				$employeeFiller .= "</option>";
		}
		echo $employeeFiller;
	}

	

/////////////////////////////////

	function managerFill($netID = "", $manArea = 0){
		global $area, $db;
		if ($manArea == 0) $manArea = $area;
		$managerFiller = "";
		try {
			$managersQuery = $db->prepare("SELECT `firstName`, `lastName`, `netID` FROM `employee` WHERE active='1' AND `netID` != :netID AND `position` 
				IN (SELECT `positionId` FROM `positions` WHERE (`area`='1' OR `area`=:manArea) AND `positionName`='Manager') ORDER BY `firstName`");
			$managersQuery->execute(array(':netID' => $netID, ':manArea' => $manArea));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$managerFiller .= "<option value=''>Please Select a Manager</option>";
		while ($curManager = $managersQuery->fetch()) {
			if ($netID == $curManager->netID) {
				$managerFiller .= "<option value='".$curManager->netID."' selected>".$curManager->firstName." ".$curManager->lastName."</option>";
			} else {
				$managerFiller .= "<option value='".$curManager->netID."'>".$curManager->firstName." ".$curManager->lastName."</option>";
			}
		}
		echo $managerFiller;
	}

////////////////////////////////////

	function getEmployeeFillSelectedDefaultOnly($netID,$area){
		global $db;
		$employeeFiller = "";
		try {
			$employeeQuery = $db->prepare("SELECT firstName, lastName, netID FROM employee WHERE area=:area AND active='1' ORDER BY firstName");
			$employeeQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		while ($curEmployee = $employeeQuery->fetch()) {
			if (strtolower($netID) == strtolower($curEmployee->netID)) {
				$employeeFiller .= "<option value='".$curEmployee->netID."' selected>".$curEmployee->firstName." ".$curEmployee->lastName."</option>";
			}
			else {
				$employeeFiller .= "<option value='".$curEmployee->netID."'>".$curEmployee->firstName." ".$curEmployee->lastName."</option>";
			}
		}
		echo $employeeFiller;
	}


	function getEmployeesNotDefaultedToCurArea(){
		global $area, $db;
		try {
			$nonDefaultQuery = $db->prepare("SELECT `employeeAreaPermissions`.`netID` FROM `employeeAreaPermissions` LEFT JOIN `employee` ON 
			`employeeAreaPermissions`.`netID`=`employee`.`netID` WHERE `employeeAreaPermissions`.`area`=:area AND `employee`.`active`=1  ORDER BY firstName");
			$nonDefaultQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$employees = array();
		while($result = $nonDefaultQuery->fetch()){
			$employees[]=$result->netID;
		}
		return $employees;
	}

	function fillNondefaultedEmployees(){
		global $db;
		$employees = getEmployeesNotDefaultedToCurArea();
		$employeeFiller = "";
		foreach($employees as $curEmployee){
			try {
				$employeeQuery = $db->prepare("SELECT firstName, lastName, netID FROM employee WHERE netID=:netID");
				$employeeQuery->execute(array(':netID' => $curEmployee));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$info = $employeeQuery->fetch();
			$employeeFiller .= "<option value='".$info->netID."'>".$info->firstName.", ".$info->lastName."*</option>";
		}
		echo $employeeFiller;
	}

	function fillNondefaultedEmployeesSelected($netID){
		global $db;
		$employees = getEmployeesNotDefaultedToCurArea();
		$employeeFiller = "";
		foreach($employees as $curEmployee){
			try {
				$employeeQuery = $db->prepare("SELECT firstName, lastName, netID FROM employee WHERE netID=:netID");
				$employeeQuery->execute(array(':netID' => $curEmployee));
			} catch(PDOException $e) {
				exit("error in query");
			}			
			$info = $employeeQuery->fetch();
			if ($netID == $curEmployee) {
				$employeeFiller .= "<option value='".$info->netID."' selected>".$info->firstName.", ".$info->lastName."*</option>";
			} else {
				$employeeFiller .= "<option value='".$info->netID."'>".$info->firstName.", ".$info->lastName."*</option>";
			}
		}
		echo $employeeFiller;
	}

?>
