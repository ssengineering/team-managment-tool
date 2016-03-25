//rightsChecklist.js

$(document).ready(function() {
	loadRights(employee,area);
});

//prints the rights menu
function loadRights(netId, area)
{
	$.ajax({
		type: 'POST',
		url: '/API/rights/print/main',
		dataType: 'json',
		data: {
			'netId': netId,
			'area' : area
		}
	}).done(function(result) {
		document.getElementById('rightsInfo').innerHTML = "";
		var menu = '<h2>'+result.name+'<br />Current Certification: '+result.certLevel+'</h2>';
		for(var i=0; i < result.levels.length; i++) {
			menu += '<span style="cursor:pointer"><div class="divTh" class="title" id="title'+result.levels[i].level+'" onclick="togglediv('+result.levels[i].level+')"><h3>'+result.levels[i].name+'</h3></div></span>';
			
			//determine level status
			var levelStatus = 1;//no rights requested
			var rightsGranted = 0;
			var requestedRight = false;
			for(var k=0; k < result.rights[i].length; k++) {
				if(result.rights[i][k].status.status == 2) {
					rightsGranted++;
				}
				if(result.rights[i][k].status.status > 0) {
					requestedRight = true;
				}
			}
			if(requestedRight) {
				if(rightsGranted == result.rights[i].length) {
					levelStatus = 0;//all rights granted
				} else {
					levelStatus = 2;//some requested but not confirmed
				}
			}
			//print level
			if(levelStatus == 0) {//all rights granted
				menu += '<div class="divTd" style="width: 100%; height: 2.5em;">';
				menu += '<div class="divTd" style="padding: 0; border: 0; width: 50%;"><font color="#228022">All rights have been Confirmed Granted</font></div>';
				menu += '<div class="divTd" style="text-align: right; padding: 0; border: 0; width: 15%; float: right;">No Emails <input type="checkbox" id="email'+result.levels[i].level+'" /></div>';
				menu += '<div class="divTd" style="text-align: right; padding: 0; border: 0; width: 35%; float: right;"><input type="button" class="button" value="Terminate All Email Rights" onclick="emailRevokeAll(\''+result.levels[i].level+'\')"/></div>';
				menu += '</div>';
			} else if(levelStatus == 1) {//no rights requested
				menu += '<div class="divTd" style="width: 100%; height: 2.5em;">';
				menu += '<div class="divTd" style="padding: 0; border: 0; width: 50%;"><font>No rights have been Requested</font></div>';
				menu += '<div class="divTd" style="text-align: right; padding: 0; border: 0; width: 15%; float: right;">No Emails <input type="checkbox" id="email'+result.levels[i].level+'" /></div>';
				menu += '<div class="divTd" style="text-align: right; padding: 0; border: 0; width: 35%; float: right;"><input type="button" class="button" value="Request All Email Rights" onclick="emailAll(\''+result.levels[i].level+'\')"/></div>';
				menu += '</div>';
			} else {//not all confirmed
				menu += '<div class="divTd" style="width: 100%; height: 2.5em;">';
				menu += '<div class="divTd" style="padding: 0; border: 0; width: 50%;"><font color="red">Some rights do not have a confirmed status.</font></div>';
				menu += '</div>';
			}
			//print rights for the level
			menu += '<div class="clearMe"></div>';
			menu += '<div id="'+result.levels[i].level+'" style="display:none;">';
			menu += '<div style="width: 15px; float: left;">&nbsp;</div>';
			menu += '<table class="imagetable">';
			for(var j=0; j < result.rights[i].length; j++) {
				menu += '<tr><th class="rightName">'+result.rights[i][j].name+'</th>';
				if(result.rights[i][j].status.status == 0) { //Rights have not been requested for this employee
					menu += '<td class="rightDetails"><b>Un-requested</b></td>';
					if(result.rights[i][j].type == "EMAIL") {
						menu += '<td class="rightType"><font color="#8f100a">Type: EMAIL</font></td>';
						menu += '<td class="childButtons"><div class="divTd childButton"><input type="button" class="button" value="Send Activation Email" onclick="requestRight(\''+result.rights[i][j].ID+'\')" /></div>';
						menu += '<div class="divTd childCheckbox">No Email <input type="checkbox" id="childEmail'+result.rights[i][j].ID+'" /></div></td></tr>';
					} else {
						menu += '<td class="rightType"><font color="#f8ad50">Type: BASIC</font></td>';
						menu += '<td class="childButtons" style="text-align: right;"><input type="button" class="button" value="Confirm Rights Granted" onclick="requestRight(\''+result.rights[i][j].ID+'\')" />';
						menu += '</td>';
					}
				
				} else if(result.rights[i][j].status.status == 1) { //If rights have been requested
					menu += '<td class="rightDetails">Requested by: <b>'+result.rights[i][j].status.requestedBy+'</b> on: <b>'+result.rights[i][j].status.requestedDate+'</b></td>';
					if(result.rights[i][j].type == "EMAIL") {
						menu += '<td class="rightType"><font color="#8f100a">Type: EMAIL</font></td>';
						menu += '<td class="childButtons"><input type="button" class="button" value="Confirm Rights Granted" onclick="confirmRight(\''+result.rights[i][j].ID+'\')" />';
						menu += '<input type="button" class="button" value="Resend Activation Email" onclick="requestRight(\''+result.rights[i][j].ID+'\')" />';
					} else {
						menu += '<td class="rightType"><font color="#f8ad50">Type: BASIC</font></td>';
						menu += '<td class="childButtons"><input type="button" class="button" value="Confirm Rights Granted" onclick="confirmRight(\''+result.rights[i][j].ID+'\')" />';
					}
					menu += '</td>';

				} else if(result.rights[i][j].status.status == 2) { //If user has rights
					menu += '<td class="rightDetails">Confirmed Granted by: <b>'+result.rights[i][j].status.updatedBy+'</b> on: <b>'+result.rights[i][j].status.updatedDate+'</b></td>';			
					if(result.rights[i][j].type == "EMAIL") {
						menu += '<td class="rightType"><font color="#8f100a">Type: EMAIL</font></td>';
						menu += '<td class="childButtons">';
						menu += '<div class="divTd childButton"><input type="button" class="button" value="Send Termination Email" onclick="terminateRight(\''+result.rights[i][j].ID+'\')" /></div>';
						menu += '<div class="divTd childCheckbox">No Email <input type="checkbox" id="childEmail'+result.rights[i][j].ID+'" /></div>';
						menu += '</td></tr>';
					} else {
						menu += '<td class="rightType"><font color="#f8ad50">Type: BASIC</font></td>';
						menu += '<td class="childButtons"><input type="button" class="button" value="Confirm Rights Removed" onclick="terminateRight(\''+result.rights[i][j].ID+'\')" /></td>';
					}

				} else { //rights have been revoked
					menu += '<td class="rightDetails">Removed by: <b>'+result.rights[i][j].status.removedBy+'</b> on: <b>'+result.rights[i][j].status.removedDate+'</b></td>';
					if(result.rights[i][j].type == "EMAIL") {
						menu += '<td class="rightType"><font color="#8f100a">Type: EMAIL</font></td>';
						menu += '<td class="childButtons"><input type="button" class="button" value="Send Activation Email" onclick="requestRight(\''+result.rights[i][j].ID+'\')" /></td></tr>';
					} else {
						menu += '<td class="rightType"><font color="#f8ad50">Type: BASIC</font></td>';
						menu += '<td class="childButtons"><input type="button" class="button" value="Confirm Rights Granted" onclick="requestRight(\''+result.rights[i][j].ID+'\')" /></td>';
					}
				}
				menu += '<tr><td colspan="4">'+result.rights[i][j].description+'</td></tr>';
			}
			menu += '</table></div>';
		}
		document.getElementById("rightsInfo").innerHTML = menu;

		//if it hasn't been previously loaded set onclick for each employee link to open their checklist
		if(!loaded) {
			var employeeLinksDiv = document.getElementById('searchResultsList');
			var employeeLinks = employeeLinksDiv.getElementsByTagName('a');
			for(var i=0; i < employeeLinks.length; i++) {
				employeeLinks[i].onclick = function() {
					employee = this.title;
					loadRights(this.title, area);
				};
			}
			//load the divs array that tracks which levels' checklists are shown
			var rightsTables = document.getElementsByClassName('imagetable');
			for(var j=0; j < rightsTables.length; j++) {
				openDivs[rightsTables[j].parentNode.id] = false;
			}
			loaded = true;
		} else {//if loading another employee without refreshing the page leave the same levels' checklists open
			for(open in openDivs) {
				if(openDivs[open] == true) {
					togglediv(open);
				}
			}
		}
	});
};
	
//This opens and closes a div specified by divname	
function togglediv(divname)
{
	if(document.getElementById(divname).style.display == "none") {
		document.getElementById(divname).style.display = "block";
		openDivs[divname] = true;
	} else {
		document.getElementById(divname).style.display = "none";                
		openDivs[divname] = false;
	}
};

//sends all activation emails for a level
function emailAll(rightLevel)
{
	var noEmail = $('#email'+rightLevel).is(':checked');
	var sure = confirm("Are you Sure you want to request ALL Rights for this level?");
	if(sure){
		$.ajax({
			data: {
				'employee' : employee,
				'manager'  : manager,
				'level'    : rightLevel,
				'noEmail'  : noEmail,
				'env'      : env,
				'area'     : area
			},
			url: "/API/rights/email",
			type: "POST"
		}).done(function() {
			loadRights(employee, area);
		});
	}
};

//sends all termination emails for a level
function emailRevokeAll(rightLevel)
{
	var noEmail = $('#email'+rightLevel).is(':checked');
	var sure = confirm("Are you Sure you want to Revoke ALL Rights for this level?");
	if(sure){
		$.ajax({
			url: '/API/rights/revoke',
			type: 'POST',
			data: {
				'manager'  : manager,
				'employee' : employee,
				'noEmail'  : noEmail,
				'level'    : rightLevel,
				'area'    : area,					
				'env'     : env
			}
		}).done(function() {
			loadRights(employee, area);
		});
	}
};

//sends an activation email for email type or grants the right for basic type
function requestRight(right)
{
	var noEmail = $('#childEmail'+right).is(':checked');
	var sure = confirm("Are you Sure you want to request this right for this employee?");
	if(sure) {
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
		}).done(function() {
			loadRights(employee, area);
		});
	}
};

//confirms that an email right has been granted
function confirmRight(right)
{
	$.ajax({
		type: "POST",
		data: {
			'employee': employee,
			'manager' : manager,
			'right'   : right,
			'area'    : area
		},
		url: "/API/rights/confirm"
	}).done(function() {
		loadRights(employee, area);
	});
};

//sends a termination email for email rights, and revokes a right for basic type
function terminateRight(right)
{
	var noEmail = $('#childEmail'+right).is(':checked');
	$.ajax({
		data: {
			'employee': employee,
			'manager' : manager,
			'right'   : right,
			'noEmail' : noEmail,
			'env'     : env
		},
		type: "POST",
		url: "/API/rights/terminate"
	}).done(function() {
		loadRights(employee, area);
	});

};
