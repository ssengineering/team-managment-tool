window.onload=function()
{
	window.timed=undefined;// used later for the edit button 
	$('.activityButtons').button();
	$.ajax({
	url:"ajax.php",
	type:'GET',
	data:{categories:'categories'},
	success:function(categories){$('#categories').html(categories);}
	});
	$.ajax({
	url:"ajax.php",
	type:'GET',
	data:{categoryJson:'categoryJson'},
	success:function(categoryJson){ json(categoryJson);}
	});
	selection=$('#curDate').text();
	$('#selectedDate').text(selection);
	calendar(selection);
	$('#weekRange').addClass("selected");
	printLog(selection);	
	
}

function json(categoryJson)
{
window.categoryJson=JSON.parse(categoryJson);
optionList();
}

//calls ajax that deals with the log that is displayed on the page. The Selection variables contains selected dates from the datepicker. The value "currentSelection" of selction is used when a function updates the information displayed without changing the selected date. 
function printLog(selection)
{
	if(selection=="currentSelection")
		{ 
			selection=$('#selectedDate').text(); 
		}
	var selectedCategories=window.selectedCategories;
	var dateRange=$('#dateRange').text();
	
	$.ajax({
	url:"ajax.php",
	type:'GET',
	data:{printLog:'printLog', startDate:selection, dateRange:dateRange, selectedCategories:selectedCategories},
	success:function(result){
	$('#sidePanel').height(0);
	$('#calendarInfo').html(result); 
		if($('#datepicker').height()+$('#categories').height()<=$('#content').height())
		{
		
		$('#sidePanel').height($('#content').height());
		}
		else
		{
		$('#sidePanel').height($('#datepicker').outerHeight()+$('#categories').outerHeight()+50);
		}
	}
	});
	
	
}






//------------------------------------------------This takes care of the calendar (datepicker) and the selection of the date range displayed on the calendar---------------------------------------------
function calendar(selection)
{
	$('#datepicker').datepicker('destroy');
	$('#datepicker').datepicker({dateFormat:"yy-mm-dd", onSelect: function (selection){ $('#selectedDate').text(selection); calendar(selection);selectDateRange($('#dateRange').text(), "clicked on date"), printLog(selection);} , defaultDate: selection,  beforeShowDay:function(date){
	
	
	
	var dayInMilliseconds=86400000;
	var curDate=new Date(selection);
	curDate=curDate.valueOf();
	var days=Math.floor(curDate/dayInMilliseconds);
	

	if($('#dateRange').text()=="day"){ var dayRange=1; }
	if($('#dateRange').text()=="3days"){ var dayRange=3; }
	if($('#dateRange').text()=="week"){ var dayRange=8; }
	if($('#dateRange').text()=="month"){ var dayRange=30; }
	if($('#dateRange').text()=="year"){ var dayRange=365; }

	if(Math.floor(date.valueOf()/dayInMilliseconds) > days && Math.floor(date.valueOf()/dayInMilliseconds) < days+dayRange)
	{
		return[true, "highlight", ""];		
	}
	return[true, "ui-datepicker-days-cell", ""];
	} });
	//creates the day range options below the calendar and calls the functions that use the date range chosen to determine what shows up
	var rangeButton=$('#selectedDate').text();
	$('#datepicker').append("<div id='rangeOptions' class='centered bold'><span><a id='dayRange' onclick='$(\"#dateRange\").text(\"day\");calendar(\""+rangeButton+"\"); selectDateRange(\"day\");'>1 DAY </a></span><span><a id='3daysRange' onclick='$(\"#dateRange\").text(\"3days\"); calendar(\""+rangeButton+"\"); selectDateRange(\"3days\");'>3 DAYS </a></span><span><a id='weekRange' onclick='$(\"#dateRange\").text(\"week\");calendar(\""+rangeButton+"\"); selectDateRange(\"week\");'> WEEK </a></span><span><a id='monthRange' onclick='$(\"#dateRange\").text(\"month\");calendar(\""+rangeButton+"\"); selectDateRange(\"month\");'> MONTH </a></span><span><a id='yearRange' onclick='$(\"#dateRange\").text(\"year\");calendar(\""+rangeButton+"\"); selectDateRange(\"year\");'> YEAR </a></span></div>");
	

}

//---------------------------------------------------------------This takes care of the darkening of the option chosen for the date range displayed.-----------------------------------------------------
function selectDateRange(range, clicked)
{
	$('#rangeOptions').removeClass("selected");
	$('#'+range+'Range').addClass("selected");
	if(clicked=="clicked on date"){}
	else{ printLog('currentSelection');}
}

//-----------------------------------------This takes care of the first level of options for the categories on the page. It takes care of making options visible or invisible----------------------------
function showHide(id)
{
	$("ul").each
	(
	  function() 
	  {
		 var elem = $(this);
		 if (elem.children().length == 0) 
		 {
		   elem.remove();
		 }
	  }
	);
	
	if($('#'+id).hasClass('invisible')===true)
		{
		$('#'+id).attr('class', 'visible');
		}
	else
		{
			if($('#'+id).hasClass('visible')===true)
			{
			$('#'+id).attr('class', 'invisible');
			$('#'+id).find("div").attr('class', 'invisible');
			$('#'+id+" :checkbox").attr("checked", false);
			}
		}
}


//---------------------------------------------------------This keeps track of the selected categories and updates the information that we get from the API----------------------------------------------
var selectedCategories=new Array();
function categoryFilter(categoryId)
{
//this part takes care of what happens when you add a new option. It basically selects the option and if it has a parent it unselects the parent.
	if($('#category'+categoryId).attr('checked')=='checked')
	{
	selectedCategories.push(categoryId);
		for(row in window.categoryJson)
		{
			if(window.categoryJson[row].ParentCategoryId!=null)
			{	
					
				if(window.categoryJson[row].CategoryId==categoryId && jQuery.inArray(window.categoryJson[row].ParentCategoryId.toString(), window.selectedCategories)!=-1)
				{

				window.selectedCategories.splice(jQuery.inArray(window.categoryJson[row].ParentCategoryId.toString(), window.selectedCategories), 1);
				
				}
			}
		}
	}
	
	
	
	if($('#category'+categoryId).attr('checked')==undefined)
	{
//uncheck self if there is no children selected. Counts for all children and sub children. At the end of this "for in" childrenSelected must be 0 to uncheck itself. Whenever you click on a child element it will already automatically remove its parent from the array.
		var childrenSelected=0;
		function children(parent)
		{
			for(row in categoryJson)
			{
				if(window.categoryJson[row].ParentCategoryId==parent)
				{
					if(jQuery.inArray(window.categoryJson[row].CategoryId.toString(), window.selectedCategories)!=-1)
					{
						childrenSelected++;
						children(window.categoryJson[row].CategoryId);
					}
					children(window.categoryJson[row].CategoryId);
				}
			}
		}
		children(categoryId)	
		if(childrenSelected==0 )
		{ 
		window.selectedCategories.splice(jQuery.inArray(categoryId, window.selectedCategories), 1); 
		}
		
//when the box that is unchecked has checked children. Whenever children and grandchildren  are found to be selected they are automatically removed from the array (selectedCategories). 
		function removeChildren(parent)
		{
			for(row in categoryJson)
			{
				if(window.categoryJson[row].ParentCategoryId==parent)
				{
					if(jQuery.inArray(window.categoryJson[row].CategoryId.toString(), window.selectedCategories)!=-1)
					{
						window.selectedCategories.splice(jQuery.inArray(window.categoryJson[row].CategoryId.toString(), window.selectedCategories), 1);
						removeChildren(window.categoryJson[row].CategoryId);
					}
					removeChildren(window.categoryJson[row].CategoryId);
				}
			}
		}
		removeChildren(categoryId);
//This checks the parent category if its child is unchecked and there is no other sibling children checked. ADD to account for the situation when the sibling's children are checked
		childrenSelected=0; 
		function addParent(parent)
		{
			
			for(row in categoryJson)
			{
				if(window.categoryJson[row].ParentCategoryId==parent )
				{
					if(jQuery.inArray(window.categoryJson[row].CategoryId.toString(), window.selectedCategories)!=-1)
					{
						childrenSelected++;
						children(window.categoryJson[row].CategoryId);
					}
					children(window.categoryJson[row].CategoryId);
				}
			}
		}
		for(row in categoryJson)
		{
			
			if(window.categoryJson[row].CategoryId==categoryId && window.categoryJson[row].ParentCategoryId!=null)
			{	
			var parentMatch=window.categoryJson[row].ParentCategoryId;
			addParent(window.categoryJson[row].ParentCategoryId);			
			}
		}
		if(childrenSelected==0 && parentMatch!=null)
		{
		window.selectedCategories.push(parentMatch.toString());
		}

	}
	
console.log(selectedCategories);
printLog('currentSelection');
}

//----------------------------------------------------------------------This brings up the ADD window that allows users to add their own entry to the database-------------------------------------------
function addWindow()
{

$('#addEntry').dialog({modal: true, title:"Add an Entry", minWidth:730, minHeight:335, resizable:true, close:function(){$('#addEntryTitle').val(''); $('option:selected').prop('selected', false); $('.nicEdit-main').html(''); $('#addEntryDate').val(''), $('#addEntryTime').val(''), $('#addEntryEndTime').val('')}});
$('#addEntryDate').datepicker({dateFormat:"yy-mm-dd", showOn: 'button', buttonImage: '../includes/libs/img/cal.gif', buttonImageOnly:true});
	if(window.nicAddOn==undefined)
	{
		var nicEdit=new nicEditor({fullPanel : true}).panelInstance("newEntryDescription",{hasPanel : true});
		window.nicAddOn=true;
	}
	

}

//---------------------------------------------------------------------------This submits the entry to the database and updates what is displayed--------------------------------------------------------
function submitNewEntry()
{
	var submitDate=$('#addEntryDate').val();
	var submitTitle=$('#addEntryTitle').val();
	var submitDescription=$('#addEntry').find('.nicEdit-main').html();
	var submitCategory=$('#submitCategories').find('.addEditOptions').val();
	if($('#addEntryTime').val().length<5 || $('#addEntryEndTime').val().length<5)
	{
		return alert("Please enter the times using the format \"xx:xx\"");
	}
	var submitStartHours=parseInt($('#addEntryTime').val().slice(0, 2));
	var submitStartMinutes=$('#addEntryTime').val().slice(3);
	var addEntryAmPm=$('#addEntryAmPm').val();
	if(addEntryAmPm=="pm")
	{
		submitStartHours+=((submitStartHours==12)?0:12);
		var submitTime=submitStartHours+":"+submitStartMinutes;
	}
	else
	{
		var submitTime=((submitStartHours==12)?00:submitStartHours)+":"+submitStartMinutes;
	}
	var submitEndHours=parseInt($('#addEntryEndTime').val().slice(0, 2));
	var submitEndMinutes=$('#addEntryEndTime').val().slice(3);
	var addEntryEndAmPm=$('#addEntryEndAmPm').val();
	if(addEntryEndAmPm=="pm")
	{
		submitEndHours+=((submitEndHours==12)?0:12);
		var submitEndTime=submitEndHours+":"+submitEndMinutes;
	}
	else
	{
		var submitEndTime=((submitEndHours==12)?00:submitEndHours)+":"+submitEndMinutes;
	}
	if($('#addEntryTitle').val()=='' || $('#addEntryDate').val()=='')
	{
	alert("Please fill up title and date");
	}
	if($('#addEntryTitle').val()!='' && $('#addEntryDate').val()!='')
	{
		if($('.nicEdit-main').text()=='')
		{
		var choice=confirm("Are you sure you don\'t want to enter any description?");
			if(choice==true)
			{
				$.ajax({
				url:"ajax.php",
				type:'GET',
				data:{submitEntry:'submitEntry', submitDate:submitDate, submitTime:submitTime, submitEndTime:submitEndTime, submitTitle:submitTitle, submitDescription:submitDescription, submitCategory:submitCategory},
				success:function(result){alert("Your entry was successfully submitted");}				
				});
				$('#addEntry').dialog('close');
			}
		}
		else
		{
			$.ajax({
			url:"ajax.php",
			type:'GET',
			data:{submitEntry:'submitEntry', submitDate:submitDate, submitTime:submitTime, submitEndTime:submitEndTime, submitTitle:submitTitle, submitDescription:submitDescription, submitCategory:submitCategory},
			success:function(result){alert("Your entry was successfully submitted");}			
			});
			$('#addEntry').dialog('close');
		}
	}
printLog("currentSelection");
}

//----------------------------------------------------------------------This generates the options of categories available at the ADD and Edit window-----------------------------------------------------------
function optionList()					
{
var parent=new Array();
var space="";
var color="white";
var options="<select class='addEditOptions'>";

	function subCategories(parentEvent)
	{
		space=space.concat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		for(child in categoryJson)
		{	
			if(parentEvent==categoryJson[child].ParentCategoryId && categoryJson[child].ParentCategoryId!=null)
			{
			((color=="white")?color="#E2E2E2":color="white");
			options+="<option style='background-color:"+color+";' value='"+window.categoryJson[child].CategoryId+"'>"+space+window.categoryJson[child].Name+"</option>";
			subCategories(window.categoryJson[child].CategoryId);//work on the spaces, they are not working.
			}
		}
		space=space.slice(0, -30);


	}
var count=0;
for (opt in categoryJson)
	{
	if(jQuery.inArray(categoryJson[opt].CategoryId.toString(), parent)==-1 && categoryJson[opt].ParentCategoryId==null) 
		{
		space="";
		options+="<option style='background-color:white;' "+((count==0)?"selected='selected'":"")+" value='"+window.categoryJson[opt].CategoryId+"'>"+categoryJson[opt].Name+"</option>";
		parent.push(categoryJson[opt].CategoryId.toString());
		subCategories(window.categoryJson[opt].CategoryId)
		count++;
		}
	}

options+="</select>";
	$('#submitCategories').html(options);
	$('#editCategories').html(options);


	
	$("ul").each
	(
	  function() 
	  {
		 var elem = $(this);
		 if (elem.children().length == 0) 
		 {
		   elem.remove();
		 }
	  }
	);
	
}


//This removes an entry in the database
function removeEntry(titleId, date, startTime, occurrenceId)
{
var title=$('#'+titleId).text();

$.ajax({
			url:"ajax.php",
			type:'GET',
			data:{removeEntry:'removeEntry', titleRemove:title, dateRemove:date, startTime:startTime, occurrenceId:occurrenceId},
			success:function(result){alert(result); printLog("currentSelection")}			
			});
		
}
//------------------------------------------------------------------------------------This allows you to select the edit button before it disappears-----------------------------------------------------
function editButtonShow(id)
{
window.timed=setTimeout(function(){ $('#'+id).css("display","none")},400)
}

//-----------------------------------------------------------------------------------This brings up the window to edit the information-------------------------------------------------------------------
var originalId=new Array();
var editions=new Array();
function editWindow(occurrenceId ,date, editStartTime, titleId, descriptionId, database, editEndTime, categoryId)
{

	$('#editEntry').dialog({modal: true, title:"Edit Entry", minWidth:730, minHeight:335, resizable:true, close:function(){ window.originalId=[]; }});
	$('#editEntryDate').datepicker({dateFormat:"yy-mm-dd", showOn: 'button', buttonImage: '../includes/libs/img/cal.gif', buttonImageOnly:true});
	if(window.nicEditOn==undefined)
	{
		var nicEdit=new nicEditor({fullPanel : true}).panelInstance("editEntryDescription",{hasPanel : true});
		window.nicEditOn=true;
	}

	var title=$('#'+titleId).html();
	var description=$('#'+descriptionId).html().trim();
	
	window.originalId={ "occurrenceId":occurrenceId, "database":database}
	
	//these few lines are to make the time output easier to read
		//start time
	window.unixEditStartTime=editStartTime;// Unix time stamp that is sent to PHP when editions are submitted
	var hourAndMinute=new Date(editStartTime*1000);
	var hours=((hourAndMinute.getHours()<10)?"0"+hourAndMinute.getHours():hourAndMinute.getHours());
	var minutes=((hourAndMinute.getMinutes()<10)?"0"+hourAndMinute.getMinutes():hourAndMinute.getMinutes());
	var amPm=((hours>=12)?"pm":"am");//am if hours are less than 12 and pm if more
	hours=((hours>12)?hours-12:hours);//if hours are more than 12 subtract 12 from it
	hours=((hours<10 && amPm=="pm")?"0"+hours:hours);
	hours=((hours==00)?12:hours);//if it is midnight add 12 hours to it
		//end time
	window.unixEditEndTime=editEndTime;// Unix time stamp that is sent to PHP when editions are submitted
	var hourAndMinuteEnd=new Date(editEndTime*1000);
	var hoursEnd=((hourAndMinuteEnd.getHours()<10)?"0"+hourAndMinuteEnd.getHours():hourAndMinuteEnd.getHours());
	var minutesEnd=((hourAndMinuteEnd.getMinutes()<10)?"0"+hourAndMinuteEnd.getMinutes():hourAndMinuteEnd.getMinutes());
	var amPmEnd=((hoursEnd>=12)?"pm":"am");//am if hours are less than 12 and pm if more
	hoursEnd=((hoursEnd>12)?hoursEnd-12:hoursEnd);//if hours are more than 12 subtract 12 from it
	hoursEnd=((hoursEnd<10 && amPmEnd=="pm")?"0"+hoursEnd:hoursEnd);
	hoursEnd=((hoursEnd==00)?12:hoursEnd);//if it is midnight add 12 hours to it

	
	//window.editions are used to make comparisons and determine if anything has changed before submitting
	window.editions['title']=title;
	window.editions['description']=description;
	window.editions['date']=date;
	window.editions['categoryId']=categoryId;
	window.editions['editStartTime']=((hourAndMinute.getHours()<10)? "0"+hourAndMinute.getHours():hourAndMinute.getHours())+":"+((hourAndMinute.getMinutes()<10)? "0"+hourAndMinute.getMinutes():hourAndMinute.getMinutes());
	window.editions['editEndTime']=((hourAndMinuteEnd.getHours()<10)? "0"+hourAndMinuteEnd.getHours():hourAndMinuteEnd.getHours())+":"+((hourAndMinuteEnd.getMinutes()<10)? "0"+hourAndMinuteEnd.getMinutes():hourAndMinuteEnd.getMinutes());
	//this gets the information from the event to populate the information shown in the diolog window
	$('#editEntryTitle').val(title);
	$('#editEntry').find('.nicEdit-main').html(description);
	$('#editEntryDate').val(date);
	$('#editCategories').find('.addEditOptions').val(categoryId);
	$('#editStartTime').val(hours+":"+minutes);
	$("#editStartAmPm").val(amPm);
	$('#editEndTime').val(hoursEnd+":"+minutesEnd);
	$("#editEndAmPm").val(amPmEnd);
/*  The nic editor below keeps messings up the code, see if there is a better substitute later. 
window.myNicEdit=new nicEditor({fullPanel : true}).panelInstance("editEntryDescription");
*/
}
//-----------------------------------------------------------------------This checks and Submits changes made to an event-------------------------------------------------------------------------------
function submitEditedEntry()
{
var editTitle=$('#editEntryTitle').val();
var editDescription=$('.nicEdit-main').html();
var editDate=$('#editEntryDate').val();
var editCategory=$('#editCategories').find('.addEditOptions option:selected').val();
		//format the time that is sent to the database
if($('#editStartTime').val().length<5 || $('#editEndTime').val().length<5)
{
	return alert("Please enter the time using the format \"xx:xx\"");
}
var startHours=parseInt($('#editStartTime').val().slice(0,2));
var startMinutes=$('#editStartTime').val().slice(3);
var editStartAmPm=$("#editStartAmPm").val();
if(editStartAmPm=="pm" && startHours<12)
{
	var editStartTime=startHours+12+":"+startMinutes;
}
else
{
	startHours=((startHours<10)?"0"+startHours:startHours);
	startHours=((startHours==12 && editStartAmPm=="am")? "00":startHours);
	var editStartTime=startHours+":"+startMinutes;
}

var endHours=parseInt($('#editEndTime').val().slice(0,2));
var endMinutes=$('#editEndTime').val().slice(3);
var editEndAmPm=$("#editEndAmPm").val();
if(editEndAmPm=="pm" && endHours<12)
{
	var editEndTime=endHours+12+":"+endMinutes;
}
else
{
	endHours=((endHours<10)?"0"+endHours:endHours);
	endHours=((endHours==12 && editEndAmPm=="am")? "00":endHours);
	var editEndTime=endHours+":"+endMinutes;
}
	if(editTitle==window.editions['title'] && editDescription==window.editions['description'] && editDate==window.editions['date'] && editStartTime==window.editions['editStartTime'] && editEndTime==window.editions['editEndTime'] && editCategory==window.editions['categoryId'])
	{
	alert('No changes have been made');
	}
	else
	{
var selection=$('#selectedDate').text(); 
var selectedCategories=window.selectedCategories;
var dateRange=$('#dateRange').text();
$.ajax({
			url:"ajax.php",
			type:'GET',
			data:{submitEdit:"submitEdit", originalId:window.originalId, editTitle:editTitle, editDescription:editDescription, editCategory:editCategory ,editDate:editDate, editStartTime:editStartTime, editEndTime:editEndTime, startDate:selection,  dateRange:dateRange, selectedCategories:selectedCategories},
			success:function(result)
			{ 
				alert(result);
				printLog("currentSelection");
				$('#editEntry').dialog('close'); 
			}
		});
	}

}

//------------------------------------------------------------This is for when the API events change and you have an editted version of that Event------------------------------------------------------ 

function eventChange(numChanges, occurrenceId, changeJson)
{
changeJson=JSON.parse(changeJson);
var eventNumber=1;
if(window.warning!="shown")
{
	$('#warningDialog').text('Changes have been made to '+numChanges+' events you have editted. Would you like to add the new information to your editions? some information like dates and times will be replaced');
	$('#eventChange').dialog({ modal: true, title:"Warning", buttons:{
	"See Changes":function()
						{
							if(window.tableHasBeenShwon==undefined)
							{
								var changeText='<thead id="changeContentTable"><tr>';
								for(value in changeJson)
								{
									if(changeJson[value]!=undefined)
									{	
									changeText+="<th> Changes made to event "+eventNumber+"</th>";
									}
									eventNumber++;
								}
								changeText+="</tr></thead><tbody><tr>";
								for(value in changeJson)
								{
									if(changeJson[value].originalDescription!=undefined)
									{
									changeText+='<td><b>Original Description: </b><span>'+changeJson[value].originalDescription+'</span><br><b> New Description: </b>'+changeJson[value].newDescription+'</td>';
									}
								}
								changeText+="</tr><tr>";
								for(value in changeJson)
								{
									if(changeJson[value].originalTitle!=undefined)
									{
									changeText+='<td><b>Original Title: </b><span>'+changeJson[value].originalTitle+'</span><br><b> New Title: </b>'+changeJson[value].newTitle+'</td>';
									}
								}
								changeText+="</tr><tr>";
								for(value in changeJson)
								{
									if(changeJson[value].originalDate!=undefined)
									{
									changeText+='<td><b>Original Date: </b><span>'+changeJson[value].originalDate+'</span><br><b> New Date: </b>'+changeJson[value].newDate+'</td>';
									}
								}
								changeText+="</tr><tr>";
								for(value in changeJson)
								{
									if(changeJson[value].originalStartTime!=undefined)
									{
									changeText+='<td><b>Original Start Time: </b><span>'+changeJson[value].originalStartTime+'</span><br><b> New Start Time: </b>'+changeJson[value].newStartTime+'</td>';
									}
								}
								changeText+="</tr><tr>";
								for(value in changeJson)
								{
									if(changeJson[value].originalEndTime!=undefined)
									{
									changeText+='<td><b>Original End Time: </b><span>'+changeJson[value].originalEndTime+'</span><br><b> New End Time: </b>'+changeJson[value].newEndTime+'</td>';
									}
								}
								changeText+="</tr></tbody>";	
								eventNumber=1;
								$('#displayEventChanges').html(changeText);
								
								$("tr").each
								(
								  function() 
								  {
									 var elem = $(this);
									 if (elem.text().length == 0) 
									 {	
										elem.remove();

									 }
								  }
								);
								
								$('#displayEventChanges').dataTable( 
								{
									"bJQueryUI": true,
									"sPaginationType": "full_numbers",
									"bFilter": false,
									"bDestroy": true,
									"bRetrieve": true
								} );
								
							window.tableHasBeenShwon=true;
							}

							$('#displayEventChanges').dialog({title:"Changes", minWidth:600});
						},
	"yes":function()
						{	
							var selection=$('#selectedDate').text(); 					
							$.ajax({
								url:"ajax.php",
								type:'GET',
								data:{changeOriginal:'changeOriginal', occurrenceIdNewInfo:occurrenceId, startDate:selection, dateRange:"month"},
								success:function(changed){ alert(changed); $('#eventChange').dialog('close'); $('#displayEventChanges').dialog('close'); }
								});
								
						},
	"not now":function()
						{
							alert("This message will appear again when this page is reloaded.");
							$('#eventChange').dialog('close');
						}
	}
	});
	window.warning="shown";
}

}






