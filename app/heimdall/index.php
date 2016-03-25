<?php

	require_once '../includes/includeme.php';

	if (!isSuperuser()) {
		echo '<h1 style="text-align:center;margin-top:20%;">You do not have permission to see this page</h1><p style="text-align:center;"> If you should have access to this page, please talk to your manager</p>';
		require ('../includes/includeAtEnd.php');
		exit();
	}

?>

<link rel='stylesheet' type='text/css' href='index.css'>
<script src='index.js' type='text/javascript'></script>

<h1>Welcome to Heimdall</h1>

<div id='searchBar' name='searchBar'>
	<input type='text' id='textFilter' name='textFilter' placeholder='Search...'>
</div>

<div class='addApplication'>
	<span class='ui-icon ui-icon-circle-plus'></span>
	<span>Add an application</span>
</div>

<div id='applications' name='applications'>

</div>

<div id='addApplicationForm'>
	<p>Please enter in the following information:</p>
	<label for='appLongName'>Long Name:</label>
	<input type='text' id='appLongName' name='appLongName'><br />
	<label for='appShortName'>Short Name:</label>
	<input type='text' id='appShortName' name='appShortName'>
</div>

<div id='deleteApplicationConfirm'>
	<p><span class='ui-icon ui-icon-alert'></span>Are you sure that you want to delete <span id='deleteAppName'></span>?</p>
</div>

<?php
	require_once '../includes/includeAtEnd.php';
?>
