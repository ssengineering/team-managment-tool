<?php
/*
*	Name: view.php
*	Application: Assessments
*	Site: ops.byu.edu
*	Author: Joshua Terrasas
*
*	Description: This is the 'View' mode of the Assessments App. It allows the user
*	to see the statistics of the different employees and groups. The contents
*	are stored in a container div that is injected into the content div of
*	the index of the app. View mode has two tabs, one to view stats for employees,
*	and one to view stats for groups. These tabs are created using jQuery UI Tabs.
*	After selecting an employee/group, an AJAX call is made that creates a
*	page with all the stats pulled from the database. Tables are used to
*	display the information.
*/

require('../includes/includeMeBlank.php'); // Standard include for OPS website, but this version does not display a header. Used for global variables.
	
$permission = can("grade", "3e80eb8f-3628-4b29-bf35-fbc7749c0a80"); // Permissions check. See if user has grading rights. //Assessments resource (from assessmentsGrade)
?>

<div id="modeContent">
	<?php
	/* Check if an employee had been previously selected (after an AJAX call for example).
	   If not, set the employee to the current user NetID. If yes, set the employee to the
	   NetID passed in through POST. In some cases, the employee variable is passed in,
	   but is completely blank, so we check for that as well, and treat it as if no NetID
	   was provided. */
	if(isset($_POST['employee']))
	{
		if($_POST['employee'] == '')
		{
			$employee = $netID;
		}
		else
		{
			$employee = $_POST['employee'];
		}
	}
	else
	{
		$employee = $netID;
	}
	
	if(isset($_POST['group'])) // Check if a group ID was passed in from an AJAX call.
	{
		$group = $_POST['group'];
	}
	else
	{
		$group = '';
	}
	
	$numberOfEmployees = 0; // The number of employees in the current group.
	$numberOfTests = 0; // The number of required tests in the current group.
	$totalPassed = 0; // The number of tests passed off.
	$percentComplete = 0; // The percentage of tests passed for the number of employees and required tests in the current group.
	?>
	
	<!--jQuery UI tabs-->
	<div id='tabs'>
		<ul>
			<li><a href="#employeeResults">Employee</a></li>
			<?php
			if($permission) // Display 'Group' tab if current user has permission.
			{
				echo "<li><a href='#groupResults'>Group</a></li>";
			}
			?>
		</ul>
		
		<!--Container divs for jQuery UI tabs-->
		<div id='employeeResults'>
			<?php
			if($permission) // Allow current user to select a different employee if they have permission. Otherwise, only display thier results.
			{
				echo "<form id='employeeForm' class='selectForm' method='post' action='view.php'>";
				echo "<label for='employee'>Employee: </label>";
				echo "<select name='employee' id='employee' onChange='viewMode(0)' title='This allows you to view test and group information for a specific employee'>";
				employeeFillSelected($employee,$area);
				echo "</select>";
				echo "</form>";
			}
			?>
			
			<h2><?php echo nameByNetID($employee); ?></h2>
			
			<table>
				<th>Group</th><th>Start Date</th><th>End Date</th>
				<?php
				try {
					$groupQuery = $db->prepare("SELECT assessmentsGroup.ID, assessmentsGroup.name, assessmentsEmployeeGroupList.employee, assessmentsEmployeeGroupList.group, assessmentsEmployeeGroupList.startDate, assessmentsEmployeeGroupList.endDate FROM assessmentsGroup RIGHT JOIN assessmentsEmployeeGroupList ON assessmentsGroup.ID = assessmentsEmployeeGroupList.group WHERE assessmentsEmployeeGroupList.employee = :employee ORDER BY assessmentsEmployeeGroupList.startDate");
					$groupQuery->execute(array(':employee' => $employee));
				} catch(PDOException $e) {
					exit("error in query");
				}
				/* Dynamically create table for employee with their groups information. */
				while($row = $groupQuery->fetch(PDO::FETCH_ASSOC))
				{
					echo "<tr>";
					echo "<td>".$row['name']."</td><td>".$row['startDate']."</td>";
					if($row['endDate'] == '0000-00-00') // If they are still part of the group, don't display an end date.
					{
						echo "<td>-</td>";
					}
					else
					{
						echo "<td>".$row['endDate']."</td>";
					}
					echo "</tr>";
				}
				?>
			</table>
	
			<h2>Test Results</h2>
			
			<table>
				<th>Test</th><th>Date</th><th>Attempt</th><th>Grader</th><th>Score</th><th>Result</th><th>Notes</th>
				<?php
				/* Dynamically create table for employee test results. */
				try {
					$testsQuery = $db->prepare("SELECT * FROM `assessmentsTest` RIGHT JOIN `assessmentsResults` ON assessmentsResults.test = assessmentsTest.ID WHERE assessmentsResults.employee = :employee AND assessmentsTest.quizFlag = 0");
					$testsQuery->execute(array(':employee' => $employee));
				} catch(PDOException $e) {
					exit("error in query");
				}

				while($row = $testsQuery->fetch(PDO::FETCH_ASSOC))
				{
					echo "<tr>";
					echo "<td>".$row['name']."</td>";
					echo "<td>".$row['date']."</td>";
					echo "<td>".$row['attempt']."</td>";
					echo "<td>".nameByNetID($row['grader'])."</td>";
					echo "<td>".$row['score']."</td>";
					if($row['passed'] == 1)
					{
						echo "<td>Passed</td>";
					}
					else
					{
						echo "<td>Failed</td>";
					}
					echo "<td>".$row['notes']."</td>";
					echo "</tr>";
				}
				?>
			</table>
			
			<h2>Quiz Results</h2>
			<table>
				<th>Test</th><th>Date</th><th>Attempt</th><th>Grader</th><th>Score</th><th>Result</th><th>Notes</th>
			<?php
			try {
				$assessmentsQuery = $db->prepare("SELECT * FROM `assessmentsTest` RIGHT JOIN `assessmentsResults` ON assessmentsResults.test = assessmentsTest.ID WHERE assessmentsResults.employee = :employee  AND assessmentsTest.quizFlag = 1");
				$assessmentsQuery->execute(array(':employee' => $employee));
			} catch(PDOException $e) {
				exit("error in query");
			}
			
				while($row = $assessmentsQuery->fetch(PDO::FETCH_ASSOC))
				{
					echo "<tr>";
					echo "<td>".$row['name']."</td>";
					echo "<td>".$row['date']."</td>";
					echo "<td>".$row['attempt']."</td>";
					echo "<td>".nameByNetID($row['grader'])."</td>";
					echo "<td>".$row['score']."</td>";
					if($row['passed'] == 1)
					{
						echo "<td>Passed</td>";
					}
					else
					{
						echo "<td>Failed</td>";
					}
					echo "<td>".$row['notes']."</td>";
					echo "</tr>";
				}
				?>
			</table>
		</div>
		
		<?php
		if($permission) // Only display groups information if the current user has permission.
		{
			/* Dynamically create a drop down select for the availible groups. */
			echo "<div id='groupResults'>
			<form id='groupForm' class='selectForm' method='post' action='view.php'>
				<label for='group'>Group </label>
				<select name='group' id='group' onChange='viewMode(1)' title='This gives you employee and status information for a specific group'>
					<option value=''>Please Select a Group</option>";
					try {
						$groupQuery = $db->prepare("SELECT * FROM `assessmentsGroup` WHERE `deleted`='0' ORDER BY `name` ASC");
						$groupQuery->execute();
					} catch(PDOException $e) {
						exit("error in query");
					}
					while($row = $groupQuery->fetch(PDO::FETCH_ASSOC))
					{
						if($row['name'] == $group)
						{
							echo "<option value='".$row['name']."' selected='selected'>".$row['name']."</option>";
						}
						else
						{
							echo "<option value='".$row['name']."'>".$row['name']."</option>";
						}
					}
				echo "</select>
			</form>
			
			<h2>".$group." Group</h2>
			
			<table>
				<th>Employees</th><th>Tests</th><th>Completed</th>
				<tr>";
					/* Get the number of employees that are a part of the current group. */
					try {
						$employeeCountQuery = $db->prepare("SELECT COUNT(assessmentsEmployeeGroupList.ID) FROM `assessmentsGroup` RIGHT JOIN `assessmentsEmployeeGroupList` ON assessmentsEmployeeGroupList.group = assessmentsGroup.ID WHERE assessmentsGroup.name = :group AND assessmentsEmployeeGroupList.endDate = '0000-00-00'");
						$employeeCountQuery->execute(array(':group' => $group));
						$groupListQuery = $db->prepare("SELECT assessmentsEmployeeGroupList.employee, assessmentsEmployeeGroupList.startDate FROM `assessmentsGroup` RIGHT JOIN `assessmentsEmployeeGroupList` ON assessmentsEmployeeGroupList.group = assessmentsGroup.ID WHERE assessmentsGroup.name = :group AND assessmentsEmployeeGroupList.endDate = '0000-00-00'");
						$groupListQuery->execute(array(':group' => $group));
					} catch(PDOException $e) {
						exit("error in query");
					}	
					
					/* Get the number of required tests for the current group. */
					try {
						$requiredTestsCountQuery = $db->prepare("SELECT COUNT(assessmentsGroupRequiredTests.ID) FROM `assessmentsGroup` RIGHT JOIN `assessmentsGroupRequiredTests` ON assessmentsGroupRequiredTests.group = assessmentsGroup.ID WHERE assessmentsGroup.name = :group");
						$requiredTestsCountQuery->execute(array(':group' => $group));
						$requiredTestsQuery = $db->prepare("SELECT assessmentsGroupRequiredTests.test FROM `assessmentsGroup` RIGHT JOIN `assessmentsGroupRequiredTests` ON assessmentsGroupRequiredTests.group = assessmentsGroup.ID WHERE assessmentsGroup.name = :group");
						$requiredTestsQuery->execute(array(':group' => $group));
					} catch(PDOException $e) {
						exit("error in query");
					}	

					$testCountResult = $requiredTestsCountQuery->fetch(PDO::FETCH_NUM);
					$employeeCountResult = $employeeCountQuery->fetch(PDO::FETCH_NUM);	
					$numberOfTests = $testCountResult[0];
					$numberOfEmployees = $employeeCountResult[0];	
					/* For each member of the group, and for each required test, get the number of tests passed off. */
					while($rowEmployees = $groupListQuery->fetch(PDO::FETCH_ASSOC))
					{	
						while($rowTests = $requiredTestsQuery->fetch(PDO::FETCH_ASSOC))
						{
							try {
								$testsQuery = $db->prepare("SELECT assessmentsGroupRequiredTests.test FROM `assessmentsGroup` RIGHT JOIN `assessmentsGroupRequiredTests` ON assessmentsGroupRequiredTests.group = assessmentsGroup.ID WHERE assessmentsGroup.name = :group");
								$testsQuery->execute(array(':group' => $group));
							} catch(PDOException $e) {
								exit("error in query");
							}	
							
							if($testsQuery->fetch())
							{
								$totalPassed += 1;
							}
						}
					}

					echo "<td>".$numberOfEmployees."</td>";
					echo "<td>".$numberOfTests."</td>";

					if($numberOfEmployees == 0 || $numberOfTests == 0) // Check for divide by zero.
					{
						$percentComplete = 'NA'; 
					}
					else
					{
						$percentComplete = $totalPassed / ($numberOfEmployees * $numberOfTests); // Calculate the percentage of tests that have been passed off by a group.
					}
					
					echo "<td>".$percentComplete."</td>";
			echo "</table>
			
			<h2>Group Employees</h2>
			
			<div id='accordion'>";
				if($group != '' && $numberOfEmployees > 0) //Check to make sure variables exist.
				{
					try {
						$groupListQuery = $db->prepare("SELECT assessmentsEmployeeGroupList.employee, assessmentsEmployeeGroupList.startDate FROM `assessmentsGroup` RIGHT JOIN `assessmentsEmployeeGroupList` ON assessmentsEmployeeGroupList.group = assessmentsGroup.ID WHERE assessmentsGroup.name = :group AND assessmentsEmployeeGroupList.endDate = '0000-00-00'");
						$groupListQuery->execute(array(':group' => $group)); // Reset internal pointer to resultant set.
					} catch(PDOException $e) {
						exit("error in query");
					}
					if($numberOfTests != 0) //Check to make sure variables exist.
					{
						try {
							$requiredTestsQuery = $db->prepare("SELECT assessmentsGroupRequiredTests.test FROM `assessmentsGroup` RIGHT JOIN `assessmentsGroupRequiredTests` ON assessmentsGroupRequiredTests.group = assessmentsGroup.ID WHERE assessmentsGroup.name = :group");
							$requiredTestsQuery->execute(array(':group' => $group)); // Reset internal pointer to resultant set.
						} catch(PDOException $e) {
							exit("error in query");
						}
					}
				}
				
				/* Get information for each employee of the group and create a jQuery accordian for each employee. */
				while($rowEmployees = $groupListQuery->fetch(PDO::FETCH_ASSOC))
				{
					$employeeNumberPassedTests = 0; // Reset variables.
					$employeeNumberTestAttempts = 0; // Reset variables.
					
					/* Get test results for each required test. */
					while($rowTests = $requiredTestsQuery->fetch(PDO::FETCH_ASSOC))
					{
						try {
							$resultsQuery = $db->prepare("SELECT * FROM `assessmentsResults` WHERE `employee` = :employee AND `test` = :test");
							$resultsQuery->execute(array(':employee' => $rowEmployees['employee'], ':test' => $rowTests['test'])); // Reset internal pointer to resultant set.
						} catch(PDOException $e) {
							exit("error in query");
						}
							
						while($row = $resultsQuery->fetch())
						{
							if($row->passed == 1)
							{
								$employeeNumberPassedTests += 1; // Add one if test was passed.
							}
								
							$employeeNumberTestAttempts += 1; // Always every results is an attempt, whether passed or not.
						}
							
					}
					
					echo "<h3><a href='#'><b class='accordionHeader'>".nameByNetID($rowEmployees['employee'])."</b> <b class='accordionHeader'>Start Date: ".$rowEmployees['startDate']."</b> <b class='accordionHeader'>Passed: ".$employeeNumberPassedTests."</b> <b class='accordionHeader'>Attempts: ".$employeeNumberTestAttempts."</b></a></h3>"; // Jquery UI accordian.
					
					/* Create table of test results. */
					echo "<div>";
					echo "<table>";
					echo "<th>Name</th><th>Attempt</th><th>Grader</th><th>Score</th><th>Passed</th><th>Notes</th>";
					
					/* Get test results from database. */
					try {
						$resultsQuery = $db->prepare("SELECT * FROM `assessmentsResults` RIGHT JOIN `assessmentsGroupRequiredTests` ON assessmentsResults.test = assessmentsGroupRequiredTests.test RIGHT JOIN `assessmentsGroup` ON assessmentsGroupRequiredTests.group = assessmentsGroup.ID WHERE assessmentsResults.employee = :employee AND assessmentsGroup.name = :group GROUP BY assessmentsResults.ID");
						$resultsQuery->execute(array(':employee' => $rowEmployees['employee'], ':group' => $group));
					} catch(PDOException $e) {
						exit("error in query");
					}
					
					while($row = $resultsQuery->fetch(PDO::FETCH_ASSOC))
					{
						echo "<tr>";
						echo "<td>".$row['name']."</td>";
						echo "<td>".$row['attempt']."</td>";
						echo "<td>".nameByNetID($row['grader'])."</td>";
						echo "<td>".$row['score']."</td>";
						if($row['passed'] == 1)
						{
							echo "<td>Passed</td>";
						}
						else
						{
							echo "<td>Failed</td>";
						}
						echo "<td>".$row['notes']."</td>";
						echo "</tr>";
					}
					
					echo "</table>";
					echo "</div>";
				}
			echo "</div>
		</div>";
		}
		?>
	</div>
</div>
