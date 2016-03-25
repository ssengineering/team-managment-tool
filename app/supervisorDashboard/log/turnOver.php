<?php
require_once($_SERVER['DOCUMENT_ROOT']."/includes/includeme.php");

try{
	$possibleEmployeesQuery = $db->prepare("SELECT * FROM `employee` WHERE `netID` IN (SELECT DISTINCT `submittedBy` FROM `supNotes` UNION ALL SELECT `clearedBy` FROM `supNotes`) ORDER BY `firstName`");
	$possibleEmployeesQuery->execute();
} catch (PDOException $e){
	exit('error in query');
}
$possibleEmployees = array();
while($employee = $possibleEmployeesQuery->fetch(PDO::FETCH_ASSOC))
{
	$possibleEmployees[] = $employee;
}
function employeeOptions()
{
	global $possibleEmployees;
	echo "<option value=''>Any</option>";
	foreach ($possibleEmployees as $index => $employee)
	{
		$active = '';
		if ($employee['active'] !== '1') $active = '*';
		echo "<option value='${employee['netID']}'> ${employee['firstName']} ${employee['lastName']} ${active} </option>";
	}
}
?>

<link rel="stylesheet" href="turnOver.css" />
<script src="turnOver.js" type="text/javascript"></script>

<h2 id="title">Turn-over Notes Log</h2>

<!-- This form is for serialization during the async calls to the turnOverNotes API -->
<div id="searchParameters">
	<label for="submittedBy" class="parameter">Submitted-by: </label>
	<select id='submittedBy' class="parameter" value='' name="submittedBy">
	<?php employeeOptions(); ?>
	</select>
	
	<label for="ownedBy" class="parameter">Closed-by: </label>
	<select id='ownedBy' class="parameter" value="" name="ownedBy">
	<?php employeeOptions(); ?>
	</select>

<div class="clearMe"></div>

	<label for="startDate" class="parameter">Start-range: </label>
	<input id='startDate' type="text" class="text parameter" value="" name="startDate">

	<label for="endDate" class="parameter">End-range: </label>
	<input id='endDate' type="text" class="text parameter" value="" name="endDate">

	<label for="cleared" class="parameter">Closed: </label>
	<select name="cleared" class="parameter" id="cleared">
		<option value="">Either</option>
		<option value="1">Yes</option>
		<option value="0">No</option>
	</select>

<div class="clearMe"></div>

	<label for="noteText" class="parameter">Note-text: </label>
	<input id="noteText" type="text" class="text parameter" value="" name="noteText">

	<label class="parameter" for="closingComment">Closing-comment: </label>
	<input name="closingComment" type="text" id="closingComment" value="" class="text parameter">

	<button id="submitQuery">Search</button>
</div>

<div id="noteLog"></div>

<?php
require_once($_SERVER['DOCUMENT_ROOT']."/includes/includeAtEnd.php");
?>
