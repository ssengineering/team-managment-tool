<?php require ('../includes/includeme.php');

	try {
		$permissionQuery = $db->prepare("SELECT * FROM employeePermissions WHERE netID=:netId");
		$permissionQuery->execute(array(':netId' => $_POST['employeeNetId']));
		$columnsQuery = $db->prepare("SHOW COLUMNS FROM employeePermissions");
		$columnsQuery->execute():
	} catch(PDOException $e) {
		exit("error in query");
	}
	$results=$permissionQuery->fetch(PDO::FETCH_ASSOC);

	$permissionNames = "";
	$hasPermission = "";
	$noPermission = "";

	while($row = $columnsQuery->fetch(PDO::FETCH_ASSOC)) {
		if($row['Field'] != "netID"){
				$hasPermission = can("update", $results/*$row['Field']*/);
			if($hasPermission == "checked"){
				$noPermission = "unchecked";
			}else{	
				$noPermission = "checked";
			}

			$permissionNames.= "<td>".$row['Field'] ."</td>";
			$permissionNames.= "<td><input type='radio' name='".$row['Field']."' value='1' ".$hasPermission."/> Yes";
			$permissionNames.="<input type='radio' name='".$row['Field']."' value='0' ".$noPermission."/>No</td></tr><tr>";
		}
	}

	function checkPermissions($array, $permission){
		if($array[$permission] == "1"){
			return "checked";
		}else{
			return "";
		}
	}
?>
<style type='text/css'>
	.label{
	text-align:center;
	width:100px;
	}
</style>
<body>
	<form name='editPermissions' method='post'>
	<input type='hidden' name='netId' value="<?php echo $_POST['employeeNetId']; ?>"/>
		<div align='center'>
		<table style="font-size:14px;">
			<tr>
				<td class='label'>Name:</td>
				<td><?php echo $results['netID']; ?></td>
			</tr>
			<tr>
			<?php echo $permissionNames;?>
			</tr>
		</table>
		<button style='width:200px;height:25px' class='button' type='submit' name='editPermissionsSubmit'>Edit Employee Permissions</button>
		</div>
	</form>
</body>


<?php require('../includes/includeAtEnd.php');?>

