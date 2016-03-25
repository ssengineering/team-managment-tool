//dialogs.js

$(document).ready(function() {

	printLevels(area);//fill Edit Levels dialog
	
	/*************************Create Right Handlers***********************/

	//set value of create right dialog employee options to be their name escaped in order to avoid problems when adding/removing multiple times
	var options = document.getElementById('employeeSelect').getElementsByTagName('option');
	for(var i=0; i < options.length; i++) {
		options[i].value = escape(options[i].value);
	}

	$('#add').on("click", function() {
		$('#employeeSelect :selected').each(function() {
			//insert in alphabetical order
			var options = document.getElementById('selectedEmployees').getElementsByTagName('option');
			if(options.length == 0) {
				$('#selectedEmployees').append("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>");
			} else {
				var insertIndex = 0;
				for(var i=0; i < options.length; i++) {
					if(this.text.toUpperCase() < options[i].text.toUpperCase()) {
						break;
					} else {
						insertIndex++;
					}
				}
				if(insertIndex == options.length) {
					$('#selectedEmployees option').eq(insertIndex-1).after($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
				} else {
					$('#selectedEmployees option').eq(insertIndex).before($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
				}					
			}
			$(this).remove();
		});
	});

	$('#addAll').on("click", function() {
		$('#employeeSelect option').each(function() {
			//insert in alphabetical order
			var options = document.getElementById('selectedEmployees').getElementsByTagName('option');
			if(options.length == 0) {
				$('#selectedEmployees').append("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>");
			} else {
				var insertIndex = 0;
				for(var i=0; i < options.length; i++) {
					if(this.text.toUpperCase() < options[i].text.toUpperCase()) {
						break;
					} else {
						insertIndex++;
					}
				}
				if(insertIndex == options.length) {
					$('#selectedEmployees option').eq(insertIndex-1).after($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
				} else {
					$('#selectedEmployees option').eq(insertIndex).before($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
				}					
			}
			$(this).remove();
		});
	});

	$('#remove').on("click", function() {
		$('#selectedEmployees :selected').each(function() {
			//insert in alphabetical order
			var options = document.getElementById('employeeSelect').getElementsByTagName('option');
			if(options.length == 0) {
				$('#employeeSelect').append("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>");
			} else {
				var insertIndex = 0;
				for(var i=0; i < options.length; i++) {
					if(this.text.toUpperCase() < options[i].text.toUpperCase()) {
						break;
					} else {
						insertIndex++;
					}
				}
				if(insertIndex == options.length) {
					$('#employeeSelect option').eq(insertIndex-1).after($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
				} else {
					$('#employeeSelect option').eq(insertIndex).before($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
				}					
			}
			$(this).remove();
		});
	});

	$('#removeAll').on("click", function() {
		$('#selectedEmployees option').each(function() {
			//insert in alphabetical order
			var options = document.getElementById('employeeSelect').getElementsByTagName('option');
			if(options.length == 0) {
				$('#employeeSelect').append("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>");
			} else {
				var insertIndex = 0;
				for(var i=0; i < options.length; i++) {
					if(this.text.toUpperCase() < options[i].text.toUpperCase()) {
						break;
					} else {
						insertIndex++;
					}
				}
				if(insertIndex == options.length) {
					$('#employeeSelect option').eq(insertIndex-1).after($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
				} else {
					$('#employeeSelect option').eq(insertIndex).before($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
				}					
			}
			$(this).remove();
		});
	});
	
	$('#createRightDialog').dialog({
		title: "Create Right",
		autoOpen: false,
		resizable: true,
		draggable: true,
		height: 640,
		width: 840,
		minWidth: 500,
		buttons: [{
			text: "Create",
			click: function() {
				createRight();
				$(this).dialog("close");
				clearCreateRights();
			}
		},{
			text: "Cancel",
			click: function() {
				$(this).dialog("close");
				clearCreateRights();
			}
		}]
	});

	$('#rightType').change(function() {
		if($(this).val() == "BASIC") {
			$('#emailInfo').hide();
		} else {
			$('#emailInfo').show();
		}
	});

	/*******************Edit Levels handlers*********************/
	$('#editLevelsDialog').dialog({
		title: "Edit Levels",
		autoOpen: false,
		resizable: true,
		draggable: true,
		height: 500,
		width: 600,
		buttons: [{
			text: "Submit",
			click: function() {
				$(this).dialog("close");
				updateLevels();
				printLevels(area);
			}
		},{
			text: "Cancel",
			click: function() {
				$(this).dialog("close");
				printLevels(area);
			}
		}]
	});

	/*********************Edit Right Handlers********************/
	$('#editRightsDialog').dialog({
		title: "Edit Rights",
		autoOpen: false,
		resizable: true,
		draggable: true,
		height: 640,
		width: 840,
		buttons: [{
			text: "Submit",
			click: function() {
				updateRight($(this).data('id'));
				$(this).dialog("close");
				clearEditRights();
			}
		},{
			text: "Cancel",
			click: function() {
				$(this).dialog("close");
				clearEditRights();
			}
		}]
	});

	$('.editButtons').on("click", function() {
		$('#editRightsDialog').dialog("open");
	});

	$('#editRightType').change(function() {
		if($(this).val() == "BASIC") {
			$('#editEmailInfo').hide();
		} else {
			$('#editEmailInfo').show();
		}
	});
});

/*********************Create Right Functions*********************/

//sends an activation email for email type rights and grants the right for basic type rights
function requestRight(right,employee,manager,noEmail){
	$.ajax({
		data: {
			'employee': employee,
			'manager' : manager,
			'right'   : right,
			'noEmail' : noEmail,
			'area'    : area,
			'env'     : env
		},
		url: "/API/rights/request",
		type: "POST"
	}).done(function(result) {
		printManager(employee, area);
	});
};

//clears all data from the create rights dialog
function clearCreateRights()
{
	document.getElementById('rightType').selectedIndex = 0;
	document.getElementById('rightLevel').selectedIndex = 0;
	document.getElementById('noEmail').checked = false;
	document.getElementById('name').value = "";	
	document.getElementById('descr').value = "";
	document.getElementById('emailInfo').style.display = 'none';
	document.getElementById('to').value = "";
	document.getElementById('cc').value = "";
	document.getElementById('addTitle').value = "";
	document.getElementById('addBody').value = "";
	document.getElementById('delTitle').value = "";
	document.getElementById('delBody').value = "";
	//Set employee list back to original setting
	$('#selectedEmployees option').each(function() {
		//insert in alphabetical order
		var options = document.getElementById('employeeSelect').getElementsByTagName('option');
		if(options.length == 0) {
			$('#employeeSelect').append("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>");
		} else {
			var insertIndex = 0;
			for(var i=0; i < options.length; i++) {
				if(this.text.toUpperCase() < options[i].text.toUpperCase()) {
					break;
				} else {
					insertIndex++;
				}
			}
			if(insertIndex == options.length) {
				$('#employeeSelect option').eq(insertIndex-1).after($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
			} else {
				$('#employeeSelect option').eq(insertIndex).before($("<option id="+$(this).attr('id')+" value="+$(this).attr('value')+" >"+unescape($(this).attr('value'))+"</option>"));
			}					
		}
		$(this).remove();
	});
}

//creates a new right and grants it to all the selected employees
function createRight() 
{
	var rightSelected = document.getElementById('rightType').selectedIndex;
	var levelSelected = document.getElementById('rightLevel').selectedIndex;
	var noEmail       = document.getElementById('noEmail').checked;

	var name       = document.getElementById('name').value;	
	var descr      = document.getElementById('descr').value;
	var rightType  = document.getElementById('rightType')[rightSelected].value;
	var rightLevel = document.getElementById('rightLevel')[levelSelected].value;

	var to         = document.getElementById('to').value;
	var cc         = document.getElementById('cc').value;
	var addTitle   = document.getElementById('addTitle').value;
	var addBody    = document.getElementById('addBody').value;
	var delTitle   = document.getElementById('delTitle').value;
	var delBody    = document.getElementById('delBody').value;
	
	var receivers = [];
	$('#selectedEmployees option').each(function() {
		receivers.push(this.id);
	});
	$.ajax({
		type: "POST",
		url: "/API/rights/create",
		dataType: "json",
		data: { 'name':       name,
				'descr':      descr,
				'rightType':  rightType,
				'rightLevel': rightLevel,
				'area':       area,
				'to':         to,
				'cc':         cc,
				'addTitle':   addTitle,
				'addBody':    addBody,
				'delTitle':   delTitle,
				'delBody':    delBody,
		}
	}).done(function(response){
		var sure = false;
		if(receivers.length > 0) {
			sure = confirm("Are you Sure you want to request this right for all of these employees?");
		}
		if(sure) {
			for(var i=0; i < receivers.length; i++) {
				requestRight(response.id,receivers[i],manager,noEmail);
			}
		}
		alert("successfully created");
		printManager();
	});
}

/************************Edit Levels Functions**************************/

//prints the edit levels dialog menu
function printLevels()
{
	$.ajax({
		dataType: 'json',
		type: "POST",
		url: "/API/rightsLevels/print",
		data: {'area': area}
	}).done(function(result) {
		var levelsTable = "<table id='levelsTable' class='imagetable' ><tr><th>Level Name</th></tr>";
		for(var i=0; i < result.levels.length; i++) {
			levelsTable += "<tr><td><input class='levelInputs' type='text' style='width:100%' name='"+result.levels[i]+"' size='24' value='"+result.names[i]+"' /></td></tr>";
		}
		levelsTable += "</table><br/><input type='button' class='button' value='Remove:' onclick='deleteLevel()' /> ";

		levelsTable += "<select name='levels' id='levels'>";
		for(var i=0; i < result.levels.length; i++) {
			levelsTable += "<option value='"+result.levels[i]+"'/>"+result.names[i]+"</option>";
		}
		levelsTable += "</select>";
		document.getElementById("results").innerHTML = levelsTable;
	});
}

//deletes a level
function deleteLevel()
{
	var id = document.getElementById('levels').value;
	var sure = confirm("Are you sure you want to Delete this Level?");
	if(sure){
		$.ajax({
			type: "POST",
			url: "/API/rightsLevels/delete/" + id,
			dataType: 'json',
			data: {'area': area}
		}).done(function(result) {
			printLevels();
		});
	}
}

//creates a new level
function insertLevel()
{
	var sure = confirm("Are you sure you want to Insert a new Level?");
	if(sure){
		$.ajax({
			type: "POST",
			url: "/API/rightsLevels/create",
			dataType: 'json',
			data: {'area': area}
		}).done(function(result) {
			printLevels();
		});
	}
}

//submits level information to the database
function updateLevels()
{
	var data = {'area': area};
	var inputs = document.getElementsByClassName('levelInputs');
	for(var i = 0; i < inputs.length; i++) {
		data[inputs[i].name] = inputs[i].value;
	}

	$.ajax({
		dataType: 'json',
		type: "POST",
		url: "/API/rightsLevels/edit",
		data: data
	}).done(function() {
		window.alert("Changes were successful");
		printManager();
	});
	
}

/************************Edit Rights Functions**************************/

//fills in the information on a given right in the edit rights dialog
function openEditRights(id) 
{
	$.ajax({
		data: {'id': id},
		url: '/API/rights/print/edit',
		type: "POST",
		dataType: "json"
	}).done(function(result) {
		$('#editName').val(result.name);
		$('#editDescr').val(result.description);
		$('#editRightLevel').val(result.level);
		$('#editRightType').val(result.type);
		if(result.type == "EMAIL") {
			$('#editRightType').val('EMAIL');
			$('#editEmailInfo').show();
			$('#editTo').val(result.to);
			$('#editCC').val(result.cc);
			$('#editAddTitle').val(result.addTitle);
			$('#editAddBody').val(result.addBody);
			$('#editDelTitle').val(result.delTitle);
			$('#editDelBody').val(result.delBody);
		}
	});
	$('#editRightsDialog').dialog("open");
	$('#editRightsDialog').data('id', id);
}

//removes all information from the edit rights dialog
function clearEditRights()
{
	$('#editName').val('');
	$('#editDescr').val('');
	$('#editRightLevel').val('');
	$('#editRightType').val('BASIC');
	$('#editEmailInfo').hide();
	$('#editTo').val('');
	$('#editCC').val('');
	$('#editAddTitle').val('');
	$('#editAddBody').val('');
	$('#editDelTitle').val('');
	$('#editDelBody').val('');
}

//updates the right with the information entered
function updateRight(id)
{
	var name       = $('#editName').val();
	var descr      = $('#editDescr').val();
	var rightType  = $('#editRightType').val();
	var rightLevel = $('#editRightLevel').val();
	var to         = $('#editTo').val();
	var cc         = $('#editCC').val();
	var addTitle   = $('#editAddTitle').val();
	var addBody    = $('#editAddBody').val();
	var delTitle   = $('#editDelTitle').val();
	var delBody    = $('#editDelBody').val();
	$.ajax({
		type: "POST",
		url: "/API/rights/edit/" + id,
		dataType: "json",
		data: {	
			'name':       name,
			'descr':      descr,
			'rightType':  rightType,
			'rightLevel': rightLevel,
			'area':       area,
			'to':         to,
			'cc':         cc,
			'addTitle':   addTitle,
			'addBody':    addBody,
			'delTitle':   delTitle,
			'delBody':    delBody
		}
	}).done(function(response){
		alert("changes submitted successfully");
		printManager();
	});
}
