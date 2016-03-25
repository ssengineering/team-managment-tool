<?php 

include('includes/dbconnect.php');
include('includes/auth.php');

try {
	$homePageQuery = $db->prepare("SELECT homePage FROM employeeAreas WHERE ID = :area");
	$homePageQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}

$result = $homePageQuery->fetch(PDO::FETCH_ASSOC);

header( 'Location: '.$result['homePage'] );

?>
