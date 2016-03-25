<?php
//addLink app
require('../includes/includeme.php');

/* database fields that need info: 
	Link name:name			(textfield)
	file path:filepath		(textfield)
	area:area				(dropdown)
	permission:permission	(textfield)
	parent group:link		(textfield)
	internal Link:internal 	(checkbox)

*/
if(isSuperuser()){

$appName = '';
$filePath = '';
$editPermissionDescription = '';
$description = '';
$createFileCheck = '';
$pageContent = '';

if (isset($_POST['appName'])) { $appName = $_POST['appName']; }
if (isset($_POST['path'])) { $filePath = $_POST['path']; }
if (isset($_POST['description'])) { $description = $_POST['description']; }
if (isset($_POST['editPermissionDescription'])) { $editPermissionDescription = $_POST['editPermissionDescription']; }
if (isset($_POST['createFileCheck'])) { $createFileCheck = $_POST['createFileCheck']; }
if (isset($_POST['pageContent'])) { $pageContent = $_POST['pageContent']; }


if (isset($_POST['submit']))
{
	if ($appName != '')
	{
		// CHECK TO SEE IF WE ONLY NEED TO CREATE A FILE
		if ($createFileCheck)
		{
			// PARSE INPUT TO CREATE FILE
			preg_match("~(.*?)(\w*?)(\.php)~", $filePath, $group);
			$directories = '../'.$group[1];
			$fileName = $group[2].$group[3];
			$tableName = $group[2];
		
			// VERIFY ALL NECESSARY INPUTS HAVE BEEN FILLED
			if ($pageContent != '' && $appName != "" && $tableName != "" && $editPermissionDescription != "" && $filePath != '')
			{

				// CREATE A NEW DIRECTORY?
				if (file_exists($directories))
				{ 
						echo "The directory {$directories} exists. <br />"; 
				}
				else
				{ 
						mkdir($directories, 0777); 
						echo "The directory {$directories} was successfully created. <br />"; 
				}
			
				// CREATE FILE AND WRITE FILE CONTENT TO CALL 'genericPage' FUNCTION
				$fh = fopen($directories.$fileName, 'x') or die("can't open file");
				$stringData = "<?php \n require('../includes/includeme.php'); \n genericPage('{$tableName}', $".'netID'."); \n require('../includes/includeAtEnd.php'); \n ?>";
				fwrite($fh, $stringData);
				fclose($fh);
				chmod($directories.$fileName, 0775);
			}
		}
		$permissionLongName = 'Edit '.$appName;
		$permissionShortName = $tableName.'Edit';
		$permissionDescription = $_POST['editPermissionDescription'];
		if($tableName == null)
			$tableName = 'null';
		$db->beginTransaction();
		try {
			$insertQuery = $db->prepare("INSERT INTO `permission` (`longName`,`shortName`,`description`,`guid`) VALUES (:long,:short,:description,:guid)");
			$insertQuery->execute(array(':long' => $permissionLongName, ':short' => $permissionShortName, ':description' => $permissionDescription, ':guid' => newGuid()));
			$permissionId = $db->lastInsertId();
			// Create App entry
			$insert2Query = $db->prepare("INSERT INTO `app` (`appName`, `description`, `filePath`, `internal`, `guid`) VALUES (:name,:description,:path,'1',:guid)");
			$insert2Query->execute(array(':name' => $appName, ':description' => $description, ':path' => $filePath, ':guid' => newGuid()));
			$appId = $db->lastInsertId();
			$insert3Query = $db->prepare("INSERT INTO `genericPage` (`submittedBy`, `table`, `content`, `guid`) VALUES (:netId, :name, :content, :guid)");
			$insert3Query->execute(array(':netId' => $netID, ':name' => $tableName, ':content' => $pageContent, ':guid' => newGuid()));
			// Create a relationship between the generic page and its editPermission
			$insert4Query = $db->prepare("INSERT INTO `appPermission` (`appId`, `permissionId`, `guid`) VALUES (:appId, :permission, :guid)");
			$insert4Query->execute(array(':appId' => $appId, ':permission' => $permissionId, ':guid' => newGuid()));
		} catch(PDOException $e) {
			$db->rollBack();
			exit("error in query");
		}
		$db->commit();
		
		echo 'Successfully Added!';
			
	}
}
//--------------HTML---------------------------
?>

<h1 align='center'>Create Generic Page</h1>
<div id=editLink float=right>
</div>

<div class='titleArea'>
<form id='appData' method="post">
   <table style="width:100%">
	    <tr><th>    
		    App Name:
		</th><td>
            <input type="text" name="appName" maxlength=200 size=40 value=""/>*This is the name for the app
        </td></tr><tr><th>
		    File Path:
		</th><td>
            <input type="text" name="path" maxlength=200 size=40 value=""/>*The path to the file with NO leading or ending slash
        </td></tr>
        <tr><th>
		   Page Description:
		</th><td>
            <input type='input' name='description'>*Describe the content the generic page will house. 
        </td></tr>
        <tr><th>
		   Edit Page Permission Description:
		</th><td>
            <input type='input' name='editPermissionDescription'>*Describe the edit rights for the new page. 
        </td></tr>
        <tr><th>
		    Create File?
		</th><td>
            <input type='checkbox' name='createFileCheck' value="1" checked>*Check to create a file. Uncheck to only create an app entry in the database.
        </td></tr>
    </table>
    <textarea name="pageContent" id="pageContent" rows="15" cols="100" style="width:100%">
        REPLACE ME WITH THE DESIRED HTML CONTENT FOR THE GENERIC PAGE!
    </textarea><br /> <br />
		<input type="submit" name="submit" value="Submit" method="post">
</form>
</div>

<?php
}else{
	echo "<h1>You are not Authorized to View this page</h1>";
}

require('../includes/includeAtEnd.php'); ?>
