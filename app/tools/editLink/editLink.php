<?php require('../../includes/includeMeSimple.php'); 
if(isSuperuser()){
$ID = $_GET['id'];
try {
	$linkQuery = $db->prepare("SELECT * FROM `link` WHERE `index` = :id");
	$linkQuery->execute(array(':id' => $ID));
} catch(PDOException $e) {
	exit("error in query");
}
$link = $linkQuery->fetch(PDO::FETCH_ASSOC);

function getArea($area){
	global $db;
	$types = " ";

	try {
		$areaQuery = $db->prepare("SELECT * FROM employeeAreas");
		$areaQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $areaQuery->fetch(PDO::FETCH_ASSOC)) {
	    if($row['ID'] == $area){
			$type = "<option value=".$row['ID']." selected>".$row['longName']."</option>";
        }else{
		    $type = "<option value=".$row['ID'].">".$row['longName']."</option>";
	    }
		$types.=$type;
	}			
	echo $types;
}

function getApps($currentApp)
{
	global $area, $db;
	$ret = "<option value='NULL'>None</option>";
	$dev = isSuperuser();
	$external = $dev;
	try {
		$appQuery = $db->prepare("SELECT * FROM `app` WHERE `appId` IN (SELECT `appId` FROM `link` WHERE `area` = :area GROUP BY `appId`) ORDER BY `appName`");
		$appQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $appQuery->fetch(PDO::FETCH_ASSOC)) {
		$selected = '';
		if ($row['appId'] == $currentApp)
		{
			$selected = ' selected="selected"';
		}
		if ($dev || ($external &&  intval($row['internal']))) {
			$ret .= "<option value='${row['appId']}'${selected}>${row['appName']}</option>";
		}
	}
	return $ret;
}

function getParent($current, $parent){
	global $area, $db;
	echo "<option value='NULL'>New Parent</option>";
	try {
		$linkQuery = $db->prepare("SELECT * FROM `link` WHERE `index` != :current AND `parent` IS NULL AND `area` = :area");
		$linkQuery->execute(array(':current' => $current, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $linkQuery->fetch(PDO::FETCH_ASSOC)) {
	    if($row['index'] == $parent){
	        echo "<option value=".$row['index']." selected>".$row['name']."</option>";
        }else{
		    echo "<option value=".$row['index'].">".$row['name']."</option>";
		}
		getSubParent($current, $row['index'], $parent);
	}
}

function getSubParent($current, $parent, $selected) {
	global $area, $db;
	try {
		$linksQuery = $db->prepare("SELECT * FROM `link` WHERE `index` != :current AND `parent` = :parent AND `area` = :area");
		$linksQuery->execute(array(':current' => $current, ':parent' => $parent, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $linksQuery->fetch(PDO::FETCH_ASSOC)) {
	    if($row['index'] == $selected){
	        echo "<option value=".$row['index']." selected>- ".$row['name']."</option>";
        }else{
		    echo "<option value=".$row['index'].">- ".$row['name']."</option>";
		}
	}
}

function getPermissions($perm){
    $permissions = pullAllPermissionInfoCurrentArea();
    echo "<option value='NULL'>No Permission</option>";
    foreach($permissions as $permission){
        if($permission['index'] == $perm){
            echo "<option value='".$permission['index']."' selected>".$permission['longName']."</option>";
        }else{
            echo "<option value='".$permission['index']."'>".$permission['longName']."</option>";
        }
    }
}

//----------------MYSQL stuff--------------------------
if(isset($_POST['submit'])){

	$name = $_POST['name'];
	if($name == ""){
	    echo "insert a valid name";
	    exit;
	}
	$appId="";
	if(isset($_POST['appId'])) { $appId = $_POST['appId']; }
	if($appId == "" || $appId == "NULL"){
	    $appId = null;
	}
	$linkArea = $_POST['area'];
	if($_POST['permission'] == 'NULL'){
	    $permissionNeeded = null;
    }else{
        $permissionNeeded = (int)$_POST['permission'];
    }
	        
	if($_POST['parent'] == 'NULL'){
	    $parent = null;
    }else{
        $parent =(int)$_POST['parent'];
    }
	if(isset($_POST['newtab'])){
		$newtab = (int)$_POST['newtab'];
	}else{
		$newtab = 0;
	}
	if(isSuperuser()) {

		try {
		    if($parent == null){
			    $sortQuery = $db->prepare('SELECT sortOrder FROM link WHERE parent IS NULL and area = :area ORDER BY sortOrder DESC LIMIT 1');
			    $sortQuery->execute(array(':area' => $area));
            }else{
			    $sortQuery = $db->prepare('SELECT sortOrder FROM link WHERE parent = :parent ORDER BY sortOrder DESC LIMIT 1');
			    $sortQuery->execute(array(':parent' => $parent));
             }
			if($sort = $sortQuery->fetch()){
				$order = ++$sort->sortOrder;
			}else{
				$order = 0;
			}
			$insertQuery = $db->prepare("INSERT INTO `link` (`index`,`name`,`appId`,`area`,`permission`,`parent`,`newTab`, `sortOrder`, `guid`) 
				VALUES (:index,:name,:app,:area,:permission,:parent,:new, :sort, :guid)
				ON DUPLICATE KEY UPDATE name=:name1, `appId`=:app1, area=:area1, permission=:permission1, parent=:parent1, newTab=:new1");
			$insertQuery->execute(array(
				':index'       => $link['index'],
				':name'        => $name,
				':app'         => $appId,
				':area'        => $linkArea,
				':permission'  => $permissionNeeded,
				':parent'      => $parent,
				':new'         => $newtab,
				':guid'        => newGuid(),
				':name1'       => $name,
				':app1'        => $appId,
				':area1'       => $area,
				':permission1' => $permissionNeeded,
				':parent1'     => $parent,
				':new1'        => $newtab,
				':sort'		   => $order));

	}   catch(PDOException $e) {
			exit("error in query");
		}
	}
?>
<script language="javascript">
window.opener.location.reload();
window.close();
</script>
<?php }

	//--------------HTML---------------------------
?>

<h1 align='center'>Edit Link</h1>

<div class='titleArea'>
	<form id='linkData' method="post">
		<table>
			<tr>
				<th> Link Name: </th><td>
				<input type="text" name="name" maxlength=200 size=40 value="<?php echo $link['name']; ?>"/>
				*This is the text that will appear on the menu bar </td>
			</tr>
			<?php
				if (isSuperuser()) {
					$print = "<tr>";
					$print .= "<th> App: </th><td>";
					$print .= "<select name='appId'>";
					$print .= getApps($link['appId']);
					$print .= "</select>";
					$print .= "</td>";
					$print .= "</tr>";
					echo $print;
				}
			?>
			<tr>
				<th> Area: </th><td>
				<select name='area'>
					<?php getArea($link['area']); ?>
				</select></td>
			</tr>
			<tr>
				<th> Permission Needed: </th><td>
				<select name='permission'>
					<?php getPermissions($link['permission']); ?>
				</select></td>
			</tr>
			<tr>
				<th> Parent Link: </th><td>
				<select name='parent'>
					<?php getParent($link['index'], $link['parent']); ?>
				</select>*Only needed if link is a Sub-link </td>
			</tr>
			<tr><th>Open In New Tab?</th>
			<td><input type='checkbox' name='newtab' value="1" <?php
			if ($link['newTab'] == 1) {
				echo 'checked';
			}
			?>>*Check if you want the link to always open in a new tab</td></tr>
		</table>
		<input type="submit" name="submit" value="Submit" method="post">
	</form>
</div>

<?php
}else{
echo "<h1>You are not Authorized to View this page</h1>";
}

require('../../includes/includeAtEnd.php');
?>
