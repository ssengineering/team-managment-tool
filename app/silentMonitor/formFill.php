<?php
	require('../includes/includeMeBlank.php');
	$employeeNetId = $_GET['employeeNetId'];
	$employeeName = nameByNetId($employeeNetId);
	echo ":<h2 style='text-align:center'>Silent Monitor for: ".$employeeName."</h2>";
	
?>