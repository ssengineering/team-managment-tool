<?php
$page_title = "BYU Network Operations"; //displays the title of your site in the titlebar of the browser
$section = ""; // $section is used only to display the section in the titlebar of the browser
$subsection = ""; // $section is used only to display the section in the titlebar of the browser
$head_content = ""; // $head_content variable allows you to put css styles sheets and other content in the original head html tag

###################################Internal Includes###################################
require_once('loadConfig.php'); //Loads configuration values from files in config/
require_once('dbconnect.php'); // Makes connections to the DB
require_once('auth.php'); // Required for CAS authentication
require_once('preferences.php'); // Load user preferences
require_once('employeeFunc.php'); //Used for several employee functions such as employeeFill() for a dropdown menu
require_once('functions.php'); //Used for general functions used accross the site such as areaSelect() for a dropdown menu with the active areas. 
require_once('scheduleFunctions.php'); //Used for Scheduling functions. 
require_once('permissionFunctions.php'); //Used for Permission Functions.
require_once('checkPermission.php'); //required for checking permissions
###################################Template/Style Includes###################################
//require('php_header.php'); // php_header is required to give your website variable and safe email address
//require('templates_lev2.php'); // templates displays the top section of the page
//require('templates_all_popup.php'); // templates_all_popup displays the menu system
//require('css.php'); //Contains CSS
require_once('header.php');
require_once('calendar.php'); //Used to print out the javascript calendar
//require('keepTrafficCall.php');// Attempting to solve the problem from SD and COS site timing out. 
//require('special_feature.php'); // displays the special articles and middle section of the homepage
//require('template_footer_lev2.php'); // displays the footer content - you may want to put a web analytics tool here and in template_footer_lev2
require_once('guid.php');
require_once('authorization.php');
require_once('notification.php');
?>
