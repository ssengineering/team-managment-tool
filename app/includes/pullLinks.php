<?php
/* LINKS FOR THE MENU SYSTEM ON ALL PAGES */

//Main parent function that will print all parent links an employee has permissions to see with their accompanying children links
// Link structure is being modified by me (Mika) and this function doesn't seem to work with the current link structure (not the new one but the old one)
// Currently, there is no `level` column. So I don't believe this function is being used in any of our code (at least I can say it already doesn't work if it is)
function getParentLinks($netID,$area){
	//Get All Parent Links from employee's current area.
	try {
		$linksQuery = $db->prepare("SELECT * FROM links WHERE area = :area AND level = 1");
		$linksQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	//Run through all the links pulled from the database
	while($links = $linksQuery->fetch(PDO::FETCH_ASSOC)){
	    //If parentNeeded returns true OR it is an external
		if(parentNeeded($links['name'],$netID,$area,2) || $links['filePath'] != NULL){
		    //If the link does not require a permission or the employee HAS the permission
			if($links['permission'] == NULL || checkLinkPermission($links['permission'])){
			    //if it is an external linke			
				if($links['filePath'] != NULL){
				    //Print it
					echo '<li><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$links['filePath'].'">'.$links['name'].'</a></li>';
				}else{
				    //else, print the parent link and get the children links
					echo '<li><a href="">'.$links['name'].'</a>';
					echo '<div class="sub"><div class="links">';
					getChildrenLinks($netID,$links['name'],$area,2);
					echo'</div></div></li>';
				}
			}
		}
	}

}

//Prints out children links that an employee has permission to see
// Link structure is being modified by me (Mika) and this function doesn't seem to work with the current link structure (not the new one but the old one)
// Currently, there is no `level` column. So I don't believe this function is being used in any of our code (at least I can say it already doesn't work if it is)
function getChildrenLinks($netID, $parentLink, $area, $level){
	//Query to get children links in the current area under the current parent
	try {
		$linksQuery = $db->prepare("SELECT * FROM links WHERE area = :area AND level = :level AND parent=:parentLink ORDER BY level ASC");
		$linksQuery->execute(array(':area' => $area, ':level' => $level, ':parentLink' => $parentLink));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	//For each child links
	while($links = $linksQuery->fetch(PDO::FETCH_ASSOC)){
	    //If there is not a permission required or the user has the permission
		if($links['permission'] == NULL || checkLinkPermission($links['permission'])) {
		    //Check if there are level 3 sublinks and recursively call this function
			//if(parentNeeded($links['name'],$netID,$area,3)){
            if( 1 == 0){
				echo '<a href="">'.$links['name'].'</a><div class="sublinks">';
				getChildrenLinks($netID,$links['name'],$area,3);
				echo '</div>';
		    //else, print the link whether it is internal or external
			}else{	
				if($links['internal'] == 1){	
						echo '<a href="http://'.$_SERVER['SERVER_NAME'].'/'.$links['filePath'].'">'.$links['name'].'</a>';		
				}else{
						echo '<a target="_blank" href="'.$links['filePath'].'" >'.$links['name'].'</a>';
				}
			}
			
		}
	}

}

//Checks if the parent link is actually necessary
//ie. there are reachable children links and its not an external link
// Link structure is being modified by me (Mika) and this function doesn't seem to work with the current link structure (not the new one but the old one)
// Currently, there is no `level` column. So I don't believe this function is being used in any of our code (at least I can say it already doesn't work if it is)
function parentNeeded($parent,$netID,$area,$level){
	$linkCount = 0;
	try {
		$query = $db->prepare("SELECT * FROM links WHERE area=:area AND level=:level AND parent=:parent ORDER BY level ASC");
		$query->execute(array(':area' => $area, ':level' => $level, ':parent' => $parent));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	while($links = $query->fetch(PDO::FETCH_ASSOC)){
		if($links['permission'] == NULL || checkLinkPermission($links['permission'])){
			return true;
		}
	}
	return false;
}


//Basically a copy paste from checkPermission.php but due to how things are working with the includes, we need a copy of the function here.
function checkLinkPermission($permission){
		//Makes $netID and $area to reference the global versions, not local (internal to the function) versions.
		global $netID,$area;
		//Gets the ID of the permission needed by area and shortName. 
		try {
			$permissionQuery = $db->prepare("SELECT `index` FROM `permissionArea` JOIN `permission` ON `permissionArea`.`permissionId`=`permission`.`permissionId` WHERE shortName=:permission AND area=:area");
			$permissionQuery->execute(array(':permission' => $permission, ':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		//If a permission with the given shortName and area is not found, returns false as that permission does not exist.
		if(!($permission = $permissionQuery->fetch(PDO::FETCH_ASSOC))) {
			return false;
		}
		
		//Calls the employeePermissions table to see if the given netID has an entry for the given permission index.
		try {
			$employeeQuery = $db->prepare("SELECT * FROM employeePermissions WHERE netID=:netID AND permission=:permission");
			$employeeQuery->execute(array(':netID' => $netID, ':permission' => $permission['index']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		//If the employee does not have the requested permission no rows will be found. If a row is found return true.
		if ($employeeQuery->fetch()) return true;
		//If no rows are found with given netID and permission index, the employee does not have the permission, return false.
		else return false;
	}

?>
