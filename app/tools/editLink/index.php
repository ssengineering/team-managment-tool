<?php //index.php

ob_start();

require ('../../includes/includeme.php');

if(isset($_POST['submit'])){
	foreach($_POST['order'] as $key=>$val){
		try {
			$updateQuery = $db->prepare("UPDATE `link` SET `sortOrder` = :val WHERE `index` = :key");
			$updateQuery->execute(array(':val' => $val, ':key' => $key));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
	header('Location: '.$_SERVER['PHP_SELF']);
	exit();	
}

?>

<script type="text/javascript" src="fixup.js"></script>
<link rel="stylesheet/less" type="text/css" href="editLink.less" />
<script src="less.js" type="text/javascript"></script>

<?php
//Prints out the Links in order to edit them.
function printLinkManager($area,$netID){
    $parentLinks = array();
    $parentLinks = getLinksWherePermissionExists($area,$netID,"IS NULL");
    
    foreach($parentLinks as $link){
        echo "<table>";
        echo "<tr><th><input type='text' id='".$link['index']."' name='order[".$link['index']."]' value='".$link['sortOrder']."' /></th><th>";
        //print link header with appropriate edit options
        echo '<span style="cursor:pointer"><div id="title" onclick="togglediv('.$link['index'].')">'.$link['name'];
        echo '</div></span></th><td>';
        echo '<input type="button" value="Edit" onclick="javascript:newwindow(\'./editLink.php?id='.$link['index'].'\')">';
        echo "";
        echo '</td><tr>';
        echo '</table>';
        //get children links
        $childrenLinks = getLinksWherePermissionExists($area,$netID,$link['index']);
        echo '<div id='.$link['index'].' style="display:none;">';
        //for each child link
        echo '<table>';
        foreach($childrenLinks as $child){
            //print link header with appropriate edit options
            echo '<tr><td>';
            echo $child['name'];
            echo '</td><td>';
            echo '<input type="button" value="Edit" onclick="javascript:newwindow(\'./editLink.php?id='.$child['index'].'\')">';
            echo '</td></tr>';
            //get sub-children links
            
            //for each sub-child link
                //print link header with appropriate edit options
        }
        echo'</table></div>';
    }
   //return
}
function recursiveCall($parentIndex)
{
	echo "<ul class='sortList'>";
	foreach($parentIndex as $index)
	{
		if(linkHasChild($index['index']) && linkIsVisible($index['index']))
		{
			//print link header with appropriate edit options
			echo "<li style='list-style-type: none;'>";
			echo "<input type='text' style='width:20px; display:none;' id='".$index['index']."' name='order[".$index['index']."]' value='".$index['sortOrder']."' />";

			echo '<div style="display: inline;" '.((isSuperuser())?'class="trigger"':'class="trigger"').' index="'.$index['index'].'" id="title'.$index['index'].'" internal="'.$index['internal'].'" onclick="togglediv(\'children'.$index['index'].'\')"><b>'.$index['name'].'</b>';

			echo '</div>';
			$content  = "<span class='popup' id='span".$index['index']."' style='display:none;margin-left:10px; '>";
			$content.= '<input type="button" value="Edit" onclick="javascript:newwindow(\'./editLink.php?id='.$index['index'].'&internal='.$index['internal'].'\')">';
			$content2= '<input type="button" value="Delete" onclick="deleteLink('.$index['index'].')">';	
			$content2.= "</span>";
			
			if(isSuperuser())
			{
				echo $content.$content2;
			}
			else
			{
				$content.="</span>";
				echo $content;
			}
			echo '<br/><br/>';

			echo  '<div id="children'.$index['index'].'" style="display:none;">';
			$nextLevel=linkPullChildren($index['index']);
			recursiveCall($nextLevel);
			echo '</div>';
			echo "</li>";
		}
		else
		{
			if(linkIsVisible($index['index']))
			{
				echo '<li style="list-style-type: none;">';
				echo "<input type='text' style='width:20px;display:none;' id='".$index['index']."' name='order[".$index['index']."]' value='".$index['sortOrder']."' />";

				echo "<span ".((isSuperuser())?'class="trigger"':'class="trigger"')." id='".$index['index']."span' index='".$index['index']."'>".$index['name'];
				echo "</span>";
				$content  = "<span class='popup' id='span".$index['index']."' style='display:none;margin-left:10px;'>";
				$content.=  '<input type="button" value="Edit" onclick="javascript:newwindow(\'./editLink.php?id='.$index['index'].'\')">';
				$content2= '<input type="button" value="Delete" onclick="deleteLink('.$index['index'].')">';
				$content2.= "</span>";
				if(isSuperuser())
				{
				echo $content.$content2;
			}
			else
			{
				$content.="</span>";
				echo $content;
			}
				echo '<br/><br/>';
				echo '</li>';
			}
		}
	}
	echo "<ul>";
}

	function pullEditableLinks($netID){
		global $area;
		
		$topLevel= getLinksWherePermissionExists($area,$netID,"IS NULL");
		recursiveCall($topLevel);
	}
		
		

//Pulls all links from the database where user has permission
function getLinksWherePermissionExists($area,$netID,$parent){
	global $db;
	//pulls all links in the given area
	if($parent == "IS NULL") {
		$linkQueryString = "SELECT `link`.*, `app`.`internal` FROM `link`LEFT JOIN `app` ON `link`.`appId` = `app`.`appId` WHERE `link`.`area`=:area AND `parent` IS NULL ORDER BY sortOrder";
		$linkQueryParams = array(':area' => $area);
	} else {
		$linkQueryString = "SELECT `link`.*, `app`.`internal` FROM `link`LEFT JOIN `app` ON `link`.`appId` = `app`.`appId` WHERE `link`.`area`=:area AND `parent` = :parent ORDER BY sortOrder";
		$linkQueryParams = array(':area' => $area, ':parent' => $parent);
	}
	try {
		$linkQuery = $db->prepare($linkQueryString);
		$linkQuery->execute($linkQueryParams);
	} catch(PDOException $e) {
		exit("error in query");
	}
    //for each link check the permissions against the permissions table
    $linksToDisplay = array();
    while($link = $linkQuery->fetch(PDO::FETCH_ASSOC)) {
        //if a links permission is not null
        if($link['permission'] != NULL){		
            //pull that permission from the employeePermissions table for the current netID
			try {
				$permissionQuery = $db->prepare("SELECT permission FROM employeePermissions WHERE `permission`= :permission AND netID=:netId");
				$permissionQuery->execute(array(':permission' => $link['permission'], ':netId' => $netID));
			} catch(PDOException $e) {
				exit("error in query");
			}
            //if that permission exists (ie. the number of rows returned is not 0) then add that link to the links to display
            if($row = $permissionQuery->fetch(PDO::FETCH_ASSOC)) {
		        $linksToDisplay[] =$link;
		    }
	    }else{
	       $linksToDisplay[]=$link; 
        }
    }
    return $linksToDisplay;
}


if(isSuperuser()){
?>
<script type="text/javascript">
	function newwindow(urlpass)
	{
		window.open(urlpass, "Edit Link", "status=1,width=1024,height=500,scrollbars=1");
	}

	function deleteLink(linkId)
	{
		var r = confirm("Are you sure you want to delete this link? \nIMPORTANT: deleting a parent link will delete ALL of its sub-links");
		if (r == true)
		{
			$.ajax(
			{
				type : "GET",
				url : "deleteLink.php",
				data : "id=" + linkId,
				success : function(data)
				{
					location.reload();
				}
			});
		}
	}

	function togglediv(divname)
	{
		if (document.getElementById(divname).style.display == "none")
		{
			document.getElementById(divname).style.display = "block";
		}
		else
		{
			document.getElementById(divname).style.display = "none";
		}
	}

</script>
<style type="text/css">
      table
      {
            width: 20%;
      }
      th
      {
            width: 10%;
      }
      td
      {
            width: 10%;
      }

</style>
<div id='whole'>
    <h2 align='center'>Links</h2>
    <b>Instructions:</b> Click on arrow next to Bolded link names to reveal children links.
    <br/>
    To change the order in which links appear in the menu bar, drag them and put them in the desired order and submit order change.
    <br>
    To edit a link put the cursor on top of the link you want to edit and editing options will show up if you have the rights to edit the link.
    <br>
    If you do not have the rights to edit the link you can still rename them by double clicking on the link name.
    <br/>
    <br/>
    <a href='../addLink/index.php'>Return to Main Page</a>
    <br/>
    <div id='managediv'>
        <form method='post' id='sortOrder' name='sortOrder'>
            <?php pullEditableLinks($netID); ?>

            <br/>
            <br/>
            <input type='submit' id='submit' name='submit' value="Submit Sort Order Changes" />
        </form>
    </div>
</div>

<?php
}else{
echo "<h1>You are not Authorized to view this page</h1>";
}
require('../../includes/includeAtEnd.php');
?>
<script type="text/javascript" src="piacenti.js"></script>
<?php ob_end_flush();  ?>
