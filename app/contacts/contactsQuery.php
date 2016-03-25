<?php
require('../includes/includeMeBlank.php');

$str=$_GET['q'];

$searchTerms=explode(' ',$str);


//USED IN BUILDING THE QUERY STRING IN ORDER TO ADD THE AND SYMBOLS AS NECESSARY
$wordCounter = 1;

//BUILDING QUERY STRING
$queryString = 'SELECT contacts.name, contacts.phone, contacts.address, contacts.position, contacts.contactPriority, contacts.managerFlag, contacts.id, Dept.id AS deptId, Dept.groupName as deptName, Dept.priority AS deptPriority, Area.id AS areaId, Area.groupName AS areaName, Area.priority AS areaPriority, Org.id AS orgId, Org.groupName AS orgName, Org.priority AS orgPriority FROM contacts JOIN contactsHierarchy AS Dept JOIN contactsHierarchy AS Area JOIN contactsHierarchy AS Org ON Dept.higherUp = Area.id AND Area.higherUp = Org.id AND contacts.department = Dept.id WHERE ';

$params = array();
$paramCount = 0;
foreach($searchTerms as $word)
{
	$word = trim($word);
	if( $wordCounter > 1)
	{
		$queryString .= " AND ";
	}
	$wordCounter++;
	$queryString.=" (contacts.name LIKE :word".$paramCount." OR contacts.position LIKE :word".($paramCount+1)." OR contacts.phone LIKE :word".($paramCount+2)." OR contacts.address LIKE :word".($paramCount+3)." OR Dept.groupName LIKE :word".($paramCount+4)." OR Area.groupName LIKE :word".($paramCount+5)." OR Org.groupName LIKE :word".($paramCount+6).") ";
	$params[':word'.$paramCount++] = '%'.$word.'%';
	$params[':word'.$paramCount++] = '%'.$word.'%';
	$params[':word'.$paramCount++] = '%'.$word.'%';
	$params[':word'.$paramCount++] = '%'.$word.'%';
	$params[':word'.$paramCount++] = '%'.$word.'%';
	$params[':word'.$paramCount++] = '%'.$word.'%';
	$params[':word'.$paramCount++] = '%'.$word.'%';
}
$queryString .= " ORDER BY orgPriority, areaPriority, deptPriority, contactPriority";

try {
	$query = $db->prepare($queryString);
	$query->execute($params);
} catch(PDOException $e) {
	exit("error in query");
}
while ($row = $query->fetch(PDO::FETCH_ASSOC))
{
	if ($row['orgId'] == "0" && $row['areaId'] == "0"){
		$row['areaName'] = $row['deptName'];
		$row['areaId'] = $row['deptId'];
		$row['orgName'] = $row['areaName'];
		$row['orgId'] = $row['areaId'];
	}
	if ($row['orgId'] == "0" && $row['areaId'] != "0"){
		$row['orgName'] = $row['areaName'];
		$row['orgId'] = $row['areaId'];
		$row['areaName']= $row['deptName'];
		$row['areaId'] = $row['deptId'];
	}
	
	$id = $row['id'];
	$department = $row['deptName'];
	$departmentid = $row['deptId'];
	$area = $row['areaName'];
	$areaid = $row['areaId'];
	$organization = $row['orgName'];
	$organizationid=$row['orgId'];

	$contact[$id]['name'] = $row['name'];
	$contact[$id]['phone'] = $row['phone'];
	$contact[$id]['address'] = $row['address'];
	$contact[$id]['position'] = $row['position'];
	$contact[$id]['managerFlag'] = $row['managerFlag'];
	$contact[$id]['priority'] = $row['contactPriority'];
	$contact[$id]['id'] = $row['id'];
	$contact[$id]['deptId'] =$departmentid;
	
	$organizationarray[$organizationid]['name'] = $organization;
	$organizationarray[$organizationid]['id'] = $row['orgId'];
	$organizationarray[$organizationid][$areaid]['name']=$area;
	$organizationarray[$organizationid][$areaid]['id']=$row['areaId'];
	$organizationarray[$organizationid][$areaid][$departmentid]['name']=$department;
	$organizationarray[$organizationid][$areaid][$departmentid]['id'] = $row['deptId'];
	$organizationarray[$organizationid][$areaid][$departmentid][$id] = $contact[$id];
}

echo "<div style='width: 100%; text-align: center;'>";

if (isset($organizationarray))
	{
	foreach($organizationarray as $org)
		{

		echo "<div style='float: left; text-align: center; width: 100%;'><h2 style='color: #003366;'>".$org['name']."</h2></div><div style='clear: both;'>&nbsp;</div>\n";
			foreach($org as $ar)
			{
		 	if (is_array($ar))
				{
				if ($ar['name']!=$org['name']) echo "\n<div style='float: left; text-align: center; width: 100%;'><h3>".$ar['name']."</h3></div><div style='clear: both;'>&nbsp;</div>\n";
				foreach($ar as $dept)
					{
					if(is_array($dept)) 
						{
						if ($dept['name']!=$ar['name'])
							{
							echo "\n<div style='float: left; text-align: center; font-weight: bold;'>".$dept['name']."</div><div style='clear: both;'>&nbsp;</div>\n";
							}
						foreach($dept as $contact)
							{
							if(is_array($contact))
								{
								if ($contact['managerFlag']==1) $classinfo='manager'; else $classinfo='contact';
								if ($contact['address']=='') $contact['address']= 'N/A';
								echo "\n<div class='".$classinfo."' style='float: left; text-align: left; min-width: 18%; max-width: 18%; margin-left: 5%; margin-right: 0%;'>".$contact['name']."</div>\n";
								echo "<div class='".$classinfo."' style='float: left; text-align: center; min-width: 16%; max-width: 16%;'>".$contact['phone']."</div>\n";
								echo "<div class='".$classinfo."' style='float: left; text-align: center; min-width: 16%; max-width: 16%;'>".$contact['address']."</div>\n";
								echo "<div class='".$classinfo."' style='float: left; text-align: left; min-width: 40%; max-width: 40%; margin-left: 0%; margin-right: 5%;'>".$contact['position']."</div><div style='clear: both;'>&nbsp;</div>\n";
								}
							}
						}
					}
				}
			}
		}
	}

echo "</div>";

?>
