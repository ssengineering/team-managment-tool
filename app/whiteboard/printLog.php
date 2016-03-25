<?php //printLog.php 
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');
require_once('logFunctions.php');

echo json_encode(getPeoples($_GET['msgId'],$area));

?>
