<?php
require('../includes/includeMeBlank.php');

$date = date('Y-m-d');
?>
<script>
window.onload=function(){
			
		//This loads everything initially
			loadList();
		//This will reload parts of the page automatically	
			setInterval('loadList()',300000);
		}

		function loadList(){
			var xmlhttp;
			if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			}else{// code for IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4 && xmlhttp.status==200){
				    document.getElementById("taskList").innerHTML=xmlhttp.responseText;
					window.location.hash="incomplete";
				}
			}
			xmlhttp.open("GET", "printTVHud.php", true);
			xmlhttp.send();
		}
	function playSound(soundfile) {
		document.getElementById("seebeck").innerHTML=
		"<embed src=\""+soundfile+"\" hidden=\"true\" autostart=\"true\" loop=\"false\" />";
 	}
</script>
<style>
table, th, td
{
border: 1px solid black;
font-size:30px;
}
</style>
<div align='center'>
<h2>Routine Task List</h2>
<input type="submit" class='button' onclick="window.location.href='index.php'" value="Return To Ops Page" />
<div id='taskList'>

</div>
</div>
