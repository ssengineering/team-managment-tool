<?php 
require('../../includes/includeme.php');
require('logFunctions.php');		
//mandatory whiteboard log
	//goal: print out in a readable format who viewed a specific mandatory whiteboard msg
if(can("access", "6e5f124a-b6f7-4ee7-8fb9-cab94ae881ae")){//whiteboardMandatoryLog resource
//---------Functions-----------------------------


?>
<!----------------------HTML------------------------>
<html>
<script>
	function newwindow(urlpass) {
	    window.open(urlpass,"Routine Task","status=1,width=800,height=700,scrollbars=1");
	}

	function loadList(msgId){
		var xmlhttp;
		if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		}else{// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function(){
			if (xmlhttp.readyState==4 && xmlhttp.status==200){
			    document.getElementById("info").innerHTML=xmlhttp.responseText;
			}
		}
		//document.getElementById("info").innerHTML=msgId;
		xmlhttp.open("GET", "printLog.php?msgId="+msgId, true);
		xmlhttp.send();
	}
</script>
<style>
#buttons{
	text-align:center;
	float:left;
	width:100%;
}
#info{
	text-align:center;

}
viewed{
	font-size:16px;
	color:#f8ad50;
}
#names{
	text-align:center;
	font-size:16px;
	color:#8f100a;
	margin:auto;
}
</style>
<h1 align='center'>Mandatory Whiteboard Log</h1>
<body>
<div id='buttons'>
<!--- <button type="button" onClick="loadList()">Has Viewed</button>
<button type="button" onClick="loadList()">Has Not Viewed</button>
<button type="button" onClick="loadList()">All</button> --->
</div>
<div align='center' width='100%'>
<form action="" name='bob'>
<select id='mandatoryMsgs' onchange="loadList(this.value)">
<?php getMandatoryMsgs($area); ?>
</select>
</form>
</div>
<div id='info'>
</div>
<body>
</html>
<?php } else{
	echo "<h1>You are Not authorized to view this page!</h1>";
	}
?>
<?php require('../../includes/includeAtEnd.php'); ?>
