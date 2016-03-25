<?php
require('../includes/includeMeBlank.php');
require('teamingFunctions.php');
// Set new period
if(isset($_POST['periodStartDate']) || isset($_POST['periodEndDate'])){
	if(($_POST['periodStartDate']>$_POST['lastEndDateOnTeaming']) && ($_POST['periodEndDate']>$_POST['periodStartDate'])){
		resetTeamingPeriod($_POST['periodStartDate'], $_POST['periodEndDate'], $area);
		echo '<script type="text/javascript"> location.replace("summary.php"); </script>';
	}
	else
	{
		echo '<script type="text/javascript">alert("Cannot create new period becuase the start or end dates overlap with a previous period. Please try again."); location.replace("summary.php"); </script>';
	}
}

?>
