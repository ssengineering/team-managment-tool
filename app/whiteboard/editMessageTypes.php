<?php
//editMessageTypes.php
require('../includes/includeme.php');

if(can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936")/*whiteboard resource*/){

//Problems with pulling escaped characters from the Database see the function pullShiftTypes() to fix it.

try {
	$tagQuery = $db->prepare("SELECT * FROM tag WHERE area = :area ORDER BY typeName ASC");
	$tagQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}
if(isset($_POST['submit'])){
    while($hourType = $tagQuery->fetch(PDO::FETCH_ASSOC)) {
		$color = $_POST[$hourType['typeId'].'color'];
		if($color == 'white' || $color == '#FFFFFF' || $color == '#ffffff'){
			$color = 'black';
		}
	if(isset($_POST[$hourType['typeId'].'mustApprove'])){
		$mustApprove = $_POST[$hourType['typeId'].'mustApprove'] == 'on'? '1':'0';
}
	else{
		$mustApprove = 0;
}
		try {
			$insertQuery = $db->prepare("INSERT INTO tag (area,typeId,typeName,color,`mustApprove`,guid) VALUES (:area, :hourType, :type, :color, :approve, :guid)
				ON DUPLICATE KEY UPDATE typeName=:name, color=:color1, `mustApprove`=:approve1");
			$insertQuery->execute(array(
				':area'     => $area,
				':hourType' => $hourType['typeId'],
				':type'     => $_POST[$hourType['typeId']],
				':color'    => $_POST[$hourType['typeId'].'color'],
				':approve'  => $mustApprove,
				':guid'     => newGuid(),
				':name'     => $_POST[$hourType['typeId']],
				':color1'   => $color,
				':approve1' => $mustApprove));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}

}




?>
<script type='text/javascript'>
window.onload = printTypes;

function printTypes(){
			var page = 'messegeTypeAjax/printTypes.php';
			
			var cb = function(result){ document.getElementById("results").innerHTML = result; };

			callPhpPage(page,cb);
	}


function deleteType(){
	var id = document.getElementById('msgTypes').value;
	var r = confirm("If you delete this message type, any active Whiteboard messages of this type will also be removed. Are you sure you want to Delete this Message Type?");
	if(r == true){
		var page = 'messegeTypeAjax/deleteType.php?id='+id;
	
		var cb = function(result){ printTypes(); };

		callPhpPage(page,cb);
	}
}

function insertType(){
	var r = confirm("Are you sure you want to Insert a new Message Type?");
	if(r == true){
		var page = 'messegeTypeAjax/insertType.php';
	
		var cb = function(result){ printTypes(); };

		callPhpPage(page,cb);
	}
}
</script>
	<div align='center'>
	<h2>Edit Whiteboard Message Types</h2>
	<input type='button' onclick="window.location.href='index.php'" value="Back to the Whiteboard" />
	</div>
	<br/>
	<div align='center'>
	Instructions: You MUST submit any changes you have made before doing anything else, or you will lose your changes. <br/> The color field will accept an HTML accepted color in the form of a string literal or hexcode like this: #000000. <br/>For a list of HTML recognized colors visit <a target='_blank' href='http://www.w3schools.com/html/html_colornames.asp'>W3 schools color list</a> 
	</div>
	<br/>
	<form name='editHours' method='post'>
	<div align='center'>
		<input type='button' class='button' name='newHour' value="Insert New Type" onclick='insertType()' />
		<input type='submit' class='button' name='submit' value="Submit Name Changes" /> 
		
	</div>
	<br/> 
	<div id='results' align='center'>  
	
	</div>
	<div align='center'>
			<br />*NOTE: If you remove a type from the list, any Whiteboard messages associated with that type <b> will be <u>permanently</u> deleted from the Whiteboard</b>. <u>This is the same for expired messages.</p>
	</div>
	</form>
	
	<br/>
<?php 

    } else{
    	echo "<h1>You are Not authorized to view this page!</h1>";
	}
	
	require('../includes/includeAtEnd.php');
?>
