<?php
/*
*	Name: groups.php
*	Application: Assessments
*	Site: ops.byu.edu
*	Author: Joshua Terrasas
*
*	Description: This is the 'Groups' mode of the Assessments app.
*	Groups mode is used for adding groups, editing what groups
*	employees are a part of, and what tests are required for each
*	group. Like the other pages in the Assessments app, Groups mode
*	uses AJAX calls to reload the page with the correct informaiton
*	based on user input. Groups control what tests an employee can
*	take, and how long they have to pass them off.
*/

require('../includes/includeMeBlank.php'); // Standard include for OPS website, but this version does not display a header. Used for global variables.

$permission = can("update", "3e80eb8f-3628-4b29-bf35-fbc7749c0a80"); // Permissions check. See if user has grading rights. //Assessments resource
?>
	
<div id="modeContent">
	<?php
	if(!$permission) // Permissions check.
	{
		echo "<h2>You do not have permission to view this page.</h2>";
		
		return;
	}
	
	if(isset($_GET['employee']))
	{
		$employee = $_GET['employee'];
	}
	else
	{
		$employee = $netID;
	}
	
	if(isset($_GET['group']))
	{
		$group = $_GET['group'];
	}
	?>
	<!--jQuery UI Tabs-->
	<div id='tabs'>
		<ul>
			<li><a href="#addGroup">Add Group</a></li>
			<li><a href="#groupMembers">Group Members</a></li>
			<li><a href="#groupTests">Required Tests</a></li>
		</ul>
		
		<div id='addGroup'>
			<!--Form to add a group to the database. JavaScript checks for invalid entries and doubles-->
			<form id='groupsAddForm' method='post' action='submit.php' onSubmit='return validateForm("groupMode", "addGroup")'>
				<input type='hidden' name='type' id='submitType' value='groupsMode'>
				<input type='hidden' name='tab' id='submitTab' value='addGroup'>
				
				<label for='groupName'>Name: </label>
				<input type='text' name='groupName' id='groupName' size=15 title='Enter the name for the new group'/>
				<input type='submit' value='Submit' /> <input type='button' id='groupsDeleteButton' name='groupsDeleteButton' value='Remove Group' onClick='groupsModeDeleteDialog()'>
			</form>
			
			<br />
			
			<?php
			echo "<h3>Current Groups</h3>";
			echo "<ul>";
			try {
				$groupQuery = $db->prepare("SELECT * FROM `assessmentsGroup` WHERE `deleted` = '0'");
				$groupQuery->execute();
			} catch(PDOException $e) {
				exit("error in query");
			}

			while($row = $groupQuery->fetch())
			{
				echo "<li class='groupsList'>".$row->name."</li>";
			}
			echo "</ul>";
			?>
			
			<div id='groupsDeleteDialog' title='Remove Group'>
				<form>
				<?php
				try {
					$groupQuery->execute();
				} catch(PDOException $e) {
					exit("error in query");
				}
				
				while($row = $groupQuery->fetch(PDO::FETCH_ASSOC))
				{
					echo "<input type='radio' id='groupRemoveRadio' name='groupRemoveRadio' value=".$row['ID'].">".$row['name']."<br>";
				}
				?>
				</form>
				
				<p>**Removing a group will also remove all of it's members from the group, and set their removal date to today**</p>
			</div>
		</div>
		
		<div id='groupMembers'>
			<form id='groupMembersForm' method='post' action='submit.php'>
				<label for='employee'>Employee: </label>
				<select name='employee' id='employee' onChange='groupsMode("members")' title='Select an employee to edit group membership'>
					<?php
					employeeFillSelected($employee, $area);
					?>
				</select>
				
				<?php
				
				$groupCounter = 0;
				
				echo "<h3>Groups</h3>";
				
				echo "<div class='groupList'>";
				
				try {
					$groupsQuery = $db->prepare("SELECT * FROM `assessmentsGroup`");
					$groupsQuery->execute();
				} catch(PDOException $e) {
					exit("error in query");
				}
				
				while($row = $groupsQuery->fetch(PDO::FETCH_ASSOC))
				{	
					echo "<dl class='listContainer'>";
					
					try {
						$listQuery = $db->prepare("SELECT * FROM `assessmentsEmployeeGroupList` WHERE `group` = :group AND `employee` = :employee AND `endDate` = '0000-00-00'");
						$listQuery->execute(array(':group' => $row['ID'], ':employee' => $employee));
					} catch(PDOException $e) {
						exit("error in query");
					}
					$memberGroup = $listQuery->fetch(PDO::FETCH_ASSOC);
					
					if(isset($memberGroup['ID']))
					{
						echo "<input type='hidden' name='groupInstance".$memberGroup['group']."' id='submitGroupInstance".$memberGroup['group']."' value='".$memberGroup['ID']."'>";
						echo "<dt>";
						echo "<input type='checkbox' name='group".$groupCounter."' id='group".$memberGroup['group']."' class='groupCheckbox' value='".$memberGroup['group']."' onChange=\"groupsModeSelectGroup(".$memberGroup['group'].")\" checked='checked' title='Check this box to remove the employee from the ".$row['name']." group'><span>".$row['name']."</span>";
						echo "</dt>";
						echo "<dd id='groupJoinDate".$memberGroup['group']."' class='groupMemberJoinDate'>Join Date: ".$memberGroup['startDate']."</dd>";
					}
					else
					{
						echo "<dt>";
						echo "<input type='checkbox' name='group".$groupCounter."' id='group".$row['ID']."' class='groupCheckbox' value='".$row['ID']."' onChange=\"groupsModeSelectGroup(".$row['ID'].")\" title='Check this box to add the employee to the ".$row['name']." group'><span>".$row['name']."</span>";
						echo "</dt>";
					}
					
					echo"</dl>";
					
					$groupCounter++;
				}
				
				echo "</div>";
				?>
			</form>
			
			<div id='groupSelectDialog' title="Add to Group">
				<form id='groupMembersAddForm' method='get' action='submit.php?type=groupsMode&tab=groupMembers&action=add'>
					<input type='hidden' name='group' id='groupMembersDialogGroupID' value=''>
					<p>Please enter the date this employee joined the group.</p>
					
					<label for='dialogStartDate'>Join Date: </label>
					<input type='text' name='dialogStartDate' id='dialogStartDate' size=8 value='<?php echo date('Y-m-d'); ?>' title='The `Join Date` affects when the employee is able to start taking tests for this group'/>
				</form>
			</div>
			
			<div id='groupDeselectDialog' title="Remove from Group">
				<form id='groupMembersRemoveForm' method='get' action='submit.php'>
					<input type='hidden' name='ID' id='groupMembersDialogID' value=''>
					<p>Please enter the date this employee was removed from the group.</p>
					
					<label for='dialogEndDate'>End Date: </label>				
					<input type='text' name='dialogEndDate' id='dialogEndDate' size=8 value='<?php echo date('Y-m-d'); ?>' title='Enter the date the employee left the group'/>
				</form>
			</div>
		</div>
		
		<div id='groupTests'>
			<form id='groupTestsForm' class='selectForm' method='post' action='groups.php'>
				<label for='group'>Group </label>
				<select name='group' id='group' onChange='groupsMode("tests")' title='Select a group to edit which tests are required'>
					<option value=''>Please Select a Group</option>
					<?php
					try {
						$groupQuery = $db->prepare("SELECT * FROM `assessmentsGroup` ORDER BY `name` ASC");
						$groupQuery->execute();
					} catch(PDOException $e) {
						exit("error in query");
					}
					while($row = $groupQuery->fetch(PDO::FETCH_ASSOC))
					{
						if($row['name'] == $group)
						{
							echo "<option value='".$row['name']."' selected='selected'>".$row['name'].	"</option>";
							
							$groupID = $row['ID'];
						}
						else
						{
							echo "<option value='".$row['name']."'>".$row['name']."</option>";
						}
					}
					?>
				</select>
				
				<?php
				if(isset($group))
				{
					echo "<h2>".$group." Group Required Tests</h2>";
					
					echo "<input type='hidden' name='groupID' id='groupID' value='".$groupID."'>";
					
					try {
						$testsQuery = $db->prepare("SELECT * FROM `assessmentsTest` ORDER BY `name` ASC");
						$testsQuery->execute();
					} catch(PDOException $e) {
						exit("error in query");
					}
					while($row = $testsQuery->fetch(PDO::FETCH_ASSOC))
					{
						$printed = false;
						
						echo "<dl class='listContainer'>";
						
						try {
							$nameQuery = $db->prepare("SELECT assessmentsGroup.name, assessmentsGroupRequiredTests.test, assessmentsGroupRequiredTests.ID FROM `assessmentsGroup` 
												   RIGHT JOIN `assessmentsGroupRequiredTests` ON assessmentsGroup.ID = assessmentsGroupRequiredTests.group WHERE 
												   assessmentsGroup.name = :group AND assessmentsGroupRequiredTests.deleted = '0'");
							$nameQuery->execute(array(':group' => $group));
						} catch(PDOException $e) {
							exit("error in query");
						}
						
						while($requiredTest = $nameQuery->fetch(PDO::FETCH_ASSOC))
						{
							if($row['ID'] == $requiredTest['test'])
							{
								echo "<input type='hidden' name='testInstance".$row['ID']."' id='submitTestInstance".$row['ID']."' value='".$requiredTest['ID']."'>";
								echo "<dt>";
								echo "<input type='checkbox' name='test".$row['ID']."' id='test".$row['ID']."' value='".$row['ID']."' onChange='groupsModeSelectTest(".$row['ID'].")'  checked='checked' title='This will remove the ".$row['name']." test from the ".$group." group'><span>".$row['name']."</span>";
								echo "</dt>";
								
								$printed = true;
							}
						}
						
						if($printed == false)
						{
							echo "<dt>";
							echo "<input type='checkbox' name='test".$row['ID']."' id='test".$row['ID']."' value='".$row['ID']."' onChange='groupsModeSelectTest(".$row['ID'].")' title='This will add the ".$row['name']." test to the ".$group." group'><span>".$row['name']."</span>";
							echo "</dt>";
						}
					
						echo"</dl>";
					}
				}
				?>
			</form>
			
			<div id='testSelectDialog' title="Add Test to Group">
				<form id='testMembersAddForm' method='get' action='submit.php?type=groupsMode&tab=groupTests&action=add'>
					<input type='hidden' name='testID' id='groupTestsSelectDialogTestID' value=''>
					<p>Are you sure you want to add this test to the requirements for the group?</p>
				</form>
			</div>
			
			<div id='testDeselectDialog' title="Remove Test from Group">
				<form id='groupMembersRemoveForm' method='get' action='submit.php'>
					<input type='hidden' name='testID' id='groupTestsDeselectDialogTestID' value=''>
					<p>Are you sure you want to remove this test from the requirements for the group?</p>
				</form>
			</div>
		</div>
	</div>
</div>
