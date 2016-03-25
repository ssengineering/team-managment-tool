<?php //checklist.php this is just a test page to build the checklist functionality into the sup report
require('../includes/includeme.php');

if(isset($_POST['submit'])){
	if(isset($_POST['openingList'])){
		echo "<br/>Opening List<br/>";
		try {
			$tasksQuery = $db->prepare("SELECT `ID`,`text` FROM supervisorReportSDTasks WHERE area= :area AND checklist = '0'");
			$tasksQuery->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($cur = $tasksQuery->fetch(PDO::FETCH_ASSOC)) {
			if(!isset($_POST[$cur['ID']])){
				echo "<br/>Not Complete: ".$cur['text']."<br/>";
			}
		}
	}
	if(isset($_POST['closingList'])){
		echo "<br/>Closing List<br/>";
		try {
			$tasks2Query = $db->prepare("SELECT `ID`,`text` FROM supervisorReportSDTasks WHERE area= :area AND checklist = '1'");
			$tasks2Query->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}
		while($cur = $tasks2Query->fetch(PDO::FETCH_ASSOC)) {
			if(!isset($_POST[$cur['ID']])){
				echo "<br/>Not Complete: ".$cur['text']."<br/>";
			}
		}
	}
}
?>
<script type='text/javascript'>
	window.onload = function(){ printList(0,"openResults"); printList(1,"closeResults"); };

	function printList(list,div){
		var page = "checklistAjax/printList.php?type="+list;
		
		var cb = function(result){ document.getElementById(div).innerHTML = result; };
		callPhpPage(page,cb);
		
	}

	function addItem(type){
		document.getElementById("itemText").value = "";
		$( "#itemEditor" ).dialog({
			resizable: false,
			width: 350,
			modal: true,
			draggable: true,
			buttons: [{ text: "Add Item", click: function() { add(type); } }]
		});
	}

	function add(type){
		$("#itemEditor").dialog("close");
		var name = document.getElementById("itemText").value;
		var page = "checklistAjax/addItem.php?text="+name+"&list="+type;
		if(type == 0){		
			var cb = function(result){ 
					$('#openList').append(result);
					};
		} else {
			var cb = function(result){ 
					$('#closeList').append(result);
					};
		}

		callPhpPage(page,cb);
	}
	
	function editItem(id){
		var text = document.getElementById("label"+id).innerHTML;
		document.getElementById("itemText").value = text;
		$( "#itemEditor" ).dialog({
			resizable: false,
			width: 350,
			modal: true,
			draggable: true,
			buttons: [{ text: "Edit Item", click: function() { edit(id); } }]
		});
		
	}
	
	function edit(id){
		$("#itemEditor").dialog("close");
		var name = document.getElementById("itemText").value;
		var page = "checklistAjax/editItem.php?text="+name+"&id="+id;
				
		var cb = function(result){ 
				document.getElementById("label"+id).innerHTML = " "+name;
				};

		callPhpPage(page,cb);
	}
	
	function deleteItem(id){
		var page = "checklistAjax/deleteItem.php?id="+id;
		
		var cb = function(result){ $('#row'+id).fadeOut('slow') };
		var r = confirm("Are you sure you want to delete this");
		if(r == true){
			callPhpPage(page,cb);
		}
	}
	
	function fadeRow(row){
		$('#'+row).fadeOut('slow');
	}
	
</script>
<h1>Checklist Test</h1>
<form id='checkLists' method='post' >
<input type='submit' value='Submit' name='submit' id='submit' />
<br/>
<br/>
<div id='openResults'>
</div>
<div id='closeResults'>
</div>
</form>
<div id='itemEditor' style='display:none;'>
	<h2>Add/Edit Item</h2>
	<table>
	<tr>
		<th>Text</th><td><input type='text' id='itemText' /></td>
	</tr>
	</table>
</div>
<?php
require('../includes/includeAtEnd.php');
?>
