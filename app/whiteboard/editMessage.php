<?php
/*
This file is for editing whiteboard messages.

/**********whiteboard_messages table***************
+-----------+-------------+------+-----+---------------------+----------------+
| Field     | Type        | Null | Key | Default             | Extra          |
+-----------+-------------+------+-----+---------------------+----------------+
| messageid | int(11)     |      | PRI | NULL                | auto_increment |
| ownerId   | varchar(255)|      |     |                     |                |
| type      | int(11)     |      |     | 0                   |                |
| title     | long-text   |      |     |                     |                |
| message   | text        |      |     |                     |                |
| postdate  | datetime    |      |     | 0000-00-00 00:00:00 |                |
| expire    | date        |      |     | 0000-00-00          |                |
| manditory | int         |      |     | 0                   |                |
+-----------+-------------+------+-----+---------------------+----------------+
*/

require('../includes/includeMeSimple.php');
$permission = can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936"); //This permission applies specifically to the ability to set a message as mandatory.//whiteboard resource
//---------------FUNCTIONS-------------------------------------------------
	//this function populates the selection box for the Type of post it is.
	function getTypes($t){
		global $area;
		global $db;
		$types = " ";

		try {
			$tagQuery = $db->prepare("SELECT * FROM tag WHERE area = :area ORDER BY typeName ASC");
			$tagQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}

		while($row = $tagQuery->fetch(PDO::FETCH_ASSOC)) {
			$type = "";
			if($row['typeId'] == $t){
				$type = "<option value=".$row['typeId']." selected='true'>".$row['typeName']."</option>";
			}else{
				$type = "<option value=".$row['typeId'].">".$row['typeName']."</option>";
			}
			$types.=$type;
		}			
		echo $types; //echos the variable holding the items in the drop down box
		
	}
	// NOTE: This function is different than it's counterpart in the postmessage.php file. Generate the html to allow the user to select which areas they would like to publish this whiteboard to
	function areaSelector($area, $msgID)
	{
		global $netID;
		global $db;
		// This query also joins the whiteboardAreas table in order to determine which areas already have the ability to view this whiteboard message
		try {
			$areasQuery = $db->prepare("SELECT * FROM `employeeAreas` LEFT JOIN `whiteboardAreas` ON `whiteboardId`=:message AND `employeeAreas`.`ID`=`areaId` AND `whiteboardAreas`.`deleted` = 0 WHERE `employeeAreas`.`ID` IN (SELECT `area` FROM `employeeAreaPermissions` WHERE `netID` = :netId) OR `employeeAreas`.`ID` IN (SELECT `area` FROM `employee` WHERE `netID` = :netId1)");
			$areasQuery->execute(array(':message' => $msgID, ':netId' => $netID, ':netId1' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$areaCheckboxes = '';
		while($areaResult = $areasQuery->fetch(PDO::FETCH_ASSOC))
		{
			$checked = '';
			// Here instead of checking to see if the area to be printed is the area the user is currently in, this checks to see whether or not the area already has the ability to view this message or not
			// if it does the checkbox is checked by default
			if ( $areaResult['whiteboardId'] )
			{
				$checked = " checked='checked' ";
			}
			$areaCheckboxes .= "<input id='area{$areaResult['ID']}' class='areaCheckbox' type='checkbox' name='areas[]' {$checked} value='{$areaResult['ID']}' /><label class='areaLabel' for='area{$areaResult['ID']}' > {$areaResult['area']}  </label>";
		}
		echo $areaCheckboxes;
	}

//---------------------Inital query to the database to get the info for the message to be edited--------
	$msgID = $_GET['messageId'];

	// This query will return multiple results but until I know wether different areas are going to want to approve whiteboards individually I just assume one approval works for all
	try {
		$areaQuery = $db->prepare("SELECT * FROM `whiteboard` JOIN `whiteboardAreas` ON `whiteboardAreas`.`whiteboardId` = `whiteboard`.`messageId` WHERE messageId = :message AND `whiteboardAreas`.`deleted` = 0");
		$areaQuery->execute(array(':message' => $_GET['messageId']));
	} catch(PDOException $e) {
		exit("error in query");
	}

	$row = $areaQuery->fetch(PDO::FETCH_ASSOC);
	$type = $row['type'];
	$title = $row['title'];
	$owner = $row['ownerId'];
	$postDate = $row['postDate'];
	$expireDate = $row['expireDate'];
	$message = $row['message'];
	$mandatory = $row['mandatory'];
	$kb = $row['kb'];
	$approved = $row['approved'];
	$approvedBy = $row['approvedBy'];
	$approvedOn = $row['approvedOn'];
		

if(isset($_POST['submit'])){ //This collects the data from the fields and inserts them into the database
//into the whiteboard table
	
	// Load types for when we need to determine if it is approved by default or not.
	function loadTypes($area)
	{
		global $db;
		$tags = array();
		try {
			$tagQuery = $db->prepare("SELECT * FROM tag WHERE area=:area ORDER BY typeName ASC");
			$tagQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($row = $tagQuery->fetch(PDO::FETCH_ASSOC))
		{
			$tags[] = $row;
		}
		return $tags;
	}
	$tags = loadTypes($area);

	// Determine if type has changed so that we can require approval if the type requires it and it has not actually gone through an approval process.
	if ($type != $_POST['type'])
	{
		// Find the correct tag for the old default whiteboard type
		foreach ($tags as $tag)
		{
			if ($tag['typeId'] == $type)
			{
				$oldTag = $tag;
				break;
			}
		}

		// Find the correct tag for the new default whiteboard type
		foreach ($tags as $tag)
		{
			if ($tag['typeId'] == $_POST['type'])
			{
				$newTag = $tag['mustApprove'];
				break;
			}
		}

		// check For Changes To Approval Requirements
		if ( $oldTag['mustApprove'] == '0' && $newTag['mustApprove'] == '1' )
		{
			$approved = 0;
			$approvedBy = '';
			$approvedOn = '';
		}
		else if ( $newTag['mustApprove'] == '0' )
		{
			$approved = 1;
			$approvedBy = $netID;
			$approvedOn = date('Y-m-d H:i:s');
		}
	}

	$title = $_POST['title'];
	$type = $_POST['type'];
	$message = $_POST['message'];
	$postDate = $_POST['startdate'];
	$postDate.= " ".date('H:i:s');
	$expireDate =  $_POST['expire'];
	if(isset($_POST['mandatory']) && $permission == 1){
		$mandatory = 1;
	}else{
		$mandatory = 0;
	}
	$kb = $_POST['kb'];
	
	// NOTE: this transaction is different from it's counterpart in the postmessage.php file
	// Start a transaction and insert the whiteboard message itself and make the entries necessary for which areas should be able to view the post
	$db->beginTransaction();
	
	// Here we do an update on duplicate key (not sure why we don't just do an update to begin with)
	// I will leave that for you to determine whether the acts of your dev team forefathers were in wisdom or in foolishness 
	try {
		$insertQuery = $db->prepare("UPDATE `whiteboard` SET type=:type1, title=:title1, message=:message1, postDate=:day1, expireDate=:expire1, mandatory=:mandatory1, kb=:kb1 WHERE messageId=:msgID");
		$insertQuery->execute(array(
			':msgID' => $msgID,
			':type1'  => $type, 
			':title1' => $title,
			':message1' => $message,
			':day1'  => $postDate,
			':expire1' => $expireDate,
			':mandatory1' => $mandatory,
			':kb1' => $kb));
		$deleteQuery = $db->prepare("DELETE FROM whiteboardMandatoryLog WHERE msgID = :id");
		$deleteQuery->execute(array(':id' => $msgID));
	} catch(PDOException $e) {
		$db->rollBack();
		exit("error in query");
	}
	// No idea why this does a delete every time, maybe it's because if you edited the message you would expect everyone to read the changes on a mandatory whiteboard but that is just a guess 
	
	$whiteboardId = $msgID;

	// Determine which areas currently have the edited whiteboard associated with them
	// then mark any unnecessary ones as deleted
	try {
		$oldEntryQuery = $db->prepare("SELECT * FROM `whiteboardAreas` WHERE `whiteboardId` = :id AND `deleted` = 0");
		$oldEntryQuery->execute(array(':id' => $msgID));
	} catch(PDOException $e) {
		$db->rollBack();
		exit("error in query");
	}
	$oldEntryArray = array();
	while ($oldEntry = $oldEntryQuery->fetch(PDO::FETCH_ASSOC))
	{
		$oldEntryArray[] = $oldEntry['areaId'];
	}
	$oldEntryArray = array_diff($oldEntryArray, $_POST['areas']);
	if (count($oldEntryArray))
	{
		$oldAreasString = implode(',', $oldEntryArray);
		try {
			$updateQuery = $db->prepare("UPDATE `whiteboardAreas` SET `deleted` = 1, `deletedBy` = :netId, `deletedOn` = NOW() WHERE `whiteboardId` = :id AND `areaId` IN (:areas)");
			$updateQuery->execute(array(':netId' => $netID, ':id' => $whiteboardId, ':areas' => $oldAreasString));
		} catch(PDOException $e) {
			$db->rollBack();
			exit("error in query");
		}
	}
	$approvedBy = $approvedBy == '' || '0000-00-00 00:00:00' ? NULL : $approvedBy;
	$approvedOn = $approvedOn == '' || '0000-00-00 00:00:00' ? NULL : "'${approvedOn}'";
	// Insert related `whiteboardAreas` entries
	foreach ( $_POST['areas'] as $areaId )
	{
		try {
			$insertQuery = $db->prepare("INSERT INTO `whiteboardAreas` (`whiteboardId`, `areaId`, `approved`, `approvedBy`, `approvedOn`, `guid`) VALUES (:id, :area, :approved, :by, :on, :guid)
				ON DUPLICATE KEY UPDATE `approved` = :approved1, `approvedBy` = :by1, `approvedOn` = :on1, `deleted` = 0, `deletedBy` = NULL, `deletedOn` = NULL");
			$insertQuery->execute(array(
				':id'         => $whiteboardId,
				':area'       => $areaId,
				':approved'   => $approved,
				':by'         => $approvedBy,
				':on'         => $approvedOn,
				':guid'       => newGuid(),
				':approved1'  => $approved,
				':by1'        => $approvedBy,
				':on1'        => $approvedOn));
		} catch(PDOException $e) {
			$db->rollBack();
			exit("error in query");
		}
	}
	
	// If all queries were successful, commit.
	$db->commit();
?>
<script>
	// Fist parameter should be an array, all parameters after that are values in the array that you would like removed.
	function remove(arr)
	{
		var what, a = arguments, L = a.length, ax;
		while (L > 1 && arr.length)
		{
			what = a[--L];
			while ((ax= arr.indexOf(what)) !== -1)
			{
				arr.splice(ax, 1);
			}
		}
		return arr;
	}

	var msgId = "<?php echo $msgID; ?>";
	setTimeout(function()
	{
		// Remove collapsed preference since the whiteboard has not been "marked as read" anymore
		var preferences = window.opener.preferences;
		for (var area in preferences)
		{
			try
			{
				remove(preferences[area].whiteboard.collapsed, msgId);
			}
			catch (e)
			{
				console.log("Error removing message from collapsed, probably because we don't have collapsed whiteboard preferences in area '"+area+"'",e);
			}
		}
		window.opener.savePreferences('', preferences);
		window.opener.getUnapprovedWhiteboards(true);
		window.opener.searchWhiteboards(true);
		window.close();
	},500);
</script>
<?php	
		
}else{

?>
<style>
	.areaCheckbox
	{
		vertical-align: middle;
	}
</style>
<script type="text/javascript">

function validateFormInput()
{
	
	var check =  CKEDITOR.instances.message.getData();
	if (check == null)
	{
		check = document.getElementById("message").value;
	}
	 
	var str = check;
	
	var tagBody = '(?:[^"\'>]|"[^"]*"|\'[^\']*\')*';
	
	var regExpression = new RegExp('script\\b' + tagBody + '>[\\s\\S]*?</script\\s*','gi');
	str = str.replace(regExpression, "script");


	check = check.replace(/\s/g, "");
	check = check.replace(/\r/g, "");
	check = check.replace(/\n/g, "");
	check = check.replace(/\t/g, "");
	check = check.toLowerCase();
	
	var checkScript = check.search(/<script|&lt;script|script>|&lt;\/script|<\/script/i);
	if (checkScript != -1)
	{
		alert("Only basic HTML is allowed.");
		return false;
	}
	else
	{
		$("#message").html(str);
	}

}

window.onload = function(){
	$("#startdate").datepicker({dateFormat: "yy-mm-dd"});
	$("#expire").datepicker({dateFormat:"yy-mm-dd"});
	CKEDITOR.replace( 'message' );
}
</script>
<!--******************************HTML****************************************** -->

<h2>Edit Whiteboard Message</h2>
<form name="whiteboardSubmit" id="whiteboardSubmit" method="post" onsubmit="return validateFormInput()">
<table>
<!---------------------------TITLE----------------------------->
<tr>
	<th>Title:</th>
	<td colspan="3"><input type="text" name="title" maxlength=200 size=70 value="<?php echo str_replace('"', '&quot;', $title);?>"/></td>
	</td>
</tr>

<!--------------------------Specify if the message is mandatory--------------------------------------->

	<!--show only the appropriate people the checkbox if the message is mandatory-->
	<?php if($permission){ ?>	
	<tr>
	<td>Is mandatory? </td>	
	<td><input type='checkbox' name='mandatory' value='1' <?php if($mandatory == 1){ echo "checked"; } ?> ></input>
	</tr>
	<?php } ?>	


<!-----------------------TYPE----------------------------->
<tr>
	<th>Type:</th>
	<td>
		<select name='type'>
		<?php getTypes($type);//call to the function that populates the drop down box?>
		</select>
	</td>
</tr>
<!-----------------------START DATE-------------------------------->
<tr>
	<th>Start Date (Y-m-d):</th>
	
	
	<td><input type='text' id='startdate' name='startdate' size='10' value="<?php echo date('Y-m-d');?> ">
		</td>
	<td>Messages appear ON the date specified.</td>
</tr>

<!-----------------------EXPIRATION DATE----------------------------->
<tr>
	<th>Expiration Date (Y-m-d):</th>
	
	<td><input type='text' id='expire' name='expire' size='10' value="<?php echo $expireDate;?>">
	</td>
	<td>Messages expire ON the date specified.</td>
</tr>
<!---------------KB reference----------------------------------->
<tr><th>KB reference:</th><td> <input type="text" name="kb" maxlength=10 size=10 value="<?php echo $kb; ?>" placeholder="1234" /></td>
</tr>
<!-----------------------MESSAGE CONTENT----------------------------->
<tr>
	<th  colspan="4">Message:</th>
</tr>
	<tr>
	<td colspan="5">
		<textarea name="message" id="message" rows="15" cols="100"><?php echo $message;?></textarea>
	</td>
	</tr>

<!-----------------------Area Selector----------------------------->
	
<tr>
	<th>Publish to Area(s): </th>
	<td colspan="2"><?php areaSelector($area, $msgID); ?></td>
</tr>

<!-----------------------SUBMIT BUTTON----------------------------->
	<tr>	
		<td><input type="submit" name="submit" value="Update Message"></td>
		<td><input type="button" onclick="window.close()" value="Cancel" /></td>
	</tr>
</table>
</form>

<?php }require('../includes/includeAtEnd.php'); ?>
