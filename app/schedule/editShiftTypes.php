<?php
//editShiftTypes.php
//Problems with pulling escaped characters from the Database see the function pullShiftTypes() to fix it.

require('../includes/includeme.php');

$permission = can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85");//schedule resource

if(!$permission)
{
	echo "<h2>You do not have permission to view this page.</h2>";

	require('../includes/includeAtEnd.php');
	
	return;
}
	
try {
	$hourTypesQuery = $db->prepare("SELECT * FROM scheduleHourTypes WHERE area = :area AND `deleted` = 0 ORDER BY value ASC");
	$hourTypesQuery->execute(array(':area' => $area));
} catch(PDOException $e) {
	exit("error in query");
}

if(isset($_POST['submit']))
{
	while($hourType = $hourTypesQuery->fetch(PDO::FETCH_ASSOC))
	{
		if(isset($_POST[$hourType['ID'].'trade'])) {
			$trade = 1;
		} else {
			$trade = 0;
		}
		if(isset($_POST[$hourType['ID'].'view'])) {
			$view = 1;
		} else {
			$view = 0;
		}
		if(isset($_POST[$hourType['ID'].'ss'])) {
			$ss = 1;
		} else {
			$ss = 0;
		}
		if(isset($_POST[$hourType['ID'].'nw'])) {
			$nw = 1;
		} else {
			$nw = 0;
		}
		try {
			$insertQuery = $db->prepare("INSERT INTO scheduleHourTypes (ID,area,value,name,color,longName,permission,tradable,defaultView,selfSchedulable, `nonwork`,guid) 
				VALUES (:id, :area, :value, :typeId, :color, :long, :permission, :trade, :view, :ss, :nw, :guid)
				ON DUPLICATE KEY UPDATE value=:value1, name=:name1, color=:color1, longName=:long1, permission=:permission1, tradable=:trade1, defaultView=:view1, selfSchedulable=:ss1, `nonwork`=:nw1");
			$insertQuery->execute(array(
			':id'          => $hourType['ID'], 
			':area'        => $area,
			':value'       => $_POST[$hourType['ID'].'value'], 
			':typeId'      => $_POST[$hourType['ID']],
			':color'       => $_POST[$hourType['ID'].'color'],
			':long'        => $_POST[$hourType['ID'].'longName'],
			':permission'  => $_POST['permission'][$hourType['ID']],
			':trade'       => $trade, 
			':view'        => $view,
			':ss'          => $ss,
			':nw'          => $nw,
			':guid'        => newGuid(),
			':value1'      => $_POST[$hourType['ID'].'value'],
			':name1'       => $_POST[$hourType['ID']],
			':color1'      => $_POST[$hourType['ID'].'color'],
			':long1'       => $_POST[$hourType['ID'].'longName'],
			':permission1' => $_POST['permission'][$hourType['ID']],
			':trade1'      => $trade,
			':view1'       => $view,
			':ss1'         => $ss,
			':nw1'         => $nw));
		} catch(PDOException $e) {
			exit("error in query");
		}
	}
}
?>

<script type='text/javascript'>

window.onload = function()
{
	printShifts();
}

function printShifts()
{
	var page = 'shiftAjax/printShift.php';
			
	var cb = function(result)
	{
		document.getElementById("results").innerHTML = result;
		normalizeValues();
	};

	callPhpPage(page,cb);
}


function deleteShift()
{
	var id = document.getElementById('shiftTypes').value;
	var r = confirm("Are you sure you want to Delete this hour type?");
	
	if(r == true)
	{
		var page = 'shiftAjax/deleteShift.php?id='+id;
	
		var cb = function(result){ printShifts(); };

		callPhpPage(page,cb);
	}
}

function insertShift()
{
	var r = confirm("Are you sure you want to Insert a new hour type?");

	if(r == true)
	{
		var page = 'shiftAjax/insertShift.php';
	
		var cb = function(result){ printShifts(); };

		callPhpPage(page,cb);
	}
}

function rowDragStart(event)
{
	event.dataTransfer.setData('text/plain', event.target.id);
	event.currentTarget.style.backgroundColor = event.currentTarget.children[3].bgColor;
	sessionStorage.setItem('dragRowId', event.target.id);
}

function rowDragEnter(event)
{
	event.preventDefault();
	
	var targetRow = event.currentTarget;
	var data = sessionStorage.getItem('dragRowId');
	var dragRow = document.getElementById(data);
	var tableRows = document.getElementById('shiftTable').childNodes[1];
	
	if (data != targetRow.id)
	{
		(tableRows.lastChild == targetRow) ? tableRows.insertBefore(dragRow, null) : tableRows.insertBefore(dragRow, targetRow);
	}
		
}

function rowDragOver(event)
{
	event.preventDefault();
}

function rowDragLeave(event)
{
	event.preventDefault();
}

function rowDrop(event)
{
	event.preventDefault();
	var data = sessionStorage.getItem('dragRowId');	
	if(data)
	{
		sessionStorage.removeItem('dragRowId');
		document.getElementById(data).style.backgroundColor = 'white';	
	}

	normalizeValues();
}

function rowDragEnd(event)
{
	event.preventDefault();	
	var data = sessionStorage.getItem('dragRowId');
	if(data)
	{
		sessionStorage.removeItem('dragRowId');
		document.getElementById(data).style.backgroundColor = 'white';	
	}

	normalizeValues();
}

function normalizeValues()
{
	var tableRows = document.getElementById('shiftTable').childNodes[1];
	var tableRowsNumber = tableRows.rows.length;
	
	for(var i = 0; i < tableRowsNumber; i++)
	{
		tableRows.rows[i].childNodes[0].value = i;
	}
}

</script>

<style>
	.operationStatus
	{
	    position: fixed !important;
	    display: table-cell;
	    vertical-align: middle;
	    right: 10px;
	    bottom: 10px;
	    text-align: right;
	    padding: .5em;
	    height: auto;
	    width: auto;
	    background-color: #369;
	    z-index: 10;
	    border: .2em solid #011948;
	    color: lightsteelblue;
	    border-radius: .8em;
	}
	.operationStatus.success
	{
		background-color: #76913C;
		border: .2em solid #587B0E;
		color: lightgrey;
	}
	.operationStatus.warning
	{
	    background-color: #EED50B;
	    border: .2em solid #E1A216;
	    color: #4d4d4d;
	}
	.operationStatus.failure
	{
	    background-color: #ED2604;
	    border: .2em solid #A11A03;
	    color: silver;
	}
</style>

<h2 align='center'>Edit Shift Types</h2>

<br/>

<div align='center'>
	Instructions: You MUST submit any changes you have made before doing anything else, or you will lose your changes. <br/> The color field will accept an HTML accepted color in the form of a string literal or hexcode like this: #000000. <br/>For a list of HTML recognized colors visit <a href='http://www.w3schools.com/html/html_colornames.asp'>W3 schools color list</a>
</div>

<form name='editHours' method='post'>
	<div align='center'>
		<input type='button' class='button' name='newHour' value="Insert New Shift" onclick='insertShift()' />
		<input type='submit' class='button' name='submit' value="Submit Changes" /> 
	</div>

	<div align='center' style="margin:auto;" id='results'>
	</div>

</form>

<br/>

<?php 
require('../includes/includeAtEnd.php');
?>
