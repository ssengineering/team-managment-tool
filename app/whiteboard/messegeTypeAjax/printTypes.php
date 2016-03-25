<?php //printTypes.php
//used to print white board messege types via ajax
require('../../includes/includeMeBlank.php');
    function pullMsgTypes($area){
		global $db;
		try {
			$tagQuery = $db->prepare("SELECT * FROM tag WHERE area=:area ORDER BY typeName ASC");
			$tagQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo "<table class='imagetable'style='margin:auto;'><tr><th>Type Name</th><th>Color</th><th>Must Approve?</th></tr><tr>";
		while($right = $tagQuery->fetch(PDO::FETCH_ASSOC)) {
			    echo "<td><input maxlength='30' type='text' name='".$right['typeId']."' style='width:200px' value='".$right['typeName']."' /></td>";
			    echo "<td bgcolor='".$right['color']."'><input maxlength='20' type='text' name='".$right['typeId']."color' style='width:100px' value='".$right['color']."' /></td>";
			    echo "<td style='text-align: center; vertical-align: middle;'><input type='checkbox' name='${right['typeId']}mustApprove' ".($right['mustApprove'] == '1'? 'checked="checked"':'')." style='vertical-align: middle;' /></td>";
					echo "</td></tr>";
		}
		
		echo "</table>";
	}

    function msgTypeSelect($area){
		global $db;
		try {
			$tagQuery = $db->prepare("SELECT * FROM tag WHERE area = :area ORDER BY typeName ASC");
			$tagQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}

        while($type = $tagQuery->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='".$type['typeId']."'/>".$type['typeName']."</option>";
        }
    }

if(can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936")/*whiteboard resource*/){
	pullMsgTypes($area);
	echo "<br/>";
	echo "<input type='button' class='button' name='deleteHour' value='Remove:' onclick='deleteType()' />";
   echo "<select name='msgTypes' id='msgTypes'>";
   msgTypeSelect($area);
   echo "</select>";

} ?>
