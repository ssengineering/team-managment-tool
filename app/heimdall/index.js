/**
 * Heimdall Javascript functions
 */

var environments = ['1','2','3'];
var heimdallURL = "http://heimdall.byu.edu/";
var shortNameCheckTimeout;


window.onload = function()
{
	initializeDialogs();
	$.ajax(
	{
		url: "http://heimdall.byu.edu/applications",
		method: "GET"
	})
	.done(function(response)
	{
		$.each(response, function(index, value)
			{
				displayApplicationSummary(value);
			});
	});

	$(".addApplication").click(addApplication);
	$('#textFilter').keyup(filterApplications);
};


function displayApplicationSummary(application){
	// console.log("New application: "+application.longName);
	
	var globalColor;
	if (application.globalVisibility)
		globalColor = 'green';
	else
		globalColor = 'red';
	
	var applicationSummaryHTML = "<div class='applicationInformation' id='application_"+application.shortName+"'>";
	applicationSummaryHTML += "<div class='applicationSummary' id='application_"+application.shortName+"_summary'>";
	applicationSummaryHTML += "<div class='appGlobalIndicator' style='background-color: "+globalColor+"' id='appGlobalIndicator_"+application.shortName+"'></div>";
	applicationSummaryHTML += "<h3>"+application.longName+"</h3>";
	applicationSummaryHTML += "</div></div>";
	$('#applications').append(applicationSummaryHTML);
	$('#application_'+application.shortName+'_summary').click({app: application.shortName}, displayApplicationDetails);
}

function displayApplicationDetails(event){

	var app = event.data.app;

	closeAll();

	$("#application_"+app+"_summary").hide();

	if ($('#application_'+app+'_details').length !== 0)
	{
		$('#application_'+app+'_details').show();
		return;
	}
	
	reloadApplicationDetails(app);
}

function reloadApplicationDetails(app)
{

	$('#application_'+app+'_details').remove();

	$.ajax
	({
		url: "http://heimdall.byu.edu/application/"+app,
		method: "GET"
	})
	.done(function(response)
	{

		var globalColor = 'red';
		if (response.globalVisibility){
			globalColor = 'green';
		}
	
		$('#appGlobalIndicator_'+app).css('background-color', globalColor);
		
		fillApplicationDetailTemplate(app);

		$('#application_'+app+'_details_title').html(response.longName+"<span class='ui-icon ui-icon-trash' onclick=\"javascript:confirmAppDeletion('"+response.shortName+"','"+response.longName+"')\"></span>");
		$('#appDetailsGlobalIndicator_'+app).css("background-color", globalColor);
		$.each(['0','1','2'], function(index, env)
		{
			if (response[env].users)
			{
				$.each(response[env].users, function(index, user)
				{
					$('#env_'+env+'_'+app+'_users').append("<span>"+user+"</span><a class='ui-icon ui-icon-trash' href=\"javascript:deleteRule('"+app+"','USER','"+user+"','"+env+"')\" /><br />");
				});
			}
		});
		$.each(['0','1','2'], function(index, env)
		{
			if (response[env].areas)
			{
				$.each(response[env].areas, function(index, area)
				{
					$('#env_'+env+'_'+app+'_areas').append("<span>"+area+"</span><a class='ui-icon ui-icon-trash' href=\"javascript:deleteRule('"+app+"','AREA','"+area+"','"+env+"')\" /><br />");
				});
			}
		});
		
		$.each(['0','1','2'], function(index,env)
		{
			$('#env_'+env+'_'+app+'_new_user').keypress({app: response.shortName, rule: 'USER', env: env}, submitNewRule);
			$('#env_'+env+'_'+app+'_new_area').keypress({app: response.shortName, rule: 'AREA', env: env}, submitNewRule);
		});

		if (response.globalVisibility)
		{
			$('#global_'+app).append("<button onclick=\"javascript:toggleGlobalVisibility('"+app+"',false)\">Disable Global Visibility</button>");
		}
		else
		{
			$('#global_'+app).append("<button onclick=\"javascript:toggleGlobalVisibility('"+app+"',true)\">Enable Global Visibility</button>");
		}

		$('#global_'+app+' button').button({icons:{primary: 'ui-icon-alert'}});
		$('#application_'+app+'_details_title').click({app: response.shortName}, hideApplicationDetails);
		$('#application_'+app+'_details').show();

	});

}

function hideApplicationDetails(event)
{
	var app = event.data.app;

	$('#application_'+app+'_details').hide();
	$('#application_'+app+'_summary').show();
}

function closeAll()
{
	var applicationDetailsDivs = $(".applicationDetails").get();
	$.each(applicationDetailsDivs, function(index, value)
	{
		$(value).hide();
	});

	var applicationSummaryDivs = $(".applicationSummary").get();
	$.each(applicationSummaryDivs, function(index, value)
	{
		$(value).show();
	});

}

function fillApplicationDetailTemplate(app)
{
	var detailsTemplateHTML = "<div class='applicationDetails' id='application_"+app+"_details'>";

	detailsTemplateHTML += "<div class='applicationDetailsGlobalIndicator' id='appDetailsGlobalIndicator_"+app+"'></div>";

	detailsTemplateHTML += "<h2 class='applicationDetailsTitle' id='application_"+app+"_details_title'></h2>";

	detailsTemplateHTML += "<div class='environmentDetails' id='environment_0_"+app+"'><h3>Development</h3><hr />";
	detailsTemplateHTML += "<div class='appUsers' id='env_0_"+app+"_users'><h4>Users</h4><input type='text' id='env_0_"+app+"_new_user' placeholder='Add a user..'></div>";
	detailsTemplateHTML += "<div class='appAreas' id='env_0_"+app+"_areas'><h4>Areas</h4><input type='text' id='env_0_"+app+"_new_area' placeholder='Add an area..'></div>";
	detailsTemplateHTML += "<div style='clear:both'></div></div>";
	detailsTemplateHTML += "<div class='environmentDetails' id='environment_1_"+app+"'><h3>Stage</h3><hr />";
	detailsTemplateHTML += "<div class='appUsers' id='env_1_"+app+"_users'><h4>Users</h4><input type='text' id='env_1_"+app+"_new_user' placeholder='Add a user..'></div>";
	detailsTemplateHTML += "<div class='appAreas' id='env_1_"+app+"_areas'><h4>Areas</h4><input type='text' id='env_1_"+app+"_new_area' placeholder='Add an area..'></div>";
	detailsTemplateHTML += "<div style='clear:both'></div></div>";
	detailsTemplateHTML += "<div class='environmentDetails' id='environment_2_"+app+"'><h3>Production</h3><hr />";
	detailsTemplateHTML += "<div class='appUsers' id='env_2_"+app+"_users'><h4>Users</h4><input type='text' id='env_2_"+app+"_new_user' placeholder='Add a user..'></div>";
	detailsTemplateHTML += "<div class='appAreas' id='env_2_"+app+"_areas'><h4>Areas</h4><input type='text' id='env_2_"+app+"_new_area' placeholder='Add an area..'></div>";
	detailsTemplateHTML += "<div style='clear:both'></div></div>";
	detailsTemplateHTML += "<div class='environmentDetails' id='global_"+app+"'><h3>Global</h3><hr />";
	detailsTemplateHTML += "<div style='clear:both'></div></div>";


	detailsTemplateHTML += "</div>";

	return $('#application_'+app).append(detailsTemplateHTML);

}

function submitNewRule(event)
{
	// If the key pressed was not the enter key, do nothing. 
	if (event.which !== 13)
		return;

	var data = {ruleType: event.data.rule, env: event.data.env};

	if (event.data.rule === "AREA")
		data.area = $(this).val();
	else
		data.user = $(this).val();

	$.ajax
	({
		url: heimdallURL+"visibility/"+event.data.app,
		type: "POST",
		data: data
	})
	.done(function(response)
	{
		if (response.error || response.code)
		{
			console.log(response);
			alert("There was an error, please try again.");
		}

		reloadApplicationDetails(event.data.app);
	});

}

function deleteRule(app, ruleType, subject, env)
{
	var data = {ruleType: ruleType, env: env};

	if (ruleType === "AREA")
		data.area = subject;
	else
		data.user = subject;

	$.ajax
	({
		url: heimdallURL+"visibility/"+app,
		type: "DELETE",
		data: data
	})
	.done(function(response)
	{	
		if (response.error || response.code)
		{
			console.log(response);
			alert("There was an error, please try again.");
		}

		reloadApplicationDetails(app);
	});
}

function initializeDialogs()
{
	$('#appShortName').keyup(function()
	{
		clearTimeout(shortNameCheckTimeout);
		shortNameCheckTimeout = setTimeout(function()
		{
			appExists($('#appShortName').val(), function(response)
			{
				if (response)
				{
					$('#appShortName').css('background-color', 'lightpink');
				}
				else
				{
					$('#appShortName').css('background-color', '');
				}

			});
		}, 300);	
	});
		
	$('#addApplicationForm').dialog
	({
		autoOpen: false,
		resizeable: false,
		height: 300,
		width: 400,
		modal: true,
		buttons:
		{
			"Add Application": function()
			{
				appExists($('#appShortName').val(), function(exists)
				{
					if (exists) {
						alert('An application already exists with that shortName, please pick a different shortName');
						return;
					}
					$.ajax
					({
						url: heimdallURL+"application/create",
						type: 'POST',
						data: {shortName: $('#appShortName').val(), longName: $('#appLongName').val()}	
					})
					.done(function(response)
					{
						if (response.error || response.code)
						{
							console.log(response);
							alert("There has been an error, please try again.");
						}
						else
						{
							displayNewApplication($('#appShortName').val());
						}
	
						$('#addApplicationForm').dialog('close');
					});
				});
			},
			"Cancel": function()
			{
				$(this).dialog('close');
			}
		},
		close: function()
		{
			$('#appShortName').val('');
			$('#appLongName').val('');
		}
	
	});

	$('#deleteApplicationConfirm').dialog
	({
		autoOpen: false,
		resizable: false,
		height: 200,
		width: 400,
		modal: true,
		buttons:
		{
			"Delete Application": function()
			{
				deleteApplication($(this).data('shortName'));
				$(this).dialog('close');
			},
			"Cancel": function()
			{
				$(this).dialog('close');
			}
			
		}
	});
}

function addApplication()
{
	$('#addApplicationForm').dialog('open');
}

function displayNewApplication(app)
{
	$.ajax(
	{
		url: "http://heimdall.byu.edu/application/"+app,
		method: "GET"
	})
	.done(function(response)
	{
		displayApplicationSummary(response);
	});
}

function confirmAppDeletion(shortName, longName)
{
	$('#deleteAppName').html(longName);
	$('#deleteApplicationConfirm').data('shortName',shortName).dialog('open');
}

function deleteApplication(app)
{
	$.ajax
	({
		url: heimdallURL+"application/"+app,
		type: "DELETE"
	})
	.done(function(response)
	{
		if (response.error || response.code)
			console.log(response);
		else
			$('#application_'+app).remove();
	});
}

function appExists(app, cb)
{
	$.ajax(
	{
		url: "http://heimdall.byu.edu/application/"+app,
		method: "GET"
	})
	.done(function(response)
	{
		if (response.error)
			return cb(false);
		else
			return cb(true);
	});
}

function toggleGlobalVisibility(app, visible)
{
	var methodType;
	if (visible)
		methodType = 'POST';
	else
		methodType = 'DELETE';
	$.ajax
	({
		url: heimdallURL+"visibility/"+app,
		type: methodType,
		data: {ruleType: 'GLOBAL'}
	})
	.done(function(response)
	{
		if (response.error || response.code)
		{
			console.log(response);
			alert('There has been an error, please try again');
		}
		reloadApplicationDetails(app);
	});
}

function filterApplications(){
	var filter = $('#textFilter').val().toLowerCase();

	$.each($('.applicationInformation'), function(index, value)
	{
		if ($(value).children('.applicationSummary').children('h3').html().toLowerCase().indexOf(filter) > -1)
			$(value).show();
		else
			$(value).hide();
	});
	

}
