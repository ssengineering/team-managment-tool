// Create an input area for comments on an RFC
function expandRFCforInput(event) {
	var sys_id = event.data.rfc['sys_id'];
	var short_description = event.data.rfc['short_description'];
	var start = event.data.rfc['work_start'];
	var end = event.data.rfc['work_end'];
	var rating = event.data.rfc['u_completion_rating'];
	
	// Read actual down time from S-NOW object
	
	var down = {minutes : 0, hours : 0, days : 0};
	if(event.data.rfc['u_actual_down_time'] != ""){
		var down_time = new Date(event.data.rfc['u_actual_down_time'] + ' UTC');
		down['seconds'] = down_time.getUTCSeconds();
		down['minutes'] = down_time.getUTCMinutes();
		down['hours'] = down_time.getUTCHours();
		down['days'] = Math.floor((down_time.getTime() - down_time.getUTCMilliseconds() -down_time.getUTCSeconds() - down['minutes'] - down['hours'])/(1000*60*60*24));
	}
	event.data.rfc['down'] = down;
	var start_time = '';
	var start_date = '';
	if(start!=''){
		var start_datetime = new Date(start + ' UTC');
		start_time = start_datetime.format("HH:MM");
		start_date = start_datetime.format("yyyy-mm-dd");
	};
	var end_time = '';
	var end_date = '';
	if(end!=''){
		var end_datetime = new Date(end + ' UTC');
		end_time = end_datetime.format("HH:MM");
		end_date = end_datetime.format("yyyy-mm-dd");
	};
	
	
	var anchor_row_id = "#rfc_row_"+sys_id;
	var row_id = 'rfc_comment_row_'+sys_id;
	$('.rfc_update_row:not("#'+row_id+'")').hide();
	$('.rfc_selected_row:not("'+anchor_row_id+'>td")').removeClass('rfc_selected_row');
	if( $('#'+row_id).length == 0) {		 
   	 $("<tr class='rfc_update_row' id='"+row_id+"'><td colspan=4 class='rfc_update_cell'><div class='rfc_update_div' >"+
   	 			"<div class='rfc_input_row'>"+
   	 				"<label class='rfc_time_label'>Actual Start Time: </label><input class='rfc_restricted_"+sys_id+"' id='actual_start_time_"+sys_id+"' type='time'><input class='rfc_restricted_"+sys_id+"' id='actual_start_date_"+sys_id+"' type='date'> "+
   	 				"<button class='rfc_restricted_"+sys_id+"' id='rfc_start_now_"+sys_id+"'>Now</button><button class='rfc_restricted_"+sys_id+"' id='rfc_start_reset_"+sys_id+"'>Reset</button> "+
   	 			"</div>"+
   	 			"<div class='rfc_input_row'>"+
   	 				"<label class='rfc_time_label'>Actual End Time: </label><input class='rfc_restricted_"+sys_id+"' id='actual_end_time_"+sys_id+"' type='time'><input class='rfc_restricted_"+sys_id+"' id='actual_end_date_"+sys_id+"' type='date'> "+
   	 				"<button class='rfc_restricted_"+sys_id+"' id='rfc_end_now_"+sys_id+"'>Now</button><button class='rfc_restricted_"+sys_id+"' id='rfc_end_reset_"+sys_id+"'>Reset</button>"+
   	 			"</div>"+
   	 			"<textarea class='rfc_input_row rfc_comment_area' id='rfc_comment_"+sys_id+"' rows=4 placeholder='Work Log Entry'></textarea>"+
   	 		"<div class='rfc_input_row'><button id='rfc_update_btn_"+sys_id+"'>Update</button><button class='rfc_restricted_"+sys_id+"' id='rfc_finish_btn_"+sys_id+"'>Mark RFC Finished</button></div>"+
   	 		"</div></td></tr>"
   	 	).insertAfter(anchor_row_id);
  	 	
   	 	
   	//Adding down-time input
   	$("<div class='rfc_input_row'>"+
   			"<label class='rfc_time_label'>Actual Down Time: </label>"+
   			"<text>Days </text><input class='small_input rfc_restricted_"+sys_id+"' id='rfc_down_days_"+sys_id+"' type='number' value='"+down['days']+"'>"+
   			"<text>Hours </text><input class='small_input rfc_restricted_"+sys_id+"' id='rfc_down_hours_"+sys_id+"' type='number' min='0' max='23' value='"+down['hours']+"'>"+
   			"<text>Minutes </text><input class='small_input rfc_restricted_"+sys_id+"' id='rfc_down_minutes_"+sys_id+"' type='number' min='0' max='59' value='"+down['minutes']+"'>"+
   			"</div>").insertBefore("#rfc_comment_"+sys_id);
   			
   	
  	 	// Adding completion rating input
  	 	$("<div class='rfc_input_row'>"+
 				"<label class='rfc_time_label'>Completion Rating: </label><select class='rfc_restricted_"+sys_id+"' id='rfc_comp_rating_"+sys_id+"'><option value=''>--None--</option>"+
 				"<option value='1'>1 - Excellent</option><option value='2'>2 - Excellent with Down Time</option><option value='3'>3 - Fair</option>"+
 				"<option value='4'>4 - Poor</option><option value='5'>5 - Unsuccessful</option></select>"+
   	 	"</div>").insertBefore("#rfc_comment_"+sys_id);
   	 			
   	$('#rfc_comp_rating_'+sys_id).val(rating);
	 	$(anchor_row_id+'>td').toggleClass('rfc_selected_row');
   	$("#actual_start_time_"+sys_id).val(start_time);
    	$("#actual_start_date_"+sys_id).val(start_date);
   	$("#actual_end_time_"+sys_id).val(end_time);
    	$("#actual_end_date_"+sys_id).val(end_date);
    	$("#rfc_start_now_"+sys_id).click({sys: sys_id}, setRFCstartNow);
    	$("#rfc_end_now_"+sys_id).click({sys: sys_id}, setRFCendNow);
    	$("#rfc_start_reset_"+sys_id).click({sys: sys_id, time: start}, resetRFCstart);
    	$("#rfc_end_reset_"+sys_id).click({sys: sys_id, time: end}, resetRFCend);
    	if(event.data.rfc['state']!=30){
    		$(".rfc_restricted_"+sys_id).prop("disabled",true);
    	}
    	
    	$("#rfc_update_btn_"+sys_id).click({sys: sys_id, short_description: short_description, finish: false, initial: event.data.rfc}, updateRFC);
    	$("#rfc_finish_btn_"+sys_id).click({sys: sys_id, short_description: short_description, finish: true, initial: event.data.rfc}, updateRFC);
	}
	else{
	 	$('#'+row_id).toggle();
	 	$(anchor_row_id+'>td').toggleClass('rfc_selected_row');
	}
}

function setRFCstartNow(event){
	var now = new Date();
	$("#actual_start_time_"+event.data.sys).val(now.format("HH:MM"));
	$("#actual_start_date_"+event.data.sys).val(now.format("yyyy-mm-dd"));
}


function setRFCendNow(event){
	var now = new Date();
	$("#actual_end_time_"+event.data.sys).val(now.format("HH:MM"));
	$("#actual_end_date_"+event.data.sys).val(now.format("yyyy-mm-dd"));
}

function resetRFCstart(event){	
	var time = '';
	var date = '';
	if(event.data.time!=''){
		var datetime = new Date(event.data.time + ' UTC');
		time = datetime.format("HH:MM");
		date = datetime.format("yyyy-mm-dd");
	};
	$("#actual_start_time_"+event.data.sys).val(time);
	$("#actual_start_date_"+event.data.sys).val(date);
}

function resetRFCend(event){	
	var time = '';
	var date = '';
	if(event.data.time!=''){
		var datetime = new Date(event.data.time + ' UTC');
		time = datetime.format("HH:MM");
		date = datetime.format("yyyy-mm-dd");
	};
	$("#actual_end_time_"+event.data.sys).val(time);
	$("#actual_end_date_"+event.data.sys).val(date);
}

function updateRFC(event){
	var data = {};
	var okay_to_finish = true;
	data['sys_id'] = event.data.sys;
	data['short_description'] = event.data.short_description;
	
	var start_time = $("#actual_start_time_"+data['sys_id']).val();
	var start_date = $("#actual_start_date_"+data['sys_id']).val();
	if(start_time!='' && start_date!=''){
		data['start'] = start_date + ' ' + start_time + ':00';
	} else if (start_time=='' && start_date=='') {
		data['start'] = '';
		okay_to_finish = false;
	} else {
		okay_to_finish = false;
	}
	var end_time = $("#actual_end_time_"+data['sys_id']).val();
   var end_date = $("#actual_end_date_"+data['sys_id']).val();
   if(end_time!='' && end_date!=''){
		data['end'] = end_date + ' ' + end_time + ':00';
	} else if (end_time=='' && end_date=='') {
		data['end'] = '';
		okay_to_finish = false;
	} else {
		okay_to_finish = false;
	}
	data['down'] = {
		days : parseInt($("#rfc_down_days_"+data['sys_id']).val()),
		hours : parseInt($("#rfc_down_hours_"+data['sys_id']).val()),
		minutes : parseInt($("#rfc_down_minutes_"+data['sys_id']).val()),
		seconds : event.data.initial['down']['seconds']
	};
	
	data['down_time'] = $("#rfc_down_days_"+data['sys_id']).val() +" "+$("#rfc_down_hours_"+data['sys_id']).val()+":"+$("#rfc_down_minutes_"+data['sys_id']).val()+":"+event.data.initial['down']['seconds'];
	
	data['rating'] = $("#rfc_comp_rating_"+data['sys_id']).val();
	var comments = $("#rfc_comment_"+data['sys_id']).val().trim();
	if (comments!=''){
		data['comments'] = '(Comment) ' + comments;
	}
	if (event.data.finish) {
		if(data['rating'] == ''){
			okay_to_finish = false;
		}
		if(!okay_to_finish){
			notify('In order to finish an RFC, all fields must be completed', {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
			return;
		}
		data['state'] = 50;
	}
	$(".rfc_update_row").hide();
	
	$.ajax({
		 	'url':'/API/rfc_update',
		 	'data': data, 
   	 	'type': 'GET'}
   	 ).done(function(response, status){
		if(response){
			notify('The change record was updated succesfully', {'position':{'my': 'center center', 'at': 'center center', 'of': window},'status':'success' });
		} else {
			notify('There was a problem writing to Service Now. Please update the change record directly.', {'position':{'my': 'center center', 'at': 'center center', 'of': window},'status':'failure' });
		}	
		   displayRfcs();   	
	}).fail(function(){
		notify('There was a problem writing to Service Now. Please update the change record directly.', {'position':{'my': 'center center', 'at': 'center center', 'of': window},'status':'failure' });
	});
   writeToSupReport(event.data.initial, data);
	$('.rfc_selected_row').removeClass('rfc_selected_row');
	$('#rfcSearch').val('');
   notify('Writing to Service-NOW', {'position':{'my': 'center center', 'at': 'center center', 'of': window}});
}

function writeToSupReport(initial_data, new_data){
	
	var entry = "<b>"+initial_data['number'] + " - " + initial_data['short_description']+"</b><br/>";
	if(typeof new_data['start'] != 'undefined'){
		var initial = new Date(initial_data['work_start']+' UTC');
		var updated = new Date(new_data['start']);
		if(initial.toUTCString() != updated.toUTCString()) {
			entry += "<b>Expected Start Time:</b> "+new Date(initial_data['start_date'] + ' UTC').format("mmm d, yyyy - h:MM TT") + "<br/>" + "<b>Actual Start Time:</b> "+updated.format("mmm d, yyyy - h:MM TT") + "<br/>";
	}}
	if(typeof new_data['end'] != 'undefined'){
		var initial = new Date(initial_data['work_end']+' UTC');
		var updated = new Date(new_data['end']);
		if(initial.toUTCString() != updated.toUTCString()) {
		entry += "<b>Expected End Time:</b> "+new Date(initial_data['end_date'] + ' UTC').format("mmm d, yyyy - h:MM TT") + "<br/>" + "<b>Actual End Time:</b> "+updated.format("mmm d, yyyy - h:MM TT") + "<br/>";
	}}
	if(typeof new_data['rating'] != 'undefined' && initial_data['u_completion_rating'] != new_data['rating']) {
		entry += "<b>Completion Rating:</b> "+$("#rfc_comp_rating_"+initial_data['sys_id']+" option:selected").text() + "<br/>";
	}
	if(initial_data['down']['days']!=new_data['down']['days'] ||
		initial_data['down']['minutes']!=new_data['down']['minutes'] ||
		initial_data['down']['hours']!=new_data['down']['hours']){
		var original = initial_data['down']['days'] + " Days, " + initial_data['down']['hours'] + " Hours, " + initial_data['down']['minutes'] + " Minutes";
		var updated = new_data['down']['days'] + " Days, " + new_data['down']['hours'] + " Hours, " + new_data['down']['minutes'] + " Minutes";
		entry += "<b>Expected Down Time:</b> "+original + "<br/>" + "<b>Actual Down Time:</b> "+ updated + "<br/>";
	}	
	if(typeof new_data['comments'] != 'undefined' ){
		entry+= "<br/>"+$("#rfc_comment_"+initial_data['sys_id']).val();
	}
	entry = entry.trim();
	
	$.ajax({
		type: "POST",
		url: "/API/supervisorReport/report/create",
		data: {netID: netid, categoryAreaPair: 6, comment: entry},
		dataType: "json"
	}).done(function(response){
		if(response)
			console.log('Successfully updated supervisor report');
		else
			console.log('Failed to update the supervisor report');
			console.log(entry);
	}).fail(function(response){
		console.log('Failed to update the new supervisor report');
			console.log(entry);
			$.ajax({
				type: "GET",
				url: "/supervisorDashboard/supReportEntry.php",
				data: {'entry': entry},
				dataType: "json"
			}).done(function(response){
				console.log('Successfully updated old supervisor report');
			});
		// Should only fail until the new supervisor report is pushed.
	});

}
