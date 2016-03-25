<?php
require_once("../includes/includeme.php");

// Ensure that only people with the development permission can access the app.
if (isSuperuser())
{
?>

<link rel="stylesheet" type="text/css" href="./index.css" />
<h1 id="editorTitle">Add/Edit App<sup>2</sup></h1>

<div id="instructions">
	<p>
		<span id="instructionsTitle">Instructions: </span>This app is for adding, editing, and deleting references to apps. In order to use it just click on the "New App" button to create a new app reference.
		You can edit any of the app references in the table by simply double-clicking the information you would like to change, then make any adjustments you would like and click away.
		You will receive notification of the update's status in the bottom right-hand corner of your window.
		In order to delete a reference to an app just click on the "X" icon.
	</p>
</div>

<div id="addAppPopup" class="popup">
	<input id="appName" class="form" type="text" name="appName" placeholder="Name of the App" />
	<label for="appName" class="form">Name: </label>
	<div class="clearMe"></div>
	<input id="description" class="form" type="text" name="description" placeholder="Description of the App" />
	<label for="description" class="form">Description: </label>
	<div class="clearMe"></div>
	<input id="filePath" class="form" type="text" name="filePath" placeholder="File path (no ending /)" />
	<label for="filePath" class="form">File path: </label>
	<div class="clearMe"></div>
	<input id="internal" class="form" name="internal" checked="checked" type="checkbox" />
	<label for="internal" class="form">Is internal: </label>
</div>

<button id="addButton">New App</button>

<table id="appTable">
	<thead>
		<tr><th style="width: 18%;">App Name</th><th>File Path</th><th>Description</th><th style="width: 8%;">Internal?</th><th style="width: 8%;">Delete?</th></tr>
	</thead>
	<tbody id="appTbody">
<?php
	try {
		$appQuery = $db->prepare("SELECT * FROM `app` ORDER BY `appName` ASC");
		$appQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($app = $appQuery->fetch(PDO::FETCH_ASSOC))
	{
		$isChecked = (int) $app['internal'];
		if ($isChecked)
		{
			$isChecked = 'checked="checked"';
		}
		else
		{
			$isChecked = '';
		}
		echo <<<ROW
<tr id="{$app["appId"]}">
	<td ondblclick="$(this).tEditable('editApp.php')" title="appName">{$app["appName"]}</td>
	<td ondblclick="$(this).tEditable('editApp.php')" title="filePath">{$app["filePath"]}</td>
	<td ondblclick="$(this).tEditable('editApp.php')" title="description">{$app["description"]}</td>
	<td title="internal" class="internalTd"><input onclick="internalToggle(this)" type="checkbox" name="internal" value="{$app["appId"]}" {$isChecked} /></td>
	<td class="deleteTd"><span onmouseover="$(this).addClass('red')" onmouseout="$(this).removeClass('red')" onclick="deleteApp(this)" class="delete ui-icon ui-icon-circle-close"></span></td>
</tr>
ROW;
	}
?>
	</tbody>
</table>

<script src="apps.js"></script>
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
