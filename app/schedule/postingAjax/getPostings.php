<?php require("../../includes/includeMeBlank.php"); 

	$year = $_GET['year'];
	
	
	// Create header for full page
	echo "<table class='postTable'><tr>
			<th>Semester</th>
			<th width='20%'>Start Date</th>
			<th width='20%'>End Date</th>";
	echo "</tr></table>";
	
	// Get semesters
	try {
		$semestersQuery = $db->prepare("SELECT `name`, `startDate`, `endDate` FROM `scheduleSemesters` WHERE `area` = :area AND YEAR(`startDate`) = :year ORDER BY `startDate` ASC");
		$semestersQuery->execute(array(':area' => $area, ':year' => $year));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	while($semester = $semestersQuery->fetch(PDO::FETCH_ASSOC)) {
		// Create entries if they don't exist
		createWeekPostEntries($semester['startDate']);
		
		// Create toggle boxes for each semester
		echo "<table class='postTable'><tr><td>";
		echo "<span style='cursor:pointer'>";
		echo "<div id='title' onclick='$(\"#".$semester['startDate']."\").slideToggle(\"fast\")'>";
		echo "<li class='ui-state-default ui-corner-all'><span class='ui-icon ui-icon-triangle-1-s'></span></li>&nbsp;";
		echo $semester['name'];
		echo "</div>";
		echo "</span>";		
		echo"</td>";
		
		// Append date ranges
		echo "<td width='20%'>".$semester['startDate']."</td><td width='20%'>".$semester['endDate']."</td></tr></table>";
		
		// Get all weeks that fall in the current semester range
		try {
			$scheduleQuery = $db->prepare("SELECT `weekStart`, `post` FROM `schedulePosting` WHERE `area` = :area AND `weekStart` >= :start AND `weekStart` <= :end ORDER BY `weekStart` ASC");
			$scheduleQuery->execute(array(':area' => $area, ':start' => $semester['startDate'], ':end' => $semester['endDate']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		// Create div for toggling visibility
		echo "<div id='".$semester['startDate']."' style='display:none;'>";
		
		while($posting = $scheduleQuery->fetch(PDO::FETCH_ASSOC)) {
			// Show week number
			echo date("W", strtotime($posting['weekStart']))." ";

			// Show check box
			echo "<label><input type='checkbox' id='".$posting['weekStart']."'";
			if ($posting['post']) echo " checked";
			echo ">";
			
			// Show week date
			echo " Week starting ".$posting['weekStart']."</label><br />";
		}
		echo "<br /></div>";
	}
	// Create submit button
	echo "<input type=button value='Submit' onclick='submitPosts()'><br/><br/>";
?>
