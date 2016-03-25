<?php //viewRights.php
require('../includes/includeme.php');
?>
<script>
	area  = "<?php echo $area; ?>";
	netId = "<?php echo $netID; ?>";
	var loaded = false;
	var openDivs = {};
</script>
<script src="/includes/template/js/libs/jquery-1.8.2.min.js"></script>
<script src="viewRights.js"></script>
<h2 align='center'>Employee Rights</h2>

<div id='employeeSearch'>
	Select an employee to view rights:
	<select id='employee' onchange="loadRightsList(area,this.options[this.selectedIndex].id);">
		<?php 
			try {
				$employeeQuery = $db->prepare("SELECT * FROM `employee` WHERE `active`=1 AND ((`area`=:area) OR (`netID` IN (SELECT `netID` FROM `employeeAreaPermissions` WHERE `area`=:area1))) ORDER BY `firstName` ASC");
				$employeeQuery->execute(array(':area' => $area, ':area1' => $area));
			} catch(PDOException $e) {
				exit("error in query");
			}
			while($employee = $employeeQuery->fetch(PDO::FETCH_ASSOC)) {
				$name = $employee['firstName']." ".$employee['lastName'];
				if($employee['area'] != $area) {
					$name = "*".$name;
				}
				if($employee['netID'] == $netID) {
					echo "<option id='".$employee['netID']."' value='".$name."' selected>".$name."</option>";
				} else {
					echo "<option id='".$employee['netID']."' value='".$name."'>".$name."</option>";
				}
			}
		?>
	</select>
</div>
<div id='rightsInfo'>	
</div>

<?php 
require('../includes/includeAtEnd.php');
?>
