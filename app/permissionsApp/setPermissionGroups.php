<?php //setPermissionGroups.php
require('../includes/includeme.php');

if(checkPermission('permissionGroup')){


//prints out the teams in a select box
function groupsSelect($area){
	global $db;
	try {
		$groupQuery = $db->prepare("SELECT * FROM permissionsGroups WHERE area=:area");
		$groupQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $groupQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value='".$cur['ID']."'>".$cur['name']."</option>";
	}

}
?>
<script type="text/javascript">
var groupID;

function groupSelect(group){
	groupID = group;
	var page="pullPermissionsGroup.php?group=" + group;
	callPhpPage(page,permissionFill);
}
function permissionFill(result){
	document.getElementById('results').innerHTML=result;
}
function showHide(element){
	if(document.getElementById(element).style.display=='none')
		document.getElementById(element).style.display= 'block';
	else
		document.getElementById(element).style.display='none';
}
function addPermission(index){
	var page="addPermission.php?group=" + groupID + "&index=" + index;
	callPhpPage(page,checkAction);
}
function removePermission(index){
	var page="removePermission.php?group=" + groupID + "&index=" + index;
	callPhpPage(page,checkAction);
}

function checkAction(result){
	if(result)
		alert(result);
}
</script>
<style>
.results{
	width:40%;
	margin:auto;
	font-size:120%;
	line-height:15px;
}
</style>
<h1 align='center'>Add Permissions To Group</h1>
<input type='button' value="Back to Group Manager" onclick='window.location.href="organizePermissionGroups.php"' />
<div align='center'>
<select id='groups' name='groups' onchange='groupSelect(this.options[this.selectedIndex].value)' >
	<option value=''>Select Permission Group</option>
<?php groupsSelect($area); ?>
</select>
</div>
<div id='results' name='results' class='results'>

</div>
<?php 
} else {
	echo "You are not authorized to view this page. Please contact your supervisor if you believe you deserve these permissions";
}
require('../includes/includeAtEnd.php');
?>
