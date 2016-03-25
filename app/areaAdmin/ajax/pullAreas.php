<?php

require('../../includes/includeMeBlank.php');

$areaNames = array();
$curAreas = getAreas();

foreach($curAreas as $cur)
{
	$areaNames[] = getAreaShortNameById($cur);
}

$data = json_encode($areaNames);

echo $data;

?>
