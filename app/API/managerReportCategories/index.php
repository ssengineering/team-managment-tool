<?php 
	require_once $_SERVER['DOCUMENT_ROOT']."/includes/includeMeBlank.php";
	
	// Result given in JSON format
	header("Content-type: application/json");
	
	/* 
	 * Only those with the 'managerReport' permission 
	 * should be able to access this API 
	 */
	if(cannot("access", "1ae4366d-1b2e-4a54-b936-529455e06bea")){ // managerReport resource
		echo json_encode(false);
		exit();
	}

	global $db;
	/*  
	 * To simplify coding, the method should be passed in as the 'type'
	 * parameter in the GET or POST request. Each method is outlined below. 
	 */
	switch ($_REQUEST['type']){
		/*
		 * Using the 'name' passed in to the API, it adds the category to the current
		 * session's area
		 */
		case "add":
			$name = $_REQUEST['name'];
			try {
				$existsQuery = $db->prepare("SELECT COUNT(id) FROM managerReportCategory WHERE category=:name");
				$existsQuery->execute(array(':name' => $name));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$result = $existsQuery->fetch(PDO::FETCH_NUM);
			$exists = ($result[0] > 0);
			if (!$exists) {
				try {
					$insertQuery = $db->prepare("INSERT INTO managerReportCategory (category, area, guid) VALUES (:name, :area, :guid)");
					$success = $insertQuery->execute(array(':name' => $name, ':area' => $area, ':guid' => newGuid()));
				} catch(PDOException $e) {
					$success = false;
				}
				if($success) {
					echo json_encode(true);
					exit();
				} else {
					echo json_encode(false);
					exit();	 		
				}
			} else {
				try {
					$updateQuery = $db->prepare("UPDATE managerReportCategory SET active=TRUE WHERE category=:name");
					$success = $updateQuery->execute(array(':name' => $name));
				} catch(PDOException $e) {
					$success = false;
				}
				if($success) {
					echo json_encode(true);
					exit();
				} else {
					echo json_encode(false);
					exit();	 		
				}
			}
			break;
			
		/*
		 * Updates the category name with the supplied 'id', to use the supplied 'name'
		 */	
		case "edit":
			$id = $_REQUEST['id'];
			$name = $_REQUEST['name'];
			try {
				$updateQuery = $db->prepare("UPDATE managerReportCategory SET category=:name WHERE id=:id");
				$success = $updateQuery->execute(array(':name' => $name, ':id' => $id));
			} catch(PDOException $e) {
				$success = false;
			}
			if($success){
				echo json_encode(true);
				exit();
			} else {
				echo json_encode(false);
				exit();	 		
			}
			break;
			
		/*
		 * If no reports have been submitted with the supplied category, the category is
		 * deleted. Otherwise, the category is marked as inactive.
		 */	
		case "deactivate":
			$id = $_REQUEST['id'];
			try {
				$reportsQuery = $db->prepare("SELECT COUNT(ID) FROM managerReports WHERE category=:id");
				$reportsQuery->execute(array(':id' => $id));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$result = $reportsQuery->fetch(PDO::FETCH_NUM);
			$count = $result[0];
			if($count==0) {
				try {
					$deleteQuery = $db->prepare("DELETE FROM managerReportCategory WHERE id=:id");
					$success = $deleteQuery->execute(array(':id' => $id));
				} catch(PDOException $e) {
					$success = false;
				}
				if($success) {
					echo json_encode(true);
					exit();
				} else {
					echo json_encode(false);
					exit();	 		
				}	
			} else {
				try {
					$deleteQuery = $db->prepare("UPDATE managerReportCategory SET active=FALSE WHERE id=:id");
					$success = $deleteQuery->execute(array(':id' => $id));
				} catch(PDOException $e) {
					$success = false;
				}
				if($success) {
					echo json_encode(true);
					exit();
				} else {
					echo json_encode(false);
					exit();	 		
				}
			}
			break;
			
		/*
		 * Used to check the impact of deleting a category. Returns the number of
		 * reports with a category matching the supplied 'id' 
		 */	
		case "checkDeleteImpact":
			$id = $_REQUEST['id'];
			try {
				$reportsQuery = $db->prepare("SELECT COUNT(ID) FROM managerReports WHERE category=:id");
				$reportsQuery->execute(array(':id' => $id));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$result = $reportsQuery->fetch(PDO::FETCH_NUM);
			$count = $result[0];
			echo json_encode($count);
			exit();
			break;
			
		/*
		 * Returns all categories in the current session's area, unless an area parameter
		 * is provided. In that case, it returns all categories for that area. Inactive 
		 * categories are also returned.
		 */
		case "getAll":
			$area = isset($_REQUEST['area']) ? $_REQUEST['area'] : $area;
			try {
				$categoryQuery = $db->prepare("SELECT * FROM managerReportCategory WHERE area=:area");
				$categoryQuery->execute(array(':area' => $area));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$categories = array();
			while($row = $categoryQuery->fetch(PDO::FETCH_ASSOC)){
				$categories[] = $row;
			}
			echo json_encode($categories);
			exit();
			break;
			
		/*
		 * If none of the above methods are supplied as the type, the API returns false
		 */
		default:
		echo json_encode(false);
			
	}
?>
