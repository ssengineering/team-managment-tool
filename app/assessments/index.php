<?php
/*
*	Name: index.php
*	Application: Assessments
*	Site: ops.byu.edu
*	Author: Joshua Terrasas
*
*	Description: This is the main page for the Assessments app. It acts as a container
*	for the rest of the app. All other viewible pages for the Assessments app are loaded
*	into this file using an AJAX call, through callPhpPage(). All content is loaded into 
*	the modeContent div. The content is chosen by the user selecting a modeButton, which is 
*	displayed using jQuery buttons. This page also loads the needed CSS style sheet, and JavaScript
*	file that are used for the entire app.
*/

//Standard include file for OPS header.
require('../includes/includeme.php');

$permissionGrade = can("grade", "3e80eb8f-3628-4b29-bf35-fbc7749c0a80"); //Assessments resource (from assessmentsGrade)
$permissionEdit = can("update", "3e80eb8f-3628-4b29-bf35-fbc7749c0a80"); //Assessments resource (from assessmentsEdit)
?>

<!--CSS-->

<!--Include for Assessments app styling-->
<link rel="stylesheet" type="text/css" href="assessments.css" />

<!--JavaScript-->

<!--Include for Assessments app functionality-->
<script language="JavaScript" src="assessments.js"></script>

<!--HTML-->

<!--Title of application-->
<h1 id="title">Assessments</h1>

<!--This div contains several radio buttons that select what part of the app to load.
They are displayed using jQuery UI buttons. When pressed, the tab runs a function that
loads the desired page.-->
<div id="modeMenu">
	<?php
	if($permissionGrade)
	{
		echo "<input type='radio' id='modeViewButton' name='modeButton' onClick='viewMode()' checked='checked'/><label for='modeViewButton'>View</label>";
		echo "<input type='radio' id='modeGradeButton' name='modeButton' onClick='gradeMode(\"default\")' /><label for='modeGradeButton'>Grade</label>";
		
		if($permissionEdit)
		{
			echo "<input type='radio' id='modeGroupsButton' name='modeButton' onClick='groupsMode()' /><label for='modeGroupsButton'>Groups</label>";
			echo "<input type='radio' id='modeTestsButton' name='modeButton' onClick='testsMode()' /><label for='modeTestsButton'>Certification</label>";
		}
	}
	?>
</div>

<!--Container div to hold the selected page from modeMenu.-->
<div id="modeContent">
</div>

<input type="text" id="numRowsToDisplay" style="display:none" value="1" >


<?php
	
//Standard include file for footer.
require('../includes/includeAtEnd.php');

?>
