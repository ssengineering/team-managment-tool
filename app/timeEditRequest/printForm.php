<?php //printForm.php
//This prints a time edit request form
require('../includes/includeMeBlank.php');

$formNum = $_GET['num'];
echoForm($formNum);

function echoForm($num){
$newNum = $num+1;
echo '
<table border="1" cellpadding="3" style="margin:auto; border-collapse:collapse;">	
	<tr>
		<th>Add/Remove</th><th>Clock In/Out</th><th>Time</th><th>Date</th>
	</tr>
		
	<tr>
		<td>
		<input name="'.$num.'[addRemove]" type="radio" value="1">
		Add
		<input name="'.$num.'[addRemove]" type="radio" value="0">
		Remove
		</td>
		<td>
		<input name="'.$num.'[inOut]" type="radio" value="1">
		In
		<input name="'.$num.'[inOut]" type="radio" value="0">
		Out
		</td>
		<td>
		<input type="text" id="time'.$num.'" name="'.$num.'[time]" value="'.date("h:00A").'" size="8" class="inlineConfig{useMouseWheel:true}"/>
		</td>
		<td>
		<input type="text" name="'.$num.'[date]" value="'.date('Y-m-d').'" class="tcal"  size="8"></td>		
		</td>
	</tr>
	</table>
	<div id='.$newNum.'>

	</div>';
}


?>
