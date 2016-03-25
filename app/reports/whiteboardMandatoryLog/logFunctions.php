<?php 
//Mandatory log functions

function getMandatoryMsgs($area){
	global $db;
	echo "<option value=''>Select Message</option>";
	try {
		$whiteboardQuery = $db->prepare("SELECT * FROM `whiteboard` JOIN `whiteboardAreas` ON `whiteboard`.`messageId` = `whiteboardAreas`.`whiteboardId` WHERE `mandatory` = 1 AND `areaId` = :area ORDER BY `expireDate` DESC");
		$whiteboardQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$today = date("Y-m-d");
	while($row = $whiteboardQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value=".$row['messageId'].">".($row['expireDate'] >= $today ? '':'*').$row['title']."</option>";
	}			
}

function getPeoples($msgId,$area){
	global $db;
	if($msgId == NULL){
		return;
	}
	try {
		$messageQuery = $db->prepare("SELECT * FROM `whiteboard` WHERE `messageId` = :id");
		$messageQuery->execute(array(':id' => $msgId));
		$readQuery = $db->prepare("SELECT `employee`.*, IF(`msgId`, 1, 0) AS hasRead FROM employee LEFT JOIN (SELECT * FROM whiteboardMandatoryLog WHERE `msgID`=:id) AS ml ON `employee`.`netID` = ml.`netID` WHERE area=:area AND `active` = 1 ORDER BY firstName ASC, lastName ASC");
		$readQuery->execute(array(':id' => $msgId, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$title = $messageQuery->fetch(PDO::FETCH_ASSOC);
	echo "<h1>".$title['title']."</h1><h2>Post Date: ".$title['postDate']."<br/>Current Date: ".date('Y-m-d')."</h2>";
	echo "<div align='center'>";
	echo "<table class='imagetable'>";
	echo "<tr><th>Employee</th><th>Has Read?</th></tr>";
	while($people = $readQuery->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr><td>".$people['firstName']." ".$people['lastName']."</td><td style='text-align: center;'>".($people['hasRead'] == '1' ? 'YES' : 'NO')."</td></tr>";
	}
	echo "</table>";
	echo "</div>";
}
?>
