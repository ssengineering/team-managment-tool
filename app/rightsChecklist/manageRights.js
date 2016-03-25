//manageRights.js

$(document).ready(function() {
	$('#createButton').on("click", function() {
		$('#createRightDialog').dialog("open");
	});

	$('#editButton').on("click", function() {
		$('#editLevelsDialog').dialog("open");
	});
	printManager();
});

//opens or closes a div
function togglediv(divname) 
{                
	if(document.getElementById(divname).display == "none") {
		openDivs[divname] = true;
        document.getElementById(divname).display = "block";                
	} else {
		openDivs[divname] = false;
        document.getElementById(divname).display = "none";             
	}
}

function deleteRight(id)
{
	$.ajax({
		type: "POST",
		url: "/API/rights/delete/" + id,
		dataType: 'json',
	}).done(function(response) {
		window.alert("successfully deleted");
		printManager();
	});
}

//prints the rights manager menu
function printManager()
{
	$.ajax({
		dataType: 'json',
		type: "POST",
		url: "/API/rights/print/manager",
		data: {
			'area' : area,
			'netId': manager
		}
	}).done(function(result) {
		var menu = "";
		for(var i=0; i < result.levels.length; i++) {
			menu += '<span style="cursor:pointer"><div id="title" onclick="togglediv('+result.levels[i].level+')"><h3>'+result.levels[i].name+'</h3></div></span>';
			menu += '<div id="'+result.levels[i].level+'" style="display:none;">';
			menu += '<table class="imagetable">';
			for(var j=0; j < result.rights[i].length; j++) {
				menu += '<tr><th>'+result.rights[i][j].name+'</th>';
				menu += '<td>'+result.rights[i][j].type+'</td>';
				menu += '<td>Level: '+result.levels[i].name+'</td>';
				menu += '<td><input type="submit" class="button" onclick="openEditRights('+result.rights[i][j].ID+')" value="Edit"/>';
				menu += '<input type="submit" class="button" onclick="deleteRight('+result.rights[i][j].ID+')" value="Delete"/></td></tr>';
				menu += '<tr><td colspan="7">'+result.rights[i][j].description+'</td></tr>';
			}
			menu += '</table></div>';
		}
		document.getElementById("managediv").innerHTML = menu;

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
		$('#managediv > span').on("click", function() {
			if($('#'+this.nextSibling.id).css('display') == "none") {
				openDivs[this.nextSibling.id] = true;
				$('#'+this.nextSibling.id).show();
			} else {
				openDivs[this.nextSibling.id] = false;
				$('#'+this.nextSibling.id).hide();
			}
		});
	});
}