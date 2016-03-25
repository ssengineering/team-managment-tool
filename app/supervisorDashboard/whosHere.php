<?php
  
  require('../includes/includeMeBlank.php');

  function array_sort($array, $on, $order=SORT_ASC)
  {
      $new_array = array();
      $sortable_array = array();

      if (count($array) > 0) {
    foreach ($array as $k => $v) {
        if (is_array($v)) {
      foreach ($v as $k2 => $v2) {
          if ($k2 == $on) {
              $sortable_array[$k] = $v2;
          }
      }
        } else {
      $sortable_array[$k] = $v;
        }
    }

    switch ($order) {
        case SORT_ASC:
      asort($sortable_array);
        break;
        case SORT_DESC:
      arsort($sortable_array);
        break;
    }

    foreach ($sortable_array as $k => $v) {
        $new_array[$k] = $array[$k];
    }
      }

      return $new_array;
  }
  
  if (isset($_GET['hourMod']))
  {
		  $hourMod = $_GET['hourMod'];
  }
  else 
  {
    $hourMod = 0;
  }
  $hSize = getHourSize();
  $hourModPlus = $hSize*($hourMod + 1);
  $hourModMinus = $hSize*($hourMod - 1);
  $hourModPlus *= 60; //Converts it to minutes so that strtotime can read it.
  $hourModMinus *= 60;
  $hourMod *= 60;
  $hourMod *= $hSize; //Accounts for the shift size.
  $now = strtotime("$hourMod minutes");  //It is the previous hour, the next hour and the current time depending on what was passed to the page. 
  //The strtotime function takes string arguments (see documentation).
  $date = date('Y-m-d', $now);
  $next = strtotime("$hourModPlus minutes");
  //Takes care of rounding to the nearest increment (can be half hour, hour, etc.). Corresponds to shift size of the area.
  $next -= $next % (3600 * $hSize);
  $prev = strtotime("$hourModMinus minutes");
  $prev -= $prev % (3600 * $hSize);
  $next = date('g:i A', $next);//The meaning of these letters is defined in the "date" PHP function dodumentation.
  $prev = date('g:i A', $prev);
  $whoIs = getWhosHereOnHour($now, $date); // returns an array of people who are working now.
  
  $whoIs = array_sort($whoIs, 'hourType');
  
  //format phone number
  function formmattedPhone($phoneNum)
  {
    if ($phoneNum=='')
    {
      return "(000)-000-0000";
    }
    else
    {
      $formatPhone = str_split($phoneNum);
      $formatFirstThreeNum = str_split($phoneNum,3);
      $formatLastFourNum = str_split($phoneNum,6);
      
      $formattedPhone = '('.$formatFirstThreeNum[0].') '.$formatPhone[3].$formatPhone[4].$formatPhone[5].'-'.$formatLastFourNum[1];
      return $formattedPhone;
    }
  }
  echo "<div class='whos'>";
  echo "<table style='border-spacing: 5px; border-collapse: separate; float: left; margin-left: auto; margin-right: auto; padding: 0; table-layout: fixed; display: block; width: 100%;'>";
  $workerCount = 0;
  foreach ($whoIs as $worker)
  {
  if (isDefaultView($worker['hourType']))
  {
    if ($workerCount == 6)
    {
      echo "<tr>";
    }
    //Get employee information
	try {
		$employeeQuery = $db->prepare("SELECT * FROM `employee` WHERE `netID`=:employee");
		$employeeQuery->execute(array(':employee' => $worker['employee']));
	} catch(PDOException $e) {
		exit("error in query");
	}
    $employeeInfoResults = $employeeQuery->fetch(PDO::FETCH_ASSOC);
    $employeePhone = $employeeInfoResults['phone'];
    $employeeLevel = $employeeInfoResults['certificationLevel'];
    $formattedEmpPhone = $employeePhone; //formmattedPhone($employeePhone);
    // Get employee's shift
		$check_datetime = time()+$hourMod*60*60;
		$check_employee = $worker['employee'];
		$check_weekday = date('N',$check_datetime);
		$check_date = date('Y-m-d', $check_datetime);
		$check_time = date('H:i', $check_datetime);
		$check_semester = getSemester($check_date);
		try {
			$weeklyQuery = $db->prepare("SELECT scheduleWeekly.endTime FROM scheduleWeekly WHERE employee=:employee AND scheduleWeekly.startTime <= :time AND (scheduleWeekly.endTime >= :time1 OR scheduleWeekly.endTime='00:00:00') AND scheduleWeekly.startDate = :start AND scheduleWeekly.deleted=0 LIMIT 1");
			$weeklyQuery->execute(array(':employee' => $check_employee, ':time' => $check_time, ':time1' => $check_time, ':start' => $check_date));
			$defaultQuery = $db->prepare("SELECT scheduleDefault.endTime FROM scheduleDefault LEFT JOIN scheduleSemesters ON scheduleDefault.period=scheduleSemesters.id WHERE employee=:employee AND scheduleSemesters.semester=:semester AND scheduleDefault.startTime <= :time AND (scheduleDefault.endTime >= :time1 OR scheduleDefault.endTime='00:00:00') AND scheduleDefault.startDate = :day AND scheduleDefault.deleted=0 LIMIT 1");
			$defaultQuery->execute(array(':employee' => $check_employee, ':semester' => $check_semester, ':time' => $check_time, ':time1' => $check_time, ':day' => $check_weekday));
		} catch(PDOException $e) {
			exit("error in query<br><br>".$e->getMessage());
		}

		$employeeShift = array();
   		// First check for shifts added to the weekly schedule
		// If no results are found, use the default schedule
		if(!($employeeShift = $weeklyQuery->fetch(PDO::FETCH_ASSOC))) {
			$employeeShift = $defaultQuery->fetch(PDO::FETCH_ASSOC);
		}
		$employeeShift = $employeeShift['endTime'];
		$employeeShift = date(' g:iA',  strtotime($employeeShift));
    
    echo "<td class='who' style='border-spacing: 5px; font-size: 100%;'>";
    echo "<div style='float: left; font-weight: bold; text-align: center; width: 100%; font-size: 80%;'>";
    echo getHourNameByValue($worker['hourType']);
    echo "</div><div class='clearMe'></div>";
    $toolTipLeft = '';
    if ($workerCount == 5)
    {
      $toolTipLeft = ' left: -8em;';
    }
    if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource*/{
      echo "<div style='float: left; width: 100%; text-align: center;'><a class='tooltip'><span style='".$toolTipLeft."'>".$employeeLevel." <br />".$formattedEmpPhone." <br />Shift End:".$employeeShift."</span><img onclick='giveOptions(this,\"".$worker['employee']."\")' width='100' height='100' src='".getenv("BYU_PI_PHOTO")."?n=".$worker['employee']."' /></a></div><div class='clearMe'></div>";
    } else {
      echo "<div style='float: left; width: 100%; text-align: center;'><a class='tooltip'><span style='".$toolTipLeft."'>".$employeeLevel." <br />".$formattedEmpPhone." <br />Shift End:".$employeeShift."</span><img width='100' height='100' src='".getenv("BYU_PI_PHOTO")."?n=".$worker['employee']."' /></a></div><div class='clearMe'></div>";
    }
    if (startingShift($worker['employee'], $date, $now))
    {
      echo "<div style='float: left; width: 100%; text-align: center; color: green; font-weight: bold; font-size: 80%;'>".nameByNetId($worker['employee']);
    }
    else if (endingShift($worker['employee'], $date, $now))
    {
      echo "<div style='float: left; width: 100%; text-align: center; color: red; font-weight: bold; font-size: 80%;'>".nameByNetId($worker['employee']);
    }
    else
    {
      echo "<div style='float: left; width: 100%; text-align: center; font-weight: bold; font-size: 80%;'>".nameByNetId($worker['employee']);
    }
    if (can("access", "2b7d6a3b-c9e9-4283-8275-3c632dfc20d9"))/*supervisorDashboard resource*/{
      echo "<a style='color: #336699' href='javascript:newwindow(\"https://".$_SERVER['SERVER_NAME']."/performance/tardy.php?employee=".$worker['employee']."&startTime=".shiftStart($worker['employee'], $date)."\", 945, 425)'>(T)</a>";
      echo "<a style='color: #336699' href='javascript:newwindow(\"https://".$_SERVER['SERVER_NAME']."/performance/absence.php?employee=".$worker['employee']."&startTime=".shiftStart($worker['employee'], $date)."\", 945, 425)'>(A)</a>";
    }    

    echo"</div></td>";
    
    $workerCount++;

    if ($workerCount == 6)
    {
      echo "</tr>";
      $workerCount = 0;
    }
  }
  }
  echo "</table>";
  echo "</div>"; 
  $links = <<<STRING
<div class="clearMe"></div>
<div class="whosHereLinks"><button style="width: 7em;" onclick="hour--; whosHere(hour, 'whoIs'); return false;" class="accHeader whoWas">$prev</button></div>
<div class="whosHereLinks"><a href="" onclick="hour = 0; whosHere(hour, 'whoIs'); return false;" class="accHeader whoIs">Current Hour</a></div>
<div class="whosHereLinks"><button style="width: 7em;" onclick="hour++; whosHere(hour, 'whoIs'); return false;" class="accHeader whoWill">$next</button></div>
STRING;
  echo $links;

?>
