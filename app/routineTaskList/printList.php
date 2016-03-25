<?php 
//printList.php
require('../includes/includeMeBlank.php');
require('routineTaskTable.php');
//echo "IM PRINTING";
$date = $_GET['date'];
//This file checks permissions and then calls the function to print out the routine task list
$permission = can("update", "f9244d83-d0fe-4205-a4eb-f0a1c9de8d88"); //routineTasks resource// this is where permissions will be checked

tableHeader($permission);
getMessages($netID,$permission,$date,$area);

?>
