<?php //manageRights.php
require('../includes/includeme.php');
if(can("access", "03478803-982d-4542-8589-367cbd8dabae")){//rightsChecklist resource
?>

<script>
var employee = "<?php echo $netID;?>";
var manager  = "<?php echo $netID;?>";
var area     = "<?php echo $area; ?>";
var env      = "<?php echo $env;  ?>";
var loaded   = false;
var openDivs = {};
</script>
<script src="/includes/template/js/libs/jquery-1.8.2.min.js"></script>
<script src='dialogs.js'></script>
<script src='manageRights.js'></script>
<style>
h3{
	text-decoration:underline;
}
#managediv{
	padding:40px;
	align:center;
}
#whole{
	font-size:16px;
	align:center;
}
table.imagetable {
	font-family: verdana,arial,sans-serif;
	font-size:15px;
	color:#333333;
	border-width: 1px;
	border-color: #999999;
	border-collapse: collapse;
}
table.imagetable th {
	color:white;
	background:#002255;
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #999999;
	width: 20%;
}
table.imagetable td {
	color:#444444;	
	background:#e9e9e9;
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #999999;
	width: 80%;
}

</style>

<div id='whole'>
<h2 align='center'>Modify Rights Checklist</h2>
<a href='index.php'>Return to Main Page</a>
	<div id='managediv'>
	</div>
	<input id='createButton' type='button' class='button' value="Create Right"/>
	<input id='editButton' type='button' class='button' value="Edit Levels"/>
</div>

<!--***********************************************************************************************-->
<!--************************************Create Right Dialog****************************************-->
<!--***********************************************************************************************-->
<div id='createRightDialog'>
	<form id='editRight' name="editRight" method="post">
	<div align="center">
	<!---------------------------TITLE--------------------------- -->
	<table class='imagetable' style='width:100%'>
		<tr>
			<th>Right Name:</th>
			<td colspan="3"><input type="text" id='name' name="name" maxlength=200 style='width:100%' value=""/></td>
			</td>
		</tr>
	<!-----------------------DESCRIPTION--------------------------- -->
		<tr>
			<th>Description:</th>
			<td>
				<textarea id='descr' name="descr" rows="5" style='width:100%'></textarea>
			</td>
		</tr>
		<tr>
	<!---------------------Right Type---------------------------------------- -->
			<th>Right Type:</th>
			<td>
				<select id="rightType" name="rightType">
					<option value="BASIC">BASIC</option>
					<option value="EMAIL">EMAIL</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>Right Level:</th>
			<td><?php 
				echo '<select id="rightLevel" name="rightLevel" >';
				try {
					$rightsLevelsQuery = $db->prepare("SELECT * FROM employeeRightsLevels WHERE area = :area");
					$rightsLevelsQuery->execute(array(':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}	
				while($levels = $rightsLevelsQuery->fetch(PDO::FETCH_ASSOC)) {
					if($levels['level'] == 1){
						echo "<option value='".$levels['level']."' selected>".$levels['name']."</option>";
					}else{
						echo "<option value='".$levels['level']."'>".$levels['name']."</option>";
					}
				}
				echo "</select>";
			?></td>
		</tr> 
	</table>
	<!--------------------Email info--------------------------------- -->
	<div id='emailInfo' style='display:none'>
		<table class='imagetable' style='width:100%'>
			<tr>
				<th colspan=2>Email Information:</th>
			</tr>
			<tr>
				<th>TO:</th>
				<td><input type="text" id='to' name="to" maxlength=200 style='width:100%' value=""/></td>
			</tr><tr>
				<th>CC:</th>
				<td><input type="text" id='cc' name="cc" maxlength=200 style='width:100%' value=""/></td>
			</tr><tr>
				<th>Request Rights Title:</th>
				<td><input type="text" id='addTitle' name="addTitle" maxlength=200 style='width:100%' value=""/></td>
			</tr><tr>
				<th>Request Rights Body:</th>
				<td><textarea id='addBody' name="addBody" rows="6" style='width:100%'></textarea></td>
			</tr><tr>
				<th>Terminate Rights Title:</th>
				<td><input type="text" id='delTitle' name="delTitle" maxlength=200 style='width:100%' value=""/></td>
			</tr><tr>
				<th>Terminate Rights Body:</th>
				<td><textarea id='delBody' name="delBody" rows="6" style='width:100%'></textarea></td>
			</tr>
		</table>
		<span><input type='checkbox' id='noEmail'><label for='noEmail'>No Email</label></span>
	</div>
	<div id='employeeSelectDiv' style='margin-left:25%;margin-right:25%width:50%'>
		<div style='float:left'>
			<h4>Employees</h4>

			<select id='employeeSelect' size='8' style='width:200px' multiple>
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
							$name .= "*";
						}
						echo "<option id='".$employee['netID']."' value='".$name."'>".$name."</option>";
					}
				?>
			</select>
			<br /><input value='Add All' type='button' id='addAll' style='margin-top:10px'>
		</div>
		<div id='employeeSelectButtons' style='float:left;vertical-align:middle;padding:75px 10px'>
			<input type='button' value='-->' id='add'><br /><br />
			<input type='button' value='<--' id='remove'>
		</div>
		<div style='float:left'>
			<h4>Grant Right</h4>
			<select id='selectedEmployees' style='width:200px' size='8' multiple>
			</select>
			<br /><input value='Remove All' type='button' id='removeAll' style='margin-top:10px'>
		</div>
	</div>

	</div>
	</form>
</div>
<!--***********************************************************************************************-->
<!--**************************************Edit Levels Dialog***************************************-->
<!--***********************************************************************************************-->
<div align='center' id='editLevelsDialog'>
	<div id='results'></div>
	<br />
	<input type='button' class='button' name='newRow' value="Insert Row" onclick='insertLevel()' />

</div>

<!--***********************************************************************************************-->
<!--***************************************Edit Rights Dialog**************************************-->
<!--***********************************************************************************************-->

<div align="center" id='editRightsDialog'>
	<!---------------------------TITLE--------------------------- -->
	<table class='imagetable' style='width:100%'>
		<tr>
			<th>Right Name:</th>
			<td colspan="3"><input type="text" id='editName' maxlength=200 style='width:100%' value=""/></td>
			</td>
		</tr>
	<!-----------------------DESCRIPTION--------------------------- -->
		<tr>
			<th>Description:</th>
			<td>
				<textarea id='editDescr' name="descr" rows="5" style='width:100%'></textarea>
			</td>
		</tr>
	<!--------------------AREA--------------------------------- -->
		<tr>
			<th>Area: </th>
			<td><?php echo getAreaName($right['area']);?></td>
		</tr> 
	<!-----------------------Right Level--------------------------- -->
		<tr>
			<th>Right Level:</th>
			<td><?php 
				echo '<select id="editRightLevel" name="rightLevel" >';
				try {
					$rightsLevelsQuery = $db->prepare("SELECT * FROM employeeRightsLevels WHERE area = :area");
					$rightsLevelsQuery->execute(array(':area' => $area));
				} catch(PDOException $e) {
					exit("error in query");
				}
				while($levels = $rightsLevelsQuery->fetch(PDO::FETCH_ASSOC)) {
					if($levels['level'] == 1){
						echo "<option value='".$levels['level']."' selected>".$levels['name']."</option>";
					}else{
						echo "<option value='".$levels['level']."'>".$levels['name']."</option>";
					}
				}
				echo "</select>";
			?></td>
		</tr>	
	<!---------------------Right Type---------------------------------------- -->
		<tr>
			<th>Right Type:</th>
			<td>
				<select id="editRightType" name="rightType">
					<option value="BASIC">BASIC</option>
					<option value="EMAIL">EMAIL</option>
				</select>
			</td>
		</tr>
	</table>
	<!--------------------Email info--------------------------------- -->
	<div id='editEmailInfo' style='display:none'>
		<table class='imagetable' style='width:100%'>
			<tr>
				<th colspan=2>Email Information:</th>
			</tr>
			<tr>
				<th>TO:</th>
				<td><input type="text" id='editTo' name="to" maxlength=200 style='width:100%' value=""/></td>
			</tr><tr>
				<th>CC:</th>
				<td><input type="text" id='editCC' name="cc" maxlength=200 style='width:100%' value=""/></td>
			</tr><tr>
				<th>Request Rights Title:</th>
				<td><input type="text" id='editAddTitle' name="addTitle" maxlength=200 style='width:100%' value=""/></td>
			</tr><tr>
				<th>Request Rights Body:</th>
				<td><textarea id='editAddBody' name="editAddBody" rows="6" style='width:100%'></textarea></td>
			</tr><tr>
				<th>Terminate Rights Title:</th>
				<td><input type="text" id='editDelTitle' name="delTitle" maxlength=200 style='width:100%' value=""/></td>
			</tr><tr>
				<th>Terminate Rights Body:</th>
				<td><textarea id='editDelBody' name="editDelBody" rows="6" style='width:100%'></textarea></td>
			</tr>
		</table>
	</div>

</div>

<?php 
} else {
	echo "<h1>You are not Authorized to view this page</h1>";
}
require('../includes/includeAtEnd.php');
?>
