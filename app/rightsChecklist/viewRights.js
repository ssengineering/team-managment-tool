//viewRights.js

$(document).ready(loadRightsList(area,netId));

//This loads the rights list via Ajax.
function loadRightsList(area,netId)
{
	$.ajax({
		dataType: 'json',
		type: "POST",
		url: "/API/rights/print/main",
		data: {
			'area' : area,
			'netId': netId
		}
	}).done(function(result) {
		var menu = '<h2>'+result.name+'<br />Current Certification: '+result.certLevel+'</h2>';
		for(var i=0; i < result.levels.length; i++) {
			menu += '<span style="cursor:pointer"><div id="title" onclick="togglediv('+result.levels[i].level+')"><h3>'+result.levels[i].name+'</h3>';
			//determine level status
			var rightsGranted = 0;
			for(var k=0; k < result.rights[i].length; k++) {
				if(result.rights[i][k].status.status == 2) {
					rightsGranted++;
				} else {
					break;
				}
			}
			if(result.rights[i].length == rightsGranted){
				menu += '<font color="#228022">All '+result.levels[i].name+' rights have been Confirmed Granted</font><br/>';
			}
			menu += '</div></span>';
			menu += '<div id="'+result.levels[i].level+'" style="display:none;">';
			menu += '<table class="imagetable">';
			for(var j=0; j < result.rights[i].length; j++) {
				menu += '<tr><th>'+result.rights[i][j].name+'</th>';
				if(result.rights[i][j].status.status == 0) { //If right has not been requested
					if(result.rights[i][j].type == "EMAIL") {
						menu += '<td><font color="#8f100a">Type: '+result.rights[i][j].type+'</font></td>';
						menu += '<td>Right Not Requested or Granted</td>';
					} else {
						menu += '<td><font color="#f8ad50">Type: '+result.rights[i][j].type+'</font></td>';
						menu += '<td>Right Not Granted</td>'; 
					}
				} else if(result.rights[i][j].status.status == 1) { //If right has been requested
					menu += '<td>Requested by: <b>'+result.rights[i][j].status.requestedBy+'</b> on: <b>'+result.rights[i][j].status.requestedDate+'</b></td>';
					menu += '<td>Rights Requested</td>';
				} else if(result.rights[i][j].status.status == 2) { //If user has the right
					menu += '<td>Confirmed Granted by: <b>'+result.rights[i][j].status.updatedBy+'</b> on: <b>'+result.rights[i][j].status.updatedDate+'</b></td>';			
					menu += '<td></td></tr>'; 
				} else { //If right has been revoked
					menu += '<td>Removed by: <b>'+result.rights[i][j].status.removedBy+'</b> on: <b>'+result.rights[i][j].status.removedDate+'</b></td>';
					if(result.rights[i][j].type == "EMAIL") {
						menu += '<td><font color="#8f100a">Type: '+result.rights[i][j].type+'</font></td>';
						menu += '<td></td></tr>';
					} else {
						menu += '<td><font color="#f8ad50">Type: '+result.rights[i][j].type+'</font></td>';
						menu += '<td></td></tr>';
					}
				}
			}
			menu += '</table></div>';
		}
		document.getElementById("rightsInfo").innerHTML = menu;

		if(!loaded) {
			var levelDivs = document.getElementsByClassName('imagetable');
			for(var i=0; i < levelDivs.length; i++) {
				openDivs[levelDivs[i].parentNode.id] = false;
			}
			loaded = true;
		} else {
			for(open in openDivs) {
				if(openDivs[open]) {
					$('#'+open).show();
				}
			}
		}
	});
}

//this opens and closes a div window.
function togglediv(divname) 
{
	if(document.getElementById(divname).style.display == "none") {
	    document.getElementById(divname).style.display = "block";
	    openDivs[divname] = true;
	} else {
	    document.getElementById(divname).style.display = "none";
	    openDivs[divname] = false;
	}        
}