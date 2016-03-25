<?php
require('../includes/includeMeBlank.php');

	$date = $_GET['date'];
	$values = $_GET['values'];
	$employees = $_GET['employees'];
?>

<link rel="stylesheet" type="text/css" media="print" href="print.css">
<style 
<style type='text/css'>
	.schedule, .schedule_time {
		margin:auto;
		border-collapse:collapse;
		border-style:solid;
		border-width:1px;
		padding:7px;
		text-align:center;
	}
	.schedule_time {
		background-color: #e0e0e0;
	}
	td.schedule {
	    /* background-color:white; */
	}
	table {
		width: 1024px;
	}
	.info {
		width:50%;
		text-align:center;
		padding-bottom:10px;
	}
	.innerLink{
		display:inline;
		margin:auto;
		text-align:center;
		font-size:120%;
		color:#023962;
		padding-left: 25px;
		padding-right: 25px;
	}
	.innerLink:hover{
		color:#f8ad50;
		cursor:pointer;
	}
	.data {
	    padding-bottom:20px;
	}
	#tools {
	    display: none;
	}
	li {
		margin: 0px;
		position: relative;
		cursor: pointer;
		float: left;
		list-style: none;
	}
	.schedule tbody tr:nth-child(2n) {
		background-color: #E7E7E7;
	}
	.schedule tbody tr:hover {
		background-color: #CECECE;
	}
	@media print {
	body {
		font-size:12pt;
	}
	header nav, footer {
		display: none;
	}
	title {
		text-align: center;
	}
	@page {
		margin: 0.5cm;
	}
}
</style>

<script type='text/javascript' src='../../includes/templates/scripts/globalJavaScript.js'></script>
<script type="text/javascript">
	function pullSchedule() {
		
		var page = "returnSchedule.php?date=" + "<?php echo $date; ?>" + "&values=" + "<?php echo $values; ?>" + "&employees=" + "<?php echo $employees; ?>";
		var cb = function(result) {
		document.getElementById('data').innerHTML=result;
		}
		
		callPhpPage(page, cb);
	}
	window.onload = pullSchedule();
</script>

<div class='info'>
	<div>
		<?php $dateDisplay = date('Y-m-d',strtotime($date)); ?>
		<?php if($area == 4){ ?>
		<div id="title"><h1><div id="date">Daily Schedule for <?php echo date('D M j, Y',strtotime($dateDisplay)); ?></div></h1></div>
		<?php } else { ?>
		<div id="title"><h1><div id="date">Full Day Schedule for <?php echo date('D M j, Y',strtotime($dateDisplay)); ?></div></h1></div>
		<?php } ?>
	</div>
	<div id="data"></div>
</div>

