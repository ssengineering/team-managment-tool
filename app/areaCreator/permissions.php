<?php
require_once("../includes/includeme.php");

// Ensure that only people with the development permission can access the app.
if (isSuperuser())
{
?>

<link rel="stylesheet" type="text/css" href="./index.css" />
<h1 id="editorTitle">Permission Editor</h1>

<div id="instructions">
	<p>
		<span id="instructionsTitle">Instructions: </span>This app is for adding, editing, and deleting permissions. In order to use it just click on the "New Permission" button to create a new permission.
		You can edit any of the permissions in the table by simply double-clicking the information you would like to change, then make any adjustments you would like and click away.
		You will receive notification of the update's status in the bottom right-hand corner of your window.
		In order to delete a permission just click on the "X" icon.
	</p>
</div>

<div id="addPermissionPopup" class="popup">
	<input id="shortName" class="form" type="text" name="shortName" placeholder="Short name (No Spaces)" />
	<label for="shortName" class="form">Short-name: </label>
	<div class="clearMe"></div>
	<input id="longName" class="form" type="text" name="longName" placeholder="Long name (Spaces OK)" />
	<label for="longName" class="form">Long-name: </label>
	<div class="clearMe"></div>
	<input id="description" class="form" type="text" name="description" placeholder="Description of permission" />
	<label for="description" class="form">Description: </label>
	<div class="clearMe"></div>
</div>

<button id="addButton">New Permission</button>

<table id="permissionTable">
	<thead>
		<tr><th>Short Name</th><th>Long Name</th><th>Description</th><th>Delete?</th></tr>
	</thead>
	<tbody id="permissionTbody">
<?php
	try {
		$permissionQuery = $db->prepare("SELECT * FROM `permission` ORDER BY `longName`");
		$permissionQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($permission = $permissionQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo "<tr id=\"{$permission["permissionId"]}\"><td ondblclick=\"$(this).tEditable('editPermission.php')\" title=\"shortName\">{$permission["shortName"]}</td><td ondblclick=\"$(this).tEditable('editPermission.php')\" title=\"longName\">{$permission["longName"]}</td><td ondblclick=\"$(this).tEditable('editPermission.php')\" title=\"description\">{$permission["description"]}</td><td class=\"deleteTd\"><span onmouseover=\"$(this).addClass('red')\" onmouseout=\"$(this).removeClass('red')\" onclick=\"deletePermission(this)\" class=\"delete ui-icon ui-icon-circle-close\"></span></td></tr>";
	}
?>
	</tbody>
</table>

<script src="permissions.js"></script>
<script type="text/javascript">
	window.onload = loadMe;
</script>
<?php
}
else
{
	echo "<h1>You are not authorized to view this page</h1>";
}
require_once('../includes/includeAtEnd.php');
?>
