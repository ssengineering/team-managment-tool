<?php
require ('../includes/includeMeSimple.php');
require ('canvas.php');
?>
<style>
body
{
	min-width: 920px;
	max-width: 920px;
}
#main-header
{
	display: none;
}
#scheduleTitle
{
	font-size: 2em !important;
	margin-top: 0.33em !important;
}
#viewMode
{
	display: none !important;
}
#weeklyDefault
{
	display: none !important;
}
#employeeSelector
{
	display: none !important;
}
#timePickerDiv
{
	display: none !important;
}
#employeeScheduleContainer
{
	margin-top: 4px !important;
}
</style>
<script type="text/javascript">
	window.onload = multiemployeeChildOnload;
	function multiemployeeChildOnload()
	{
		employeeScheduleOnload();
		// Make sure the iframe is the correct height
		var newIframeHeight = $('body').height()+25;
		$(window.frameElement).height(newIframeHeight);
	}
</script>
<?php
require ('../includes/includeMeSimpleAtEnd.php');
?>
