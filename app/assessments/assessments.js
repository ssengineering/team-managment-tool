/*
*	Name: assessments.js
*	Application: Assessments
*	Site: ops.byu.edu
*	Author: Joshua Terrasas
*
*	Description: This is the main JavaScript file used
*	by the Assessments app. All of the JavaScript the
*	Assessments app uses is contained in this file. It
*	controls almost all the functionality of the app, as
*	well as a lot of the looks of the app because of the
*	jQuery UI that is used in several parts. The jQuery
*	library is used very heavily in this app. Comments
*	throughout the code are comprehensive since a lot of
*	the same things are done throughout the code. If
*	there is no comment, more than likely the same things
*	is done elsewhere, and a comment is included.
*/

window.onload = function()
{
	$('#modeMenu').buttonset(); // jQuery function that groups the four mode buttons into one.
	viewMode(0); // Default mode to enter upon page load.
}

/* Sets up and loads the 'View' page. If a tab number is provided, it defaults to that tab being open. */
function viewMode(tab)
{
	var page = 'view.php'; // Page to call with AJAX.
	
	var cb = function (result) // Function to run after AJAX call.
	{
		$('#modeContent').replaceWith(result); // Load AJAX results into content div on 'index.php'.
		$('#tabs').tabs({ selected: tab }); // Load jQuery UI tabs.
		$('#accordion').accordion({ active: false }); // Load jQuery UI accordion.
		
		/* Load jQuery UI tooltips. */
		$('#modeContent').tooltip
		({
			track: true
		});
	}

	$.post(page, $(".selectForm").serialize(), cb); // jQuery AJAX call using the POST method.
}

/* Sets up and loads the 'Grade' page. The 'event' parameter is used to check what the user filled out on the form,
   so that the proper AJAX call is made. */ 
function gradeMode(event)
{
    if(typeof gradeMode.counter == 'undefined')
    {
        gradeMode.counter = 0;
    }
    
	if(event == 'employee') // Employee drop down was filled out.
	{
		var page = 'grade.php?employee=' + $('#employee').val();
	}
	else if(event == 'test') // Test drop down was filled out.
	{
		var page = 'grade.php?employee=' + $('#employee').val() + '&group=' + $('#test').find("option:selected").parent().attr("label") + '&test=' + $('#test').val();
	}
	else if(event == 'date') // Date field was filled out.
	{
		var page = 'grade.php?employee=' + $('#employee').val() + '&group=' + $('#test').find("option:selected").parent().attr("label") + '&test=' + $('#test').val() + '&date=' + $('#date').val() + '&score=' + $('#score').val();
	}
	else
	{
		var page = 'grade.php'; // Default page to load.
	}
	
	var cb = function (result)
	{
		$('#modeContent').replaceWith(result);
		$('#date1').datepicker({ dateFormat: 'yy-mm-dd' });
		
		//$('#modeContent').tooltip();
		
		if(event == 'default')
		{
			$('#date').val('');
			$('#test').attr('disabled', 'true');
			$('#date').attr('disabled', 'true');
			$('#score').attr('disabled', 'true');
			$('#resultPass').attr('disabled', 'true');
			$('#resultFail').attr('disabled', 'true');
			$('#notes').attr('disabled', 'true');
			$('.warningText').hide();
		}
		else if(event == 'employee')
		{
			$('#date').val('');
			$('#test').removeAttr('disabled');
			$('#date').attr('disabled', 'true');
			$('#score').attr('disabled', 'true');
			$('#resultPass').attr('disabled', 'true');
			$('#resultFail').attr('disabled', 'true');
			$('#notes').attr('disabled', 'true');
			$('.warningText').hide();
		}
		else if(event == 'test')
		{
			$('#date').val('');
			$('#test').removeAttr('disabled');
			$('#date').removeAttr('disabled');
			$('#score').removeAttr('disabled');
			$('#resultPass').removeAttr('disabled');
			$('#resultFail').removeAttr('disabled');
			$('#notes').removeAttr('disabled');
			$('.warningText').hide();
		}
		else if(event == 'date')
		{
			$('.warningText').hide();
			
			if($('#testDatePassed').val() == 'false')
			{
				if($('#dateException').val() == 1)
				{
					$('#resultFail').attr('checked', 'checked');
					$('#dateWarningText').show('blind', 800);
				}
				else if($('#dateException').val() == 2)
				{
					$('#score').attr('disabled', 'true');
					$('#resultPass').attr('disabled', 'true');
					$('#resultFail').attr('disabled', 'true');
					$('#notes').attr('disabled', 'true');
					$('#dateErrorText').show('blind', 800);
				}
			}
			else
			{
				$('#resultPass').attr('checked', 'checked');
			}
		}
		
	}
	//if(event!="employee")
	{
	callPhpPage(page, cb); // AJAX call using GET.	
	}
}
/*---------------------------------------------------------------------------NEW CODE-------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
*/

function updateNumberOfRows()
{
	$("#numRowsToDisplay").val($("#numberOfRows").val());
	var selectedRadio = $("#gradeForm input[type=radio]:checked").val();
	var initialNumberOfTables = $("#gradeRow table").size(); // number of rows displayed
	if($("#gradeRow table").size()<$("#numRowsToDisplay").val())
	{
	var i=0;
	var difference = $("#numRowsToDisplay").val()-$("#gradeRow table").size(); //number of rows user wants to see - current number of rows
		//this creates the new tables based on the first one that always shows by default
		while(i < difference)//make as many rows of tables as the difference would require
		{
			$("#gradeRow").append($("#table1").clone());
			i++;
		}
		//this creates ids and makes the necessary changes to the newly created tables
		var i = initialNumberOfTables+1; //
		while(i<=$("#numRowsToDisplay").val())
		{
			$("#gradeRow table:nth-child("+i+")").attr("id", "table"+i);//generates a new id for the table
			$("#table"+i+" td:nth-child(1) select").attr({id:"employee"+i, name:"employee"+i, onchange:'fillTestSelect(\"employee'+i+'\",\"test'+i+'\");validateRows("date", '+i+');'});
			$("#table"+i+" td:nth-child(2) select").attr({id:"test"+i, name:"test"+i, disabled:"disabled", onchange:"enableNext('date"+i+"');validateRows('score', "+i+");validateRows('date', "+i+")"});
			$("#table"+i+" td:nth-child(3)").css("background-color", "white");
			$("#table"+i+" td:nth-child(3) input").attr({id:"date"+i, name:"date"+i, disabled:"disabled", onchange:"enableNext('score"+i+"');validateRows('date', "+i+");"}).removeAttr("class").val("");
			$("#table"+i+" td:nth-child(3) input").datepicker({ dateFormat: 'yy-mm-dd' });
			$("#table"+i+" td:nth-child(4)").css("background-color", "white");
			$("#table"+i+" td:nth-child(4) input").attr({id:"score"+i, name:"score"+i, disabled:"disabled", onchange:"enableNext('resultPass"+i+"', 'resultFail"+i+"');validateRows('score', "+i+");"}).val("");
			$("#table"+i+" td:nth-child(4) input").siblings("p").remove(); //remove any comments added to initial row
			$("#table"+i+" td:nth-child(5) :input:eq(0)").attr({id:"resultPass"+i, name:"result"+i, disabled:"disabled", onchange:"enableNext('notes"+i+"'); validateRows('score', "+i+");"});
			$("#table"+i+" td:nth-child(5) :input:eq(0)").removeAttr("checked"); //removes checks that are passed on due to cloning
			$("#table"+i+" td:nth-child(5) :input:eq(1)").attr({id:"resultFail"+i, name:"result"+i, disabled:"disabled", onchange:"enableNext('notes"+i+"');validateRows('score', "+i+");"});
			$("#table"+i+" td:nth-child(5) :input:eq(1)").removeAttr("checked"); //removes checks that are passed on due to cloning
			$("#table"+i+" td:nth-child(6) textarea").attr({id:"notes"+i, name:"notes"+i, disabled:"disabled"});
			$("#table"+i+" td:nth-child(7) div").html("<br><b>Row "+i+"</b></br>").css({"-webkit-transform":"rotate(90deg)", "transform":"rotate(90deg)","-ms-transform":"rotate(90deg)", "margin":" 20px 0px 0px 10px !important", "color":"white"});
			i++;
		}
		//this reselects the right radio button because when table is cloned the radio check is messed up due to shared identities that happen during a short time.
		if($("#table1 td:nth-child(5) :input:eq(0)").val()==selectedRadio)
		{
			$("#table1 td:nth-child(5) :input:eq(0)").attr("checked", true);
		}
		else if($("#table1 td:nth-child(5) :input:eq(1)").val()==selectedRadio)
		{
			$("#table1 td:nth-child(5) :input:eq(1)").attr("checked", true);
		}
	}
	//this takes care of deleting tables if user reduces number of tables to be displayed
	else if ($("#gradeRow table").size()>$("#numRowsToDisplay").val() && $("#numRowsToDisplay").val()>0)
	{
		var removeCount = $("#gradeRow table").size() - $("#numRowsToDisplay").val(); //total number of rows - quantity user wants to see
		var totalElements = $("#gradeRow table").size();
		
		for(var i = totalElements; i > (totalElements - removeCount); i--)
		{
			$("#table"+i).remove(); //i starts as total elements, as long as i is bigger than what user wants to see remove that row
		}
	}
	
}
//gets the tests to be displayed after choosing an employee
function fillTestSelect(employeeRowId, testRowId)
{
	var employee = $("#"+employeeRowId+" option:selected").val();
	if($("#"+employeeRowId+" option:selected").text()!="Please Select an Employee")
	{
		$.ajax
		({
			url:"getTests.php",
			type:'GET',
			data:{employee : employee},
			success:function(result){$('#'+testRowId).html(result); $('#'+testRowId).removeAttr("disabled");}
		});
	}
	else
	{
		$('#'+testRowId).attr("disabled", "disabled");
	}
}
//enables disabled areas after the previous one was filled up
function enableNext(id1, id2)
{
	$("#"+id1).removeAttr("disabled"); //used for every cell starting at when you select a test, id1 is the id for the next cell (ex. "date" if you are selecting "test")
	$("#"+id2).removeAttr("disabled"); //id2 is only used when the next cell includes the radio buttons
	var count=1;
	var i;
	var base;
	while($.isNumeric(id1.slice(-count,id1.length))==true) //this basically parse out the number and the base part of the ids
	{
		i=id1.slice(-count,id1.length);
		base=id1.slice(0, id1.length-count);
		count++;
	}

	if($("#test"+i).val()=="defaultOption") //if later on test is changed to the default option this will make sure that all following fields are emptied and disabled
	{
		$("#date"+i).attr("disabled","disabled");
		$("#date"+i).val("");
		$("#date"+i).parent().css("background-color","white");
		$("#score"+i).attr("disabled","disabled");
		$("#score"+i).parent().css("background-color","white");
		$("#score"+i).val("");
		$("#resultPass"+i).attr("disabled","disabled");
		$("#resultFail"+i).attr("disabled","disabled");
		$("#table"+i+" input:checked").attr("checked", false);
		$("#notes"+i).attr("disabled","disabled");
		$("#notes"+i).val("");
		$("#score"+i).siblings("p").remove();
	}
	if($("#date"+i).val()=="") //this does the same as the test one but it takes care of what comes after date if the date is cleared
	{
		$("#score"+i).attr("disabled","disabled");
		$("#score"+i).val("");
		$("#score"+i).parent().css("background-color","white");
		$("#resultPass"+i).attr("disabled","disabled");
		$("#resultFail"+i).attr("disabled","disabled");
		$("#table"+i+" input:checked").attr("checked", false);
		$("#notes"+i).attr("disabled","disabled");
	}
	if($("#score"+i).val()=="")	//similar to previous one
	{
		$("#score"+i).parent().css("background-color","white");
		$("#resultPass"+i).attr("disabled","disabled");
		$("#resultFail"+i).attr("disabled","disabled");
		$("#table"+i+" input:checked").attr("checked", false);
		$("#notes"+i).attr("disabled","disabled");
	}
	if(base=="date") //takes care of showing the percentage to pass and the max points for the test selected
	{
		var employee = $("#table"+i+" td:nth-child(1) :selected").val();
		var test = $("#table"+i+" td:nth-child(2) :selected").val();
		var temp = new Object();	
		
			//ajax call
			$.ajax
				({
					url:"warnings.php",
					type:'GET',
					async: false,
					data:{employee : employee, test : test},
					success:function(result){ temp = JSON.parse(result);}
				});
			//ajax returned values
		var maxPoints = temp.maxPoints; 
		var passingPercentage = temp.testPercentage;
		$("#score"+i).siblings("p").remove(); //deletes whatever was there before if this gets called again
		$("#score"+i).parent().append("<p style='margin:0px 0 !important;'>out of "+maxPoints+" points<br>"+passingPercentage+"% to pass</p>");
	}
}
//submit the results
function submitGameModeRows()
{
	var validation=new validateRows(); //validation object that has parameters generated by validation function
	//alert(validation['errorNone']);
	if(validation['errorNone']==false && validation['rows'].length>0 && validation['dateError']==false) //if validation comes false and retunrs more than one row that is valid to submit and there is no date error it asks user if he wants to submit only valid rows
	{
		var submitWithError=confirm("Do you want to submit the rows that are valid?");
	}
	else if(validation['dateError']==true) //date errors do not allow for exceptions
	{
		alert("There is a date error, please fix the error(s) before you submit rows");
	}
	if(validation['errorNone']==true || (submitWithError==true && validation['dateError']==false)) //if validation returned true or if there was no date error and user created an exception for score error this will submit all the rows that were valid or had exceptions made for
	{
		var row;
		var subrow;
		var data=new Object();
		for(row in validation['rows']) //makes ajax calls to submit each row.
		{
			if(row!="attempt")
			{
				var i=validation['rows'][row];
				data.employee = $("#employee"+i).val();
				data.test = $("#test"+i).val();
				data.date = $("#date"+i).val();
				data.score = $("#score"+i).val();
				data.result = $("#table"+i+" input:checked").val();
				data.notes = $("#notes"+i).val();
				data.attempt=validation['rows']['attempt'][row];
				
				$.ajax
				({
					url:"submit.php",
					type:"POST",
					data:{ "employee":data.employee, "test":data.test, "date":data.date, "score":data.score, "result":data.result, "notes":data.notes, "attempt":data.attempt, type:"gradeMode"}
				});
			}
		}
	}
}
//validates all data and returns an array with errors and rows that are valid for submit
function validateRows(instance, instanceId)
{
	if(instance==undefined && instanceId==undefined)
	{
		var error =  new Object(); //store error types
		error.date = false;
		error.score =  false;
		var string = new Object(); //stores the error message information for date and score
		string.date="";
		string.score="";
		$('#dateErrorText').hide();
		$('#scoreWarningText').hide();
		error.none=true;
		
		var rowsCheck= new Array();//checks all rows for errors and keeps track of which rows are valid or have exceptions made for
		rowsCheck["attempt"]=new Array(); //stores the number of attempts connected to the test being taken
		var dayInMilliseconds=86400000; //number of milliseconds in a day
			for(var i=1; i<=$("#gradeRow table").size(); i++) //loop that checks all rows for errors
			{
				//ajax data
				var employee = $("#table"+i+" td:nth-child(1) :selected").val();
				var test = $("#table"+i+" td:nth-child(2) :selected").val();
				var chosenDate = Math.floor((new Date($("#table"+i+" td:nth-child(3) input").val())).valueOf()/dayInMilliseconds);
				var temp = new Object();
				rowsCheck[i] = false;		
				if(employee!="" && test!=undefined && test!="defaultOption" && chosenDate!=undefined)
				{
					//ajax call
					$.ajax
						({
							url:"warnings.php",
							type:'GET',
							async: false,
							data:{employee : employee, test : test},
							success:function(result){ temp = JSON.parse(result);}
						});
					//ajax returned values
					var maxPoints = temp.maxPoints; 
					var passingPercentage = temp.testPercentage;
					var joinGroupDate = Math.floor((new Date(temp.joinGroupDate)).valueOf()/dayInMilliseconds);
					var testCreation = Math.floor((new Date(temp.testCreation)).valueOf()/dayInMilliseconds);
					var timePeriod = ((temp.timePeriod==0)? undefined:temp.timePeriod);
					rowsCheck["attempt"][i] = temp.currentAttempt;
				
					//comparison constants
					
					var currentPoints = $("#table"+i+" td:nth-child(4) input").val();
		
					//date test for error 
						//when date before date of joining the group
					if(chosenDate - joinGroupDate < 0)
					{
					
						if(error.date==false)
						{
							string.date += "The row(s) below have a date error because the date chosen for the completion of the test/quiz happened before the employee joined the group.<br><br> row(s): "+i;
							error.date = true;
							rowsCheck[i]=true;
						}
						else if (error.date==true)
						{
							string.date +=", "+i;
						}
					}
						//when date is after time limit period
					else if(
					((testCreation>joinGroupDate && chosenDate-testCreation>timePeriod) || (testCreation>joinGroupDate && testCreation>chosenDate)) 
					|| 
					(testCreation<joinGroupDate && chosenDate-joinGroupDate>timePeriod) 
					&& 
					timePeriod!=undefined)
					{
						if(error.date==false)
						{
							string.date += "The row(s) below have a date error because the date chosen for the completion of the test/quiz is after the time period employee had to complete assignment or set before the creation of the test.<br><br> row(s): "+i;
							error.date = true;
							rowsCheck[i]=true;
						}
						else if (error.date==true)
						{
							string.date +=", "+i;
						}
						
					}
					//radio button test for error
					var radioButton = $("#table"+i+" input:checked").val();
					if(radioButton==undefined)
					{
						alert("You have not filled all options");
						error.none=false;
						rowsCheck[i]=true;
					}
					//score test for error
					if(((currentPoints/maxPoints)*100<passingPercentage && radioButton==1)|| ((currentPoints/maxPoints)*100>passingPercentage && radioButton==0) && $.isNumeric(currentPoints)==true) //checks if after pressing radio button the score is too low or too high for the radio button chosen.
					{
						var confirmException=confirm("Do you want to submit score exceptions for row "+i+"?");
						if(error.score==false && confirmException==false)
						{												

							string.score +="The row(s) below have one of the following problems: it has a score lower than what is necessary to pass and is set to pass, a score higher than what is necessary to pass and is set to fail, or characters rather than numbers are being entered.<br><br> row(s): "+i;
							error.score =  true;
							rowsCheck[i]=true;
						
						}
						else if(error.score==true && confirmException==false)
						{
							string.score += ", "+i;
							rowsCheck[i]=true;
						}
						else if(confirmException==true)
						{
							error.none=true;
							rowsCheck[i]=false;
						}
					}
					else if($.isNumeric(currentPoints)==false && $("#score"+i).attr("disabled")!="disabled" && currentPoints!="") //checks if letters were entered
					{
						if(error.score==false)
						{												

							string.score +="The row(s) below have one of the following problems: it has a score lower than what is necessary to pass and is set to pass, a score higher than what is necessary to pass and is set to fail, or characters rather than numbers are being entered.<br><br> row(s): "+i;
							error.score =  true;
							rowsCheck[i]=true;
						
						}
						else if(error.score==true)
						{
							string.score += ", "+i;
							rowsCheck[i]=true;
						}
					}		
				}
				//if row has not been completely filled or partially filled
				else if(employee!="")
				{
					error.none=false;
					rowsCheck[i]=true;
				}	
				else
				{
					rowsCheck[i]=true;
				}
			}
		//makes an array with the index of the rows that are valid and can be submitted
		var rowsToSubmit=new Array();
		rowsToSubmit['attempt']=new Array();
		var count=1;
		for(var i=1; i<=$("#gradeRow table").size(); i++)
		{
			if(rowsCheck[i]==false)
			{
				rowsToSubmit[count]=i;
				rowsToSubmit['attempt'][count]=rowsCheck["attempt"][i];
				count++;
			}
		}
		//for(var i=1; i<rowsToSubmit.length; i++)
		{
			//alert(rowsToSubmit[i]);
		}
	
		$('#dateErrorText p').remove(); //remove previous error text
		$('#scoreWarningText p').remove(); //remove previous error text
	
		$('#dateErrorText').append("<p>"+string.date+"</p>"); //adds new error text
		$('#scoreWarningText').append("<p>"+string.score+"</p>"); //adds new error text
	
		if(error.date == true) //show error
		{
			$('#dateErrorText').show('blind', 800);
			error.none=false;
		}
		if(error.score==true) //show error
		{
			$('#scoreWarningText').show('blind', 800);
			error.none=false;
		}
		var returnValues=new Array();
		returnValues['errorNone']=error.none;
		returnValues['dateError']=error.date;
		returnValues['rows']=rowsToSubmit;
		return returnValues; //array of returned values
	}
	//real time error check similar to what is above excpet it only changes the cell color and uses the validateRows() parameters to check only what is being changed rather than all rows
	else if(instance!=undefined && instanceId!=undefined)
	{
				
		var i=instanceId;	
		var dayInMilliseconds=86400000;
		var employee = $("#table"+i+" td:nth-child(1) :selected").val();
		var test = $("#table"+i+" td:nth-child(2) :selected").val();
		var chosenDate = Math.floor((new Date($("#table"+i+" td:nth-child(3) input").val())).valueOf()/dayInMilliseconds);
		var temp = new Object();		
			//ajax call
			$.ajax
				({
					url:"warnings.php",
					type:'GET',
					async: false,
					data:{employee : employee, test : test},
					success:function(result){ temp = JSON.parse(result);}
				});
			//ajax returned values
			var maxPoints = temp.maxPoints; 
			var passingPercentage = temp.testPercentage;
			var joinGroupDate = Math.floor((new Date(temp.joinGroupDate)).valueOf()/dayInMilliseconds);
			var testCreation = Math.floor((new Date(temp.testCreation)).valueOf()/dayInMilliseconds);
			var timePeriod = ((temp.timePeriod==0)? undefined:temp.timePeriod);
			//comparison constants
			
			var currentPoints = $("#table"+i+" td:nth-child(4) input").val();

			//date test for error
			if(chosenDate - joinGroupDate < 0 && instance=="date")
			{
				$("#date"+i).parent().css('background-color', 'rgba(255, 0, 0, 0.52)');
			}
			else if(((testCreation>joinGroupDate && chosenDate-testCreation>timePeriod) || (testCreation>joinGroupDate && testCreation>chosenDate)) 
					|| (testCreation<joinGroupDate && chosenDate-joinGroupDate>timePeriod) 
					&& timePeriod!=undefined && instance=="date")
			{
				$("#date"+i).parent().css('background-color', 'rgba(255, 0, 0, 0.52)');
				
			}
			else if(instance=="date")
			{
				$("#date"+i).parent().css('background-color', 'white');
			}
			
			//score test for error
			var radioButton = $("#table"+i+" input:checked").val();
			if(((currentPoints/maxPoints)*100<passingPercentage && instance=="score" && radioButton==1)|| ((currentPoints/maxPoints)*100>passingPercentage && instance=="score" && radioButton==0))
			{
				$("#score"+i).parent().css('background-color', 'rgba(255, 0, 0, 0.52)');
			}
			//checks if letters were entered instead of numbers
			else if($.isNumeric(currentPoints)==false && $("#score"+i).attr("disabled")!="disabled" && currentPoints!="" && instance=="score")
			{
				$("#score"+i).parent().css('background-color', 'rgba(255, 0, 0, 0.52)');
			}	
			else if(instance=="score")
			{
				$("#score"+i).parent().css('background-color', 'white');
			}
	}
	
}
/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
*/
/* Sets up and loads the 'Groups' page. The 'event' parameter is for handling AJAX calls. */
function groupsMode(event)
{
	if(event == 'members') // Load Group Members tab with data for selected employee.
	{
		var page = 'groups.php?employee=' + $('#employee').val();
	}
	else if(event == 'tests') // Load Required Tests tab with data for selected group.
	{
		if($('#group').val() == '')
		{
			var page = 'groups.php'; // Load default if a group is not selected.
		}
		else
		{
			var page = 'groups.php?group=' + $('#group').val();
		}
	}
	else
	{
		var page = 'groups.php'; // Load default page.
	}
	
	var cb = function (result)
	{
		$('#modeContent').replaceWith(result);
		
		/* Load jQuery UI tooltips. */
		$('#modeContent').tooltip
		({
			track: true
		});
		
		/* jQuery dialog for adding an employee to a group. */
		$('#groupSelectDialog').dialog
		({
			autoOpen: false,
			position: 'center',
			minWidth: 380,
			modal: true,
			buttons:
			{
				Submit:
					function() // Makes AJAX call to the submit.php page that adds the employee to the selected group in the database.
					{
						var employee = $('#employee').val(); // Get employee to add to group.
						var group = $('#groupMembersDialogGroupID').val(); // Get the group to add the employee to.
						var startDate = $('#dialogStartDate').val(); // Get the date chosen as the day the employee joined the group.
						var page = 'submit.php?type=groupsMode&tab=groupMembers&action=add&employee=' + employee + '&group=' + group + '&startDate=' + startDate;
						
						var cb = function() // Empty call back function.
						{
						}
						
						callPhpPage(page, cb);
						
						$('#groupSelectDialog').dialog('close'); // Close dialog.
					},
				Cancel:
					function() // Cancel adding emplyee to selected group.
					{	
						$('#groupSelectDialog').dialog('close');
					}
			},
			open:
				function()
				{
				},
			close:
				function()
				{
					groupsMode(); // Load Groups Mode upon closing.
				}
		});
		
		/* jQuery dialog for removing an employee from a group. */
		$('#groupDeselectDialog').dialog(
		{
			autoOpen: false,
			position: 'center',
			minWidth: 450,
			modal: true,
			buttons:
			{
				Submit:
					function()
					{
						var instance = $('#groupMembersDialogID').val(); // Get the instance ID of the employee's group membership so that we can close it.
						var endDate = $('#dialogEndDate').val(); // Get selected end date for when the employee left the group.
						var page = 'submit.php?type=groupsMode&tab=groupMembers&action=remove&ID=' + instance + '&endDate=' + endDate;
						
						var cb = function()
						{
						}
						
						callPhpPage(page, cb);
						
						$('#groupDeselectDialog').dialog('close');
					},
				Cancel:
					function()
					{	
						$('#groupDeselectDialog').dialog('close');
					}
			},
			close:
				function()
				{
					groupsMode();
				}
		});
		
		/* jQuery dialog for adding a test to a group. */
		$('#testSelectDialog').dialog
		({
			autoOpen: false,
			position: 'center',
			minWidth: 380,
			modal: true,
			buttons:
			{
				Yes:
					function()
					{
						var group = $('#groupID').val();
						var test = $('#groupTestsSelectDialogTestID').val();
						var page = 'submit.php?type=groupsMode&tab=groupTests&action=add&group=' + group + '&test=' + test;
						
						var cb = function()
						{
							groupsMode('tests');
						}
						
						callPhpPage(page, cb);
						
						$('#testSelectDialog').dialog('close');
					},
				Cancel:
					function()
					{	
						$('#testSelectDialog').dialog('close');
					}
			},
			close:
				function()
				{
					groupsMode('tests');
				}
		});
		
		/* jQuery dialog for removing a test from a group. */
		$('#testDeselectDialog').dialog
		({
			autoOpen: false,
			position: 'center',
			minWidth: 380,
			modal: true,
			buttons:
			{
				Yes:
					function()
					{
						var test = $('#groupTestsDeselectDialogTestID').val();
						var ID = $('#submitTestInstance' + test).val();
						var page = 'submit.php?type=groupsMode&tab=groupTests&action=remove&ID=' + ID;
						
						var cb = function()
						{
						}
						
						callPhpPage(page, cb);
						
						$('#testDeselectDialog').dialog('close');
					},
				Cancel:
					function()
					{	
						$('#testDeselectDialog').dialog('close');
					}
			},
			close:
				function()
				{
					groupsMode('tests');
				}
		});
		
		/* jQuery dialog for removing a group in general. */
        $('#groupsDeleteDialog').dialog
		({
            autoOpen: false,
            height: 'auto',
			width: '400',
            modal: true,
            buttons:
			{
                'Remove':
					function()
					{
						var page = 'submit.php?type=groupsMode&tab=addGroup&action=delete&group=' + $('#groupRemoveRadio:checked').val();
						
						var cb = function()
						{
						}
						
						callPhpPage(page, cb);
						
						groupsMode('add');
						$(this).dialog('close');
					},
                Cancel:
					function()
					{
						$(this).dialog('close');
					}
			}
        });
		
		/* Load correct tab based on 'event'. */
		if(event == 'members')
		{
			$('#tabs').tabs({ selected: 1 });
		}
		else if(event == 'tests')
		{
			$('#tabs').tabs({ selected: 2 });
		}
		else if(event == 'add')
		{
			$('#tabs').tabs({ selected: 0 });
		}
		else
		{
			$('#tabs').tabs({ selected: 1 });
		}
	}
	
	callPhpPage(page, cb);
}

/* Sets up and loads the 'Tests' page. The 'event' parameter is for handling AJAX calls. */
function testsMode(event)
{
	if(event == 'edit')
	{
		var page = 'tests.php?test=' + $('#test').val();
	}
	else
	{
		var page = 'tests.php';
	}
	
	var cb = function (result)
	{
		$('#modeContent').replaceWith(result);
		
		/* Load jQuery UI tooltips. */
		$('#modeContent').tooltip
		({
			track: true
		});
		
		if(event == 'edit')
		{
			$('#tabs').tabs({ selected: 1 });
		}
		else
		{
			$('#tabs').tabs({ selected: 0 });
		}
		
		/* jQuery dialog for selecting the 'Passing Percentage' field. */
		$('#addSliderDialog').dialog
		({
			autoOpen: false,
			position: 'center',
			modal: true,
			buttons:
			{
				Enter:
					function(event,ui)
					{
						$('#testPassingPercentage').val($('#addSlider').slider("option", "value")); // Set 'Passing Percentage' field to selected value.
						$('#addSliderDialog').dialog('close');
					},
				Cancel:
					function()
					{	
						$('#addPercent').html($('#testPassingPercentage').val() + '%'); // Revert to previous percentage.
						$('#addSliderDialog').dialog('close');
					}
			},
			open:
				function()
				{
					if($('#testPassingPercentage').val() != '')
					{
						$('#addPercent').html($('#testPassingPercentage').val() + '%'); // Set previous percentage in case user cancels.
					}
				},
			close:
				function()
				{
					$('#addPercent').html($('#testPassingPercentage').val() + '%'); // Revert back to previous percentage.
				}
		});
		
		/* jQuery slider for the slider dialog. */
		$('#addSlider').slider
		({
			value: 0,
			max: 100,
			min: 0,
			slide:
				function (event, ui)
				{
					$('#addPercent').html(ui.value + '%'); // Set the dialogs displayed value for the slider.
				},
		});
		
		/* jQuery dialog for selecting the editing 'Passing Percentage' field. */
		$('#editSliderDialog').dialog
		({
			autoOpen: false,
			position: 'center',
			modal: true,
			buttons:
			{
				Enter:
					function(event,ui)
					{
						$('#testEditPassingPercentage').val($('#editSlider').slider("option", "value"));
						$('#editSliderDialog').dialog('close');
					},
				Cancel:
					function()
					{	
						$('#editPercent').html($('#testEditPassingPercentage').val() + '%');
						$('#editSliderDialog').dialog('close');
					}
			},
			open:
				function()
				{
					if($('#testEditPassingPercentage').val() != '')
					{
						$('#editPercent').html($('#testEditPassingPercentage').val() + '%');
					}
				},
			close:
				function()
				{
					$('#editPercent').html($('#testEditPassingPercentage').val() + '%');
				}
		});
		
		/* jQuery slider for the editing 'Passing Percentage' dialog. */
		$('#editSlider').slider
		({
			value: 0,
			max: 100,
			min: 0,
			slide:
				function (event, ui)
				{
					$('#editPercent').html(ui.value + '%');
				},
		});
	}
	
	callPhpPage(page, cb);
}

/* Used to validate different forms throughout the app. 'form' tells us which page is requesting validation,
   and 'tab' tells us which tab is being submitted. Returning false keeps the requesting page form submitting. */
function validateForm(form, tab)
{
	/*if(form == 'gradeMode') // Grade Mode is making the request.
	{
		var dateException = $('#dateException'); // Get jQuery handles so we don't need to keep requesting them.
		var notes = $('#notes');
		
		if($('#employee').val() == '') // Check to make sure an employee was selected for 'Employee'.
		{
			alert('You must enter an employee.'); // Display alert.
			
			return false; // Return false for an invalid form.
		}
		if($('#test').val() == 'defaultOption') // Check if default drop down selection was chosen for 'Test'.
		{
			alert('You must enter a test.');
			
			return false;
		}
		if($('#date').val() == '' && $("#groupId").text()!=4) // Check if 'Date' was left blank.
		{
			alert('You must enter a date.');
			
			return false;
		}
		if($('#score').val() == '') // Check if 'Score' was left blank.
		{
			alert('You must enter a score.');
			
			return false;
		}
		if($('input[name=result]:checked').val() != 1 && $('input[name=result]:checked').val() != 0) // Check if niether 'Pass' or 'Fail' was selected.
		{
			alert('You must enter a result.');
			
			return false;
		}
		
		if($('input[name=result]:checked').val() == 1) // Check if 'Pass' was selected.
		{
			if(dateException.val() == 1) // Check if a date exception was thrown so that we can add a note of it since the result was a pass.
			{
				notes.val(function (index, val) { return val + ' --NOTE: This test was not passed off in the amount of time required, but an exception was made for this instance.-- '; });
			}
		
			if(scoreException == 1) // Check if a score exception was thrown so that we can add a note of it since the result was a pass.
			{
				notes.val(function (index, val) { return  val + ' --NOTE: This test did not score well enough to pass, but an exception was made for this instance.-- '; });
			}
		}
	}*/
	 if(form == 'groupMode') // Groups Mode is making the request.
	{
		if(tab == 'addGroup')
		{
			for(i = 0; i < $('.groupsList').size(); i++) // Iterate through group list.
			{
				if($('#groupName').val() == $('.groupsList:eq(' + i + ')').text()) // Check if a group with entered name already exists.
				{
					alert('A group with that name already exists. Please choose a different name for your group.');
				
					return false;
				}
			}
		}
	}
	else if(form == 'testMode') // Tests Mode is making the request.
	{
		var error = false; // Since we are submitting this form through AJAX to check the entered data, this keeps tack of intermitant errors.
		
		if(tab == 'addTest')
		{
			if($('#testTimePeriod').val() == '') // Check to make sure a time period was entered.
			{
				alert('You must enter a time period.');
				
				return false;
			}

			if(isNaN($('#testTimePeriod').val())) // Check if time period entered is not a number.
			{
				alert('You must enter a number for the time period.');
				
				return false;
			}
			
			if($('#testPoints').val() == '') // Check to make sure points were entered.
			{
				alert('You must enter the number of points possible.');
				
				return false;
			}
			
			if(isNaN($('#testPoints').val())) // Check to make sure points entered is a number.
			{
				alert('You must enter a number for the points.');
				
				return false;
			}
			
			if($('#testPassingPercentage').val() == '') // Check to make sure a passing percentage was entered.
			{
				alert('You must enter a passing percentage.');
				
				return false;
			}
			
			if(isNaN($('#testPassingPercentage').val())) // check to make sure the passing percentage entered is a number.
			{
				alert('You must enter a number for the passing percentage.');
				
				return false;
			}
			
			if($('#testName').val() == '') // Check to make sure a name was entered.
			{
				alert('You must enter a name for the test.');
				
				return false;
			}
			
			$('#test').children().each(function(index) // Run this check on all matching elements.
			{
				if($(this).text() == $('#testName').val()) // Check if the test name has already been used.
				{
					alert('The name you have chosen has already been used. Please pick a new name.');
					error = true;
					
					return false;
				}
			});
			
			if(error == true)
			{	
				return false;
			}
		}
		else if(tab == 'editTest')
		{
			if($('#testEditTimePeriod').val() == '') // Check to make sure a time period was entered.
			{
				alert('You must enter a time period.');
				
				return false;
			}
			if($('#testEditPoints').val() == '') // Check to make sure points were entered.
			{
				alert('You must enter the number of points possible.');
				
				return false;
			}
			if($('#testEditPassingPercentage').val() == '') // Check to make sure a passing percentage was entered.
			{
				alert('You must enter a passing percentage.');
				
				return false;
			}
			if($('#testEditName').val() == '') // Check to make sure a name was entered.
			{
				alert('You must enter a name for the test.');
				
				return false;
			}
			$('#test').children().each(function(index) // Run check on all matching elements.
			{
				if($(this).text() == $('#testEditName').val() && $(this).text() != $('#test :selected').text()) // Check if name has already been used.
				{
					alert('The name you have chosen has already been used. Please pick a new name.');
					error = true;
					
					return false;
				}
			});
			
			if(error == true)
			{	
				return false;
			}
		}
	}
}

/* Check the score recieved on a test, and compare it to the minimum needed to pass
   the test. If the minimum is not met, display warning message. */
function gradeModeCheckScore()
{		
		var employeeScorePercent = (($('#score').val() / $('#testMaxPoints').val()) * 100); // Get test instance percent correct.
		
		if($('#score').val() == "") // Check if score feild was cleared.
	    {
	        scoreException = 0; // Reset score exception.
	        $('#scoreWarningText').hide('blind', 800); // Hide warning text.
	        
	        if($('#resultFail').is(':checked') && $('#dateException').val() == 0) // Check if we need to reset the results.
	        {
	            $('#resultPass').attr('checked', 'checked'); // Reset result value.
	        }
	        
	        return; // Exit function.
	    }
		
		if(employeeScorePercent < $('#testPassingPercentage').val()) // Compare to mimimum passing percentage.
		{
			scoreException = 1; // Flag exception.
			$('#resultFail').attr('checked', 'checked'); // Auto mark test instance as a fail.
			$('#warningTextPercent').text(employeeScorePercent); // Set up warning message with employee test percentage data.
			$('#scoreWarningText').show('blind', 800); // Display warning text.
		}
		else
		{
		    if($('#resultFail').is(':checked'))
		    {
		        scoreException = 0; // Reset score exception flag.
		    }
		    else
		    {
			    $('#resultPass').attr('checked', 'checked'); // Auto mark test instance as pass.
			    scoreException = 0; // Reset score exception flag.
			}
		}
}

/* Function for when a group is selected or deselected. The function checks whether the group
   was selected or deselected, and then sets up the dialog acordingly. */
function groupsModeSelectGroup(groupID)
{	
	if($('#group' + groupID).is(':checked')) // Check if this is a select event, or a deselect.
	{
	    /* Set up jQuery datepicker. */
		$('#dialogStartDate').datepicker
		({
		    dateFormat: 'yy-mm-dd',
		    showOn: 'button',
		    buttonImage: '../includes/libs/img/cal.gif',
		    buttonImageOnly: true
		});
		
		$('#dialogStartDate').datepicker('disable'); // This keeps the calendar from being open when the dialog opens.
		$('#groupSelectDialog').dialog('open'); // Open jQuery dialog.
		$('#dialogStartDate').datepicker('enable'); // Enable jQuery datepicker after opening dialog, since we diabled earlier.
		$('#groupMembersDialogGroupID').val(groupID); // Set the group ID to passed in ID.
	}
	else
	{
		/* Set up jQuery datepicker. */
		$('#dialogEndDate').datepicker
		({
		    dateFormat: 'yy-mm-dd',
		    showOn: 'button',
		    buttonImage: '../includes/libs/img/cal.gif',
		    buttonImageOnly: true
		});
		
		$('#dialogEndDate').datepicker('disable'); // This keeps the calendar from being open when the dialog opens.
		$('#groupDeselectDialog').dialog('open'); // Open jQuery dialog.
		$('#dialogEndDate').datepicker('enable'); // Enable jQuery datepicker after opening dialog, since we diabled earlier.
		$('#groupMembersDialogID').val($('#submitGroupInstance' + groupID).val()); // Set the group ID that we want to add.
	}
}

/* Opens the Grops Mode test dialogs that allow the user to add or remove a test from the group. */
function groupsModeSelectTest(testID)
{
	if($('#test' + testID).is(':checked')) // Check if this was a select or deselect event.
	{
		$('#testSelectDialog').dialog('open'); // Open jQuery dialog.
		$('#groupTestsSelectDialogTestID').val(testID); // Set test ID to passed in ID.
	}
	else
	{
		$('#testDeselectDialog').dialog('open'); // Open jQuery dialog.
		$('#groupTestsDeselectDialogTestID').val(testID); // Set test ID to passed in ID.
	}
}

/* Opens the Groups Mode delete dialog. */
function groupsModeDeleteDialog()
{		
	$('#groupsDeleteDialog').dialog('open');
}

/* Opens the Tests Mode add test dialog and slider. */
function testsModeAddTestSlider()
{
	$('#addSliderDialog').dialog('open');
	$('#addSlider').slider('value', $('#testPassingPercentage').val())
}

/* Opens the Tests Mode edit test dialog and slider. */
function testsModeEditTestSlider()
{
	$('#editSliderDialog').dialog('open');
	$('#editSlider').slider('value', $('#testEditPassingPercentage').val())
}

/* Deletes a test from app after confirming with user. */
function testsModeDeleteDialog()
{
	if(confirm('Are you sure you want to delete this test?\n\nNote: Removing a test will not delete the instances when employee took the test.')) // Confirm with user.
	{
		var page = 'submit.php?type=testsMode&tab=editTest&action=delete&test=' + $('#test').val(); // Send request to submit page.
						
		var cb = function()
		{
			testsMode(); // Go to Tests page once completed.
		}
						
		callPhpPage(page, cb); // AJAX call.
	}
}
		

