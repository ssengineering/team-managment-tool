<?php
require('../includes/includeMeBlank.php');

$permission = can("grade", "3e80eb8f-3628-4b29-bf35-fbc7749c0a80"); //Assessments resource
?>

<div id="modeContent">
	<?php
	if(!$permission)
	{
		echo "<h2>You do not have permission to view this page.</h2>";
		
		return;
	}
?>

	<h2>Grade Employee Test</h2>

	<form id='gradeForm' method='post' action='submit.php'>
		<input type='hidden' name='type' id='submitType' value='gradeMode'>
		<span> Enter the number of rows to be viewed: </span> 
		<input type="text" id="numberOfRows" size=3 onchange="updateNumberOfRows();">
		<br>
		<br>
		
		<div id="gradeRow">
		<table id="table1">
			<th class="ui-state-default">Employee</th><th class="ui-state-default">Test/Quiz</th><?php echo (($area==10)?"":"<th class='ui-state-default'>Date</th>") ?><th class="ui-state-default">Score</th><th class="ui-state-default">Result</th><th class="ui-state-default">Notes</th><th class="ui-state-default"></th>
			<tr>
				<td>
					<select name='employee1' id='employee1' onChange="fillTestSelect('employee1', 'test1');validateRows('date',1)" title='Select which employee took the test you are grading'>
						<?php
						if(isset($employee))
						{
							employeeFillSelected($employee,$area);
						}
						else
						{
							employeeFill($area);
						}
						?>
					</select>
				</td>
				
				<td>
					<select name='test1' id='test1' onchange='enableNext("date1");validateRows("score",1);validateRows("date",1)' disabled="disabled" title='Select which test the employee took'>
						<option value="defaultOption">Please select a test</option>
					</select>
				</td>
			
				<?php echo (($area==10)?"":"<td><input type='text' name='date1' id='date1' onchange='enableNext(\"score1\");validateRows(\"date\", 1)' disabled=\"disabled\" size=8 placeholder='Y-M-D' title='Enter the date the test was taken'/>
				</td>") ?>
				
				<td><input type='text' name='score1' id='score1' size=2 onchange = "enableNext('resultPass1', 'resultFail1');validateRows('score', 1);" disabled="disabled" onKeyPress='return disableEnterKey(event)' title='Enter the score received'/>
					<?php
					if(isset($testMaxPoints))
					{
						echo "<div>out of $testMaxPoints</div>";
					}
					?>
				</td>
				
				<td>
					<input type='radio' name='result' id='resultPass1' value='1' disabled="disabled" onchange = "enableNext('notes1');validateRows('score', 1)" title='Select if the employee passed the test'/>
					<label for='resultPass1'>Pass</label>
					<br />
					<input type='radio' name='result' id='resultFail1' value='0' disabled="disabled" onchange = "enableNext('notes1');validateRows('score', 1)" title='Select if the employee did not pass the test'/>
					<label for='resultFail1'>Fail</label>
				</td>
				
				<td><textarea name='notes' id='notes1' cols='10' rows='4' disabled="disabled" title='Enter any notes you want to make for this test'></textarea></td>
				<td style="padding:0px 0px 0px 0px;" class="ui-state-default"><div style="-webkit-transform:rotate(90deg);transform:rotate(90deg);-ms-transform:rotate(90deg);margin: 20px 0px 0px 10px !important;"><br><b>Row 1</b></div></td>
			</tr>
		</table>
		</div>
		
		<?php
		if(isset($test))
		{
			echo "<input type='hidden' id='testMaxPoints' value='$testMaxPoints'>";
			echo "<input type='hidden' id='testPassingPercentage' value='$testPassingPercentage'>";
			echo "<input type='hidden' name='attempt' id='testAttempts' value='$currentAttempt'>";
			echo "<input type='hidden' name='dateException' id='dateException' value='$dateException'>";
		}
		if(isset($testDatePassed))
		{
			echo "<input type='hidden' id='testDatePassed' value='$testDatePassed'>";
		}
		?>
		
		<div class='toggler'>
			<div id='dateErrorText' class='warningText ui-widget-content ui-corner-all'>
				<h3 class='ui-widget-header ui-corner-all'>Warning: Date</h3>
				
				<!--"<p>The date you entered ($testDate) takes place before the employee joined the $group group ($testGroupStartDate). You must enter a date that takes place after the employee joined the group, or change the date the employee joined the $group group.</p>"; -->
			</div>
		</div>
		
		<div class='toggler'>
			<div id='dateWarningText' class='warningText ui-widget-content ui-corner-all'>
				<h3 class='ui-widget-header ui-corner-all'>Warning: Date</h3>
				
				<!--"<p>According to the date entered, the employee took this test <span id='warningTextDays'>$timeDifference->d</span> days after joining the <span id='warningTextGroup'>$group</span> group (<span id='warningTextRequiredDays'>$timePeriod->d</span> days is the requirement). You may still override this by setting the 'Result' to 'Pass', but please note why the exception was made. If an exception is made, a note will be automatically placed in the notes field, along with your notes.</p>"; -->
			</div>
		</div>

		<div class='toggler'>
			<div id='scoreWarningText' class='warningText ui-widget-content ui-corner-all'>
				<h3 class='ui-widget-header ui-corner-all'>Warning: Score</h3>
			
				<!--"<p>According to the points entered, the employee received <span id='warningTextPercent'></span>. This results in a failing grade (the test requires a $testPassingPercentage). You may still override this by setting the 'Result' to 'Pass', but please note why the exception was made. If an exception is made, a note will be automatically placed in the notes field, along with your notes.</p>"; -->
			</div>
		</div>
		
		<input type='button' value='Submit' onclick="submitGameModeRows()" />
	</form>
</div>

