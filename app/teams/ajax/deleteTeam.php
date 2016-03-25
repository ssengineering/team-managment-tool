<?php //This will delete a team
require('../../includes/includeMeBlank.php');

if(isset($_GET['team'])){
    $id = $_GET['team'];

	try {
		$deleteQuery = $db->prepare("DELETE FROM teams WHERE ID = :id AND area = :area");
		$deleteQuery->execute(array(':id' => $id, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}

} else {
    echo "Invalid Data";
}
?>
