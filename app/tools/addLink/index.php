<?php
//addLink app
require('../../includes/includeme.php');

/* database fields that need info: 
	Link name:name			(textfield)
	file path:filepath		(textfield)
	area:area				(dropdown)
	permission:permission	(textfield)
	parent group:link		(textfield)
	internal Link:internal 	(checkbox)

*/
if(isSuperuser()){

//------------------PHP and FUNCTIONS-------------------
function getArea()
{
	global $area, $db;
	try {
		$nameQuery = $db->prepare("SELECT longName FROM employeeAreas WHERE ID=:area");
		$nameQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$curArea = $nameQuery->fetch(PDO::FETCH_ASSOC);
	echo $curArea['longName'];
}

function getApps()
{
	global $area, $db;
	echo "<option value='NULL'>None</option>";
	$dev = isSuperuser();
	$external = $dev;
	try {
		$appQuery = $db->prepare("SELECT * FROM `app` WHERE `appId` IN (SELECT `appId` FROM `link` WHERE `area` = :area GROUP BY `appId`) ORDER BY `appName`");
		$appQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $appQuery->fetch(PDO::FETCH_ASSOC)) {
		if ($dev || ($external && intval($row['internal']))) {
			echo "<option value='${row['appId']}'>${row['appName']}</option>";
		}
	}
}

function getParent()
{
	global $area, $db;
	echo "<option value=''>New Parent</option>";
	try {
		$parentQuery = $db->prepare("SELECT * FROM `link` WHERE parent IS NULL AND `area` = :area AND `visible` = 1");
		$parentQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $parentQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value=".$row['index'].">".$row['name']."</option>";
		getSubParent($row['index']);
	}	
}

function getSubParent($parent)
{
	global $area, $db;
	try {
		$parentQuery = $db->prepare("SELECT * FROM `link` WHERE parent = :parent AND `area` = :area AND `visible` = 1");
		$parentQuery->execute(array(':parent' => $parent, ':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($row = $parentQuery->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value=".$row['index'].">- ".$row['name']."</option>";
	}
}

function getPermissions(){
    $permissions = pullAllPermissionInfoCurrentArea();
    echo "<option value=''>No Permission</option>";
    foreach($permissions as $permission){
        echo "<option value='".$permission['index']."'>".$permission['longName']."</option>";
    }
}


//----------------MYSQL stuff--------------------------
if(isset($_POST['submit'])){

	$name = $_POST['name'];
	if($name == ""){
	   echo "insert a valid name";
	   exit;
    }
	$appId = $_POST['appId'];
	$permissionNeeded = $_POST['permission'];
	if(!$permissionNeeded == ''){
		$permissionNeeded = $permissionNeeded;
	}else{
		$permissionNeeded = null;
	}
	$parent = $_POST['parent'];
	if(!$parent == ''){
		$parent = $parent;
	}else{
		$parent = null;
	}
	if(isset($_POST['internal'])){
		$internal = $_POST['internal'];
	}else{
		$internal = "";
	}
	if(isset($_POST['newtab'])){
		$newtab = (int)$_POST['newtab'];
	}else{
		$newtab = 0;
	}
	if($appId == "NULL")
		$appId = null;

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

                $insertQuery = $db->prepare("INSERT INTO `link` (name,appId,area,permission,parent,newTab,sortOrder,guid) 
                     VALUES (:name,:appId,:area,:permission,:parent,:new,:sort,:guid)");
                $insertQuery->execute(array(
                    ':name'       => $name,
                    ':appId'      => $appId,
                    ':area'       => $area,
                    ':permission' => $permissionNeeded,
                    ':parent'     => $parent,
                    ':new'        => $newtab,
					':sort'       => $order,
					':guid'       => newGuid()));

 		 
	  } catch(PDOException $e) {
				 exit("error in query");
      }
	}
}

//--------------HTML---------------------------
?>

<h1 align='center'>Add Link to Website</h1>
<div id=editLink float=right>
    <a href="../editLink/index.php">Edit Existing Link</a>
</div>

<div class='titleArea'>
    <form id='linkData' method="post">
        <table>
            <tr>
                <th> Link Name: </th><td>
                <input type="text" name="name" maxlength=200 size=40 value=""/>
                *This is the text that will appear on the menu bar </td>
            </tr>
            <tr>
                <th> App: </th><td>
                <select name="appId">
                	<?php getApps(); ?>
                </select>
                <?php echo ((isSuperuser())?"":"*The path to the file with NO leading slash </td>") ?>
            </tr>
            <tr>
                <th> Area: </th><td> <?php getArea(); ?>
                </td>
            </tr>
            <tr>
                <th> Permission Needed: </th><td>
                <select name='permission'>
                    <?php getPermissions(); ?>
                </select></td>
            </tr>
            <tr>
                <th> Parent Link: </th><td>
                <select name='parent'>
                    <?php getParent(); ?>
                </select>*Only needed if link is a Sub-link </td>
            </tr>
            <tr><th>Open In New Tab?</th>
            <td><input type='checkbox' name='newtab' value="1">*Check if you want the link to always open in a new tab</td></tr>
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
