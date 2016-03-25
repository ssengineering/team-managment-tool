<?php //file to print out the silent monitor
//require('../includes/includeMeSimple.php');

function showCalendar($form, $field){
		echo ("<script language=\"JavaScript\">
	
				//Code for calendar
				var d_today = new Date();
				d_today.setDate(d_today.getDate());
				var s_today = f_tcalGenerDate(d_today);

				new tcal ({
					// form name
					'formname': '".$form."',
					// input name
					'controlname': '".$field."',
					'today' : s_today
				});
				
				</script>");
	}

function printSelectMenu(){
	//This prints the options in the select boxes
	//Yes = 1, No = 0, Partial = 2
	echo "<option value='Yes'>Yes</option>";
	echo "<option value='No'>No</option>";
	echo "<option value='Partial'>Partial</option>";
}

function printLoadedSelectMenu($selected){
	//This prints the options in the select boxes
	if($selected == "Yes"){
		echo "<option value='Yes' selected>Yes</option>";
	} else {
		echo "<option value='Yes'>Yes</option>";
	}
	if($selected == "No"){
		echo "<option value='No' selected>No</option>";
	} else {
		echo "<option value='No'>No</option>";
	}
	if($selected == "Partial"){
		echo "<option value='Partial' selected>Partial</option>";
	} else {
		echo "<option value='Partial'>Partial</option>";
	}
	
}

function getRatingsBox($callNum,$num){
	echo "
		<table>
			<tr>
				<td align='center'><label class='ratingLabel' for='rating".$callNum."_1'>1</label></td>
				<td align='center'><label for='rating".$callNum."_2'>2</label></td>
				<td align='center'><label for='rating".$callNum."_3'>3</label></td>
				<td align='center'><label for='rating".$callNum."_4'>4</label></td>
				<td align='center'><label for='rating".$callNum."_5'>5</label></td>
			</tr>
			<tr>";
			if($num == 1){
				echo "<td><input type='radio' id='rating".$callNum."_1' name='rating[".$callNum."]' value='1' checked /></td>";
			}else{
				echo "<td><input type='radio' id='rating".$callNum."_1' name='rating[".$callNum."]' value='1' /></td>";
			}
			if($num == 2){
				echo "<td><input type='radio' id='rating".$callNum."_2' name='rating[".$callNum."]' value='2' checked /></td>";
			} else {				
				echo "<td><input type='radio' id='rating".$callNum."_2' name='rating[".$callNum."]' value='2' /></td>";
			}
			if($num == 3){
				echo "<td><input type='radio' id='rating".$callNum."_3' name='rating[".$callNum."]' value='3' checked /></td>";
			}else {
				echo "<td><input type='radio' id='rating".$callNum."_3' name='rating[".$callNum."]' value='3' /></td>";
			}
			if($num == 4){
				echo "<td><input type='radio' id='rating".$callNum."_4' name='rating[".$callNum."]' value='4' checked /></td>";
			} else {
				echo "<td><input type='radio' id='rating".$callNum."_4' name='rating[".$callNum."]' value='4' /></td>";
			}
			if($num == 5){
				echo "<td><input type='radio' id='rating".$callNum."_5' name='rating[".$callNum."]' value='5' checked /></td>";
			} else {
				echo "<td><input type='radio' id='rating".$callNum."_5' name='rating[".$callNum."]' value='5' /></td>";
			}
			echo "</tr>
		</table>";
}

//this function prints a blank call for the silent monitor application
function PrintBlankCall($callNumber){
	echo "<tr><th class='invisibleTable'><span style='cursor:pointer'><div class='callLabel' onclick='$(\"#".$callNumber."\").slideToggle(\"medium\")'>Call ".$callNumber."+</div></span></th><td class='invisibleTable'>";
	global $area, $db;
	try {
		$criteriaQuery = $db->prepare("SELECT * FROM silentMonitorCriteriaInfo WHERE area = :area");
		$criteriaQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	echo "<div id='".$callNumber."' style='display:none;'>";
	echo "<table>";
	while($cur = $criteriaQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><th colspan='3'>".$cur['index'].") ".$cur['title']."</th>";
		echo "<td rowspan='2' valign='center' ><select id='select".$callNumber."_".$cur['index']."' class='dropDown".$callNumber."' name='select[".$callNumber."][".$cur['index']."]'>";
						printSelectMenu();
		echo "</select></td>";
		echo "</tr><tr>";
		echo "<td>".$cur['contents']."</td></tr>";
			
	}
	echo "</table></div><table>";
	echo "<tr><th>Comments</th><th>Overall Rating</th><th>Calls Took Place on:</th></tr><tr>
					<td><textarea class='comment' cols='60' rows='4' name='comments[".$callNumber."]'></textarea></td>";
	echo "<td>";
		getRatingsBox($callNumber,3);
	echo "</td><td>";
	echo "<input type='text' name='startDate[".$callNumber."]' id='startDate[".$callNumber."]' size='10' onChange='isValidDate(document.postmessage.startdate)' placeholder='YYYY-MM-DD' class='tcal' />";
		//Showcalendar("silentMonitor","startDate[".$callNum."]");	
	echo "</td></tr>";
	echo "</table>";
	echo "</td></tr>";	
}			

//this function will take data in from a loaded silent monitor and print that.
function PrintLoadedCall($callNum,$comments,$rating,$selectData,$date){
	global $db, $area;
	echo "<div id='callForm".$callNum."' class='callForm'>";	
	echo "<table class='invisibleTable'>";
	echo "<tr><th class='invisibleTable'><span style='cursor:pointer'><div class='callLabel' onclick='$(\"#".$callNum."\").slideToggle(\"medium\")'>Call ".$callNum."+</div></span></th><td class='invisibleTable'>";
	try {
		$criteriaQuery = $db->prepare("SELECT * FROM silentMonitorCriteriaInfo WHERE area = :area");
		$criteriaQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	echo "<div id='".$callNum."' style='display:none;'>";
	echo "<table>";
	$selectNum = 1;
	while($cur = $criteriaQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<tr><th colspan='3'>".$cur['index'].") ".$cur['title']."</th>";
		echo "<td rowspan='2' valign='center' ><select id='select".$callNumber."_".$cur['index']."' class='dropDown".$callNumber."' name='select[".$callNum."][".$cur['index']."]'>";
						printLoadedSelectMenu($selectData[$selectNum]);
		echo "</select></td>";
		echo "</tr><tr>";
		echo "<td>".$cur['contents']."</td></tr>";
		$selectNum+=1;			
	}
	echo "</table></div><table>";
	echo "<tr><th>Comments</th><th>Overall Rating</th><th>Calls Took Place on:</th></tr><tr>
					<td><textarea class='comment' cols='60' rows='4' name='comments[".$callNum."]'>".$comments."</textarea></td>";
	echo "<td>";
		getRatingsBox($callNum,$rating);
	echo "</td><td>";
	echo "<input type='text' class='tcal' name='startDate[".$callNum."]' id='startDate[".$callNum."]' size='10' onChange='isValidDate(document.postmessage.startdate)' value='".$date."' />";
	echo "</td></tr>";
	echo "</table>";
	echo "</td></tr>";
	echo "</table>";
	echo "</div>";
}

?>
