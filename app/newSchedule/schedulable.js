window.onload=function(){
	fillList();
}

function fillList(){
	$.ajax({
		url : '/API/tags/search',
		type : 'POST',
		dataType : 'json',
		data : {'area' : area, 'tag' : tag } 
	}).done(function(response){
		var doNotSchedule = response;
		for (var i=0; i < availableEmployees.length; i++){
			var netID = availableEmployees[i]['netID'];
			var empName = availableEmployees[i]['lastName']+", "+availableEmployees[i]['firstName'];
			var row = "<div class='emp-row'><label class='emp-label' id='emp-label_"+netID+"' for='"+netID+"'>"+empName;
			row += "</label><input type='checkbox' id='check_"+netID+"' checked='true' onclick='schedulable(\""+netID+"\")' /></div>";
			$('#schedule-list').append(row);
			if(jQuery.inArray(netID, doNotSchedule) >= 0){
				$('#check_'+netID).attr('checked', false);
			}
			if(!(jQuery.inArray(netID, defaultAreaEmployees) >= 0)){
				$('#emp-label_'+netID).css('color' , '#cc0000'); 
			}
		}
	});
	
}

function schedulable(netID){
	if ($('#check_'+netID).prop("checked")){
		$.ajax({
			url : "/API/tags/drop",
			type : "POST",
			dataType : 'json',
			data : {'netid' : netID, 'area' : area, 'tag' : tag}
		});	
	} else {
		$.ajax({
			url : "/API/tags/add",
			type : "POST",
			dataType : 'json',
			data : {'netid' : netID, 'area' : area, 'tag' : tag, 'long' : tag_long}
		});
			
	}
}

