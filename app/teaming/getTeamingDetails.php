<?php require('../includes/includeMeBlank.php');

	$employee = $_GET['employee'];
	$leader = $_GET['leader'];
	$startDate = $_GET['startDate'];
	$endDate = $_GET['endDate'];
	$teamed = $_GET['teamed'];
	$timely = $_GET['timely'];
	$params = array(':area' => $area);
	$fields = "area = :area";
	
	if ($employee != "") {
		$fields.=" AND netID = :netId";
		$params[':netId'] = $employee;
	}
	if ($leader != "") {
		$fields.=" AND supervisorID = :leader";
		$params[':leader'] = $leader;
	}
	if ($startDate != "") {
		$fields.=" AND startDate >= :start";
		$params[':start'] = $startDate;
	}
	if ($endDate != "") {
		$fields.=" AND endDate <= :end";
		$params[':end'] = $endDate;
	}
	if ($teamed != "") {
		$fields.=" AND teamed = :teamed";
		$params[':teamed'] = $teamed;
	}
	if ($timely != "") {
		$fields.=" AND timely = :timely";
		$params[':timely'] = $timely;
	}
	$teamingQueryString = "SELECT netID, supervisorID, `startDate`, `endDate`, teamed, timely FROM teaming WHERE ".$fields;
	try {
		$teamingQuery = $db->prepare($teamingQueryString);
		$teamingQuery->execute($params);
	} catch(PDOException $e) {
		exit("error in query");
	}
	echo "<table><tr>
			<th>Employee</th>
			<th>Leader</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Trained</th>
			<th>Timely</th>
		</tr>";
	
	while($current = $teamingQuery->fetch(PDO::FETCH_ASSOC)) {
		$empTeamed = '';
		$wasTimely = '';
		if ($current['teamed']) $empTeamed = 'Yes'; else $empTeamed = 'No';
		if ($current['timely']) $wasTimely = 'Yes'; else $wasTimely = 'No';
		echo "<tr>";
		echo "<td>".getEmployeeNameByNetId($current['netID'])."</td>";
		echo "<td>".getEmployeeNameByNetId($current['supervisorID'])."</td>";
		echo "<td>".$current['startDate']."</td>";
		echo "<td>".$current['endDate']."</td>";
		echo "<td>".$empTeamed."</td>";
		echo "<td>".$wasTimely."</td>";
		echo "</tr>";
	}
	
	echo "</table>";
?>
