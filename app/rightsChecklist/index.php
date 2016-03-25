<?php //rightsChecklist/index.php

require('../includes/includeme.php');

if(can("access", "03478803-982d-4542-8589-367cbd8dabae")){//rightsChecklist 
?>

<!--------------------------HTML---------------------------------->
<link rel="stylesheet" type="text/css" href="style.css" />
<script>
var employee = "<?php echo $netID;?>";
var manager  = "<?php echo $netID;?>";
var area     = "<?php echo $area; ?>";
var env      = "<?php echo $env; ?>";
var loaded = false;
var openDivs = {};
</script>
<script src="/includes/template/js/libs/jquery-1.8.2.min.js"></script>
<script src="rightsChecklist.js" type="text/javascript"></script>
<style>
h3 {
	cursor:hand;
	text-decoration:underline
	
}
h3:hover {
	text-color:#f8ad50;
}
#headDiv {
	font-size:16px;

}
</style>

<?php
	if($env == 2){
		echo "<style> #errors{ display:none; } </style>";
	}
?>

<body>
<div id='errors'>

</div>
<div id='headDiv' align='left'>

	<div width='100%' align='center'>
		
		<h2 align='center'>Employee Rights Checklist</h2>
		<a href='manageRights.php' style="float:right;">Manage Rights Page</a><br />
		
	</div>
</div>

	
<div id='employeeSearch'>
	<?php employeeList(); ?>
</div>
	

<div id='rightsInfo'>	
</div>

<?php 
} else {
	echo "<h1>You are not Authorized to View this page</h1>";
}
require('../includes/includeAtEnd.php'); ?>
