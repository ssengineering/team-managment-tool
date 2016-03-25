<?php //editHud.php
/*
This file produces the HUD for editing all of the routine tasks in the in the routineTasks table from the database. It will display all of the information about each individual task and then have an option to edit the current task.
*/

//-----------------------INCLUDES------------------------------
require('../includes/includeme.php');
require('routineTaskTable.php');
//----------------------FUNCTIONS-----------------------------


?>
<style type="text/css">

input.edit
{
	font-family:Times New Roman,serif;
	color:#FFFFFF;
	background-color:#000099;
}

</style>
<SCRIPT type="text/javascript">
		function newwindow(urlpass) {
		    window.open(urlpass,"Routine Task","status=1,width=1024,height=500,scrollbars=1");
		}
		window.onload=function(){
			
		//This loads everything initially
			loadList("all");
		}

		function loadList(sortedBy){
			var xmlhttp;
			if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			}else{// code for IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4 && xmlhttp.status==200){
				    document.getElementById("editHud").innerHTML=xmlhttp.responseText;
						sorttable.makeSortable(document.getElementById('HUD'));
				}
			}
			xmlhttp.open("GET", "printHUD.php?sortedBy="+sortedBy, true);
			xmlhttp.send();
		}
</SCRIPT>
		<h1 align='center'>Edit Task HUD</h1>
	<body>
		<div alight='left' >
			<input type="button" onClick="window.location.href='index.php'" value="Back to Task List"/>
			<font size='3'>Sort List by the following:</font>
			<input type="button" onClick="loadList('enabled')" value="Enabled"/>
			<input type="button" onClick="loadList('disabled')" value="Disabled"/>
			<input type="button" onClick="loadList('one')" value="One-Shot"/>
			<input type="button" onClick="loadList('routine')" value="Routine Tasks"/>
			<input type="button" onClick="loadList('all')" value="All Tasks"/>
		</div>
		<br /><br />
		<div id='editHud'>
			
			<?php
				//this is where the function call will go to populate the table with all of the information about the tasks that we need to display. ?>

		</div>
	</body>
</html>


<?php require('../includes/includeAtEnd.php');

