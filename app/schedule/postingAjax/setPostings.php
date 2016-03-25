<?php require("../../includes/includeMeBlank.php");

	$year = $_GET['year'];
	$posts = explode(",", $_GET['posts']);
	
	try {
		$scheduleQuery = $db->prepare("SELECT `weekStart` FROM `schedulePosting` WHERE `area` = :area AND `weekStart` >= :year AND `weekStart` <= :year1 ORDER BY `weekStart` ASC");
		$scheduleQuery->execute(array(':area' => $area, ':year' => $year.'-01-01', ':year1' => $year.'-12-31'));
	} catch(PDOException $e) {
		exit("error in query");
	}
	// loop through and set any that in the list, clear any that aren't
	while($row = $scheduleQuery->fetch(PDO::FETCH_ASSOC)) {
		$value = 1;
		if (!in_array($row['weekStart'], $posts)) $value = 0;

		try {
			$insertQuery = $db->prepare("INSERT INTO `schedulePosting` (`weekStart`,`area`,`post`,`guid`) VALUES (:start,:area,:value,:guid) ON DUPLICATE KEY UPDATE `post`=:value1");
			$insertQuery->execute(array(':start' => $row['weekStart'], ':area' => $area, ':value' => $value, ':value1' => $value, ':guid' => newGuid()));
			$updateQuery = $db->prepare("UPDATE `scheduleWeekly` SET `posted` = :value WHERE `area` = :area AND `startDate` >= :start AND `startDate` < DATE_ADD(:start1, INTERVAL 1 WEEK)");
			$updateQuery->execute(array(':value' => $value, ':area' => $area, ':start' => $row['weekStart'],  ':start1' => $row['weekStart']));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	echo "Success!";
?>
