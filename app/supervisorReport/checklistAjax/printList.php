<?php //printlist.php this prints the checklist of items
require('../../includes/includeMeBlank.php');

$list = $_GET['type'];
try {
	$tasksQuery = $db->prepare("SELECT * FROM supervisorReportSDTasks WHERE checklist = :list AND area = :area ORDER BY `order` ASC");
	$tasksQuery->execute(array(':list' => $list, ':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}

if($list == 0){
	echo "<table id='openList'>
	<tr>
		<th>Opening Office Checklist<br/><input type='checkbox' id='openingList' name='openingList' /><label for='opening' >Please check if this is an opening shift</label></th><td>";
	if(can("update", "7db1df8d-0a15-46ed-9c83-701393e9596c"))/*reportInstructions resource*/{
		echo "<input type='button' value=\"Add Item\" onclick='addItem(0)' />";
	}
	echo "<input type='button' value=\"Reload\" onclick='printList(0,\"openResults\")' /></td></tr>";
} else {
	echo "<table id='closeList'>
	<tr>
		<th>Closing Office Checklist<br/><input type='checkbox' id='closingList' name='closingList' /><label for='closing' >Please check if this is a closing shift</label></th><td>";
	if(can("update", "7db1df8d-0a15-46ed-9c83-701393e9596c"))/*reportInstructions resource*/{
		echo "<input type='button' value=\"Add Item\" onclick='addItem(1)' />";
	}
	echo"<input type='button' value=\"Reload\" onclick='printList(1,\"closeResults\")' /></td></tr>";
}

while($cur = $tasksQuery->fetch(PDO::FETCH_ASSOC)) {

	echo "<tr id='row".$cur['ID']."'><td><input onclick='fadeRow(\"row".$cur['ID']."\")' type='checkbox' id='task".$cur['ID']."' name='task".$cur['ID']."' class='task' value = '".$cur['text']."' ><label for='".$cur['ID']."' id='label".$cur['ID']."'> ".$cur['text']."</label></td><td>";
	if(can("update", "7db1df8d-0a15-46ed-9c83-701393e9596c"))/*reportInstructions resource*/{
		echo "<input type='button' value='Edit' onclick='editItem(\"".$cur['ID']."\")' /><input type='button' value='Delete' onclick='deleteItem(\"".$cur['ID']."\")' />";
	}
	echo "</td></tr>";
}
