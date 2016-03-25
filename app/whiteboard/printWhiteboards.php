<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');
require_once("logFunctions.php");

// Setting a bunch of variables

$text = '';
if (isset($_POST['text']))
{
	$text = $_POST['text'];
}

$start = '';
if (isset($_POST['start']))
{
	$start = $_POST['start'];
}

$end = '';
if (isset($_POST['end']))
{
	$end = $_POST['end'];
}

$postedBy = '';
if (isset($_POST['postedBy']))
{
	$postedBy = $_POST['postedBy'];
}

$kb = '';
if (isset($_POST['kb']))
{
	$kb = $_POST['kb'];
}

$primaryTag = '';
if (isset($_POST['primaryTag']))
{
	$primaryTag = $_POST['primaryTag'];
}

$mandatory = '';
if (isset($_POST['mandatory']))
{
	$mandatory = $_POST['mandatory'];
}

echo json_encode(getWhiteboards($netID, $area, $mandatory, $primaryTag, $kb, $postedBy, $start, $end, $text));

?>
