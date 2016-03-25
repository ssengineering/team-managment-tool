<?php

require('./sendEmail.php');

$grp = 'Supervisor Report';

$mess = null;

$subj = null;

if (isset($_GET['grp']))
{
	$grp = $_GET['grp'];
}

if (isset($_GET['mess']))
{
	$mess = $_GET['mess'];
}

if (isset($_GET['subj']))
{
	$subj = $_GET['subj'];
}

sendEmail($grp, $mess, $subj);

?>
