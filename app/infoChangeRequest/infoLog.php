<?php //infoLog.php
require('../includes/includeme.php');

if(isset($_POST['submit'])){
	foreach($_POST['status'] as $id => $status) {
		try {
			$updateQuery = $db->prepare("UPDATE reportInfoChangeRequest SET status = :status WHERE id=:id");
			$updateQuery->execute(array(':status' => $status, ':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	foreach($_POST['realComments'] as $id => $realComments){
		try {
			$updateQuery = $db->prepare("UPDATE reportInfoChangeRequest SET comments = :comments WHERE id=:id");
			$updateQuery->execute(array(':comments' => $realComments, ':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
?>
<script>
var cb = function(result){
	document.getElementById('log').innerHTML = result;
}

//alert(month);
callPhpPage("printLog.php",cb);
	//ajax call to a print summary file that prints summary out.
</script>
<div id='title' align='center'>
<h1>Info Change Request</h1>
</div>
<form method='post'>
<div align='center'>
<input type='submit' name='submit' id='submit' value="Submit Changes" />
</div>
<div align='center' id='log' name='log'>

</div>
</form>
<?php 
require('../includes/includeAtEnd.php');
?>
