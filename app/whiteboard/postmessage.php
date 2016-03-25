<?php
/*
This file creates the page used to post a message to the whiteboard.
It consists of many fields that contain information about the whiteboard post

*/
require('../includes/includeMeSimple.php');
$permission = can("update", "6db1ee4f-4d80-424d-a062-97dc4cc22936");//whiteboard resource

	// Load types for when we want to print them or just use them.
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
	//this function populates the selection box for the Type of post it is.
	function printTypes($tags)
	{
		$types = " ";
		foreach ($tags as $row)
		{
			$type = "<option value=".$row['typeId'].">".$row['typeName']."</option>";
			$types.=$type;
		}
		echo $types;
	}
	// Generate the html to allow the user to select which areas they would like to publish this whiteboard to
	function areaSelector($area)
	{
		global $netID, $db;
		try {
			$areaQuery = $db->prepare("SELECT * FROM `employeeAreas` WHERE `ID` IN (SELECT `area` FROM `employeeAreaPermissions` WHERE `netID` = :netId) OR `ID` IN (SELECT `area` FROM `employee` WHERE `netID` = :netId1)");
			$areaQuery->execute(array(':netId' => $netID, ':netId1' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$areaCheckboxes = '';
		while($areaResult = $areaQuery->fetch(PDO::FETCH_ASSOC))
		{
			$checked = '';
			if ( $area == $areaResult['ID'] )
			{
				$checked = " checked='checked' ";
			}
			$areaCheckboxes .= "<input id='area{$areaResult['ID']}' class='areaCheckbox' type='checkbox' name='areas[]' {$checked} value='{$areaResult['ID']}' /><label class='areaLabel' for='area{$areaResult['ID']}' > {$areaResult['area']}  </label>";
		}
		echo $areaCheckboxes;
	}
//----------------------------------------------------------------------------------------
//when the submit button is clicked this collects the information and adds it to the database and then displays the post success page to the user.
if(isset($_POST['submit'])){

	$title = $_POST['title'];
	$owner = $netID;
	$type = $_POST['type'];
	$message = $_POST['message'];
	$kb = $_POST['kb'];
	$postDate = $_POST['startdate'];
	if($postDate == ""){
		$postDate = date('c');
	} else {
		$postDate == date('Y-m-d',strtotime($_POST['startdate']));
	}
		$postDate.= " ".date('H:i:s');
	$expireDate =  $_POST['expire'];
	if($expireDate == ""){
		$status = 'FAIL';
	}
	if(isset($_POST['mandatory']) && $permission == 1){
			$mandatory = 1;
			$messageMandatory = "mandatory"; //This is used to post in the notification message.
	}else{
			$mandatory = 0;
			$messageMandatory = "";
	}
	
	// Start a transaction and insert the whiteboard message itself and make the entries necessary for which areas should be able to view the post
	$db->beginTransaction();
	
	try {
		$insertQuery = $db->prepare("INSERT INTO `whiteboard` (ownerId,type,title,message,postDate,expireDate,mandatory,kb,guid) 
			VALUES (:owner,:type,:title,:message,:day,:expire,:mandatory,:kb,:guid)");
		$insertQuery->execute(array(
			':owner'     => $owner,
			':type'      => $type,
			':title'     => $title,
			':message'   => $message,
			':day'       => $postDate,
			':expire'    => $expireDate,
			':mandatory' => $mandatory,
			':kb'        => $kb,
			':guid'      => newGuid()));
	} catch(PDOException $e) {
		$db->rollBack();
		exit("error in query");
	}
	$whiteboardId = $db->lastInsertId();
	$approved = 0;
	// Find the correct tag for the default whiteboard type
	foreach ($tags as $tag)
	{
		if ($tag['typeId'] == $type)
		{
			$approved = $tag['mustApprove'] == '1'? '0':'1';
			break;
		}
	}

	foreach ( $_POST['areas'] as $areaId )
	{
		try {
			$insertQuery = $db->prepare("INSERT INTO `whiteboardAreas` (`whiteboardId`, `areaId`, `approved`, `guid`) VALUES (:id, :area, :approved, :guid)");
			$insertQuery->execute(array(':id' => $whiteboardId, ':area' => $areaId, ':approved' => $approved, ':guid' => newGuid()));
		} catch(PDOException $e) {
			$db->rollBack();
			exit("error in query");
		}
	}
	
	// If all queries were successful, commit
	$db->commit();
	$status = 'OK';
	if($mandatory) { 
		forceNotify("d3ca192f-c522-11e5-bdda-0242ac110003", nameByNetId($owner)." posted a $messageMandatory whiteboard message.");
	} else {
		notify("d3ca192f-c522-11e5-bdda-0242ac110003", nameByNetId($owner)." posted a $messageMandatory whiteboard message.");
	}


?>
<script>
	//Refresh parent window.
	var postStatus = "<?php echo $status; ?>";
	if (postStatus == 'OK')
	{
		window.opener.notify("Posted whiteboard successfully!", {'status':'success'});
			}
	else
	{
		window.opener.notify("Failed to post whiteboard!", {'status': 'failure'});
	}
	window.opener.getUnapprovedWhiteboards();
	window.opener.searchWhiteboards();
	window.close();
</script>
<?php		
}else{

?>


<!--*****************************HTML******************************************* -->
<style>
	.areaCheckbox
	{
		vertical-align: middle;
	}
</style>
<script type="text/javascript">

window.onload = function(){
	$("#startdate").datepicker({dateFormat: "yy-mm-dd"});
	$("#expire").datepicker({dateFormat:"yy-mm-dd"});
	CKEDITOR.replace( 'message' );
}

function validateFormInput()
{
	
	if ($("#startdate").val().length == 0 ||
		$("#expire").val().length == 0 ||
		$("#title").val().length == 0)
	{
		alert("Please fill out the Title, Start Date, and Expiration Date");
		return false;	
	}

	var check =  CKEDITOR.instances.message.getData();
	if (check == null)
	{
	//check = $('#message').html();	
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
		document.forms["whiteboardSubmit"].submit();
	}
	
}

</script>
<h2>New Whiteboard Message</h2>
<form name="whiteboardSubmit" id="whiteboardSubmit" method="post" onsubmit="return validateFormInput()">
<!---------------------------TITLE----------------------------->
<table style='font-size:14px;'>
<tr>
	<th>Title:</th>
	<td colspan="3"><input type="text" name="title" id="title" maxlength=150 size=70 value=""/></td>
	</td>
</tr>

<!--------------------------Specify if the message is mandatory--------------------------------------->

	<!--show only the appropriate people the checkbox if the message is mandatory-->
	<?php if($permission){ ?>	
	<tr>
	<th>Is mandatory? </th>	
	<td><input type='checkbox' name='mandatory' value='1'></input>
	</tr>
	<?php } ?>	




<!-----------------------TYPE----------------------------->
<tr>
	<th>Type:</th>
	<td>
		<select name='type'>
		<?php printTypes($tags);//call to the function that populates the drop down box?>
		</select>
	</td>
</tr>
<!-----------------------START DATE-------------------------------->
<tr>
	<th title="Messages appear ON the date specified.">Start Date (Y-m-d):</th>
	
	<td><input type='text' id='startdate' name='startdate' size='10'  value="<?php echo date('Y-m-d'); ?>" >
</td><td><font color='#8f100a'><b>*This determines the order it will appear on the Whiteboard.</b></font></td>
</tr>

<!-----------------------EXPIRATION DATE----------------------------->
<tr>
	<th title="Messages expire ON the date specified.">Expiration Date (Y-m-d):</th>
	
	<td><input type='text' id='expire' name='expire' size='10'>
</td><td><font color='#8f100a'><b>*IMPORTANT: You MUST select an expiration date!</b></font></td>

</tr>
<!---------------KB reference----------------------------------->
<tr><th>KB reference:</td><td> <input type="text" name="kb" maxlength=10 size=10 value="" placeholder='1234' /></th>
</tr>

<!-----------------------MESSAGE CONTENT----------------------------->
<tr>
	<th colspan="4">Message:</th>
</tr>
	<tr>
	<td colspan="5">
		<textarea name="message" id="message" rows="15" cols="100"></textarea>
	</td>
	</tr>

<!-----------------------Area Selector----------------------------->
	
<tr>
	<th>Publish to Area(s): </th>
	<td colspan="2"><?php areaSelector($area); ?></td>
</tr>
	<tr>

<!-----------------------SUBMIT BUTTON----------------------------->
	
		<td><input type="submit" name="submit" value="Post Message"></td>
		<td><input type='button' onclick="window.location.href='index.php'" value="Cancel" /></td>
	</tr>
</table>
</form>

<?php }

require('../includes/includeMeSimpleAtEnd.php');

 ?>
