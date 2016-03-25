<?php //organizePermissionGroups.php this page is for managing permission groups.
require('../includes/includeme.php');

if(checkPermission('permissionGroups')){

try {
	$permissionsQuery = $db->prepare("SELECT * FROM permissionsGroups WHERE area = :area");
	$permissionsQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}
$numRows = 0;
if(isset($_POST['submit'])){
    while($team = $permissionsQuery->fetch(PDO::FETCH_ASSOC)) {
		$numRows++;
		try {
			$updateQuery = $db->prepare("UPDATE permissionsGroups  SET name=:name WHERE ID=:id");
			$updateQuery->execute(array(':name' => $_POST[$team['ID']], ':id' => $team['ID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
?>
<script type='text/javascript'>
window.onload = printGroups;

function printGroups(){
			var page = 'groupsAjax/printGroups.php';
			
			var cb = function(result){ document.getElementById("results").innerHTML = result; };

			callPhpPage(page,cb);
	}


function deleteGroup(){
	var id = document.getElementById('groups').value;
	var r = confirm("Are you sure you want to Delete this Group?");
	if(r == true){
		var page = 'groupsAjax/deleteGroup.php?id='+id;
	
		var cb = function(result){ printGroups(); };

		callPhpPage(page,cb);
	}
}

function insertGroup(){
	var r = confirm("Are you sure you want to Insert a new Group?");
	if(r == true){
		var page = 'groupsAjax/insertGroup.php';
	
		var cb = function(result){ printGroups(); };

		callPhpPage(page,cb);
	}
}
</script>
<h1 align='center'>Permission Groups</h1>
<div align='center'>
<b>To manage the permissions in Specific Groups</b> <a href='setPermissionGroups.php'>Click Here</a>
</div>
<form name='addGroup' method='post'>
	<div align='center' style="margin:auto;">
    <input type='button' class='button' name='newGroup' value="Insert New Group" onclick="insertGroup()" />
    <input type='submit' class='button' name='submit' value="Submit Changes" /> 
     
	</div>
	<div align='center' id='results' name='results'>
	
	</div>
	</form>

<?php
} else {
	echo "You are not Authorized to view this page. Please see your supervisor if you believe you should have permissions to view this page.";
}
require('../includes/includeAtEnd.php');

?>
