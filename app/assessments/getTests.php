<?php

require('../includes/includeMeBlank.php');

if (isset($_GET['employee']))
	{
		$employee = $_GET['employee'];
		$testOptions = "<option value='defaultOption'>Please select a test</option>";

		try {
			$groupQuery = $db->prepare("SELECT assessmentsGroup.name, assessmentsEmployeeGroupList.group FROM `assessmentsEmployeeGroupList` RIGHT JOIN `assessmentsGroup` ON 
										assessmentsEmployeeGroupList.group = assessmentsGroup.ID WHERE assessmentsEmployeeGroupList.employee = :employee AND 
										assessmentsEmployeeGroupList.endDate = '0000-00-00' ORDER BY assessmentsGroup.name ASC");
			$groupQuery->execute(array(':employee' => $employee));
		} catch(PDOException $e) {
			exit("error in query");
		}
	
		$resultExists = false;	
		while($rowEmployeeGroup = $groupQuery->fetch(PDO::FETCH_ASSOC))
		{
			$resultExists = true;
			$testOptions .= "<optgroup label='".$rowEmployeeGroup['name']."'>";

			try {
				$testQuery = $db->prepare("SELECT assessmentsTest.ID, assessmentsTest.name FROM `assessmentsGroupRequiredTests` RIGHT JOIN `assessmentsTest` ON 
										   assessmentsGroupRequiredTests.test = assessmentsTest.ID WHERE assessmentsGroupRequiredTests.group = :group AND 
										   assessmentsGroupRequiredTests.deleted = '0' ORDER BY assessmentsTest.name ASC");
				$testQuery->execute(array(':group' => $rowEmployeeGroup['group']));
			} catch(PDOException $e) {
				exit("error in query");
			}

			while($rowEmployeeGroupTest = $testQuery->fetch(PDO::FETCH_ASSOC))
			{	
				if($rowEmployeeGroupTest['ID'] == null)
				{
					$testOptions .= "<option value='".$rowEmployeeGroupTest['ID']."' selected='selected'>".$rowEmployeeGroupTest['name']."</option>";
				}
				else
				{
					$testOptions .= "<option value='".$rowEmployeeGroupTest['ID']."'>".$rowEmployeeGroupTest['name']."</option>";
				}
			}
		
			$testOptions .= "</optgroup>";
		}
		if(!$resultExists)
		{
			$testOptions = "<option value='defaultOption'>Not a member of any groups</option>";
		}
		echo $testOptions;
	}

?>
