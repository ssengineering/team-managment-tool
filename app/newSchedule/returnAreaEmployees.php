<?php
/*
*  Name: returnAreaEmployees.php
*  Application: Schedule
*  Site: ops.byu.edu
*  Author: Joshua Terrasas
*
*  Description: This page returns a JSON formatted array of employee objects who
*  have rights to the current area (defualt and cross-functional).
*/

require_once('../includes/includeMeBlank.php');

global $area;
$requestedArea = $area;

if(isset($_GET['area']))
{
	$requestedArea = $_GET['area'];
}
try {
	$employeesQuery = $db->prepare("SELECT `netID`, `firstName`, `lastName`, `area` FROM `employee` WHERE `netID` IN (SELECT `netID` FROM `employeeAreaPermissions` WHERE `area` = :area1) OR `area` = :area2 ORDER BY `employee`.`firstName`");
	$employeesQuery->execute(array(':area1' => $requestedArea, ':area2' => $requestedArea));
} catch(PDOException $e) {
	exit("error in query");
}

$employees = array();

while($employee = $employeesQuery->fetch(PDO::FETCH_ASSOC))
{
	$employees[] = $employee;
}


echo json_encode($employees);
