<?php
	require_once('guid.php');

	$hourSize = getHourSize();
	$showUnposted = 0;

	function getHourSize() {
		global $area, $db;
		try {
			$hourQuery = $db->prepare("SELECT hourSize FROM employeeAreas WHERE ID=:area");
			$hourQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $hourQuery->fetch();
		return $result->hourSize;
	}
	function getStartDay() {
		global $area, $db;
		try {
			$startQuery = $db->prepare("SELECT startDay FROM employeeAreas WHERE ID=:area");
			$startQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $startQuery->fetch();
		return $result->startDay;
	}
	function getEndDay() {
		global $area, $db;
		try {
			$endQuery = $db->prepare("SELECT endDay FROM employeeAreas WHERE ID=:area");
			$endQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $endQuery->fetch();
		return $result->endDay;
	}
	function getStartTime() {
		global $area, $db;
		try {
			$startQuery = $db->prepare("SELECT startTime FROM employeeAreas WHERE ID=:area");
			$startQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $startQuery->fetch();
		return $result->startTime;
	}
	function getEndTime() {
		global $area, $db;
		try {
			$endQuery = $db->prepare("SELECT endTime FROM employeeAreas WHERE ID=:area");
			$endQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $endQuery->fetch();
		return $result->endTime;
	}
	
	function getSaturday($date) {
		$weekday = date("N", strtotime($date));
		
		return date("Y-m-d", strtotime($date." - ".(($weekday + 1) % 7)." days"));
	}
	
	function getSemesterStartDate($semester) {
		global $area, $db;
		try {
			$startQuery = $db->prepare("SELECT `startDate` FROM `scheduleSemesters` WHERE `area`=:area AND `semester`=:semester");
			$startQuery->execute(array(':area' => $area, ':semester' => $semester));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $startQuery->fetch();
		return $result->startDate;
	}
	function getSemesterEndDate($semester) {
		global $area, $db;
		try {
			$endQuery = $db->prepare("SELECT `endDate` FROM `scheduleSemesters` WHERE `area`=:area AND `semester`=:semester");
			$endQuery->execute(array(':area' => $area, ':semester' => $semester));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $endQuery->fetch();
		return $result->endDate;
	}
	
	// Returns the semester short name based on the date
	function getSemester($date) {
		global $area, $db;
		try {
			$semesterQuery = $db->prepare("SELECT semester FROM scheduleSemesters WHERE startDate<=:date1 AND endDate>=:date2 AND area=:area");
			$semesterQuery->execute(array(':date1' => $date, ':date2' => $date, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($result = $semesterQuery->fetch())
			return $result->semester;
		else
			return "";
	}
	
	// Returns the semester long name based on the date
	function getSemesterName($date) {	    
		global $area, $db;
		try {
			$semesterQuery = $db->prepare("SELECT name FROM scheduleSemesters WHERE startDate<=:date1 AND endDate>=:date2 AND area=:area");
			$semesterQuery->execute(array(':date1' => $date, ':date2' => $date, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($result = $semesterQuery->fetch())
			return $result->name;
		else
			return "";
	}
	
	// Returns a boolean indicating whether
	function isDateInRange($startDate, $endDate, $date) {
		return (($date >= $startDate) && ($date <= $endDate));
	}
	
	function hasTradesOnDate($employee, $date) {
		global $db;
		try {
			$semesterQuery = $db->prepare("SELECT `hour` FROM scheduleTradeRequests WHERE `date`=:day AND `netID`=:employee AND `area`=:area");
			$semesterQuery->execute(array(':day' => $date, ':employee' => $employee, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($semesterQuery->fetch())
			return true;
		else
			return false;
	}
	
	function getCanEmployeesEditWeeklySchedule() {
		global $area, $db;
		
		try {
			$editQuery = $db->prepare("SELECT `canEmployeesEditWeeklySchedule` FROM `employeeAreas` WHERE `ID`=:area");
			$editQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $editQuery->fetch();
		return $result->canEmployeesEditWeeklySchedule;
	}

	function isScheduleLocked($semester)
	{
		global $area, $db;
		try {
			$lockedQuery = $db->prepare("SELECT `locked` FROM `scheduleSemesters` WHERE `semester`=:semester AND `area`=:area");
			$lockedQuery->execute(array(':semester' => $semester, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$lock = $lockedQuery->fetch();
		return $lock->locked;
	}
	
	function hourTypeListBox($name = "hourlist", $class="hourlist", $callback = "", $size = 5, $multiple = true) {
	    global $area, $db;
	    $output = "<select name=$name id=$name class=$class onChange='$callback' size=$size ".($multiple ? "multiple='multiple'" : "").">";
	    
	    try {
			$hourTypesQuery = $db->prepare("SELECT * FROM scheduleHourTypes WHERE area=:area AND `deleted`=0 ORDER BY value ASC");
			$hourTypesQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		
		while ($row = $hourTypesQuery->fetch(PDO::FETCH_ASSOC)) {
			$output.="<option value='".$row['ID']."'".($row['defaultView'] > 0 ? " selected" : "").">".$row['longName']."</option>";
		}
		
		$output.="</select>";
		
		return $output;
	}

	function employeeNameListBox($name = "namelist", $class="namelist", $callback = "", $size = 5, $multiple = true) {
	    global $area, $db;
	    $output = "<select name=$name id=$name class=$class onChange='$callback' size=$size ".($multiple ? "multiple='multiple'" : "").">";

	    try {
			$employeeQuery = $db->prepare("SELECT * FROM `employee` WHERE `active`=1 AND (`area`=:area1 OR `netID` IN (SELECT `netID` FROM `employeeAreaPermissions` WHERE `area`=:area2)) ORDER BY CASE WHEN `area`=:area3 THEN 0 ELSE 1 END,`firstName`");
			$employeeQuery->execute(array(':area1' => $area, ':area2' => $area, ':area3' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
	
		while ($row = $employeeQuery->fetch(PDO::FETCH_ASSOC)) 
		{
			if($row['area'] == $area)
			{
				$output.="<option value='".$row['netID']."' selected>".nameByNetId($row['netID'])."</option>";
			}
			else
			{
				$output.="<option value='".$row['netID']."' selected>*".nameByNetId($row['netID'])."</option>";
			}
		}
		
		$output.="</select>";
		
		return $output;
	}
		
	function getSemesterList() {
	    global $area, $db;
	    $output = "";
	    $date = date("Y-m-d");
	    
	    try {
			$semesterQuery = $db->prepare("SELECT semester, name, startDate, endDate FROM scheduleSemesters WHERE area=:area ORDER BY startDate ASC");
			$semesterQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		
		while ($row = $semesterQuery->fetch(PDO::FETCH_ASSOC)) {
			$output.="<option value='".$row['startDate']."'".(isDateInRange($row['startDate'], $row['endDate'],$date) ? " selected" : "").">";
			$output.=$row['name'];
			$output.="</option>";
		}
		
		echo $output;
	}
	
	// Converts a decimal hour to a descriptive time (ex 14.25 = "2:15p")
	function hourToTime($hour) {
		$time = (floor($hour) % 12);
		if ($time == 0) $time = 12;
		
		$minute = round(($hour * 60) % 60);
		
		$time .= ":";
		
		if ($minute < 10) $time .= "0";
		$time .= $minute;
		
		$time .= (($hour % 24) >= 12) ? "p" : "a";
		
		return $time; 
		//return $hour.":00";
	}
	
	function hourToTimeFullSuffix($hour) {
		$time = (floor($hour) % 12);
		if ($time == 0) $time = 12;
		
		$minute = round(($hour * 60) % 60);
		
		$time .= ":";
		
		if ($minute < 10) $time .= "0";
		$time .= $minute;
		
		$time .= (($hour % 24) >= 12) ? " PM" : " AM";
		
		return $time;
	}
	
	function hourToMilitary($hour) {
		$time = (floor($hour) % 24);
		
		// This chacks for times that need a zero prepended.
		if ($time < 10 && $time < 0) $time = '-0'.abs($time);
		else if ($time < 10) $time = '0'.$time;
		
		$minute = round(($hour * 60) % 60);
		
		$time .= ":";
		
		if ($minute < 10) $time .= "0";
		$time .= $minute;
				
		return $time;
	}
    
	// returns the name of an hour type based on its value
    function getHourNameByValue($ID) {
    	global $area, $db;
    	try {
			$nameQuery = $db->prepare("SELECT longName FROM scheduleHourTypes WHERE area=:area AND `ID`=:ID");
			$nameQuery->execute(array(':area' => $area, ':ID' => $ID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $nameQuery->fetch();
		return $result->longName;
	}
	
	function getHourPermissionById($typeId) {
		global $area, $db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM scheduleHourTypes WHERE area=:area AND `ID`=:ID");
			$permissionQuery->execute(array(':area' => $area, ':ID' => $typeId));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($result = $permissionQuery->fetch())
			return $result->permission;
		else
			return "";
	}
	
	function getHourPermissionByValue($value) {
		global $area, $db;
		try {
			$permissionQuery = $db->prepare("SELECT permission FROM scheduleHourTypes WHERE area=:area AND `value`=:value");
			$permissionQuery->execute(array(':area' => $area, ':value' => $value));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($result = $permissionQuery->fetch())
			return $result->permission;
		else
			return "";
	}

    // Returns a boolean indicating whether the hour passed to the function
    // begins a shift.
    // NOTE: This does not currently check the previous day if the hour is 0.
    function startingShift($netID, $day, $hour) 
    {
    	global $area, $db;
	
		// Get the hour size from the table and times by 60 to get out minutes
		try {
			$hourSizeQuery = $db->prepare("SELECT `hourSize` FROM `employeeAreas` WHERE `ID`=:area");
			$hourSizeQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$hourSize = $hourSizeQuery->fetch(PDO::FETCH_ASSOC);
		$hourSize = $hourSize['hourSize'] * 60;        

		$hour = date('H:i:s', $hour);
	    
		try {
			$scheduleQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE `area`=:area AND `employee`=:employee AND `deleted`=0 AND `startDate`=:day AND CONCAT(`startDate`,' ',`startTime`) <= :start AND CONCAT(`endDate`,' ',`endTime`) > :end");
			$scheduleQuery->execute(array(':area' => $area, ':employee' => $netID, ':day' => $day, ':start' => $day." ".$hour, ':end' => $day." ".$hour));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$schedule = $scheduleQuery->fetch(PDO::FETCH_ASSOC);

		return($hour == "0" || (strtotime($schedule['startTime'])+($hourSize*60)) >= strtotime($hour)&&strtotime($hour) >= strtotime($schedule['startTime']));
//Uses 60 to change the hourSize 
		//to seconds so that it can be properly added to the timestamp.
		//NOTE: THE NUMBER 60 IS DEPENDENT ON THE DELCLARATION ABOVE THAT $hourSize=$hourSize['hourSize']*60.
    }
    
    // Returns a boolean indicating whether the hour passed to the function
    // ends a shift.
    function endingShift($netID, $day, $hour) 
    {
    	global $area, $db;
	
		// Get the hour size from the table and times by 60 to get out minutes
		try {
			$hourSizeQuery = $db->prepare("SELECT `hourSize` FROM `employeeAreas` WHERE `ID`=:area");
			$hourSizeQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$hourSize = $hourSizeQuery->fetch(PDO::FETCH_ASSOC);
		$hourSize = $hourSize['hourSize'] * 60;        

		$hour = date('H:i:s', $hour);
	        $nextHour = date('H:i:s', strtotime("+$hourSize minutes", strtotime("$day $hour")));
	    
	    try {
			$scheduleQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE `employee`=:netID AND `deleted` = 0 AND :time BETWEEN CONCAT(`startDate`,' ',`startTime`) AND CONCAT(`endDate`,' ',`endTime`)");
			$scheduleQuery->execute(array(':netID' => $netID, ':time' => $day." ".$hour));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$schedule = $scheduleQuery->fetch(PDO::FETCH_ASSOC);
	
		return ($hour == "0" || strtotime($schedule['endTime']) <= strtotime($nextHour));
		//See explanation for this comparison in the function startingShift
    }
    
   	// Returns the length in hours of the shift that starts at $hour on $date
   	// NOTE: The hour is in 0.0 - 23.5 format, no "12:30p" values will work
    function shiftLength($netId, $day) {
    	global $hourSize,$db;
    	
    	try {
    		$lengthQuery = $db->prepare("SELECT hourTotal FROM `scheduleWeekly` WHERE employee=:netID AND startDate=:day AND startTime <= CURTIME() AND endTime >= CURTIME()");
    		$lengthQuery->execute(array(':netID' => $netId, ':day' => $day));
    	} catch(PDOException $e) {
    		exit("error in query");
    	}
        $result = $lengthQuery->fetch();
        return $result->hourTotal;
    }
    
    function shiftStart($netId, $day) {
    	global $hourSize, $db;
    	try {
    		$shiftQuery = $db->prepare("SELECT startTime FROM `scheduleWeekly` WHERE employee=:netID AND startDate=:start AND startTime <= CURTIME() AND endTime >= CURTIME()");
    		$shiftQuery->execute(array(':netID' => $netId, ':start' => $day));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $shiftQuery->fetch(PDO::FETCH_OBJ);
		if ($result) {
			$timestamp = explode(":", $result->startTime);
			return (intval($timestamp[0]) + $timestamp[1]/60);
		}
		else 
			return NULL;
    }
    
    function shiftEnd($netId, $day) {
    	global $hourSize, $db;
    	try {
    		$shiftQuery = $db->prepare("SELECT endTime FROM `scheduleWeekly` WHERE employee=:netID AND startDate=:start AND startTime <= CURTIME() AND endTime >= CURTIME()");
    		$shiftQuery->execute(array(':netID' => $netId, ':start' => $day));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $shiftQuery->fetch(PDO::FETCH_OBJ);
		if ($result) { 
			$timestamp = explode(":", $result->endTime);
			return (intval($timestamp[0]) + $timestamp[1]/60);
		} else
			return NULL;
    }

	function getWhosHereOnHour($hour, $date) {
		global $area, $db;
		$dayOfWeek = strtolower(date("D", strtotime($date)));
		$semester = getSemester($date);
		$hour = date('H:i:s', $hour);

		if (isSchedulePosted($date) || can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85"))/*schedule resource*/ {
			try {
				$employeeQuery = $db->prepare("SELECT employee, hourType FROM `scheduleWeekly` WHERE `area`=:area AND `deleted`=0 AND :start >= CONCAT(`startDate`,' ',`startTime`) AND :end < CONCAT(`endDate`,' ',`endTime`);");
				$employeeQuery->execute(array(':area' => $area, ':start' => $date." ".$hour, ':end' => $date." ".$hour));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$return = array();
			while($curEmployee = $employeeQuery->fetch(PDO::FETCH_ASSOC)){
				$return[]=$curEmployee;
			}
			return $return;
		
		} else {
			//If the schedule today is not posted, return an empty array
			$return = array();
			return ($return);
		}		
	}
	
	function isDefaultView($value) {
		global $area, $db;
		
		try {
			$defaultQuery = $db->prepare("SELECT `defaultView` FROM `scheduleHourTypes` WHERE `ID`=:ID");
			$defaultQuery->execute(array(':ID' => $value));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $defaultQuery->fetch();
		
		return $result->defaultView;
	}
	
	function getCurrentHour() {
		global $hourSize, $db;
	    $currentHour = date("G");
		$currentMinute = date("i");
		$currentTime = $currentHour;
		if ($currentMinute / 60 >= $hourSize) $currentTime += $hourSize;
		
		return $currentTime;
	}
	
	function createWeekPostEntries($date) {
		$semester = getSemester($date);
		if ($semester == "") {
			createPostEntry(getSaturday($date));
		}
		else {
			$startDate = getSaturday(getSemesterStartDate($semester));
			$endDate = getSaturday(getSemesterEndDate($semester));
			$firstWeek = date("W", strtotime($startDate));
			$lastWeek = date("W", strtotime($endDate));
			if ($firstWeek > $lastWeek) {
				for ($h = 0; $h <= 53 - $firstWeek; $h++) {
					$currentWeek = date("Y-m-d", strtotime($startDate." + ".$h." weeks"));
					createPostEntry($currentWeek);
				}
				$firstWeek = 0;
			}
			for ($i = 0; $i <= $lastWeek - $firstWeek; $i++) {
				$currentWeek = date("Y-m-d", strtotime($startDate." + ".$i." weeks"));
				createPostEntry($currentWeek);
			}
		}
	}
	function createPostEntry($week) {
		global $area, $db;
		try {
			$insertQuery = $db->prepare("INSERT INTO `schedulePosting` (`weekStart`,`area`,`post`,`guid`) VALUES (:week,:area,:schedule,:guid) ON DUPLICATE KEY UPDATE `ID`=`ID`");
			$insertQuery->execute(array(':week' => $week, ':area' => $area, ':schedule' => getPostSchedulesByDefault(), ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	function getPostSchedulesByDefault() {
		global $area, $db;
		try {
			$scheduleQuery = $db->prepare("SELECT postSchedulesByDefault FROM employeeAreas WHERE ID=:area");
			$scheduleQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $scheduleQuery->fetch();
		return $result->postSchedulesByDefault;
	}
	function isSchedulePosted($date) {
		global $area, $db;
		
		$date = getSaturday($date);
		try {
			$postQuery = $db->prepare("SELECT `post` FROM `schedulePosting` WHERE `weekStart`=:start AND `area`=:area");
			$postQuery->execute(array(':start' => $date, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}

		if (getPostSchedulesByDefault() == '1') {
		    return '1';
		} else {
			if($result = $postQuery->fetch()) {
				return $result->post;
			} else {
				return '0';
			}
		}		
	}
	
	// this function is being used while the fullWeekSchedule is being built. -Daniel
	function getShowUnposted() {
		global $showUnposted;
		return $showUnposted;
	}
	
?>
