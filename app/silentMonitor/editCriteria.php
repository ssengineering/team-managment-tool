<?php
//editCriteria.php
require('../includes/includeme.php');

   // if(checkPermission('silentMonitor')) {
		if(1){
//Problems with pulling escaped characters from the Database see the function pullShiftPeriods() to fix it.

//---------------------POST Data----------------------------------
try {
	$criteriaCountQuery = $db->prepare("SELECT COUNT(`index`) FROM silentMonitorCriteriaInfo WHERE area = :area ORDER BY `index`");
	$criteriaCountQuery->execute(array(':area' => $area));
	$criteriaQuery = $db->prepare("SELECT * FROM silentMonitorCriteriaInfo WHERE area = :area ORDER BY `index`");
	$criteriaQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}
$result = $criteriaCountQuery->fetch(PDO::FETCH_NUM);
$numRows = $result[0];
$newIndex = $numRows+1;

if(isset($_POST['submit'])){
    while($Type = $criteriaQuery->fetch(PDO::FETCH_ASSOC)) {
		//Edit Query values and update portions to match table.
		try {
			$insertQuery = $db->prepare("INSERT INTO silentMonitorCriteriaInfo (`index`,title,contents,area,guid) VALUES (:index,:title,:content,:area,:guid) ON DUPLICATE KEY UPDATE title=:title1, contents=:contents");
			$insertQuery->execute(array(':index' => $_POST[$Type['index']], ':title' => $_POST[$Type['index'].'title'], ':content' => $_POST[$Type['index'].'content'], ':area' => $area, ':guid' => newGuid(), ':title1' => $_POST[$Type['index'].'title'], ':contents' => $_POST[$Type['index'].'content']));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}

if(isset($_POST['newCriteria'])){  
	try {
		$insertCriteriaQuery = $db->prepare("INSERT INTO silentMonitorCriteriaInfo (`index`,title,contents,area,guid) VALUES (:new,'new','new',:area,:guid)");
		$insertCriteriaQuery->execute(array(':new' => $newIndex, ':area' => $area, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

if(isset($_POST['deleteCriteria'])){
	try {
		$deleteCriteriaQuery = $db->prepare("DELETE FROM silentMonitorCriteriaInfo WHERE `index`=:rows AND area = :area");
		$deleteCriteriaQuery->execute(array(':rows' => $numRows, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

//-------------------FUNCTIONS------------------------------------
    function pullCriteria($area){
		global $db;
		try {
			$infoQuery = $db->prepare("SELECT * FROM silentMonitorCriteriaInfo WHERE area=:area ORDER BY `index` ASC");
			$infoQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo "<table class='imagetable'style='margin:auto;'><tr><th>Index</th><th>Title/ Details</th></tr><tr>";
		while($criteria = $infoQuery->fetch(PDO::FETCH_ASSOC)) {
		   // if(){ //Place holder for filtering out criteria
			    echo "<th rowspan='2'><input type='text' name='".$criteria['index']."' style='width:20px' value='".$criteria['index']."' /></th>";
			    echo '<td><input type="text" name="'.$criteria['index'].'title" style="width:300px" value="'.$criteria['title'].'" /></td></tr>';
			    echo "<tr><td><textarea cols='80' rows='4' name='".$criteria['index']."content'>".$criteria['contents']."</textarea>";
			    echo "</td></tr>";
            //}	
		}
		echo "</table>";
	}

?>
	<h1 align='center'>Edit Silent Monitoring Criterion</h1>
<input type='button' id='editCriteria' value="Back To Silent Monitor" onClick="window.location.href='index.php'" />
	<div align='center'>
		Use this page to edit the criterion for silent monitoring.<br/>
		Each area can have their own silent monitoring format.<br/>
		Each line of the 'details' text areas below (each time you hit enter, that is) will be printed on their own line.<br/>
		<b>If you change the index of an entry be sure to change the duplicate index, as duplicate indicies will cause the program to error</b><br/>
	</div>
    <form name='editCriteria' method='post'>
	<div align='center' style="margin:auto;">
    <input type='submit' class='button' name='newCriteria' value="Insert New Critera"/>
    <input type='submit' class='button' name='deleteCriteria' value="Remove Last Criteria" />
    <input type='submit' class='button' name='submit' value="Submit Changes" />   
	</div>
	<div>
	<br/>
    <?php pullCriteria($area); ?>
	</div>	
	</from>
	<br/>
<?php 

    } else{
	    echo "<h1>You are Not authorized to view this page!</h1>";
	}
	
    require('../includes/includeAtEnd.php');
?>

