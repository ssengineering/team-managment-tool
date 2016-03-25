<?php

	require("../includes/includeme.php");
/**************************************************************************************
		Pull user's posted information
***************************************************************************************/

if (can("update", "f49362ef-983b-4615-ac64-727b769a713f"))//contacts resource

{

$userorg = '';
$userar = '';
$userdept = '';
$username = '';
$userphone = '';
$useraddress = '';
$userposition = '';
$usermanagerFlag = 0;
$userpriority = '';
$userid = '';
$new_priority = '';
$current_org = '';
$current_area = '';
$current_dept = '';

$add_contact = '';
$edit_contact = '';
$remove_contact = '';

if (isset($_POST['org'])) $userorg = $_POST['org'];
if (isset($_POST['area'])) $userar = $_POST['area'];
if (isset($_POST['dept'])) $userdept = $_POST['dept'];
if (isset($_POST['name'])) $username = $_POST['name'];
if (isset($_POST['phone'])) $userphone = $_POST['phone'];
if (isset($_POST['address'])) $useraddress = $_POST['address'];
if (isset($_POST['position'])) $userposition = $_POST['position'];
if (isset($_POST['managerFlag'])) $usermanagerFlag = $_POST['managerFlag'];
if (isset($_POST['priority'])) $userpriority = $_POST['priority'];
if (isset($_POST['id'])) $userid = $_POST['id'];
if (isset($_POST['new_priority'])) $new_priority = $_POST['new_priority'];
if (isset($_POST['current_org'])) $current_org = $_POST['current_org'];
if (isset($_POST['current_area'])) $current_area = $_POST['current_area'];
if (isset($_POST['current_dept'])) $current_dept = $_POST['current_dept'];

if (isset($_POST['add_contact'])) $add_contact = $_POST['add_contact'];
if (isset($_POST['edit_contact'])) $edit_contact = $_POST['edit_contact'];
if (isset($_POST['remove_contact'])) $remove_contact = $_POST['remove_contact'];

/****************************************************************************************
		If needed, make any changes to the database first
****************************************************************************************/
 
if ($add_contact!=""){
	if ($userdept != "") {$groupid = $userdept;}
	else if ($userar != "") {$groupid = $userar;}
	else $groupid = $userorg;

	try {
		$contactPriorityQuery = $db->prepare("SELECT * FROM contacts WHERE department=:group ORDER BY contactPriority DESC LIMIT 1");
		$contactPriorityQuery->execute(array(':group' => $groupid));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$addContactPriority = $contactPriorityQuery->fetch(PDO::FETCH_ASSOC);
	$userpriority = $addContactPriority['contactPriority'] + 1;
	try {
		$addContactQuery = $db->prepare("INSERT INTO contacts (name, phone, address, position, contactPriority, managerFlag, department, guid) VALUES (:user, :phone, :address, :position, :priority, :manager, :group, :guid)");
		$addContactQuery->execute(array(':user' => $username, ':phone' => $userphone, ':address' => $useraddress, ':position' => $userposition, ':priority' => $userpriority, ':manager' => $usermanagerFlag, ':group' => $groupid, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

if ($edit_contact!=""){
	try {
		$infoQuery = $db->prepare("SELECT department FROM contacts WHERE id=:id");
		$infoQuery->execute(array(':id' => $userid));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$oldinfo = $infoQuery->fetch(PDO::FETCH_ASSOC);
	if ($oldinfo['department'] != $userdept) //if moving to a new group
	{
		try {
			$maxPriorityQuery = $db->prepare("SELECT * FROM contacts WHERE department=:dept ORDER BY contactPriority DESC");
			$maxPriorityQuery->execute(array(':dept' => $userdept));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$maxpri = $maxPriorityQuery->fetch(PDO::FETCH_ASSOC);
		$new_pri = $maxpri['contactPriority'] + 1;
		try {
			$updateQuery = $db->prepare("UPDATE contacts SET contactPriority=:priority, department=:dept WHERE id=:id");
			$updateQuery->execute(array(':priority' => $new_pri, ':dept' => $userdept, ':id' => $userid));
		} catch(PDOException $e) {
			exit("error in query");
		}
		try {
			$contactsQuery = $db->prepare("SELECT * FROM contacts WHERE department=:dept");
			$contactsQuery->execute(array(':dept' => $oldinfo['department']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while ($row = $contactsQuery->fetch(PDO::FETCH_ASSOC)) {
			if ($row['contactPriority'] > $userpriority) {
				$new_pri=$row['contactPriority'] - 1;
				try {
					$updateQuery = $db->prepare("UPDATE contacts SET contactPriority=:priority WHERE id=:id");
					$updateQuery->execute(array(':priority' => $new_pri, ':id' => $row['id']));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
	}
	try {
		$updateQuery = $db->prepare("UPDATE contacts SET name=:user, phone=:phone, address=:address, position=:position, managerFlag=:manager WHERE id=:id");
		$updateQuery->execute(array(':user' => $username, ':phone' => $userphone, ':address' => $useraddress, ':position' => $userposition, ':manager' => $usermanagerFlag, ':id' => $userid));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

if ($remove_contact!=""){
	try {
		$removeContactQuery = $db->prepare("DELETE FROM contacts WHERE id=:user");
		$removeContactQuery->execute(array(':user' => $userid));//Delete the selected contact
	} catch(PDOException $e) {
		exit("error in query");
	}
	try {
		$deptDataQuery = $db->prepare("SELECT * FROM contacts WHERE department=:dept ORDER BY contactPriority");
		$deptDataQuery->execute(array(':dept' => $userdept));//Delete the selected contact
	} catch(PDOException $e) {
		exit("error in query");
	}

	while ($contact = $deptDataQuery->fetch(PDO::FETCH_ASSOC)){ //Shift up the order of all the contacts in the department with higher number
		if ($contact['contactPriority'] > $userpriority){
			$move_down = $contact['contactPriority'] - 1;
			try {
				$updatePriorityQuery = $db->prepare("UPDATE contacts SET contactPriority=:priority WHERE id=:id");
				$updatePriorityQuery->execute(array(':priority' => $move_down, ':id' => $contact['id']));//Delete the selected contact
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
}

if ($new_priority!="") //Used if the priority has been changed (not the contact added, or deleted though)
{
	try {
		$deptDataQuery = $db->prepare("SELECT * FROM contacts WHERE department=:dept ORDER BY contactPriority");
		$deptDataQuery->execute(array(':dept' => $userdept));//Delete the selected contact
	} catch(PDOException $e) {
		exit("error in query");
	}

	while ($contact = $deptDataQuery->fetch(PDO::FETCH_ASSOC)){
		if ($contact['contactPriority'] >= $new_priority && $contact['contactPriority'] < $userpriority)
		{
			$move_up = $contact['contactPriority'] + 1;
			try {
				$updateMoveUpQuery = $db->prepare("UPDATE contacts SET contactPriority=:moveUp WHERE id=:id");
				$updateMoveUpQuery->execute(array(':moveUp' => $move_up, ':id' => $contact['id']));//Delete the selected contact
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
		else if ($contact['contactPriority'] == $userpriority)
		{
			try {
				$updateQuery = $db->prepare("UPDATE contacts SET contactPriority=:priority WHERE id=:id");
				$updateQuery->execute(array(':priority' => $new_priority, ':id' => $contact['id']));//Delete the selected contact
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
		else if ($contact['contactPriority'] > $userpriority && $contact['contactPriority'] <= $new_priority)
		{
			$move_down = $contact['contactPriority'] - 1;
			try {
				$updateMoveDownQuery = $db->prepare("UPDATE contacts SET contactPriority=:priority WHERE id=:id");
				$updateMoveDownQuery->execute(array(':priority' => $move_down, ':id' => $contact['id']));//Delete the selected contact
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
}

/****************************************************************************************
		Gather Database data for contacts and assemble into an array
*****************************************************************************************/
try {
	$contactsQuery = $db->prepare("SELECT * FROM contacts ORDER BY contactPriority");
	$contactsQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}
while ($row = $contactsQuery->fetch(PDO::FETCH_ASSOC))
{
	$id = $row['id'];
	$dept_id = $row['department'];

	$contactsarray[$dept_id][$id]['name'] = $row['name'];
	$contactsarray[$dept_id][$id]['phone'] = $row['phone'];
	$contactsarray[$dept_id][$id]['address'] = $row['address'];
	$contactsarray[$dept_id][$id]['position'] = $row['position'];
	$contactsarray[$dept_id][$id]['managerFlag'] = $row['managerFlag'];
	$contactsarray[$dept_id][$id]['priority'] = $row['contactPriority'];
	$contactsarray[$dept_id][$id]['id'] = $row['id'];
	$contactsarray[$dept_id][$id]['dept_id'] =$dept_id;
}
//print_r($organizationarray);


/****************************************************************************************
		Gather Database data for hierarchy and assemble into an array 
*****************************************************************************************/

try {
	$hierarchyDataQuery = $db->prepare("SELECT Dept.id AS dept_id, Dept.groupName as dept_name, Dept.priority AS dept_priority, Area.id as area_id, Area.groupName AS area_name, Area.priority AS area_priority, Org.id AS org_id, Org.groupName AS org_name, Org.priority AS org_priority FROM contactsHierarchy AS Dept JOIN contactsHierarchy AS Area JOIN contactsHierarchy AS Org ON Dept.higherUp = Area.id AND Area.higherUp = Org.id ORDER BY org_priority, area_priority, dept_priority");
	$hierarchyDataQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}

while ($row = $hierarchyDataQuery->fetch(PDO::FETCH_ASSOC))
{
	$department = $row['dept_name'];
	$departmentid = $row['dept_id'];
	$area = $row['area_name'];
	$areaid = $row['area_id'];
	$organization = $row['org_name'];
	$organizationid = $row['org_id'];

	if ($departmentid == "0"){
	} 
	else if ($areaid == "0"){
		$hierarchyarray[$departmentid]['name'] = $department;
		$hierarchyarray[$departmentid]['id'] = $departmentid;  
	}
	else if ($organizationid == "0"){
		$hierarchyarray[$areaid][$departmentid]['name'] = $department;
		$hierarchyarray[$areaid][$departmentid]['id'] = $departmentid;
	}
	else {
		$hierarchyarray[$organizationid][$areaid][$departmentid]['name'] = $department;
		$hierarchyarray[$organizationid][$areaid][$departmentid]['id'] = $departmentid;
	}
}
//print_r($hierarchyarray);


/************************************************************************************
		Begin actual page, with javascript functions
*************************************************************************************/

?>
<html>
<head>
<title>Edit Contacts</title>
<script type='text/javascript'>
function togglediv (whichdiv)
{
var elem, vis;
elem = document.getElementById(whichdiv);
vis = elem.style;
if (vis.display=='none') vis.display='block';
else vis.display='none';
}

function showdiv(whichdiv)
{
var elem, vis;
elem = document.getElementById(whichdiv);
vis = elem.style;
vis.display='block';
}

function validate_form(thisform){
with (thisform){
if (name.value != "" || position.value != "")
{return true} else {alert("Name or Position Required"); return false}
}
}

function delete_confirm()
{
var r = confirm("This action cannot be undone! Are you sure?")
return r
}

</script></head>
<body onLoad="document.new_contact.name.focus()">
<h1 align='center'>Edit the Contacts</h1>
<div id='links' align='center'>Back to the <a href="index.php"><b>Main Contacts page</b></a><br>
Go to the <a href="contactsEditHierarchy.php"><b>Contacts Hierarchy page</b></a>
</div>

<a href="javascript:togglediv('instructions');"><h3>Instructions</h3></a>
<div id='instructions' style='display:none'>
<p>The Organization selector below is used for narrowing down the page to the contacts within that Organization. Selecting an organization will bring up a drop down menu for selecting an area, and selecting an area will bring the department selector. These drop down menus also cause the 'Add contact' link to appear. Click this link to add a contact to the currently selected Organization, Area, and Department. Fill out the form and click 'Add this contact'. The check box for the VIP determines whether the contact will be shown as a manager on the contacts page. Take a look at the main contacts page and you'll see the difference. If you want to add a contact to a group that doesn't exist in these drop down menus, click on the link above to go to the Contacts Hierarchy Page. There you can add a group to the hierarchy, and then return to this page to add the contact.
<p>Below the drop down menus is the list of contacts in the database. It includes a drop down menu showing their order, as well as their name, phone, address, a description of their position, a checkbox if they are a Manager, the group they belong to, and two buttons for 'Edit' and 'Delete'. Selecting a new number for the order will move the selected contact to that number within the group, and shift the other contact's orders around accordingly. All other changes can be made to the text fields directly - or to the group field that designates which group the contact belongs to - and then hitting the 'Edit' button. The 'Delete' button will delete this contact from the database.
</div>
<?php


/**********************************************************************************
		Create the Organization Selector
**********************************************************************************/
echo "<hr>";
echo "<table style='border: none;'><tr style='border: none;'><td style='border: none;'>";
echo "<h4>Choose an Organization:</h4>";
echo "<form action='' method='POST' name='org_selector'>";
echo "<select name='current_org' onchange='document.org_selector.submit()'><option></option>";
	foreach($hierarchyarray as $org)
	{
		if(is_array($org)) {
			echo "<option value='".$org['id']."'";
			if($org['id'] == $current_org) echo " selected";
			echo ">".$org['name']."</option>\n";
		}
	}
echo "</select>";
echo "</form></td>";


/**********************************************************************************
		Create the Area Selector
**********************************************************************************/

echo "<td style='border: none;'>";

if ($current_org != "")
{
	$areaarray = $hierarchyarray[$current_org];
	echo "<h4>Select an area:</h4>";
	echo "<form action='' method='POST' name='area_selector'>";
	echo "<select name='current_area' onchange='document.area_selector.submit()'>\n";
	echo "<option></option>";
		foreach($areaarray as $ar)
		{
			if(is_array($ar)) { 
				echo "<option value='".$ar['id']."'";
				if($ar['id'] == $current_area) echo " selected";
				echo ">".$ar['name']."</option>\n";
			}
		}
	echo "</select>";
	echo "<input type='hidden' name='current_org' value='".$current_org."'>";
	echo "</form>";
}
echo "</td>";

/**********************************************************************************
		Create the Department Selector
**********************************************************************************/

echo "<td style='border: none;'>";

if ($current_area != "")
{
	$departmentarray = $areaarray[$current_area];
	echo "<h4>Select a Department:</h4>";
	echo "<form action='' method='POST' name='department_selector'>";
	echo "<select name='current_dept' onchange='document.department_selector.submit()'>\n";
	echo "<option></option>";
	foreach($departmentarray as $dept)
	{
		if(is_array($dept)) { 
			echo "<option value='".$dept['id']."'";
			if($dept['id'] == $current_dept) echo " selected";
			echo ">".$dept['name']."</option>\n";
		}
	}
	echo "</select>";
	echo "<input type='hidden' name='current_org' value='".$current_org."'>";
	echo "<input type='hidden' name='current_area' value='".$current_area."'>";
	echo "</form>";
}

echo "</td></tr></table>";

/**********************************************************************************
		Show the form for adding a new contact if a department is selected
**********************************************************************************/

if ($current_org !="")
	echo "<a href=\"javascript:showdiv('addcontact');\">Add a Contact</a>";

if ($add_contact!=""){
$displayAdd = "block";
}else $displayAdd= "none";
?>

<div id='addcontact' style="display:<?php echo $displayAdd; ?>">
<form name='new_contact' action='' method='POST'>
<input type='hidden' name='current_org' value="<?php echo $current_org;?>">
<input type='hidden' name='current_area' value="<?php echo $current_area;?>">
<input type='hidden' name='current_dept' value="<?php echo $current_dept;?>">
<input type='hidden' name='org' value="<?php echo $current_org;?>">
<input type='hidden' name='area' value="<?php echo $current_area;?>">
<input type='hidden' name='dept' value="<?php echo $current_dept;?>">
Name: <input type='text' name='name'>
Phone: <input type='text' name='phone' size='8'>
Address: <input type='text' name='address' size='8'>
Position: <input type='text' size='40' name='position'>
VIP: <input type='checkbox' name='managerFlag' value='1'>
<input type='submit' name='add_contact' value='Add this contact'>
</form>
</div>
<hr>
<?php

/**********************************************************************************
		Function used for generating each contact row
**********************************************************************************/
function printcontact($dept_array, $current_org, $current_area, $current_dept)
{
$length= count($dept_array);
$contact_order = 0;
	if (is_array($dept_array)){
		foreach ($dept_array as $contact)
		{
			//Create one form that is just for the priority drop down
			echo "<tr><td><form name='change_order_".$contact['id']."' action='' method='POST'>\n";
			echo "<select name='new_priority' onchange='document.change_order_".$contact['id'].".submit()'>\n";
			for ($i=0; $i<$length; $i++){
				echo "<option value='".$i."'";
				if ($i == $contact_order) echo " selected";
				echo ">".$i."</option>\n";
			}
			echo "</select>";
			echo "<input type='hidden' name='current_org' value='".$current_org."'>\n";
			echo "<input type='hidden' name='current_area' value='".$current_area."'>\n";
			echo "<input type='hidden' name='current_dept' value='".$current_dept."'>\n";
			echo "<input type='hidden' name='dept' value='".$contact['dept_id']."'>\n";
			echo "<input type='hidden' name='id' value='".$contact['id']."'>\n";
			echo "<input type='hidden' name='priority' value='".$contact['priority']."'></form></td>\n";
			
			//Create another form for all the other information
			if ($contact['managerFlag']==1) $flag='checked'; else $flag='';
			echo "\n<form action='' method='POST' onSubmit='return validate_form(this)'>\n";
			echo "<td><input type='text' name='name' value='".$contact['name']."' size='15'></td>\n";
			echo "<td><input type='text' name='phone' value='".$contact['phone']."' size='6'></td>\n";
			echo "<td><input type='text' name='address' value='".$contact['address']."' size='7'></td>\n";
			echo "<td><input type='text' name='position' value='".$contact['position']."' size='35'></td>\n";
			echo "<td align='center'><input type='checkbox' name='managerFlag' ".$flag." value='1'></td>\n";
			echo "<input type='hidden' name='priority' value='".$contact['priority']."'>\n";
			echo "<td>".createDDBox(3, $contact['dept_id'],-1)."</td>";
			echo "<td><input type='Submit' name='edit_contact' value='EDIT'></td>\n";
			echo "<td><input type='submit' name='remove_contact' value='DELETE' onClick='return delete_confirm()'></td>\n";
			echo "<input type='hidden' name='current_org' value='".$current_org."'>\n";
			echo "<input type='hidden' name='current_area' value='".$current_area."'>\n";
			echo "<input type='hidden' name='current_dept' value='".$current_dept."'>\n";
			echo "<input type='hidden' name='id' value='".$contact['id']."'></tr></form>\n";

			$contact_order++;
		}
	}
}
/***************************************************************************************
		Function for creating the drop down box of available areas to move the selected group
****************************************************************************************/
function createDDBox ($order, $higherUp, $id){
	global $hierarchyarray;
	$dropDown = "<select name='dept' style='width:110'>";
	if ($order < 3) {
		$dropDown .="<option value='0'";
		if ($higherUp == 0) $dropDown.=" selected";
		$dropDown .=">Organization</option>";
	}
	if ($order > 0) {
		foreach($hierarchyarray as $org){
			$dropDown .= "<option value='".$org['id']."'";
			if ($higherUp == $org['id']) $dropDown.= " selected";
			if ($id == $org['id']) $dropDown.=" disabled";
			$dropDown .= ">".$org['name']."</option>\n";
			if ($order > 1) {
				foreach ($org as $area){
					if (is_array($area)) {
						$dropDown .= "<option value='".$area['id']."'";
						if ($higherUp == $area['id']) $dropDown.=" selected";
						if ($id == $area['id']) $dropDown.=" disabled";
						$dropDown.=">&nbsp;&nbsp;-".$area['name']."</option>\n";
						if ($order > 2) {
							foreach ($area as $dept) {
								if (is_array($dept)) {
									$dropDown .= "<option value='".$dept['id']."'";
									if ($higherUp == $dept['id']) $dropDown.=" selected";
									if ($id == $dept['id']) $dropDown.=" disabled";
									$dropDown.=">&nbsp;&nbsp;&nbsp;&nbsp;-".$dept['name']."</option>\n";
								}
							}
						}
					}
				}
			}
		}
	}
	$dropDown .= "</select>";
	return $dropDown;
}
/**************************************************************************************
		Show the contact information for all contacts or selected Organization 
***************************************************************************************/
echo "<table style='table-layout: fixed; font-size: 80%; width: 106%; margin-left: -28px;'>";
	echo "<tr><th class='title' style='width: 5%;'>Order</th><th class='title'  style='width: 13%;'>Name</th><th class='title'  style='width: 6%;'>Number</th><th class='title' style='width: 7%;'>Address</th><th class='title' style='width: 26%;'>Position</th><th class='title' style='width: 2%;'>VIP</th><th class='title' style='width: 28%;'>Group</th><th class='title' style='width: 5%;'></th><th class='title' style='width: 7%;'></th></tr>";
foreach($hierarchyarray as $org)
{
	if (is_array($org) && ($org['id']==$current_org || $current_org==""))
	{
		echo "\n<tr><td colspan='9' style='text-align: center;'><h1>".$org['name']."</h1>\n";
		if ($current_area =="")
		{
			if (isset($contactsarray[$org['id']]))
			{
				printcontact($contactsarray[$org['id']], $current_org, $current_area, $current_dept);
			}
		}
		foreach($org as $ar)
		{
			if (is_array($ar) && ($ar['id']==$current_area || $current_area==""))
			{
				echo "\n<tr><td colspan='9' style='text-align: center;'><h2>".$ar['name']."</h2></td></tr>\n";
				if ($current_dept == "")
					if (isset($contactsarray[$ar['id']]))
					{
					printcontact($contactsarray[$ar['id']], $current_org, $current_area, $current_dept);
					}
				foreach($ar as $dept)
				{
					if(is_array($dept) &&($dept['id']==$current_dept || $current_dept=="")) 
					{
						echo "\n<tr><td colspan='9'><b>".$dept['name']."</br></td></tr>\n";
						if (isset($contactsarray[$dept['id']]))
						{
						printcontact($contactsarray[$dept['id']], $current_org, $current_area, $current_dept);
						}
					}
				}
			}
		}	
		
	}	
}
echo "</table>";

}
else echo '<br /><br /><h1 style="text-align: center;"> You Do Not Have Rights To View This Page! </h1><br /><h2 style="text-align: center;">If this is an error please speak with your manager to get your rights granted.<br /><br />Thank you for not trying to hack the site!<br />~The OPS Dev Team</h2>';

require("../includes/includeAtEnd.php");

?>
