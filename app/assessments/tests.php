<?php
/*
*	Name: tests.php
*	Application: Assessments
*	Site: ops.byu.edu
*	Author: Joshua Terrasas
*
*	Description: This is the 'Tests' mode of the Assessments app.
*	Tests mode is used to add a test to the database, or edit an
*	excisting test. It uses a simple form to enter the needed in-
*	formation about the test, and uses AJAX calls to reload the
*	page to check the information given. The page checks for dup-
*	licates in the database, and validates the information. The
*	editing feature is basically just the same as the form used
*	to add a new test, but is preloaded with information, and
*	saves over the old test in the database.
*/

require('../includes/includeMeBlank.php');

$permission = can("update", "3e80eb8f-3628-4b29-bf35-fbc7749c0a80");//Assessments resource (from assessmentsEdit)
?>

<div id="modeContent">
	<?php
	if(!$permission)
	{
		echo "<h2>You do not permission to view this page.</h2>";
		
		return;
	}
	
	if(isset($_GET['test']))
	{
		$testID = $_GET['test'];
	}
	else
	{
		$testID = '';
	}
	
	$testOptions = "<option value='defaultOption'>Please select a test</option>";
	
	try {
		$testsQuery = $db->prepare("SELECT * FROM `assessmentsTest` WHERE `deleted`='0' ORDER BY `name` ASC");
		$testsQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	while($row = $testsQuery->fetch(PDO::FETCH_ASSOC))
	{	
		if($row['ID'] == $testID)
		{
			$testOptions .= "<option id='testOption".$row['ID']."' value='".$row['ID']."' selected='selected'>".$row['name']."</option>";
		}
		else
		{
			$testOptions .= "<option id='testOption".$row['ID']."' value='".$row['ID']."'>".$row['name']."</option>";
		}
	}
	
	if($testID != '')
	{
		try {
			$testQuery = $db->prepare("SELECT * FROM `assessmentsTest` WHERE `ID` = :id");
			$testQuery->execute(array(':id' => $testID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$row = $testQuery->fetch(PDO::FETCH_ASSOC);
		
		$testName = $row['name'];
		$testTimePeriod = $row['timePeriod'];
		$testPoints = $row['points'];
		$testPassingPercentage = $row['passingPercentage'];
	}
	?>
	
	<div id='tabs'>
		<ul>
			<li><a href="#addTest">Add Test</a></li>
			<li><a href="#editTest">Edit Test</a></li>
		</ul>
		
		<div id='addTest'>
			<h2>Add Test</h2>
			
			<form id='testAddForm' method='post' action='submit.php' onSubmit='return validateForm("testMode", "addTest")'>
				<input type='hidden' name='type' id='submitType' value='testMode'>
				<input type='hidden' name='tab' id='submitTab' value='addTest'>
				
				<table>
					<th>Name</th><?php echo (($area==4)?"":"<th>Time Period</th>") ?><th>Points</th><th>Passing Percentage</th><th>Type</th>
				
					<tr>
						<td><input type='text' name='testName' id='testName' title='Enter a name for the new test'></td>
						<?php echo (($area==4)?"":"<td><input type='text' name='testTimePeriod' id='testTimePeriod' size=10 title='Enter how many days after joining the group the employee has to pass the test'></td>") ?>
						<td><input type='text' name='testPoints' id='testPoints' size=4 title='Enter the number of points possible on the test'></td>
						<td><input type='text' name='testPassingPercentage' id='testPassingPercentage' size=20 onClick='testsModeAddTestSlider()' title='Choose what the minimum passing percentage is'></td>
						<td><select id="testType" name='testType'>
								<option>Test</option>
								<option>Quiz</option>
							</select>	
						</td>
					</tr>
				</table>
				
				<input type='submit' value='Submit' />
			</form>
			
			<div id='addSliderDialog'>
				<label for='addPercent'>Percent: </label><span name='addPercent' id='addPercent'>0%</span>
				
				<div id='addSlider'>
				</div>
			</div>
		</div>
		
		<div id='editTest'>
			<h2>Edit Test</h2>
			
			<select name='test' id='test' onChange='testsMode("edit")' title='Select a test to edit'>
				<?php
				echo $testOptions;
				?>
			</select>
			
			<?php
			if($testID != '')
			{
				echo "<h2></h2>
				
				<form id='testEditForm' method='post' action='submit.php' onSubmit='return validateForm(\"testMode\", \"editTest\")'>
					<input type='hidden' name='type' id='submitType' value='testMode'>
					<input type='hidden' name='tab' id='submitTab' value='editTest'>
					<input type='hidden' name='testID' id='testID' value='".$testID."'>
				
					<table>
						<th>Name</th><th>Time Period</th><th>Points</th><th>Passing Percentage</th>
				
						<tr>
							<td><input type='text' name='testEditName' id='testEditName' value='".$testName."' title='The name of the test'></td>
							<td><input type='text' name='testEditTimePeriod' id='testEditTimePeriod' size=10 value='".$testTimePeriod."' title='How many days the employee has to pass the test'></td>
							<td><input type='text' name='testEditPoints' id='testEditPoints' size=4 value='".$testPoints."' title='How many points are possible on the test'></td>
							<td><input type='text' name='testEditPassingPercentage' id='testEditPassingPercentage' size=20 value='".$testPassingPercentage."' onClick='testsModeEditTestSlider()' title='The minimum passing percentage for the test'></td>
						</tr>
					</table>
				
					<input type='submit' value='Submit' /> <input type='button' id='testsDeleteButton' name='testsDeleteButton' value='Remove Test' onClick='testsModeDeleteDialog()'>
				</form>";
			}
			?>
			
			<div id='editSliderDialog'>
				<label for='editPercent'>Percent: </label><span name='editPercent' id='editPercent'>0%</span>
				
				<div id='editSlider'>
				</div>
			</div>
		</div>
	</div>
</div>
