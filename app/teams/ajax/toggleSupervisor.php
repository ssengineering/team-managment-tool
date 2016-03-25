<?php 
require('../../includes/includeMeBlank.php');

if(isset($_GET['netid']) && isset($_GET['team'])){
    $employee = $_GET['netid'];
    $team = $_GET['team'];

    $list = explode(",",$employee);

    $size = count($list);
  
    for($i = 0; $i < $size; $i++){
		try {
			$updateQuery = $db->prepare("UPDATE teamMembers SET isSupervisor = !isSupervisor  WHERE netID = :netId AND teamID = :team");
			$updateQuery->execute(array(':netId' => $list[$i], ':team' => $team));
		} catch(PDOException $e) {
			exit("error in query");
		}
    }
} else {
    echo "Invalid Data";
}
?>
