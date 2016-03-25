<?php
require ('../../includes/includeMeBlank.php');
if (isset($_POST['overwriteWeekly']))
{
	$overwriteWeekly=(int)$_POST['overwriteWeekly'];

	try {
		$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE area = :area ORDER BY ID");
		$semestersQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

	// Returns the first instance (the equivalent of a PDO::fetch(PDO::FETCH_ASSOC) from the scheduleWeekly table) of a default shift
	// after the specified DateTime object ($fromDate)
	function firstInstanceOf($defaultShift, $fromDate)
	{
		// We add 1 here because we are converting from PHP's day of the week numbering to MySQL's DayOfWeek formatting
		$fromDayOfWeek=1 + ((int)($fromDate->format('w')));

		$difference=$defaultShift['startDate'] - $fromDayOfWeek;
		if ($difference < 0)
		{
			// Before setting the correct difference for the first full instance we check to see if there is a cross period instance
			// we should be worried about
			$endDifference=$defaultShift['endDate'] - $fromDayOfWeek;
			if (!$endDifference < 0)
			{
				$partialInstance=$defaultShift;
				$partialInstance['startDate']=$fromDate->format('Y-m-d');
				$partialInstance['endDate']=date('Y-m-d', strtotime('+'.$endDifference.' days', $fromDate->getTimestamp()));
				$partialInstance['startTime']=$fromDate->format('H:i:s');
				// Not sure if we should really do this or not but I am going to associate the partial instance with the default shift
				$partialInstance['defaultID']=$partialInstance['ID'];
				unset($partialInstance['ID']);
			}
			$difference+=7;
		}

		// Make a copy of the defaultShift so we can return an instance of it
		$instance=$defaultShift;
		// Make a copy of the fromDate so that we can add intervals to it to act as the new Start and end Dates
		$newStart=clone $fromDate;
		$newStart->add(new DateInterval('P'.$difference.'D'));
		$instance['startDate']=$newStart->format('Y-m-d');
		$startEndDiff=$defaultShift['endDate'] - $defaultShift['startDate'];
		if ($startEndDiff < 0)
		{
			$startEndDiff+=7;
		}
		$newEnd=$newStart;
		$newEnd->add(new DateInterval('P'.$startEndDiff.'D'));
		$instance['endDate']=$newEnd->format('Y-m-d');

		$instance['defaultID']=$instance['ID'];
		unset($instance['ID']);
		$returnArray[]=$instance;
		if (isset($partialInstance))
		{
			$returnArray[]=$partialInstance;
		}
		return $returnArray;
	}

	// Gets what would be the next instance of a default shift if the period were to last forever
	function nextInstance($currentInstance)
	{
		$newStart=new DateTime($currentInstance['startDate']);
		$newEnd=new DateTime($currentInstance['endDate']);
		$newStart->add(new DateInterval('P1W'));
		$newEnd->add(new DateInterval('P1W'));
		$nextInstance=$currentInstance;
		$nextInstance['startDate']=$newStart->format('Y-m-d');
		$nextInstance['endDate']=$newEnd->format('Y-m-d');
		return $nextInstance;
	}

	// Check for conflicts and if none insert instance into weekly schedule (we also pass in the $result variable by
	// reference in order to make sure all inserts succeed or roll back the transaction)
	function insertInstance($instance)
	{
		global $db;
		$instanceStart=$instance['startDate'].' '.$instance['startTime'];
		$instanceEnd=$instance['endDate'].' '.$instance['endTime'];
		$employee=$instance['employee'];
		// Ensure we have no conflicting shifts
		try {
			$conflictQuery = $db->prepare("SELECT * FROM `scheduleWeekly` WHERE `employee` = :employee AND
				(
					( CONCAT(`startDate`, ' ', `startTime`) <=  :start0 AND CONCAT(`endDate`, ' ', `endTime`) > :start1 )
					OR ( CONCAT(`startDate`, ' ', `startTime`) < :end0 AND CONCAT(`endDate`, ' ', `endTime`) >= :end1 )
					OR ( CONCAT(`startDate`, ' ', `startTime`) > :start2 AND CONCAT(`endDate`, ' ', `endTime`) < :end2 )
				) AND `deleted`=0");
			$conflictQuery->execute(array(
				':employee' => $employee,
				':start0'   => $instanceStart,
				':start1'   => $instanceStart,
				':end0'     => $instanceEnd,
				':end1'     => $instanceEnd,
				':start2'   => $instanceStart,
				':end2'     => $instanceEnd));
		} catch(PDOException $e) {
			$db->rollBack();
			$returnArray['status']='failure';
			$returnArray['message']='There was a problem updating one of the periods.<br \>Please try refreshing the page and trying again.';
			echo json_encode($returnArray);
			exit("error in query");
		}

		// If there are no conflicts insert new instance of the default shift otherwise do nothing
		if (!($row = $conflictQuery->fetch(PDO::FETCH_ASSOC)))
		{
			try {
				$insertQuery = $db->prepare("INSERT INTO `scheduleWeekly` (employee,hourType,startTime,startDate,endTime,endDate,hourTotal,area,defaultID,guid)
					VALUES(:employee,:hourType,:startTime,:startDate,:endTime,:endDate,:total,:area,:default,:guid)");
				$insertQuery->execute(array(
					':employee'  => $instance['employee'], 
					':hourType'  => $instance['hourType'], 
					':startTime' => $instance['startTime'], 
					':startDate' => $instance['startDate'], 
					':endTime'   => $instance['endTime'], 
					':endDate'   => $instance['endDate'], 
					':total'     => $instance['hourTotal'], 
					':area'      => $instance['area'], 
					':default'   => $instance['defaultID'],
					':guid'      => newGuid()));
			} catch(PDOException $e) {
				$db->rollBack();
				$returnArray['status']='failure';
				$returnArray['message']='There was a problem updating one of the periods.<br \>Please try refreshing the page and trying again.';
				echo json_encode($returnArray);
				exit("error in query");
			}
		}
	}

	$returnArray=array();

	while ($schedulingPeriod=$semestersQuery->fetch(PDO::FETCH_ASSOC))
	{
		if(!isset($_POST[$schedulingPeriod['ID'].'changed']))
			continue;
		if ((int)$_POST[$schedulingPeriod['ID'].'changed'])
		{
			// Get difference between old start and end dates for the scheduling period and compare them against the new ones to
			// determine if we need to insert or remove any related default shifts
			$newStartString=$_POST[$schedulingPeriod['ID'].'start'];
			$newEndString=$_POST[$schedulingPeriod['ID'].'end'];
			$oldStartString=$schedulingPeriod['startDate'];
			$oldEndString=$schedulingPeriod['endDate'];

			$newStart=new DateTime($newStartString);
			$newEnd=new DateTime($newEndString);
			// Add a day to the end date because the period should include that as the last day of the period
			$newEnd->add(new DateInterval('P1D'));
			$oldStart=new DateTime($oldStartString);
			$oldEnd=new DateTime($oldEndString);
			// Add a day to the old end date because it also ought to include the entire day as schedulable
			$oldEnd->add(new DateInterval('P1D'));

			// Array for start and end of periods that should be either removed or added
			$periodsToBeAdded=array();
			$periodsToBeRemoved=array();

			// If the range of the period now ends before the period used to even start
			// or if it starts after the old period used to end
			if ($newEnd < $oldStart || $newStart > $oldEnd)
			{
				$periodsToBeRemoved[]=$oldStart;
				$periodsToBeRemoved[]=$oldEnd;

				$periodsToBeAdded[]=$newStart;
				$periodsToBeAdded[]=$newEnd;
			}
			else
			{
				// If the new Start is earlier than the old start add a period of that many days
				if ($newStart < $oldStart)
				{
					$periodsToBeAdded[]=clone $newStart;
					$periodsToBeAdded[]=clone $newEnd;
				}
				// If the new start is after the old start remove a period of that many days
				else if ($newStart > $oldStart)
				{
					$periodsToBeRemoved[]=clone $oldStart;
					$periodsToBeRemoved[]=clone $newStart;
				}

				// Same as above except for the end of the period
				if ($newEnd < $oldEnd)
				{
					$periodsToBeRemoved[]=clone $newEnd;
					$periodsToBeRemoved[]=clone $oldEnd;
				}
				else if ($newEnd > $oldEnd)
				{
					$periodsToBeAdded[]=clone $oldEnd;
					$periodsToBeAdded[]=clone $newEnd;
				}
			}

			// Start transaction for updating the current period (e.g. semester)
			$db->beginTransaction();

			// Make sure we remove weekly shifts if we were told to and that we don't if we weren't
			$deleteWeeklyString=" AND `defaultID` IS NOT NULL";
			if ($overwriteWeekly)
			{
				$deleteWeeklyString="";
			}
			// For each section of the period that no longer belongs to the period delete any default (and maybe weekly) shifts that
			// are associated with the period
			while ( ($oldPortionStart=array_shift($periodsToBeRemoved)) && ($oldPortionEnd=array_shift($periodsToBeRemoved)) )
			{
				// Update Weekly Schedule to mark as deleted all instances of the default schedule that belong to the old portion of the
				// period (and maybe the weekly schedules too)
				$startString=$oldPortionStart->format('Y-m-d');
				$endString=$oldPortionEnd->format('Y-m-d');
				try {
					$deleteQuery = $db->prepare("UPDATE `scheduleWeekly` SET `deleted` = 1 WHERE `startDate`>=:start AND `endDate`<:end ".$deleteWeeklyString." AND `area`=:area");
					$deleteQuery->execute(array(':start' => $startString, ':end' => $endString, ':area' => $area));
				} catch(PDOException $e) {
					$db->rollBack();
					$returnArray['failed'][]=$schedulingPeriod['semester'];
					exit("error in query");
				}
			}

			// Grab all default shifts that we will need to make instances of
			try {
				$defaultQuery = $db->prepare("SELECT * FROM `scheduleDefault` WHERE `period` = :id AND `deleted`=0");
				$defaultQuery->execute(array(':id' => $schedulingPeriod['ID']));
			} catch(PDOException $e) {
				$db->rollBack();
				$returnArray['failed'][]=$schedulingPeriod['semester'];
				exit("error in query");
			}
			$insertResults=true;
			// Same as above except this time we are adding instances of the default schedule to the new portions of the period
			// A note: apparently shift_array() if called twice in a while loop without putting () around each call will set the
			// first variable to true instead of whatever value the array would have at that point
			// I think it has something to do with the first call not completing properly if the parenthesis are not used to ensure
			// the statement is executed completely before the second statement
			while ( ($newPortionStart=array_shift($periodsToBeAdded)) && ($newPortionEnd=array_shift($periodsToBeAdded)) )
			{
				while ($defaultShift=$defaultQuery->fetch(PDO::FETCH_ASSOC))
				{
					// Insert instances of the default schedule into the new portions of the period
					$firstInstances=firstInstanceOf($defaultShift, $newPortionStart);
					$instance=$firstInstances[0];
					// Check to see if there is also a partial shift that we should worry about
					if (isset($firstInstances[1]))
					{
						insertInstance($firstInstances[1], $insertResults);
					}

					// Check to make sure that the instance we will be inserting does not extend past the end date of the period to be added
					while (strtotime($instance['endDate']) < $newPortionEnd->getTimestamp())
					{
						insertInstance($instance, $insertResults);
						// Iterate to next instance
						$instance=nextInstance($instance);
					}

					// Check to see if the last instance is a cross period instance and should be inserted as a partial shift
					if (strtotime($instance['startDate']) < $newPortionEnd->getTimestamp())
					{
						$instance['endDate']=$newPortionEnd->format('Y-m-d');
						$instance['endTime']=$newPortionEnd->format('H:i:s');
						insertInstance($instance, $insertResults);
					}
				}

				// Return the cursor to the beginning of the result set for us to run through it again in case there is another new
				// portion of the period
				try {
					$defaultQuery->execute(array(':id' => $schedulingPeriod['ID']));
				} catch(PDOException $e) {
					$db->rollBack();
					$returnArray['failed'][]=$schedulingPeriod['semester'];
					exit("error in query");
				}
			}
			// Check if we need to update the way the period handles locking
			$locked=0;
			if (isset($_POST['locked']))
			{
				if (in_array($schedulingPeriod['ID'], $_POST['locked']))
				{
					$locked=1;
				}
			}

			// Now that we finally have finished updating the weekly schedule with the necessary changes to the added and/or removed
			// portions of the period let's update the actual period
			try {
				$updateQuery = $db->prepare("UPDATE `scheduleSemesters` SET `semester`=:id, `name`=:name, `startDate`=:startDate, `endDate`=:endDate, `locked`=:locked WHERE `ID`=:id1");
				$updateQuery->execute(array(
					':id'        => $_POST[$schedulingPeriod['ID']],
					':name'      => $_POST[$schedulingPeriod['ID'].'name'],
					':startDate' => $_POST[$schedulingPeriod['ID'].'start'],
					':endDate'   => $_POST[$schedulingPeriod['ID'].'end'],
					':locked'    => $locked,
					':id1'       => $schedulingPeriod['ID']));
			} catch(PDOException $e) {
				$db->rollBack();
				$returnArray['failed'][]=$schedulingPeriod['semester'];
				exit("error in query");
			}

			//all queries were successful, commit changes to the database
			$db->commit();
			$returnArray['succeeded'][]=$schedulingPeriod['semester'];
		}
	}

	// Check to see whether we have any failed updates and if so then return a failed status and message and then of course
	// the array of failed updates
	if (isset($returnArray['failed']))
	{
		$returnArray['status']='failure';
		$returnArray['message']='There was a problem updating one of the periods.<br \>Please try refreshing the page and trying again.';
		// We should have the failed array already added at this point so we don't need to do anything with it here
	}
	else
	{
		$returnArray['status']='success';
		$returnArray['message']='All operations completed successfully.';
	}
	echo json_encode($returnArray);
}
?>
