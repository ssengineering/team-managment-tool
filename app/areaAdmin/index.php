<?php
require('../includes/includeme.php');
/*
This application is designed to give users access to different areas of the website 
ie. Ops, SD, COS, etc.
It will allow the granting and revoking of access to areas of the website beyond their default area.
A user cannot be removed from their default area.
*/

$permission = isSuperuser();

if(!$permission)
{
	echo "<h2>You do not have permission to view this page.</h2>";

	require('../includes/includeAtEnd.php');

	return;
}

function pullAreasToSelect(){
	global $area, $db;
	$selected = "";
	try {
		$areaQuery = $db->prepare("SELECT ID,longName FROM employeeAreas");
		$areaQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($cur = $areaQuery->fetch(PDO::FETCH_ASSOC)){
		if($cur['ID'] == $area){
			$selected = "selected";
		} else {
			$selected = "";
		}
		echo "<option value='".$cur['ID']."' ".$selected.">".$cur['longName']."</option>";
	}

}

?>
<script>
var areas;
var previousArea;

window.onload = function () 
{ 
	populateHeader();
	pullEmployeesInArea()
	$("#all_employees").change(function() 
	{ 
		var selectedValues = [];    
		$("#all_employees :selected").each(function(){
		    selectedValues.push($(this).val()); 
		});

		var page = 'ajax/areaSelectTable.php?employees='+selectedValues;
		
		var cb = function(result){
			$("#areaTableBody").empty();
            $("#areaTableBody").append(result);
		 	
		};

		callPhpPage(page,cb); 
		return false;
 	}); 
};

function pullEmployeesInArea(){
	$("#areaTableBody").empty();
	$("#all_employees").empty();
	var page = 'ajax/pullEmployees.php?area='+$("#areaSelect").val();
		
	var cb = function(result){
            
			var emps = JSON.parse(result);
			for (var i = 0; i < emps.length; i++){
				$("#all_employees").append(new Option(emps[i].name,emps[i].netID,false,false));	
			}			
			};

	callPhpPage(page,cb); 
}

function populateHeader(){
	var page = 'ajax/pullAreas.php';
		
	var cb = function(result){
            
		 	areas = JSON.parse(result);
			for (var i = 0; i < areas.length; i++){
				$("#areaRow").append("<th>"+areas[i]+"</th>");
			}
			$("#areaRow").append("<th>Default</th>");
			};

	callPhpPage(page,cb); 
	
}

function selectAll(){
	var selectBox = document.getElementById("all_employees");
	for (var i = 0; i < selectBox.options.length; i++) {
            selectBox.options[i].selected = selectAll;
        }
	$("#all_employees").change();
}

function grantAreaPerm(value){
	var page = 'ajax/grantAreaPerm.php?data='+value;
	callPhpPage(page,checkAction);
}

function revokeAreaPerm(value){
	var page = 'ajax/revokeAreaPerm.php?data='+value;
	callPhpPage(page,checkAction);
}

function checkAction(result){
	return true;
}

function changeDefault(emp){
	var newArea = $("#"+emp).val();
	var r=confirm("Changing an employee's default area will remove their access to the current Defaulted Area. Are you sure you want to continue?");
	
	if (r==true)
	{
		var page = 'ajax/changeDefaultArea.php?employee='+emp+'&newArea='+newArea;
		callPhpPage(page,checkAction);
	}
	else 
	{
		$("#"+emp).val(previousArea);
	}
}

function selectClick(emp){
	previousArea = $("#"+emp).val();
}

</script>
<style>
#employeeDiv{
	float:left;
	width:30%;
}

#areaAdminDiv{
	float:left;
	width:70%;
}

#tablediv{
	width:100%
	height:100%;
}

tbody tr:nth-child(2n) {
	background-color: #E7E7E7;
}

tbody tr:hover {
	background-color: #CECECE;
}	

</style>
<h2>Area Administration</h2>
<div id='bigDiv'>
	<div id='selectDiv' >
		<select id='areaSelect' onchange='pullEmployeesInArea()'>
			<?php pullAreasToSelect(); ?>
		</select>
	</div>
	<div id='employeeDiv'>
		<select name="all_employees" id="all_employees" size=23 style="width:250px" multiple>
		</select>
		<button id='selectAll' onclick='selectAll()'>Select All</button>
	</div>
	<div id='areaAdminDiv'>
		<table id='areaManager'>
			<tr id='areaRow'>
				<th></th>
			</tr>
			<tbody id='areaTableBody'> 

			</tbody>
		</table>
	</div>
</div>
<?php
require('../includes/includeAtEnd.php');
?>
