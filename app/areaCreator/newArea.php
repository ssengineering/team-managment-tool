<?php
require_once("../includes/includeMeBlank.php");

if (isset($_POST['area']))
{
	try {
		$query = $db->prepare("INSERT INTO `employeeAreas`(`area`, `longName`, `homePage`, `hourSize`, `startDay`, `startTime`, `endDay`, `endTime`, 
							  `postSchedulesByDefault`, `canEmployeesEditWeeklySchedule`, `guid`) VALUES 
							  (:area,:long,:home,:hour,:startDay,:startTime,:endDay,:endTime,:post,:edit,:guid)");
		$success = $query->execute(array(':area'  => $_POST['area'],  ':long'    => $_POST['longName'],':home'     => $_POST['homePage'], 
							             ':hour'  => $_POST['hourSize'],          ':startDay'=> $_POST['startDay'],':startTime'=> $_POST['startTime'], 
							             ':endDay'=> $_POST['endDay'],':endTime' => $_POST['endTime'], ':post'     => $_POST['postSchedulesByDefault'],
							             ':edit'  => $_POST['canEmployeesEditWeeklySchedule'], ':guid' => newGuid()));
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success)
	{
		$areaId = $db->lastInsertId();
		// Give the current employee rights to the new area
		try {
			$insertQuery = $db->prepare("INSERT INTO `employeeAreaPermissions` (`netID`, `area`, `guid`) VALUES (:netID, :area, :guid)");
			$insertQuery->execute(array(':netID' => $netID, ':area' => $areaId, ':guid' => newGuid()));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo json_encode(array('status'=>"OK", 'areaId'=>$areaId));
	}
	else
	{
		echo json_encode(array('status'=>"FAIL", 'error'=>"error in query"));
	}
}

?>
