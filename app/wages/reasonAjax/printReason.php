<?php //printReason.php this responds to an ajax call
require('../../includes/includeMeBlank.php');

function reasonsSelect($area){
	global $db;

	try {
		$reasonsQuery = $db->prepare("SELECT * FROM employeeRaiseReasons WHERE area=:area");
		$reasonsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $reasonsQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value='".$cur['index']."'>".$cur['reason']."</option>";
	}

} 
function pullReasons($area){
	global $db;
	try {
		$raiseReasonQuery = $db->prepare("SELECT * FROM employeeRaiseReasons WHERE area=:area ORDER BY reason");
		$raiseReasonQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $raiseReasonQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><td><input type='text' name='reason[".$cur['index']."]' value=\"".$cur['reason']."\" /></td><td><input type='text' name='raise[".$cur['index']."]' value=\"".$cur['raise']."\"</tr>";
	}
	
}

echo "<table class='sortable'>
<tr><th>Reason</th><th>Default Raise in $</th></tr>";	
pullReasons($area);
echo	"</table>";
echo '<input type="button" value="Delete Reason:" onclick="deleteReason()" />';
echo "<select name='reasons' id='reasons'>";
reasonsSelect($area);
echo '</select>';

?>
