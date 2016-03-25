<?php //insertReason.php used to insert a new reason into the current area.
require('../../includes/includeMeBlank.php');
if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af")/*wages resource*/){

$employeeNetId = $_GET['employee']; //employee's net Id
$date = $_GET['date']; //The date of the raise
$raise = $_GET['raise']; //The raise amount
$comments = $_GET['reason']; //The reason for the raise

if($raise == ''){ //TODO Add check for non numeric values
	echo "Invalid Raise amount";
	return;
}
	//This is the logic for entering the raise into the database. ***************
	
	//A "Manual Edit" raise is just for updating our records, it never enters the pending state.
	if($comments == "Manual Edit"){ 
		try {
			$wageQuery = $db->prepare("SELECT wage FROM employeeWages WHERE netID=:netId");
			$wageQuery->execute(array(':netId' => $employeeNetId));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $wageQuery->fetch(PDO::FETCH_ASSOC);
		$newWage = $result['wage'] + $raise;
		
		if ($date==""){ //This accounts for the lack of a date given
			$date = date("Y-m-d H:i:s",strtotime("now"));
			try {
				$insertQuery = $db->prepare("INSERT INTO employeeRaiseLog (netID,raise,newWage,submitter,comments,date,isSubmitted,guid)
					VALUES (:employee,:raise,:wage,:netId,:comments,:day,'1',:guid)");
				$insertQuery->execute(array(
					':employee' => $employeeNetId,
					':raise'    => $raise,
					':wage'     => $newWage,
					':netId'    => $netID,
					':comments' => $comments,
					':day'      => $date,
					':guid'     => newGuid()));
				$updateQuery = $db->prepare("UPDATE employeeWages SET wage = :wage WHERE netID = :netId");
				$updateQuery->execute(array(':wage' => $newWage, ':netId' => $employeeNetId));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}else{ //this accounts for the normal entry method
			$date.= " ".date('H:i:s',strtotime("now"));
			try {
				$insertQuery = $db->prepare("INSERT INTO employeeRaiseLog (netID,raise,newWage,submitter,comments,date,isSubmitted,guid)
					VALUES (:employee,:raise,:wage,:netId,:comments,:day,'1',:guid)");
				$insertQuery->execute(array(
					':employee' => $employeeNetId,
					':raise'    => $raise,
					':wage'     => $newWage,
					':netId'    => $netID,
					':comments' => $comments,
					':day'      => $date,
					':guid'     => newGuid()));
				$updateQuery = $db->prepare("UPDATE employeeWages SET wage = :wage WHERE netID = :netId");
				$updateQuery->execute(array(':wage' => $newWage, ':netId' => $employeeNetId));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	} else { //This is for any comments other than a manual edit
		try {
			$wageQuery = $db->prepare("SELECT wage FROM employeeWages WHERE netID=:netId");
			$wageQuery->execute(array(':netId' => $employeeNetId));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$result = $wageQuery->fetch(PDO::FETCH_ASSOC);
		$newWage = $result['wage'] + $raise;
		if ($date==""){ //This accounts for no date being given, so we have to manually time stamp it
			$date = date("Y-m-d H:i:s",strtotime("now"));
			try {
				$insertQuery = $db->prepare("INSERT INTO employeeRaiseLog (netID,raise,newWage,submitter,comments,date,guid)
					VALUES (:employee,:raise,:wage,:netId,:comments,:day,:guid)");
				$insertQuery->execute(array(
					':employee' => $employeeNetId,
					':raise'    => $raise,
					':wage'     => $newWage,
					':netId'    => $netID,
					':comments' => $comments,
					':day'      => $date,
					':guid'     => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}else{ //This is the normal entry method if all information is normal. the "default case"
			$date.= " ".date('H:i:s',strtotime("now"));
			try {
				$insertQuery = $db->prepare("INSERT INTO employeeRaiseLog (netID,raise,newWage,submitter,comments,date,guid)
					VALUES (:employee,:raise,:wage,:netId,:comments,:day,:guid)");
				$insertQuery->execute(array(
					':employee' => $employeeNetId,
					':raise'    => $raise,
					':wage'     => $newWage,
					':netId'    => $netID,
					':comments' => $comments,
					':day'      => $date,
					':guid'     => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
		echo "Raise is now pending"; //This gets echo'd back so the page alerts letting the user know it was successful
	}
}
?>
