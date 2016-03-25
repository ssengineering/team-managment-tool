<?php
	require('../includes/includeme.php'); 
	if(!checkPermission('permissions')){
		echo "<h1>You are not authorized to view this page.</h1>";
		require('../includes/includeAtEnd.php');
		return;
	}
?>
<script>
	var netID;
	function load(){
		obj = document.getElementById('userSelect');
		netID = obj.options[obj.selectedIndex].value;
		userSelect(netID);
	}
	function userSelect(user){
		netID=user;
		var page="pullGroupedPermissions.php?netID=" + user;
		callPhpPage(page,permissionFill);
	}
	function nameFill(result){
		document.getElementById('name').innerHTML="<h2>Currently editing rights for "+result+"</h2>";
	}
	function permissionFill(result){
		document.getElementById('main').innerHTML=result;
	}
	function reselect(){
		if(document.getElementById('nameSelect').style.display == "none"){
			document.getElementById('name').style.display = "none";
			document.getElementById('nameSelect').style.display = "block";
		}
	}
	function showHide(element){
		if(document.getElementById(element).style.display=='none')
			document.getElementById(element).style.display= 'block';
		else
			document.getElementById(element).style.display='none';
	}
	function grantPermission(index){
		var page="grantPermission.php?netID=" + netID + "&index=" + index;
		callPhpPage(page,checkAction);
	}
	function revokePermission(index){
		var page="revokePermission.php?netID=" + netID + "&index=" + index;
		callPhpPage(page,checkAction);
		//userSelect(netID);
	}
	function grantGroupPermission(index){
		var page="grantGroupPermission.php?netID=" + netID + "&index=" + index;
		callPhpPage(page,checkAction);
		alert("Granting rights from user: " + netID + " for selected Group");			
		userSelect(netID);
	}
	function revokeGroupPermission(index){
		var page="revokeGroupPermission.php?netID=" + netID + "&index=" + index;
		callPhpPage(page,checkAction);
		alert("Removing rights from user: " + netID + " for selected Group");	
		userSelect(netID);
		
		//userSelect(netID);
	}

	function revokeAll(){
		var r=confirm("WARNING: You are about to revoke ALL rights to this website for user: " + netID);
		if (r==true){
			var page="revokeAllPermissions.php?netID=" + netID;
			callPhpPage(page,checkAction);
			userSelect(netID);
		}
	}

	function checkAction(result){
		if(result)
			alert(result);
	}
	window.onload=load;
</script>

<style>
.header{
	text-align:center;
	padding-bottom:10px;
}
.main{
	width:40%;
	margin:auto;
	font-size:120%;
	line-height:15px;
}
.title{
	display:inline;
}
.description{
	font-style:italic;
	margin-top:10px;
	margin-left:20px;
}
</style>

<div class='header'>
	<h1>Permissions</h1>
	<div align='center'>
	<b>Instructions: </b>You may only Grant others permissions that YOU currently have. <br/>If you remove a permission from yourself you will not be able to grant it back to yourself.<br/>As soon as a box is checked all permissions in that group are granted.<br/><br/>
	</div>
	<div id='nameSelect'>Select Employee:<select id='userSelect' onchange='userSelect(this.options[this.selectedIndex].value)'><?php employeeFillSelected($netID, $area); ?></select></div>
	<!--Depracated Code for showing who you are editing. <div id='name'></div> -->
</div>
<div id='main' class='main'>

</div>
<div align='center'>
<br/><br/>
<input type='button' value="Remove All Permissions" id='removeAll' name='removeAll' onclick='revokeAll()' />
</div>
<?php
	require('../includes/includeAtEnd.php');
?>
