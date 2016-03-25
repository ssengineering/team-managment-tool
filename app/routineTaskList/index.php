<?php
//this is the index file for the full routine task application. it will be the fully functional version of the task list. routineTaskTable.php will include the functions and code necessary to populate a space in a dashboard. so see those files.

require('../includes/includeme.php');
//check on status of the checkpermissions.php file and see if that was 
//included in the "includeme.php" file.
//

?>

<?php
//the file with methods for filling the routine task table.
require('routineTaskTable.php'); 
//HTML and PHP for setting up the page.

$permission = can("update", "f9244d83-d0fe-4205-a4eb-f0a1c9de8d88"); //routineTasks resource // this is where permissions will be checked
//$permission = 1;
if(isset($_GET['date'])){
	$date = $_GET['date'];
}else{
	$date = date('Y-m-d');
}
/*
$tom = date('d',strtotime($date))+1;
$tomorrow = date('Y-m-').$tom;
$yest = date('d',strtotime($date))-1;
$yesterday = date('Y-m-').$yest;
*/
$tomorrow = date('Y-m-d',strtotime($date."+1 day"));
$yesterday = date('Y-m-d',strtotime($date."-1 day"));


?>
<!-----------------------------------HTML----------------------------------->
<style type="text/css">
a{
color:black;
}
a:hover{
color:#023962;
}
#rtlTable{
font-size:13px; 
table-layout:fixed;
width:100%;
word-wrap: break-word;
}
.comments{
min-height: 50px;
max-height: 200px;
overflow-y: auto;
}


</style>
<!--------------------------------------------JAVASCRIPT---------------------------------------------->
<script language="JavaScript" src="/includes/libs/tcal.js"></script>
<link rel="stylesheet" type="text/css" href="/includes/libs/tcal.css" />

	<SCRIPT type="text/javascript">
		function newwindow(urlpass) {
		    window.open(urlpass,"Routine Task","status=1,width=1024,height=500,scrollbars=1");
		}
		window.onload=function(){
			$("#viewDate").datepicker({dateFormat: "yy-mm-dd"});
		//This loads everything initially
			loadList();
		//This will reload parts of the page automatically	
			setInterval('loadList()',30000);
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
			xmlhttp.open("GET", "printList.php?date="+'<?php echo $date;?>', true);
			xmlhttp.send();
		}

		function completeTask(id){
			$("#completeCommentDialog").find("textarea").val("");
			$("#completeCommentDialog").dialog({title:"Enter Comments Below", buttons:
			[{text:"OK", click:function()
				{
					$.post( "completeTask.php", {"id":id, "comment":$("#completeCommentDialog").find("textarea").val(), "date": '<?php echo $date;?>'});
					$("#completeCommentDialog").dialog("close");
					loadList()
				}}, {text:"Cancel", click:function()
				{
					$("#completeCommentDialog").dialog("close")
				}}]
			});
			//OLD code below
			/*
			if (window.XMLHttpRequest)
						  {// code for IE7+, Firefox, Chrome, Opera, Safari
						  xmlhttp=new XMLHttpRequest();
						  }
						else
						  {// code for IE6, IE5
						  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
						  }
						xmlhttp.onreadystatechange=function()
						  {
						  if (xmlhttp.readyState==4 && xmlhttp.status==200)
							{
							document.getElementById("taskList").innerHTML=xmlhttp.responseText;
							loadList();
							}
						  }
						var commentText = prompt("Enter Comments below","");
						if(commentText != null){
							xmlhttp.open("GET","completeTask.php?id="+ id+"&comment="+escape(commentText)+"&date="+'<?php echo $date;?>',true);
						xmlhttp.send();
						}*/
			
		}	
		function muteTask(id){
			if (window.XMLHttpRequest)
			  {// code for IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			  }
			else
			  {// code for IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			  }
			xmlhttp.onreadystatechange=function()
			  {
			  if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
				document.getElementById("taskList").innerHTML=xmlhttp.responseText;			
				loadList();
				}
			  }
			var commentText = prompt("Enter Comments below","");
			if(commentText != null){
				xmlhttp.open("POST","muteTask.php?id=" + id+"&comment="+escape(commentText)+"&date="+'<?php echo $date;?>',true);
				xmlhttp.send();
			}
		}

	function changeDate(){
		var reviewDate = document.getElementById("viewDate").value;
		window.location.href='index.php?date='+reviewDate;



	}
	</SCRIPT>
<!----------------------------BODY HTML---------------------------------->

		<div align='center'>
		<p></p>
			<img src="routinetasklist.png"/><p>
			<font size="3" face="Arial" color="#023962"><b>Tasks for <?php echo date('l, F jS, Y',strtotime($date)); ?></font></b>
			<div align='center'>
					<div align='center'>
					View Date: <input type="text" value="<?php echo $date; ?>" name="viewDate" id="viewDate" size=10 onChange="changeDate();" /><br/><br/>
			<?php //if we're on the day prior to the current date do not show this button			
				if($yesterday >= date('Y-m-d',strtotime("yesterday"))){
				 ?>	
					<input type="button" class='button' onClick="window.location.href='index.php?date=<?php echo $yesterday;?>'" value="<-"/>
			<?php } ?>
					<input type="button" class='button' onClick="window.location.href='index.php?date=<?php echo date('Y-m-d');?>'" value="Today"/>

			<?php  //if the date is the current tomorrow, don't show this button
 				if($tomorrow <= date('Y-m-d',strtotime("tomorrow"))){
			?>
					<input type="button" class='button' onClick="window.location.href='index.php?date=<?php echo $tomorrow;?>'" value="->"/>
			<?php } ?>
			</div>
			<br/>
			<div style="display:block; float:left;" align='right' id='listHeader'>			
		<?php if($permission){	 ?>				
			<input type="submit" class='button' onclick="javascript:newwindow('./createTask.php')" value="Create Routine Task"/>
			<input type="submit" class='button' onclick="javascript:newwindow('./createOneShot.php')" value="Create One-shot Task">					
		<?php } ?>
			</div>
			<div style="display:block; float:right;" align='right' id='editButtons'>
				<input type="button" class='button' onclick="window.location.href='taskListHud.php'" value="Ops Display HUD" />
			<?php if($permission){	 ?>			
				<input type="button" class='button' onClick="window.location.href='editHud.php'" value="Edit Tasks"/>
			<?php } ?>	
			</div>
			<br /><br/>
			<div id='taskList'>

			</div>
			</table>
		</div>
	</div>
	<div id ="completeCommentDialog" style="display: none"><textarea style="width: 95%; height: 95%; margin-left: auto; margin-right: auto"></textarea></div>

<?php require('../includes/includeAtEnd.php');?>
