<?php //raise worksheet
/*
This application will take all of the employees for a given team and then print out information about their performance over a given period.
It also caclulates their overall performance percentage out of 100 base on things like tardy's, tickets, attitude, etc. The manager then can select 
if the employee's attitude and effectiveness should affect the raise positively or negatively
The over all rating is then shown and a suggested raise is shown. There will be a box for the manager to input the amount of the raise 
*/ 
require('../../includes/includeme.php');

if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af"))/*wages resource*/{

?>















<?php
}else {
    echo "<h1>You are not Authorized to View this page</h1>";
}
include('../../includes/includeAtEnd.php');

?>
 
 
