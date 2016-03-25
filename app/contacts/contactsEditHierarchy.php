<?php

	require("../includes/includeme.php");

/**************************************************************************************
		Pull user's posted information
***************************************************************************************/

if (can("update", "f49362ef-983b-4615-ac64-727b769a713f"))//contacts resource 
{

$groupName = '';
$higherUp = '';
$userorg = '';
$userar = '';
$userdept = '';
$priority = '';
$removedata = '';
$removecontactdata = '';
$removehierarchydata = '';

if (isset($_POST['new_group'])) $groupName = $_POST['new_group'];
if (isset($_POST['new_higherUp'])) $higherUp = $_POST['new_higherUp'];
if (isset($_POST['org'])) $userorg = $_POST['org'];
if (isset($_POST['area'])) $userar = $_POST['area'];
if (isset($_POST['dept'])) $userdept = $_POST['dept'];
if (isset($_POST['new_priority'])) $priority= $_POST['new_priority'];


/****************************************************************************************
		If needed, make any changes to the database first
****************************************************************************************/

if (isset($_POST['add_group']) && $_POST['add_group']!=""){
	try {
		$deptDataQuery = $db->prepare("SELECT * FROM contactsHierarchy WHERE higherUp=:higher ORDER BY priority DESC");
		$deptDataQuery->execute(array(':higher' => $higherUp));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$deptdata = $deptDataQuery->fetch(PDO::FETCH_ASSOC);
	$new_pri = $deptdata['priority'] + 1;
	try {
		$insertQuery = $db->prepare("INSERT INTO contactsHierarchy (groupName, higherUp, priority, guid) VALUES (:group, :higher, :priority, :guid)");
		$insertQuery->execute(array(':group' => $_POST['new_group'], ':higher' => $_POST['new_higherUp'], ':priority' =>  $new_pri, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

if (isset($_POST['edit_group']) && $_POST['edit_group']!=""){
	try {
		$deptDataQuery = $db->prepare("SELECT * FROM contactsHierarchy WHERE higherUp=:higher ORDER BY priority DESC");
		$deptDataQuery->execute(array(':higher' => $higherUp));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$deptdata = $deptDataQuery->fetch(PDO::FETCH_ASSOC);
	try {
		$deptData2Query = $db->prepare("SELECT * FROM contactsHierarchy WHERE id=:id");
		$deptData2Query->execute(array(':id' => $userdept));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$deptdata2 = $deptdata2Query->fetch(PDO::FETCH_ASSOC);
	if ($deptdata2['higherUp'] == $higherUp)   //if not moving to a new group
	{
		try {
			$deptData2Query = $db->prepare("UPDATE contactsHierarchy SET groupName=:groupName, higherUp=:higher WHERE id=:id");
			$deptData2Query->execute(array(':group' => $groupName, ':higher' => $higherUp, ':id' => $userdept));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	else {
		$new_pri = $deptdata['priority'] + 1;
		try {
			$updateQuery = $db->prepare("UPDATE contactsHierarchy SET groupName=:group, priority=:priority, higherUp=:higher WHERE id=:id");
			$updateQuery->execute(array(':group' => $groupName, ':priority' => $new_pri, ':higher' => $higherUp, ':id' => $userdept));
		} catch(PDOException $e) {
			exit("error in query");
		}
		try {
			$deptData3Query = $db->prepare("SELECT * FROM contactsHierarchy WHERE higherUp=:higher");
			$deptData3Query->execute(array(':higher' => $deptdata2['higherUp']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while ($row = $deptData3Query->fetch(PDO::FETCH_ASSOC)) {
			if ($row['priority'] > $priority) {
				$new_pri=$row['priority'] - 1;  //shift the rest of the priorities up
				try {
					$updateQuery = $db->prepare("UPDATE contactsHierarchy SET priority=:priority WHERE id=:id");
					$updateQuery->execute(array(':priority' => $new_pri, ':id' => $row['id']));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
	}
}

if (isset($_POST['priority_down_x']) && $_POST['priority_down_x']!="") {
	try {
		$deptDataQuery = $db->prepare("SELECT * FROM contactsHierarchy WHERE higherUp =:higher ORDER BY priority");
		$deptDataQuery->execute(array(':higher' => $higherUp));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($group = $deptDataQuery->fetch(PDO::FETCH_ASSOC)){
		if ($group['priority'] == $priority) {
			$new_pri=$priority+1;
			try {
				$updateQuery = $db->prepare("UPDATE contactsHierarchy SET priority=:priority WHERE id=:group");
				$updateQuery->execute(array(':priority' => $new_pri, ':group' => $group['id']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
		else if ($group['priority'] == ($priority +1)) {
			try {
				$updateQuery = $db->prepare("UPDATE contactsHierarchy SET priority=:priority WHERE id=:group");
				$updateQuery->execute(array(':priority' => $priority, ':group' => $group['id']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
}
if (isset($_POST['priority_up_x']) && $_POST['priority_up_x']!="") {
	try {
		$deptDataQuery = $db->prepare("SELECT * FROM contactsHierarchy WHERE higherUp =:higher ORDER BY priority");
		$deptDataQuery->execute(array(':higher' => $higherUp));
	} catch(PDOException $e) {
		exit("error in query");
	}
	while ($group = $deptDataQuery->fetch(PDO::FETCH_ASSOC)){
		if ($group['priority'] == $priority) {
			$new_pri=$priority-1;
			try {
				$updateQuery = $db->prepare("UPDATE contactsHierarchy SET priority=:priority WHERE id=:group");
				$updateQuery->execute(array(':priority' => $new_pri, ':group' => $group['id']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
		else if ($group['priority'] == ($priority -1)) {
			try {
				$updateQuery = $db->prepare("UPDATE contactsHierarchy SET priority=:priority WHERE id=:group");
				$updateQuery->execute(array(':priority' => $priority, ':group' => $group['id']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
	}
}

if (isset($_POST['remove_group']) && $_POST['remove_group']!=""){
	try {
		$deptDataQuery = $db->prepare("SELECT * FROM contacts WHERE department = :dept");
		$deptDataQuery->execute(array(':dept' => $userdept));
	} catch(PDOException $e) {
		exit("error in query");
	}
	try {
		$contactsQuery = $db->prepare("SELECT * FROM contactsHierarchy WHERE higherUp=:higher");
		$contactsQuery->execute(array(':higher' => $userdept));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$emptyCheck = '';
	$emptyCheck2 = '';
	while ($row = $deptDataQuery->fetch(PDO::FETCH_ASSOC)){
		$emptyCheck = 1;
		$removecontactdata.= $row['name'] . ", ";
	}
	
	while ($row = $contactsQuery->fetch(PDO::FETCH_ASSOC)){
		$emptyCheck2 = 1;
		$removehierarchydata.= $row['groupName'] . ", ";
	}
	
	if ($emptyCheck == "" && $emptyCheck2 == ""){
		try {
			$deleteQuery = $db->prepare("DELETE FROM contactsHierarchy WHERE id=:id");
			$deleteQuery->execute(array(':id' => $userdept));
		} catch(PDOException $e) {
			exit("error in query");
		}
		try {
			$contactsQuery = $db->prepare("SELECT * FROM contactsHierarchy WHERE higherUp=:higher");
			$contactsQuery->execute(array(':higher' => $higherUp));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while ($row = $contactsQuery->fetch(PDO::FETCH_ASSOC)) {
			if ($row['priority'] > $priority) {
				$new_pri=$row['priority'] - 1;  //shift the rest of the priorities up
				try {
					$updateQuery = $db->prepare("UPDATE contactsHierarchy SET priority=:priority WHERE id=:id");
					$updateQuery->execute(array(':priority' => $new_pri, ':id' => $row['id']));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
	}
	else {
		$removedata = "<h4>Unable to delete this group! This group has the following dependants:</h4>";
		$removedata .= "Dependant Contacts: ".$removecontactdata;
		$removedata .= "<br>Dependent groups: ".$removehierarchydata;
	}
}


/****************************************************************************************
		Gather Database data for hierarchy and assemble into an array 
*****************************************************************************************/

try {
	$hierarchyQuery = $db->prepare("SELECT Dept.higherUp AS dept_higherUp, Dept.id AS dept_id, Dept.groupName as dept_name, Dept.priority AS dept_priority, Area.id as area_id, Area.groupName AS area_name, Area.priority AS area_priority, Org.id AS org_id, Org.groupName AS org_name, Org.priority AS org_priority FROM contactsHierarchy AS Dept JOIN contactsHierarchy AS Area JOIN contactsHierarchy AS Org ON Dept.higherUp = Area.id AND Area.higherUp = Org.id ORDER BY org_priority, area_priority, dept_priority");
	$hierarchyQuery->execute();
} catch(PDOException $e) {
	exit("error in query");
}

while ($row = $hierarchyQuery->fetch(PDO::FETCH_ASSOC))
{
	$department = $row['dept_name'];
	$departmentid = $row['dept_id'];
	$departmentup = $row['dept_higherUp'];
	$departmentpriority = $row['dept_priority'];
	$area = $row['area_name'];
	$areaid = $row['area_id'];
	$organization = $row['org_name'];
	$organizationid = $row['org_id'];

	if ($departmentid == "0"){
	} 
	else if ($areaid == "0"){ 	//if it is an organization
		$hierarchyarray[$departmentid]['name'] = $department;
		$hierarchyarray[$departmentid]['id'] = $departmentid;  
		$hierarchyarray[$departmentid]['higherUp'] = $departmentup;
		$hierarchyarray[$departmentid]['priority'] = $departmentpriority;
		$hierarchyarray[$departmentid]['ddbox'] = 2;
	}
	else if ($organizationid == "0"){	//if it is an area
		$hierarchyarray[$areaid][$departmentid]['name'] = $department;
		$hierarchyarray[$areaid][$departmentid]['id'] = $departmentid;
		$hierarchyarray[$areaid][$departmentid]['higherUp'] = $departmentup;
		$hierarchyarray[$areaid][$departmentid]['priority'] = $departmentpriority;
		$hierarchyarray[$areaid][$departmentid]['ddbox'] = 2;
		$hierarchyarray[$areaid]['ddbox']=1;
	}
	else {				//if it is a department
		$hierarchyarray[$organizationid][$areaid][$departmentid]['name'] = $department;
		$hierarchyarray[$organizationid][$areaid][$departmentid]['id'] = $departmentid;
		$hierarchyarray[$organizationid][$areaid][$departmentid]['higherUp'] = $departmentup;
		$hierarchyarray[$organizationid][$areaid][$departmentid]['priority'] = $departmentpriority;
		$hierarchyarray[$organizationid][$areaid][$departmentid]['ddbox'] = 2;
		$hierarchyarray[$organizationid][$areaid]['ddbox'] = 1;
		$hierarchyarray[$organizationid]['ddbox']=0;
	}
}
//print_r($hierarchyarray);

/************************************************************************************
		Begin actual page, with javascript functions
*************************************************************************************/

echo $removedata;

?>

<style>
.wrapper
{
width: 84%;
}
</style>

<script type="text/javascript">
function togglediv (whichdiv)
{
var elem, vis;
elem = document.getElementById(whichdiv);
vis = elem.style;
if (vis.display=='none') vis.display='block';
else vis.display='none';
}

function validate_form(thisform){
with (thisform){
if (isFinite(new_priority.value) && isFinite(new_higherUp.value) && new_higherUp.value >= 0 && new_group.value!="")
{return true} else {alert("Invalid input"); return false}
}
}

function delete_confirm()
{
var r = confirm("This action cannot be undone! Are you sure?")
return r
}

</script>
<div style="width: 100%; margin: auto; background-color: white; padding: 1%; margin-left: -10px;">
<h1 align='center' style="font-family: 'Palatino Linotype', Palatino, Times, 'Times New Roman', serif; margin: 0px; padding: 0px;">Edit Contacts Hierarchy</h1><br />
<div id='links' align='center' ><a href=index.php style="text-decoration: none;"><b>Main Contacts page</b></a>
 | <a href=contactsEdit.php style="text-decoration: none;"><b>Edit Contacts page</b></a><br /><br />
</div>
<a href="javascript:togglediv('instructions');"><h3>Instructions</h3></a>
<div id='instructions' style='display:none'>
<p>The Organization selector below is used for adding Organizations, Areas, and Departments to the contacts page hierarchy.You can create a new Organization from this menu - or select an Organization. Selecting an Organization will load another menu where you can create an area under the selected organization, or select an area. The same follows for departments under areas. Creating Organizations, Areas, or Departments is easy - simply select the 'Create' option from the drop down menu, enter the new group name, and hit 'Add'. To add contacts to your new group, follow the link above to go back to the 'Edit Contacts' page.
<p>Below the Organization selector form is the current contacts page hierarchy. Each row represents a group, with its name, its 'parent', arrows to move this group up or down, an edit button, and a delete button. If the group is first or last in its category, or if it is the only one in its category, the arrows will display accordingly. To change the name of the group, or the parent of the group, simply make the necessary changes and hit 'Edit'. To make the group an Organization (top level), select 'Organization' from the drop down menu. To delete a group, hit the 'Delete' button. This will bring a dialog box confirming your request to delete the group. However, if the group has any dependant groups, or any dependant contacts, the group will <i>not</i> be deleted and at the top of your screen it will display the dependant groups and contacts. Delete or move these first and then you will be able to delete that group.

</div>
<?php

/**********************************************************************************
		Create the Organization Selector
**********************************************************************************/
echo "<hr>";
echo "<table style='border: none;'><tr style='border: none;'><td style='border: none;'>";
echo "\n<h4>Choose an Organization:</h4>";
echo "<form action='' method='POST' name='org_selector'>";
echo "<select name='org' onchange='document.org_selector.submit()'><option></option>\n";
	foreach($hierarchyarray as $org)
	{
		if(is_array($org)) {
			echo "<option value='".$org['id']."'";
			if($org['id'] == $userorg) echo " selected";
			echo ">".$org['name']."</option>\n";
		}
	}
echo "<option value='create_org'";
if (isset($_POST['org']) && $_POST['org'] == 'create_org') echo " selected";
echo ">-- Create Organization --</option>";
echo "</select>";
echo "</form></td>";


/**********************************************************************************
		Create the Area Selector
**********************************************************************************/

echo "<td style='border: none;'>";

if ($userorg != "" && $userorg != "create_org")
{
	$areaarray = $hierarchyarray[$userorg];
	echo "\n<h4>Select an area:</h4>";
	echo "<form action='' method='POST' name='area_selector'>";
	echo "<select name='area' onchange='document.area_selector.submit()'>\n";
	echo "<option></option>";
		foreach($areaarray as $ar)
		{
			if(is_array($ar)) { 
				echo "<option value='".$ar['id']."'";
				if($ar['id'] == $userar) echo " selected";
				echo ">".$ar['name']."</option>\n";
			}
		}
	echo "<option value='create_area'";
	if (isset($_POST['area']) && $_POST['area'] == 'create_area') echo " selected";
	echo ">-- Create Area --</option>";
	echo "</select>";
	echo "<input type='hidden' name='org' value='".$userorg."'>";
	echo "</form>";
}
echo "</td>";

/**********************************************************************************
		Create the Department Selector
**********************************************************************************/

echo "<td style='border: none;'>";

if ($userar != "" && $userar != "create_area")
{
	$departmentarray = $areaarray[$userar];
	echo "\n<h4>Select a Department:</h4>";
	echo "<form action='' method='POST' name='department_selector'>";
	echo "<select name='dept' onchange='document.department_selector.submit()'>\n";
	echo "<option></option>";
	foreach($departmentarray as $dept)
	{
		if(is_array($dept)) { 
			echo "<option value='".$dept['id']."'";
			if($dept['id'] == $userdept) echo " selected";
			echo ">".$dept['name']."</option>\n";
		}
	}
	echo "<option value ='create_department'";
	if (isset($_POST['dept']) && $_POST['dept'] == 'create_department') echo " selected";
	echo ">-- Create Department --</option>";
	echo "</select>";
	echo "<input type='hidden' name='org' value='".$userorg."'>";
	echo "<input type='hidden' name='area' value='".$userar."'>";
	echo "</form>";
}

echo "</td></tr></table>";


/**********************************************************************************
		Show the form if the user wants to create a new group
**********************************************************************************/

if ($userorg == "create_org")
echo "Add an Organization:<form action='' method='POST'>
Name:<input type='text' name='new_group' value=''><br>
<input type='hidden' value='0' name='new_higherUp'><br>
<input type='submit' name='add_group' value='Add Organization'>
</form>\n";

if ($userar == "create_area")
echo "\n\nAdd an Area: <form action='' method='POST'>
Name:<input type='text' name='new_group' value=''><br>
<input type='hidden' value='$userorg' name='new_higherUp'><br>
<input type='hidden' value='$userorg' name='org'>
<input type='submit' name='add_group' value='Add Area'>
</form>\n";

if ($userdept == "create_department")
echo "\n\nAdd a Department: <form action='' method='POST'>
Name:<input type='text' name='new_group' value=''><br>
<input type='hidden' value='$userar' name='new_higherUp'>
<input type='hidden' value='$userar' name='area'>
<input type='hidden' value='$userorg' name='org'>
<input type='hidden' value='create_department' name='dept'>
<input type='submit' name='add_group' value='Add Department'>
</form>\n";


/********************************************************************************
		Function used for generating each group's row
**********************************************************************************/
function createRow($name, $id, $higherUp, $priority, $order, $length, $indent)
{
	global $debug;
	echo "\n<tr style='text-align: center;'>";
	for ($i=0; $i<$indent; $i++) { echo "<td style='border: none;'></td>"; }
	echo "<form action='' method='POST' onSubmit='return validate_form(this)'>\n";
	if ($debug) echo "<td style='border: none;' align=right>".$id."</td>\n";
	echo "<td colspan='4' style='border: none;'><input type='text' name='new_group' value='".$name."' size='28'></td>\n";
	echo "<input type='hidden' name='dept' value='".$id."'>\n";
	echo "<td colspan='4' style='border: none;'>".createDDBox($order, $higherUp, $id)."</td>";
	echo "<input type='hidden' name='new_priority' value='".$priority."'>\n";
	if ($length != 1 && $priority != $length)
		echo "<td style='border: none; text-align: right;'><input type='image' src='arrow_down.GIF' name='priority_down' border='1'  width='20' height='20'></td>";
	else echo "<td style='border: none;'></td>";
	if ($length != 1 && $priority != 1)
		echo "<td style='border: none; text-align: center;'><input type='image' src='arrow_up.GIF' name='priority_up' border='1' width='20' height='20'></td>";
	else echo "<td style='border: none;'></td>";
	if ($debug) echo "<td style='border: none;'>".$priority."</td>";
	echo "<td style='border: none;'><input type='submit' name='edit_group' value='EDIT' ></td>\n";
	echo "<td style='border: none;'><input type='submit' name='remove_group' value='DELETE' onClick='return delete_confirm()'></td></form></tr>\n";

}
/***************************************************************************************
		Function for creating the drop down box of available areas to move the selected group
****************************************************************************************/
function createDDBox ($order, $higherUp, $id){
	global $hierarchyarray;
	$dropDown = "<select name='new_higherUp' style=''>";
	$dropDown .="<option value='0'";
	if ($higherUp == 0) $dropDown.=" selected";
	$dropDown .=">Organization</option>";
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

echo "<hr><table style='width: 100%; font-size: 80%; border: none; padding: 0px; margin: auto; border-collapse: collapse; text-align: center;'>";
$orglength = count($hierarchyarray);
foreach($hierarchyarray as $org)
{
	$arealength = count($org)- 5;
	createRow($org['name'], $org['id'], $org['higherUp'], $org['priority'], $org['ddbox'], $orglength,  0);
	foreach ($org as $area){
		$deptlength = count($area)- 5;
		if (is_array($area)){
			createRow($area['name'], $area['id'], $area['higherUp'], $area['priority'], $area['ddbox'], $arealength, 4);
			foreach ($area as $dept){
				if (is_array($dept)){
					createRow($dept['name'], $dept['id'], $dept['higherUp'], $dept['priority'], $dept['ddbox'], $deptlength, 8);
				}
			}
		}
	}
}


echo "</table>";
echo "</div>";

}
else echo '<br /><br /><h1 style="text-align: center;"> You Do Not Have Rights To View This Page! </h1><br /><h2 style="text-align: center;">If this is an error please speak with your manager to get your rights granted.<br /><br />Thank you for not trying to hack the site!<br />~The OPS Dev Team</h2>';

include("../includes/includeAtEnd.php");

?>
