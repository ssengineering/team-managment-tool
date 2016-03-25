<?php
require_once("../includes/includeme.php");

// Ensure that only people with the development permission can access the app.
if (isSuperuser())
{
?>

<link rel="stylesheet" type="text/css" href="./index.css" />
<h1 id="editorTitle">App Permissions Editor</h1>

<div id="instructions">
	<p>
		<span id="instructionsTitle">Instructions: </span>This app is for adding or removing associations between apps and permissions.
		I.e. this app shows which apps make use of which permissions.
		In order to add an association between an app and a permission simply click the "New Association" button.
		If you would like to delete an association just click on the "X" icon.
	</p>
</div>

<div id="addAssociationPopup" class="popup">
	<select id="appId" class="form" type="text" name="appId">
<?php
	try {
		$appsQuery = $db->prepare("SELECT * FROM `app` ORDER BY `appName`");
		$appsQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($app = $appsQuery->fetch())
	{
		echo '<option value="'.$app->appId.'">'.$app->appName.'</option>';
	}
?>
	</select>
	<label for="appId" class="form">App: </label>
	<div class="clearMe"></div>
	<select id="permissionId" class="form" type="text" name="permissionId">
<?php
	try {
		$permissionQuery = $db->prepare("SELECT * FROM `permission` ORDER BY `shortName`");
		$permissionQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($permission = $permissionQuery->fetch())
	{
		echo '<option value="'.$permission->permissionId.'">'.$permission->shortName.'</option>';
	}
?>
	</select>
	<label for="permissionId" class="form">Permission: </label>
	<div class="clearMe"></div>
</div>

<button id="addButton">New Association</button>

<table id="associationTable">
	<thead>
		<tr>
			<th>App Name</th>
			<th>App File Path</th>
			<th>App Description</th>
			<th>Permission Short Name</th>
			<th>Permission Long Name</th>
			<th>Permission Description</th>
			<th>Delete?</th>
		</tr>
	</thead>
	<tbody id="associationTbody">
<?php
	try {
		$associationQuery = $db->prepare("SELECT *, `app`.`description` AS 'appDescription', `permission`.`description` AS 'permissionDescription' 
										  FROM `appPermission` JOIN `app` ON `app`.`appId` = `appPermission`.`appId` JOIN `permission` 
										  ON `permission`.`permissionId` = `appPermission`.`permissionId` ORDER BY `appName` ASC");
		$associationQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($association = $associationQuery->fetch(PDO::FETCH_ASSOC))
	{
		echo <<<ROW
<tr id="{$association["appPermissionId"]}">
	<td>{$association["appName"]}</td>
	<td>{$association["filePath"]}</td>
	<td>{$association["appDescription"]}</td>
	<td>{$association["shortName"]}</td>
	<td>{$association["longName"]}</td>
	<td>{$association["permissionDescription"]}</td>
	<td class="deleteTd"><span onmouseover="$(this).addClass('red')" onmouseout="$(this).removeClass('red')" onclick="deleteAssociation(this)" class="delete ui-icon ui-icon-circle-close"></span></td>
</tr>
ROW;
	}
?>
	</tbody>
</table>

<script src="appPermissions.js"></script>
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
