<?php

/*
*	Name: index.php
*	Application: Manager Report
*
*	Description: This page allows a manager to submit entries to be sent to the director in the Manager Report as well as
*	review other manager's Highlights/Accomplishments, Challenges, and Interactions and choose which ones will be emailed
*	to the director.  They are also able to edit the comments.
*/

	//Standard include file for the site header
	require('../includes/includeme.php');

	//Check permissions
	$permission = can("access", "1ae4366d-1b2e-4a54-b936-529455e06bea");//managerReport resource

	//If they don't have permission tell them they don't and don't print the rest of the page content.
	if(!$permission)
	{
		echo '<div style="text-align:center;">
				<h2>You do not have permission to view this page.</h2>
			</div>';
		
		//Standard include file for footer
		require('../includes/includeAtEnd.php');
	
		return;
	}//if
    
?>

<!--CSS-->
<link rel="stylesheet" type="text/css" href="managerReport.css" />

<!--JavaScript-->
<script language="JavaScript" src="managerReport.js"></script>

<!--HTML-->
<div id="managerReport" style="margin:10px;">

	<div id="title">
		<br>
		<h1>Manager Report</h1>	
	</div>
	
	<br>
	<hr>
	
	<!--Area of page where managers can submit new entries-->
	<div id="submitEntries">
		<form id="managerEntry">
			
			<h3>Submit your entries</h3>
			<i>Submit new entries for the report here.</i><br><br>
			<a onclick="showCategoryEditorPopup()">Edit Categories</a></br>
			<select id="newEntryCategory">
					
				<option value="0">Categories</option>
				
			</select>
			
			<input id="newEntry" name="newEntry" class="item" type="text"  style="width:648px;"></input>
			<button onclick='submitEntry(event)' style="float:right;margin-bottom:10px;margin-right:10px;">Submit</button>
			
		</form>
	</div>
	
	<br>
	<hr>
	
	<!--Area of report where managers can change search criteria to filter the results shown in the report in the finalReport div-->
	<div id="searchCriteria">
		<h3>Search criteria</h3>
		<i>Use the following options to filter the results shown in the report below.</i><br><br>
		<table id="filterTable" width="880px">
			<tr>
				<td>
					<b>Start Date:</b> <span style="cursor:pointer;"><input type="text" id="startDate" style="width:6em;"/></span>
				</td>
				<td>
					<b>End Date:</b> <span style="cursor:pointer;"><input type="text" id="endDate"  style="width:6em;"/></span>
				</td>
				<td>
					<b>Categories:</b>
					<li class="ui-state-default ui-corner-all" onClick="toggleVisibility('categories');" style="width:16px; height:16px; float:right;">
						<span class="ui-icon ui-icon-wrench"></span>
					</li>
				</td>
				<td>
					<b>Employees:</b>
					<li class="ui-state-default ui-corner-all" onClick="toggleVisibility('employees');" style="width:16px; height:16px; float:right;">
						<span class="ui-icon ui-icon-wrench"></span>
					</li>
				</td>
				<td>
					<b>Checked vs. Non-Checked:</b><br>
					<input type="radio" name="submitted" value="2" checked="checked" class="searchChecked">All<br>
					<input type="radio" name="submitted" value="1" class="searchChecked">Checked Entries<br>
					<input type="radio" name="submitted" value="0" class="searchChecked">Non-Checked Entries<br>
				</td>
			</tr>
		
		</table>
				
		
		<button onclick='getReport()' style="float:right;margin-bottom:10px;margin-right:10px;">Search</button>
		
	</div>
	
	<div style="clear:both;">
	</div>
	
	<hr>
	
	<!--Area where the results from the searchCriteria div are displayed.  Managers can then select which entries will be included in the report being emailed.-->
	<div id="finalReport">
	
	</div>
	
	<!--This div is by default invisible.  By clicking on the small blue box with a white wrench in it next to "Categories:" this div will be made visible in a dialog box.
	From this div Managers can filter which categories will be displayed in the finalReport div.-->
	<div id="categories" title="Select Categories" class="filterDiv">
		
		<!--Checkbox used to select all categories-->
		<input id="searchCategories" type="checkbox" onclick='checkContent("searchCategories")' checked="checked"><i> All</i><br>
		<input id="searchCategories_default" class="searchCategory" type="checkbox" checked="checked">Loading...<br>
	</div>
	
	<!--This div is by default invisible.  By clicking on the small blue box with a white wrench in it next to "Employees:" this div will be made visible in a dialog box.
	From this div Managers can filter which employees will be displayed in the finalReport div.-->
	<div id="employees" title="Select Employees" class="filterDiv">
		
		<!--Checkbox used to select all employees-->
		<input id="searchEmployees" type="checkbox" onclick='checkContent("searchEmployees")'><i> All</i><br>
		
		<?php
			//Get the list of categories from the DB
			try {
				$employeeQuery = $db->prepare("SELECT DISTINCT `employee`.`netID`, `employee`.`firstName`, `employee`.`lastName` FROM `employee` INNER JOIN `managerReports` ON `employee`.`netID` = `managerReports`.`netID` WHERE `employee`.`active` = '1' ORDER BY `employee`.`firstName` ASC");
				$employeeQuery->execute();
			} catch(PDOException $e) {
				exit("error in query");
			}
			//Create an array of employees			
			$managerReportEmployees = array();
			while($cur = $employeeQuery->fetch(PDO::FETCH_ASSOC))
			{
				$managerReportEmployees[] = $cur;
			}//while
			
			/*Check to see if the employee is in the list and if not add them to it, otherwise there won't be an employee selected
			** and the page throws an error that you haven't selected an employee.*/
			$empNetIDInList = false;
			foreach ($managerReportEmployees as $employee){
				if ($employee['netID'] == $netID){
					$empNetIDInList = true;
				}//if
			}//foreach
			
			//*Note - I know this next part is ugly but I ran out of time to make it any better and I just focused on getting it to work.
			//feel free to fix it
			if (!$empNetIDInList){
				//Get the list of categories from the DB
				try {
					$nameQuery = $db->prepare("SELECT `firstName`, `lastName` FROM `employee` WHERE `netID` = :netId");
					$nameQuery->execute(array(':netId' => $netID));
				} catch(PDOException $e) {
					exit("error in query");
				}
				//Create an array of employees			
				$missingEmployee = array();
				while($cur = $nameQuery->fetch(PDO::FETCH_ASSOC))
				{
					$missingEmployee[] = $cur;
				}//while
				
				//Print the checkbox with employee's first and last name
				echo '<input id="searchEmployees_' .$netID. '" class="searchEmployees" type="checkbox"  value="' .$netID. '" name="' .$netID. '" checked="checked"> '.$missingEmployee[0]['firstName'].' ' .$missingEmployee[0]['lastName']. '<br>';		
			}//if
					

			//Print out checkboxes of all employees
			foreach ($managerReportEmployees as $employee) 
			{
				if($employee['netID'] == $netID){
					echo '<input id="searchEmployees_' .$employee['netID']. '" class="searchEmployees" type="checkbox"  value="' .$employee['netID']. '" name="' .$employee['netID']. '" checked="checked"> '.$employee['firstName'].' ' .$employee['lastName']. '<br>';		
				} else {
					echo '<input id="searchEmployees_' .$employee['netID']. '" class="searchEmployees" type="checkbox"  value="' .$employee['netID']. '" name="' .$employee['netID']. '"> '.$employee['firstName'].' ' .$employee['lastName']. '<br>';		
				}//if-else
				
			}//foreach
		?>
	</div>
	
	
	<!--This div is by default invisible.  By clicking on the "Preview Final Report" button this div will be made visible in a dialog box.
	In this div Managers can review the report before emailing it.-->
	<div id='preview'>
		<i>Enter who the report will be emailed to separated by "," (e.g. person@gmail.com, otherperson@gmail.com)</i><br>
		To:	<input id="toInput" class="emailInput" type="text" style="width:300px;"><br>
		Cc:	  <input id="ccInput" class="emailInput" type="text" style="width:300px;"><br>
		<!--This div is by default invisible.  It holds the text that will be sent in the email report.-->
		<div id='emailText'></div>
	</div>
	
	<div id="category-manager" hidden>
		<span id="category-list">
		</span>
		<span class="do-not-empty">
			<input type="text" id="new-category" class="category-name" placeholder="New Category" /><span id="add-category">+</span>
		</span>
	</div>
	
	<script type="text/javascript" src="categories.js">
	</script>
	
</div>

<?php

	//Standard include file for footer
	require('../includes/includeAtEnd.php');
?>

	


