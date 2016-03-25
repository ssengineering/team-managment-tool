<?php 
//printList.php
require('../includes/includeMeBlank.php');
require('routineTaskTable.php');
//echo "IM PRINTING";
$date = date('Y-m-d');
//This file checks permissions and then calls the function to print out the routine task list
// this is where permissions will be checked

tableTVHeader();
getTVMessages($date,$area);

?>
