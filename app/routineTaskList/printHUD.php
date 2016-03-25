<?php 
//printHUD.php
require('../includes/includeMeBlank.php');
require('routineTaskTable.php');

//This file prints the edit HUD 

editHUDTableHeader();
editHUDTable($_GET['sortedBy'],$area);
?>
