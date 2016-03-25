<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');
require_once("logFunctions.php");

if (can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936")/*whiteboard resource*/)
{
	echo json_encode(getUnapprovedWhiteboards($area));
}
else
{
	echo json_encode(array());
}

?>
