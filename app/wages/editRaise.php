<?php require('../includes/includeMeSimple.php'); 
//editRaise.php
//This file allows the editing of a wage before being submitted by the 
if(can("access", "0551dfe7-cf9c-4747-829c-3299fbd455af")){ //check wages permission //wages resource
$id = $_GET['raiseId']; //id of the raise in the employeeRaiseLog table

try {
	$logQuery = $db->prepare("SELECT * FROM employeeRaiseLog WHERE `index` = :id");
	$logQuery->execute(array(':id' => $id));
} catch(PDOException $e) {
	exit("error in query");
}
$curRaise = $logQuery->fetch(PDO::FETCH_ASSOC);

try {
	$wageQuery = $db->prepare("SELECT wage FROM employeeWages WHERE netID=:netId");
	$wageQuery->execute(array(':netId' => $curRaise['netID']));
} catch(PDOException $e) {
	exit("error in query");
}
$result=$wageQuery->fetch(PDO::FETCH_ASSOC);
$currentWage = $result['wage']; //pull the employee's current wage.

if(isset($_POST['submit'])){ //This submits the edited data
	$newRaise = $_POST['newRaise'];
	$newComments = $_POST['newComments'];
	if($_POST['newRaise'] == ''){
		header("location:editRaise.php?raiseId=".$id."&error=1");
  }else if($_POST['newComments'] == ''){
    header("location:editRaise.php?raiseId=".$id."&error=1");
  }else{
		try {
			$updateQuery = $db->prepare("UPDATE employeeRaiseLog SET raise=:raise, comments=:comments WHERE `index`=:id");
			$updateQuery->execute(array(':raise' => $newRaise, ':comments' => $newComments, ':id' => $id));
		} catch(PDOException $e) {
			exit("error in query");
		}
?>	<script>window.close()</script>
<?php
	}
}

?>
<div align='center'>
<h2>Edit Raise for <?php echo nameByNetId($curRaise['netID']); ?></h2>
<?php 
if(isset($_GET['error'])){
	echo "<font color='red'>Invalid Form Data, please try again!</font>";
}
?>
<form id='editRaise' method="post">
<table>
	<tr>
		<th>Employee</th>
		<th>Current Wage Before Raise</th>
		<th>Requested Raise</th>
		<th>Comments</th>
		<th>Date Requested</th>
	</tr>
	<tr>
		<td><?php echo $curRaise['netID']; ?> </td>
		<td>$<?php echo $currentWage; ?></td>
		<td><input type='text' name='newRaise' size='4' value="<?php echo $curRaise['raise']; ?>" /> </td>
		<td><input type='text' name='newComments' size='9' maxlength='26' value="<?php echo $curRaise['comments']; ?>" /> </td>
		<td><?php echo date("Y-m-d",strtotime($curRaise['date'])); ?></td>
	</tr>
</table>
<input type="submit" name="submit" value="Submit" method="post">
</form>
</div>
<?php 
} else {
	echo "YOU ARE NOT AUTHORIZED TO VIEW THIS PAGE. CONTACT YOUR MANAGER FOR RIGHTS.";
}
require('../includes/includeAtEnd.php');
?>
