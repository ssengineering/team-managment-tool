<?php

/*
*       Name: reportCard.php
*       Application: Silent Monitor
*       Site: psp.byu.edu
*       Author: Joshua Terrasas
*
*       Description: This is a reporting app for individuals to view the
*	Silent Monitors done for them.
*/

// Standard include for PSP header
include_once('../includes/includeme.php');

?>

<!--JavaScript-->
<script language="JavaScript" src="reportCard.js"></script>

<!--CSS-->
<link rel="stylesheet" type="text/css" href="reportCard.css" />

<h1 id="title">Silent Monitor Report Card</h1>

<span id="dateContainer">Start Date: <input type="text" id="startDate"> End Date: <input type="text" id="endDate"></span>

<div id="container">
</div>

<?php

// Standard include for PSP footer
include_once('../includes/includeAtEnd.php');

?>
