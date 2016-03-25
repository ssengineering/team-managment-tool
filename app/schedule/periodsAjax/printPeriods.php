<?php 
//deleteTeam.php
require('../../includes/includeMeBlank.php');

function pullShiftPeriods($area){
	global $db;
		try {
			$scheduleQuery = $db->prepare("SELECT * FROM `scheduleSemesters` WHERE `area`=:area AND `endDate` >= DATE_SUB(NOW(), INTERVAL 1 YEAR) ORDER BY startDate DESC");
			$scheduleQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo "<table class='imagetable'style='margin:auto;'><tr><th>Period</th><th>Name</th><th>Start Date</th><th>End Date</th><th>Locked</th></tr><tr>";
		while($right = $scheduleQuery->fetch(PDO::FETCH_ASSOC)) {
		   $lockChecked = '';
		   if ($right['locked'])
		   {
					$lockChecked = 'checked';
		   }
		      echo '<input type="hidden" name="'.$right['ID'].'changed" value="0" />';
			    echo "<td><input type='text' name='".$right['ID']."' style='width:160px' value='".$right['semester']."' /></td>";
			    echo '<td><input type="text" name="'.$right['ID'].'name" style="width:300px" value="'.$right['name'].'" /></td>';
			    echo "<td><input type='text' name='".$right['ID']."start' style='width:110px' value='".$right['startDate']."' id='".$right['ID']."start' class='datepicker' onchange='trackDate()'/>";
					
			    echo "</td>";
			    echo "<td><input type='text' name='".$right['ID']."end' style='width:100px' value='".$right['endDate']."' id='".$right['ID']."end' class='datepicker'onchange='trackDate()'/></td>";
					
					echo "<td style='text-align: center; vertical-align: middle;'><input type='checkbox' name='locked[]' value='".$right['ID']."' id='locked".$right['ID']."' ".$lockChecked." />";
					
			    echo "</td></tr>";
		}
		
		echo "</table>";
	}
    
    function semesterSelect($area){
		global $db;
		try {
			$semestersQuery = $db->prepare("SELECT * FROM scheduleSemesters WHERE area = :area");
			$semestersQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
        while($type = $semestersQuery->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='".$type['ID']."'/>".$type['name']."</option>";   
        }
    }

	pullShiftPeriods($area);
	echo "<br/>"; 
?>
