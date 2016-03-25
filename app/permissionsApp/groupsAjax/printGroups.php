<?php //printGroups.php
//This will print the groups to be edited.
require('../../includes/includeMeBlank.php');

//prints out the teams in a select box
function groupsSelect($area){
	global $db;
	try {
		$groupsQuery = $db->prepare("SELECT * FROM permissionsGroups WHERE area=:area");
		$groupsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $groupsQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value='".$cur['ID']."'>".$cur['name']."</option>";
	}

}
function pullGroups($area){
	global $db;
	try {
		$groupsQuery = $db->prepare("SELECT * FROM permissionsGroups WHERE area=:area");
		$groupsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $groupsQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><td><input type='text' name='".$cur['ID']."' value=\"".$cur['name']."\" /></td>";
		echo "</tr>";
	}
}

	echo "<table>
		<tr><th>Name</th></tr>";	
    pullGroups($area);
	echo "</table>";
	echo "<input type='button' class='button' name='delete' id='delete' value='Remove:' onclick='deleteGroup()' />";
  echo  "<select name='groups' id='groups' >";
    groupsSelect($area);
  echo  "</select>";
