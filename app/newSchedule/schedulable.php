<?php
require ($_SERVER['DOCUMENT_ROOT'].'/includes/includeme.php');

if(!can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85")/*schedule resource*/){
	echo '<h1 style="text-align:center;margin-top:20%;">You do not have permission to see this page</h1><p style="text-align:center;"> If you should have access to this page, please talk to your manager</p>';
	require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includeAtEnd.php');
	exit();
}

echo "<script type='text/javascript'>";
try {
	$employeeQuery = $db->prepare("SELECT DISTINCT employee.firstName, employee.lastName, employee.netID FROM employee LEFT JOIN employeeAreaPermissions ON employeeAreaPermissions.netID=employee.netID WHERE (employeeAreaPermissions.area=:area1 OR employee.area=:area2) AND employee.active=1 ORDER BY employee.lastName ASC");
	$employeeQuery->execute(array(':area1' => $area, ':area2' => $area));
} catch(PDOException $e) {
	exit("error in query");
}
$list = array();
while($current = $employeeQuery->fetch(PDO::FETCH_ASSOC)) {
	$list[] = $current;		
}
echo "var availableEmployees = ".json_encode($list).";";
try {
	$netIdQuery = $db->prepare("SELECT DISTINCT employee.netID FROM employee WHERE employee.area=:area AND employee.active=1 ORDER BY employee.lastName ASC");
	$netIdQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}
$list = array();
while($current = $netIdQuery->fetch(PDO::FETCH_ASSOC)){
	$list[] = $current['netID'];		
}
echo "var defaultAreaEmployees = ".json_encode($list).";";
echo "var area = $area;";
echo "var currentUser = '$netID';";
echo "var tag = 'not-schedulable';";
echo "var tag_long = 'Have an employee not show up in the schedule list by default in an area that they have access to';";
echo "</script>";

?>
<link rel="stylesheet" href="schedulable.css" />
<h1 id='page-title'>Scheduling List</h1>
<p id='page-description'>This page allows those with scheduling rights to control which employees are listed in the dropdown menu on the schedule for their area. Note that employees will still be able to see their own schedule in any area they have access to. Employees listed in <span style="color: #bb0000;">red</span> do not have <?php echo getAreaName(); ?> as their default area.</p>

<script type="text/javascript" src="schedulable.js" >
</script>

<div id='schedule-list'>
</div>

<?php
require ($_SERVER['DOCUMENT_ROOT'].'/includes/includeAtEnd.php');
?>
