/*
*	Name: weeklyReport.js
*	Application: Weekly Report
*
*	Description: This is the JavaScript file used
*	by the Weekly Report app. All of the JavaScript the
*	Weekly Report app uses is contained in this file. It
*	controls almost all the functionality of the app.
*/

//When the window first loads
window.onload = function(){
	loadCategories();
	//Set up the search inputs
	var d = new Date();
	var todaysDate = d.format("mm/dd/yyyy")
	
	$("#startDate").datepicker();
	$("#startDate").val(todaysDate);
	$("#startDate").change(function() {
		$("#endDate").datepicker("option", "minDate", $("#startDate").val());
	});
	$("#endDate").datepicker( {minDate: new Date()} );
	$("#endDate").val(todaysDate);

	//Populate the report
	getReport();
	
	//Prevent default when hitting enter key in newEntry input
	$("input[id*=newEntry]").keypress(function(ev) {
		if (ev.which === 13) {
			ev.preventDefault();
		}//if
	});

	$('button').button();
	
	//This is a global variable that needed to be declared before the function was called
	editedTimeoutFunction = null;
}//window.onload


//This is the ajax call to get the report from the DB and then print it on the index.php page.
function getReport(){

	//Declare variables
	var startDate = $("#startDate").val();
	var endDate = $("#endDate").val();
	var categoriesList = new Array();
	var employeeList = new Array();
	var checked = $(".searchChecked:checked").val();
	
	//If they didn't choose any categories warn them and don't continue with the rest of the function
	if($(".searchCategory:checked").length == 0){
		
		$noCategoriesChosenNotification = notify("<h3 style='color:silver;'>You have to choose at least one category.</h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});

		//This makes it so that if the window is resized the notification stays in the center
		$(window).resize(function() {
   			$noCategoriesChosenNotification.position({'my': 'center center', 'at': 'center center', 'of': window});
   			$noCategoriesChosenNotification.css({'bottom':'auto', 'right':'auto'});
   		});
   		
   		return;		
	}//if
	
	//If they didn't choose an employee warn them and don't continue with the rest of the function
	if($(".searchEmployees:checked").length == 0){
		
		$noEmployeesChosenNotification = notify("<h3 style='color:silver;'>You have to choose at least one employee.</h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});

		//This makes it so that if the window is resized the notification stays in the center
		$(window).resize(function() {
   			$noEmployeesChosenNotification.position({'my': 'center center', 'at': 'center center', 'of': window});
   			$noEmployeesChosenNotification.css({'bottom':'auto', 'right':'auto'});
   		});
   		
   		return;		
	}//if
	
	
	//Add all the checked categories to the categoriesList
	$(".searchCategory:checked").each(function(){
			categoriesList.push($(this).val());
	});
	
	//Add all the checked categories to the categoriesList
	$(".searchEmployees:checked").each(function(){
			employeeList.push($(this).val());
	});
	
	
	//Post to printReport.php and check result
	$.ajax({
	
		type: "POST",
		url: "printReport.php",
		data: { startDate: startDate, endDate: endDate, categoriesList: categoriesList, employeeList: employeeList, checked: checked }
		
	}).done(function ( result ){
		
		var resultList = JSON.parse(result);
		
		printReport(resultList);
	});

}//getReport

//This is the ajax call to print the report on the index.php page.
function printReport(entries){
	
	//Declare variables
	var lastEmpNetID = "";
	var lastType = "";
	var num = 0;
	var html = "";
	
	html += "<div id='finalReport'>";
	
	//If the query didn't return any entries tell the user
	if(entries.length == 0){
			
		//html += "<div id='finalReport'>";
		html += "<br />";
		html += "<h2>Your search returned no results.  Please try modifying your search.</h2>";
		html += "</div>"
	
		$( '#finalReport' ).replaceWith(html);

		return;

	}//if
	
	//Create html
	//html += '<div id="finalReport">';
	html += '<h3>Review entries to be sent to your director</h3>';
	html += '<div id="reportTable">';
	html +=	'<b>Additional Notes:</b>';
	html +=	'<br />';
	html +=	'<i>Additional notes will be added to the beginning of the report being sent to your director.</i>';
	html +=	'<textarea id="notes" rows="4" style="margin:0px;width:880px;height:81px;"></textarea>';
	html += '<br>';
	html += '<br>';
	html += '<b>Instructions:</b><br>';
	html += '<i>Only the comments that are checked will be included in the emailed report.</i>';
	html += "<form id='report'>";
	
	for( var i = 0; i < entries.length; i++){
		
		//More variables
		var curType = entries[i].category;
		var curEmpNetID = entries[i].netID;
		var curID = entries[i].ID;
		
		/*Each time the curType changes to be different from the last type
		* print closing tags for the tbody and table, except the very first time through.*/
		if(curType != lastType && num == 1)
		{
			html += "</tbody>";
			html += "</table>";
		}//if
		
		/*Each time the curType changes to be different from the last type
		* create the h2, a new div, and include all (highlights/challenges/interactions)
		* checkbox, and opening tags for the table.*/
		if(curType != lastType)
		{
			/*Clear out the lastEmp variable incase the last emp for the last type of comment
			* is the same as the first employee for this type of comment.*/
			lastEmpNetID = '';
			
			//If it's not the first time through close the div of the last type before starting this one.
			if (num == 1)
			{
				html += "</div>";
			}
			
			html += "<h2>" + curType + "</h2>";
			html += '<div class="report">';
			html += '<input id="' + curType + '" type="checkbox" onclick="checkContent(&quot;' + curType + '&quot;)" name="' + curType + '" /> Include all ' + curType + '<br><br>';
			html += '<table class ="reportTable">';
			html += '<tbody>';
			
			/*Once it's past the first time the $num will be set to 1, all we care about is whether 
			* or not it's the first time through.*/
			num = 1;
			
		}//if
		
		/*Each time the curEmpNetID changes to be different from the lastEmpNetID create the table header,
		* and a checkbox to include all of that employee's comments.*/
		if(curEmpNetID != lastEmpNetID)
		{
			//id of checkbox
			id = curType + '_' + curEmpNetID;
			
			html += '<tr>';
			html += '<th>' + entries[i].firstName + ' ' + entries[i].lastName + ' ';
			html += '<input id="' + id + '" type="checkbox" onclick="checkContent(&quot;' + id + '&quot;)" name="' + id + '"/>';
			html += '</th>';
			html += '</tr>';
			
		}//if
		
		/*Echo a checkbox to include the comment the employee submitted, and a textarea with the comment.*/  
			
		//Adjust the number of rows in the text area depending on the number of characters in the comment.
		var characterCount = entries[i].comments.length;		
		var rows = "1";
		if(characterCount > 110)
		{
			rows = "2";
		}//if
		
		if(characterCount > 220)
		{
			rows = "3";
		}//if
		
		//Convert submitDate string from DB to Date object, then format it for the report.
		var d = new Date(entries[i].submitDate.replace(/-/g, "/"));
		var formattedSubmitDate = d.format("mmm d - ");

		html += '<tr>';
		html += '<td>';

		//If the checkbox was checked, print a checked checkbox
		if(entries[i].checked == 1){
			html += '<input id="' + curType + '_' + curEmpNetID + '_' + curID + '" class="comment" type="checkbox" checked="checked" name="' + curEmpNetID + '" /> ';
		} else {
			html += '<input id="' + curType + '_' + curEmpNetID + '_' + curID + '" class="comment" type="checkbox" name="' + curEmpNetID + '" /> ';
		}//if-else
		
		
		html += formattedSubmitDate;
		html += '<textarea rows="' + rows + '"' + 'id="' + entries[i].ID + '" class="textareaComment">' + entries[i].comments + '</textarea>';
		html += ' ';
		html += '<a id="' + entries[i].ID + '" href="" class="deleteLink" onclick="deleteEntry(event)">Delete</a>';
		html += '</td>';
		html += '</tr>';

		//Set lastEmpNetID and lastType in preparation for the next time through the for loop	
		lastEmpNetID = curEmpNetID;
		lastType = curType;
			
	}//for
	
	//Finally close off the last div, tbody, and table tags.
	html += '</div>';
	html += '</tbody>';
	html += '</table>';
	html += '</form>';
	html += '</div>';
	
	html += '<br>';
	html += '<hr>';
	html += '<br>';
	
	html += '<button style="float:right; margin-right:10px; margin-bottom:10px;" id="previewBtn" onclick="previewEmail(event)">Preview Final Report</button>';
	html += '</div>';
	
	//Update content div with new html
	$( '#finalReport' ).replaceWith(html);
	
	//Reset the values of #newentry and #newEntryCategory
	$( '#newEntry' ).val('');
	$( "#newEntryCategory" ).val('');
	
	//Make all h2 headings accordions
	h2Accordion();
	
	//On click start a countdown, when the countdown is finished get all the elements with an edited class and save the changes to the DB.	
	$( ".comment" ).click(function(){
		edited($(this));
	});
	
	//On keyup start a countdown, when the countdown is finished get all the elements with an edited class and save the changes to the DB.
	$( ".textareaComment" ).keyup(function(){
		edited($(this));
	});

}//printReport


//Make all h2 in the report collapsible headers
function h2Accordion(){
	$('#report>h2').click(function() { 
		$(this).next().toggle('slow'); 
		return false; 
	}).next();
		
	$('button').button();
}//h2Accordion


/*This will take the new manager entry, submit it to the DB, inform the user if it was successful or
not then update the report lower down on the index.php page.*/
function submitEntry(e) {
	e.preventDefault();
	
	//Declare variables
	var entry = $( "#newEntry" ).val();
	var category = $( "#newEntryCategory" ).val();
	
	//Check to make sure they chose a category
	if (category == 0) {
		$noCategoryChosenNotification = notify("<h3 style='color:silver;'>Please choose a category.</h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
		$(window).resize(function() {
   			$noCategoryChosenNotification.position({'my': 'center center', 'at': 'center center', 'of': window});
   			$noCategoryChosenNotification.css({'bottom':'auto', 'right':'auto'});
   		});
       		
		return;
	}//if
	
	//Check to make sure they made an entry
	if (entry == "") {
		$noCommentsNotification = notify("<h3 style='color:silver;'>Please enter a comment in the text box.</h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
		$(window).resize(function() {
   			$noCommentsNotification.position({'my': 'center center', 'at': 'center center', 'of': window});
   			$noCommentsNotification.css({'bottom':'auto', 'right':'auto'});
   		});
   		
		return;
	}//if
	
	var notification = notify("Submitting your entry...", {'clickToDismiss':true, 'status':'warning'});
	
	//Post to submitEntry.php and check result
	$.ajax({
	
		type: "POST",
		url: "submitEntry.php",
		data: { comment: entry, category: category }
		
	}).done(function ( msg ) {

		//Result object
		var result = JSON.parse(msg);
		
		//Dismiss the warning notification that the entry is being submitted.
		notification.click();
		
		//Notify the user if the result is successful or not
		if (result.status){
			notify("Entry submitted successfully.", {'status':'success', 'duration':10000});
		} else {
			$errorSubmittingToDBNotification = notify("<h3 style='color:silver;'>There was an error submitting your entry to the database.<br>Please try again.  </h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
			$(window).resize(function() {
	   			$errorSubmittingToDBNotification.position({'my': 'center center', 'at': 'center center', 'of': window});
	   			$errorSubmittingToDBNotification.css({'bottom':'auto', 'right':'auto'});
	   		});
	   		
	   		console.log("Error: " + result.error);
			return;
		}//if-else
		
		//Refresh report on bottom of page
		getReport();
		
	});//ajax

}//submitEntry


//Marks an entry as deleted in the DB.
function deleteEntry(e) {
	e.preventDefault();
	
	//Double check to make sure they want to delete the entry, if no then don't delete it.
	var confirmWindow = confirm("Are you sure you want to delete this entry?" + '\n\n' + "If you delete this you won't be able to undo it.");
	if(!confirmWindow){
		return;
	}//if
	
	//Get the id of the comment (this is the ID in the managerReports table).
	var id = e.currentTarget.getAttribute("id");
	
	//Create notification that the report and email are being processed.
    var deletingEntryNotification = notify("Your entry is being deleted..", {'clickToDismiss':true, 'status':'warning'});
    
    //Post data to deleteEntry.php
    $.ajax({
    	type: "POST",
    	url: "deleteEntry.php",
    	data: { id: id }
    
    }).done(function ( msg ) {
    
    //Result object
    var result = JSON.parse(msg);
    
    //Dismiss the warning notification that the entry is being deleted.
    deletingEntryNotification.click();
    
    //Notify the user if the result was successful or not
    if (result.status){
   		notify("Entry has been successfully deleted.", {'status':'success', 'duration':10000});
    } else {
    	$errorDeletingEntryNotification = notify("<h3 style='color:silver;'>There was an error deleting the entry.<br>Please try again.  </h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
		$(window).resize(function() {
   			$errorDeletingEntryNotification.position({'my': 'center center', 'at': 'center center', 'of': window});
   			$errorDeletingEntryNotification.css({'bottom':'auto', 'right':'auto'});
   		});
   		
		console.log("Error: " + result.error);
		return;
    }//if-else
    
        //Refresh the report entries on the page
	    getReport();
    
    });//ajax
    
}//deleteEntry


//Saves any edits to the final manager report to the DB.
function saveChanges(editedObject) {
			
	//Declare variables
	var dbID = "";
	var comments = "";
	var checked = "";
	
	//Check whether the item is a checkbox or a textarea and set appropriate variables
	if(editedObject.is("input")) {
	
		var objectID = editedObject.attr("id");
		
		dbID = objectID.split("_")[2];
		
		if(dbID == undefined) {
			return;
		}//if
		
		if(editedObject.attr("checked") == "checked") {
			checked = 1;
		} else {
			checked = 0;
		}//if-else
		
	}//if
	
	//If the item is a textarea do this
	if(editedObject.is("textarea")) {
		dbID = editedObject.attr("id");
		comments = editedObject.val();
	}//if

			
	//Update the comments in the DB
	$.ajax({
		type: "POST",
		url: "saveChanges.php",
		data: { id: dbID, comments: comments, checked: checked }
	}).done(function ( msg ) {
	
		//Result object
		var result = JSON.parse(msg);
		
		//Notify the user if the result was not successful
		if (result.status){
			//Set the object class to not include "edited" anymore
			editedObject.removeClass("edited");
			
		} else {
			$errorSavingData = notify("<h3 style='color:silver;'>There was an error saving the changes you made.<br>Please try again.</h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
			$(window).resize(function() {
	   			$errorSavingData.position({'my': 'center center', 'at': 'center center', 'of': window});
	   			$errorSavingData.css({'bottom':'auto', 'right':'auto'});
	   		});
	   
	   		//console.log("Error: " + result.error);
			return;
			
		}//if-else
	
	});//ajax

}//saveChanges


//Shows a preview of the email to be sent
function previewEmail(e) {

	//If they haven't selected any comments to include in the report tell them and don't show the preview.
    if($('.comment:checked').length == 0){
       
       //alert('You have not selected anything for the report.');
       e.preventDefault();
       
       $noEntriesCheckedNotification = notify("<h3 style='color:silver;'>You didn't check any entries to include in the report.</h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
       $(window).resize(function() {
       		$noEntriesCheckedNotification.position({'my': 'center center', 'at': 'center center', 'of': window});
       		$noEntriesCheckedNotification.css({'bottom':'auto', 'right':'auto'});
       });
       
       return;
    }//if
    

	//Clear out the html being built for the email.
    $( "#emailText" ).html("");
    
//First get the text from the additional notes.

    //Keep some of the styling done in the notes textarea.
    var additionalNotes = $("#notes").val();
    if (additionalNotes != '')
	{
		additionalNotes = additionalNotes.replace(/(\r\n|\r|\n)/g, '<br />');
		additionalNotes = additionalNotes.replace( /(\#)/g, '');
		additionalNotes = additionalNotes.replace( /(\&)/g, 'and');
	}//if
	
	$( "#emailText" ).append(additionalNotes);

//Next get all of the text from the inputs

	//Declare variables
	var lastNetID = "";
	var counter = 0;

	//Get a list of the categories
	var categories = [];
	$("#newEntryCategory").children().each(function() {
		categories.push($(this).text());
	});
	
	//Loop through each category in the list and add them to the email text
	for (var i=0; i<categories.length; i++) {
		
		var category = categories[i];

		//Add on the different entries
		
		/*If the category doesn't have any comments that are checked then skip this iteration
		**of the for loop and continue on.*/
		if($("input[id*=" + category + "].comment:checked").length == 0)
		{
			continue;
		}//if
		
		//Add on the header for the category
		$( "#emailText" ).append('<h2 style="width=737px;">' + category + '</h2>');
		
		//For each checked comment in this category
		$( "input[id*=" + category + "].comment:checked" ).each(function()
		{
			//Declare variables
			var curNetID = $(this).attr("name");

			//If the curNetID isn't the same as the lastNetID then do this.
			if(lastNetID != curNetID)
			{
				//AJAX request to get name of employee
				var request = $.ajax({
				async: false,
				url: "getNameByNetID.php",
				type: "POST",
				data: {netID : curNetID},
				dataType: "html"
				});
	
				//When the ajax request is done
				request.done(function(msg) 
				{	
					//If it's the first time through don't append the break, otherwise append it
					if(counter != 0)
					{	
						$( "#emailText" ).append("<br />");
					}//if
		
					$( "#emailText" ).append('<b><span class="name">' + msg + '</span></b><br />');
		
				});//request.done
	
				//When the ajax request fails
				request.fail(function(jqXHR, textStatus) 
				{
					alert( "Request failed: " + textStatus );
				});//request.fail
	
			}//if

			//Append comment
			var comment = $(this).parent().children().eq(1).val();
			$( "#emailText" ).append(" - " + comment + "<br />");
		
			//Update netID for next input
			lastNetID = curNetID;
			counter ++;

		});//each highlight

		//Reset variables for next round
		counter = 0;
		lastNetID = "";
	
	}//for

	//Prevents the form from submitting
	e.preventDefault();
	
	
//Finally create the dialog to display the report

	$( "#emailText" ).css("display","block");
	$( "#preview" ).dialog({
		resizable: false,
		width: 800,
		modal: true,
		draggable: true,
		title: "Manager Report",
		buttons: [{ text: "Email Report", 
			click: 	function() {
						var r = confirm("Is all the information correct?");
						if(r == true){
						
							//First check to make sure we have an email in the toInput field.
							var regExPattern = new RegExp(".*@.*\....");
							if(!regExPattern.test($("#toInput").val())){
								$noToInput = notify("<h3 style='color:silver;'>You have to enter an email to send the report to.", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
								$noToInput.css({'z-index':'1002'});
								$(window).resize(function() {
						   			$noToInput.position({'my': 'center center', 'at': 'center center', 'of': window});
						   			$noToInput.css({'bottom':'auto', 'right':'auto'});
						   		});
						   		return;
							}//if
							
							submitFinalReport();
							$( "#preview").dialog("close");
						}	
					} 
				}]
	   });//dialog
    
}//previewEmail


/*Creates a post object that includes the report to email, edits done to comments, 
* which comments were included in the report, and which weren't. Sends it to submitFinal.php
* so the email can be sent to the director and the DB updated.*/
function submitFinalReport(){
	
	//Declare variables
    var toInput = $("#toInput").val();
    var ccInput = $("#ccInput").val();
    var email =  $("#emailText").html();
    var commentsArray = new Array();
    
    
    /*Create an array of objects from the comments on finalReport.php.
    * Each object has the following properties: id, checked, editedText.*/
    $(".comment").each(function(){
    	
		//Create object
    	obj = new Object();
    	
    	//id property
    	obj.id = $(this).parent().children().eq(1).attr("id");
    	
    	//checked property
    	if($(this).attr("checked") != null)//If the comment is checked
    	{
    		obj.checked = 1;	
    	}//if
    	else//If the comment isn't checked
    	{
    		obj.checked = -1;
    	}//else
    	
    	//editedText property
    	if($(this).parent().children().eq(1).attr("class").indexOf("edited") != -1)//If the comment was edited
    	{
    		obj.editedText = $(this).parent().children().eq(1).val();
    	}//if
    	else//If the comment wasn't edited
    	{
    		obj.editedText = null;
    	}//else
    	
    	//Add object to array
    	commentsArray.push(obj);
    	
    });//each
    
    //Create a JSON string of the array we just made.
    var commentsArrayJSON = JSON.stringify(commentsArray);
    
    //Create notification that the report and email are being processed.
    var emailPreparationNotification = notify("The report email is being prepared...", {'clickToDismiss':true, 'status':'warning'});
    
    //Post data to submitFinal.php
    $.ajax({
    	type: "POST",
    	url: "submitFinal.php",
    	data: { emailText: email, commentsArray: commentsArrayJSON, to: toInput, cc: ccInput }
    	
    }).done(function ( msg ){
    	
    	//Result object
    	var result = JSON.parse(msg);
    	
    	//Dismiss the warning notificaiton that the entry is being submitted.
    	emailPreparationNotification.click();
    	
    	//Notify the user if the result is successful or not
    	if (result.status){
			notify("The report has been emailed successfully.", {'status':'success', 'duration':10000});
		}
		else {
			$errorSendingEmailNotification = notify("<h3 style='color:silver;'>There was an error sending the email.<br>Please try again.</h3>", {'clickToDismiss':true, 'status':'failure', 'position':{'my': 'center center', 'at': 'center center', 'of': window}});
			$(window).resize(function() {
       			$errorSendingEmailNotification.position({'my': 'center center', 'at': 'center center', 'of': window});
       			$errorSendingEmailNotification.css({'bottom':'auto', 'right':'auto'});
       		});
       
       		console.log("Error: " + result.error);
			return;
		}
		
		//Refresh the report entries on the page
		getReport();
    	
    });//ajax
    
}//submitReport


//Function used to make the include all and employee name checkboxes work properly.
function checkContent(id){
    checkedState = $("input[id*=" + id + "]").prop("checked");
    $("input[id*=" + id + "]").prop("checked", checkedState);
    $("input[id*=" + id + "]").each(function() {
    	edited($(this));
    });
    
}//checkContent


//Changes the class of a textarea or input that is edited to class="edited" and starts a countdown, when the countdown finishes
//all elements with an edited class have their changes saved to the DB.
function edited(editedObject){
	
	editedObject.addClass("edited");
		
	//Clear the timeout function if it's already been started.  This way we don't hit the DB every time a checkbox is clicked.
	if(editedTimeoutFunction != null){
		clearTimeout(editedTimeoutFunction);
	}//if

	editedTimeoutFunction = setTimeout(function() {
		$(".edited").each(function(){
			saveChanges($(this));
		});		
	}, 500);//editedTimeoutFunction
}//edited


//Toggles the visibility of a dialog
function toggleVisibility(id) {		
	$( "#" + id ).dialog({ resizable: true, width: 300 });
}//toggleVisibility

