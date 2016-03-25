<?php //editRaiseReasons.php
//This is for editing, adding and removing raise reasons
require('../includes/includeme.php');
//See the reasonAjax folder for the specific scripts that are called in this file.

if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af")/*wages resource*/){

if(isset($_POST['submit'])){
	foreach($_POST['reason'] as $key => $val){
		try {
			$updateQuery = $db->prepare("UPDATE employeeRaiseReasons SET reason = :reason WHERE `index` = :index");
			$updateQuery->execute(array(':reason' => $val, ':index' => $key));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	foreach($_POST['raise'] as $key => $val){
		try {
			$updateQuery = $db->prepare("UPDATE employeeRaiseReasons SET raise = :raise WHERE `index` = :index");
			$updateQuery->execute(array(':raise' => $val, ':index' => $key));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}

?>
<script type='text/javascript'>
window.onload = printReasons;

//This prints out the reasons list
function printReasons(){
			var page = 'reasonAjax/printReason.php';
			
			var cb = function(result){ document.getElementById("results").innerHTML = result; };

			callPhpPage(page,cb);
	}

//This is the AJAX call for deleting a reason
function deleteReason(){
	var id = document.getElementById('reasons').value;
	var r = confirm("Are you sure you want to Delete this Reason?");
	if(r == true){
		var page = 'reasonAjax/deleteReason.php?id='+id;
	
		var cb = function(result){ printReasons(); };

		callPhpPage(page,cb);
	}
}

//This is the AJAX call for inserting a new defaul reason
function insertReason(){
	var id = document.getElementById('reasons').value;
	var r = confirm("Are you sure you want to Insert a new Reason?");
	if(r == true){
		var page = 'reasonAjax/insertReason.php?id='+id;
	
		var cb = function(result){ printReasons(); };

		callPhpPage(page,cb);
	}
}
</script><div align='center'>
<h1>Employee Raise Reasons</h1>
<input type='button' onclick='window.location.href="index.php"' value="Return to Wages" />
<h3><b>Instructions:</b> After editing a Raise Reason <br/>click "Submit Changes" <b>before</b> doing anything else or your changes will not be saved.</h3>
</div>
<form name='reasons' method='post'>
	<div align='center' style="margin:auto;">
    <input type='button' name='newReason' value="Insert New Reason" onclick='insertReason()' />
	
    <input type='submit' class='button' name='submit' value="Submit Changes" /> 
     
	</div>
	<div name='results' id='results' align='center'>
	
	</div>
	</form>



<?php }
else {
	echo '<h2>Your are not authorized to view this page.<h2>';
}
require('../includes/includeAtEnd.php');
?>
