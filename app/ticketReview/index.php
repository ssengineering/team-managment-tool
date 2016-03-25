<?php
require('../includes/includeme.php');
require('ticketReviewFunctions.php');

if(can("access", "dc200fff-46a6-4744-92aa-76fd017049a5"))//ticketReview resource
{

$addSuccessfulMes = "";
$employeeNetId="";
$clearSuccMess="";
$sendEmailFlag = 0;
$justSubmittedtimeStamp="";


// This php function gets the information of that employee that is selected from the list.
	if (isset($_POST['employeeNetId'])) $employeeNetId = $_POST['employeeNetId'];
	elseif(isset($_POST['employeeReviewedNetID'])) $employeeNetId = $_POST['employeeReviewedNetID'];
	else $employeeNetId = $netID;
	try {
		$employeeQuery = $db->prepare("SELECT * FROM employee WHERE `netID` = :netId LIMIT 1");
		$employeeQuery->execute(array(':netId' => $employeeNetId));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$employeeReviewed = $employeeQuery->fetch(PDO::FETCH_ASSOC);
	
	// Table to show what has been submitted:
	
	//PHP code to add ticket review to the database
	$entryNum=1;
	while(isset($_POST['ticketNum'.$entryNum]))
	{	
		if (isset($_POST['ticketNum'.$entryNum]) || isset($_POST['ticketDate'.$entryNum])|| isset($_POST['requestor'.$entryNum])
			|| isset($_POST['contactInfo'.$entryNum]) || isset($_POST['serviceOrSymtomCat'.$entryNum])|| isset($_POST['ticketSource'.$entryNum])|| isset($_POST['priority'.$entryNum])
			|| isset($_POST['kbOrSource'.$entryNum])|| isset($_POST['workOrder'.$entryNum])|| isset($_POST['template'.$entryNum])|| isset($_POST['troubleshooting'.$entryNum])
			|| isset($_POST['closureCodes'.$entryNum])|| isset($_POST['professionalism'.$entryNum]) ||isset($_POST['comment'.$entryNum]))
		{
	
		//insert user's comments into the database
		try {
			$insertQuery = $db->prepare("INSERT INTO ticketReview (
				ticketNum,       agentID,      submitterID,     ticketDate, requestor,       contactInfo,
				ssc,             ticketSource, priority,        kbOrSource, workOrderNumber, templates, 
				troubleshooting, closureCodes, professionalism, comments,   sentEmail,       area,
				guid,            reviewDate,   viewDate,        agentViewed)
				Values (
				:ticketNum,      :employee,    :netId,          :day,       :requestor,      :contact,
				:service,        :source,      :priority,       :kb,        :order,          :template,
				:troubleshooting,:closure,     :professionalism,:comment,   '0',             :area,
				:guid,           :reviewDate,  :viewDate,       :agentViewed)");
			$insertQuery->execute(array(
				':ticketNum'       => trim($_POST['ticketNum'.$entryNum]),
				':employee'        => $_POST['employeeReviewedNetID'],
				':netId'           => $netID,
				':day'             => $_POST['ticketDate'.$entryNum],
	 			':requestor'       => $_POST['requestor'.$entryNum],
				':contact'         => $_POST['contactInfo'.$entryNum],
				':service'         => $_POST['serviceOrSymtomCat'.$entryNum],
		 		':source'          => $_POST['ticketSource'.$entryNum],
				':priority'        => $_POST['priority'.$entryNum],
				':kb'              => $_POST['kbOrSource'.$entryNum],
				':order'           => $_POST['workOrder'.$entryNum],
		 		':template'        => $_POST['template'.$entryNum],
				':troubleshooting' => $_POST['troubleshooting'.$entryNum],
				':closure'         => $_POST['closureCodes'.$entryNum],
				':professionalism' => $_POST['professionalism'.$entryNum],
				':comment'         => $_POST['comment'.$entryNum],
				':area'            => $area,
				':guid'            => newGuid(),
				':reviewDate'      => '0000-00-00',
				':viewDate'        => '0000-00-00',
				':agentViewed'     => 0
			));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$addSuccessfulMes = '<div style="float:right; font-size:119%; margin-right:180px;"> Review recent submissions for this person <a href="#" onClick="showTicketSubmittedSummary();"> here.</a></div><div style="float:right;color:#011948;font-size:123%;font-weight:bold;margin-right:5px;">Success! </div>';
		}
		$entryNum = $entryNum + 1;
		$sendEmailFlag = 1;
	}

	if($sendEmailFlag)
	{
		try {
			$timestampQuery = $db->prepare("SELECT `timeStamp` FROM `ticketReview` WHERE `timeStamp`=(SELECT MAX(`timeStamp`) FROM `ticketReview` WHERE agentID=:reviewed) AND agentID=:reviewed1 AND sentEmail= 0");
			$timestampQuery->execute(array(':reviewed' => $_POST['employeeReviewedNetID'], ':reviewed1' => $_POST['employeeReviewedNetID']));
		} catch(PDOException $e) {
			exit("error in query");
		}
		$timeStampInfo = $timestampQuery->fetch(PDO::FETCH_ASSOC);
		$emailSentSuccessfully=sendEmail($_POST['employeeReviewedNetID'],$timeStampInfo['timeStamp'], $netID);
		if($emailSentSuccessfully)
		{
			try {
				$updateQuery = $db->prepare("UPDATE `ticketReview` SET `sentEmail` = 1 WHERE `timeStamp` = :timestamp");
				$updateQuery->execute(array(':timestamp' => $timeStampInfo['timeStamp']));
			} catch(PDOException $e) {
				exit("error in query");
			}
		}
		else
		{
			echo 'There was an error sending the ticket review submitted.  Please talk to the site administrator.';
		}
		$sendEmailFlag = 0;
		$justSubmittedtimeStamp = $timeStampInfo['timeStamp'];
	}

?>
<!--...............HTML Code Here.................-->

<link rel="stylesheet" type="text/css" href="ticketStyle.css" />

	<!---------------------------------------------- Java Script functions----------------------------------------------->
	
	<style type="text/css">
		p.sansserif{font-family:Arial,Helvetica,sans-serif;
			    font-style:oblique;
			    font-size:120%;
			    text-align:center;}
	</style>	
	
<script type="text/javascript">
	
	var defaultFiveRows = 1;
	// Function to validate ticket form
	function validateForm()
	{
	  // If missingRequirements = 0 ---> Form is valid
	  // else if missingRequirements = 1 ---> empty fields
	  // else if missingRequirements = 2 ---> Incorrect Date
	  // else if missingRequirements = 3 ---> Incorrect Ticket Number
	  // else if missingRequirements = 4 ---> SCXXXXXX ticket with an extra digit. 
	  var missingRequirements = 0;
	  //Number of tickets
	  var num = $('.clonedInput').length;
	  
	  var i = 0;
	  // Clean all the red markings
	  for (i=1; i<=num; i++)
	  {
	  	$('#row2Input' + i).children(':first').removeClass('highlight');
	  	$('#row2Input' + i).children('TR TD:nth-child(2)').removeClass('highlight ');
	  	$('#row4Input' + i).children('TR TD:nth-child(6)').removeClass('highlight');
	  }
	 
	  //Check for missing/incorrect form values
	  for (i=1; i<=num; i++)
	  {
	    var ticketNumber=document.forms["ticketReviewForm"]["ticketNum"+i].value;
	  	var ticketDate=document.forms["ticketReviewForm"]["ticketDate"+i].value;
	  	var ticketComment=document.forms["ticketReviewForm"]["comment"+i].value;
	  	
		// Valid date format
		var validDateFormat = /^([0-9]{4})\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/;
		var checkForValidDate = validDateFormat.test(ticketDate);
		//Incident ticket number
		var validINCTicketNum = /(\INC)([0-9]{7})/g;
		//Enhancement ticket number
		var validENHTicketNum = /(\ENH)([0-9]{7})/g;
		//RITM ticket
		var validRITMTicketNum = /(\RITM)([0-9]{6})/g;
		//Service Desk ticket number
		var validSCTicketNum = /(\SC)([0-9]{6})/g;
		//Request Ticket
		var validREQTicketNum = /(\REQ)([0-9]{6})/g;
		//ENG tickets
		var validENGTicketNum = /(\ENG)([0-9]{6})/g;
	
		// Check 
		var checkForValidTicket = validINCTicketNum.test(ticketNumber);
		if(checkForValidTicket==false)
		{
			checkForValidTicket = validENHTicketNum.test(ticketNumber);
		
			if(checkForValidTicket==false)
			{
				checkForValidTicket = validRITMTicketNum.test(ticketNumber);
				
				if(ticketNumber.length==8)
				{
					checkForValidTicket = validSCTicketNum.test(ticketNumber);
				}
				else if(ticketNumber.length==9)
				{
					checkForValidTicket = validREQTicketNum.test(ticketNumber);
					if(checkForValidTicket==false)
					{
						checkForValidTicket = validENGTicketNum.test(ticketNumber);
					}
				}
			}
		}
		
		// check for ticket number
		
		if(ticketComment =="")
		  {
			$('#row4Input' + i).children('TR TD:nth-child(6)').addClass('highlight');
		  	missingRequirements = 1;
		  }
		
		if(ticketDate =="")
		  {
			$('#row2Input' + i).children('TR TD:nth-child(2)').addClass('highlight ');
		  	missingRequirements = 1;
		  }
	    else if(checkForValidDate==false)
		  {
			$('#row2Input' + i).children('TR TD:nth-child(2)').addClass('highlight ');
			missingRequirements = 2;

		  }
		  
		if (ticketNumber =="")
		  {
		    $('#row2Input' + i).children(':first').addClass('highlight');
		  	missingRequirements = 1;
		  }
		else if(checkForValidTicket==false)
		  {
			$('#row2Input' + i).children(':first').addClass('highlight');
		  	missingRequirements = 3;
		  }  
	  }
		
	    if(missingRequirements)
		{
			if(missingRequirements == 1)
			{
				alert("Please fill the required fields in red!");
		  	}
			else if (missingRequirements==2)
			{
				alert("Please enter a date in the following format YYYY-MM-DD.");
			}
			else if (missingRequirements==3)
			{
				alert("Please enter a ticket number in the following format e.g. INC0001234, ENH0001234, SC001234, RITM100001, ENG000001 or REQ100001.");
			}
			return false;
		}
	}
	
	  // Clear success messege
	function hideTicketSubmittedSummary()
	{
		document.getElementById("ticketSubmissionSummary").style.display= "none";
	}

    function showTicketSubmittedSummary()
    {
    	
    	var divTicketSummary = document.getElementById("ticketSubmissionSummary").innerHTML;
    	
    	document.getElementById("success").style.display= "none";

    	printList();   	
    	$( "#ticketSubmissionSummary").dialog({
			resizable: true,
			width: 1000,
			modal: true,
			draggable: true,
			buttons: [{ 
				text: "Close", 
				click: 	function() {
					$("#ticketSubmissionSummary").dialog("close"); 	
				} 
			}]
		});
    	
    }
	
	// Print summary tickets list
	function printList(){
		var timeStamp = "<?php echo urlencode($justSubmittedtimeStamp); ?>";
		var empNetId = "<?php echo $employeeNetId; ?>";
		var page = "printRecentlySubmitted.php?empNetId="+empNetId+"&timeStamp="+timeStamp;	
		
		var cb = function(result){ 
				document.getElementById("ticketSubmissionSummary").innerHTML=result; 
			};
		
		callPhpPage(page,cb);
	}

// Set date picker for the newly cloned fuction.
function setDatePicker(id)
{
	$('#ticketDate'+id).datepicker({dateFormat:"yy-mm-dd", autoSize: true});	
}
	
window.onload = function()
{	
	$('#ticketDate1').datepicker({dateFormat:"yy-mm-dd", autoSize: true});
	$(document).ready(function() {
				
            $('#btnAdd').click(function() {
                var num = $('.clonedInput').length; // how many "duplicatable" input fields we currently have   //ticketRow3Title
                var newNum  = new Number(num + 1);  // the numeric ID of the new input field being added
			//1st row
			var newElemRow1 = $('#ticketRow1Title' + num).clone().attr('id', 'ticketRow1Title' + newNum);
			//2nd row
		        // create the new element via clone(), and manipulate it's ID using newNum value  (new div)---->in our case is a TR
		        var newElemRow2 = $('#row2Input' + num).clone().attr('id', 'row2Input' + newNum);  // Create new element that is being passed. 
                	// manipulate the name/id values of the input inside the new element
					//Ticket number
				     newElemRow2.children().children(':first').attr('id', 'ticketNum' + newNum).attr('name', 'ticketNum' + newNum).attr('value','');   
			 		//ticket date
				     newElemRow2.children('TR TD:nth-child(2)').children(':first').attr('id', 'ticketDate' + newNum).attr('name', 'ticketDate' + newNum);
				     newElemRow2.children('TR TD:nth-child(2)').children(':first').removeClass('hasDatepicker');
					// requestor
					newElemRow2.children('TR TD:nth-child(3)').children(':first').attr('id', 'requestor' + newNum).attr('name', 'requestor' + newNum);
					// professionalism
					newElemRow2.children('TR TD:nth-child(4)').children(':first').attr('id', 'contactInfo' + newNum).attr('name', 'contactInfo' + newNum);
					// Creation
					newElemRow2.children('TR TD:nth-child(5)').children(':first').attr('id', 'serviceOrSymtomCat' + newNum).attr('name', 'serviceOrSymtomCat' + newNum);
					// Research
					newElemRow2.children('TR TD:nth-child(6)').children(':first').attr('id', 'ticketSource' + newNum).attr('name', 'ticketSource' + newNum);
					// Resolution
					newElemRow2.children('TR TD:nth-child(7)').children(':first').attr('id', 'priority' + newNum).attr('name', 'priority' + newNum);		
					// updates
					newElemRow2.children('TR TD:nth-child(8)').children(':first').attr('id', 'kbOrSource' + newNum).attr('name', 'kbOrSource' + newNum);		
					
			//3rd Row
			var newElemRow3 = $('#ticketRow3Title' + num).clone().attr('id', 'ticketRow3Title' + newNum);
			
			//4th Row
			var newElemRow4 = $('#row4Input' + num).clone().attr('id', 'row4Input' + newNum);
			// Work Order option
			newElemRow4.children().children(':first').attr('id', 'workOrder' + newNum).attr('name', 'workOrder' + newNum);
			// Template option
			newElemRow4.children('TR TD:nth-child(2)').children(':first').attr('id', 'template' + newNum).attr('name', 'template' + newNum);
			//Troubleshooting option
			newElemRow4.children('TR TD:nth-child(3)').children(':first').attr('id', 'troubleshooting' + newNum).attr('name', 'troubleshooting' + newNum);
			//ClosureCodes option
			newElemRow4.children('TR TD:nth-child(4)').children(':first').attr('id', 'closureCodes' + newNum).attr('name', 'closureCodes' + newNum);
			//Professionalism option
			newElemRow4.children('TR TD:nth-child(5)').children(':first').attr('id', 'professionalism' + newNum).attr('name', 'professionalism' + newNum);
			// comments
			newElemRow4.children('TR TD:nth-child(6)').children(':first').attr('id', 'comment' + newNum).attr('name', 'comment' + newNum);
			
			// 5th row
			// Add separator
			var newElemRow5 = $('#row5Separator' + num).clone().attr('id','row5Separator'+newNum);
			newElemRow5.children(':first').attr('id', 'separatorCell' + newNum).attr('name', 'separatorCell' + newNum);
		
			$('#row5Separator' + num).after(newElemRow1);
			$(newElemRow1).after(newElemRow2);
			$(newElemRow2).after(newElemRow3);
			$(newElemRow3).after(newElemRow4);
 			$(newElemRow4).after(newElemRow5);
			// Set date picker for the new element that was cloned. 
			setDatePicker(newNum);
 			
                // enable the "remove" button
                $('#btnDel').removeAttr('disabled');
 
                // business rule: you can only add 30 tickets
                if (newNum == 30)
                    $('#btnAdd').attr('disabled','disabled');
            });
			// Load the default rows
			if(defaultFiveRows)
			{
				$('#btnAdd').click();
				$('#btnAdd').click();
				$('#btnAdd').click();
				$('#btnAdd').click();
				defaultFiveRows = 0;
			}
			
            $('#btnDel').click(function() {
                var num = $('.clonedInput').length; // how many "duplicatable" input fields we currently have
                $('#row2Input' + num).remove();     // remove the last element  
 				$('#ticketRow1Title' + num).remove();
 				$('#row4Input' + num).remove();     // remove the last element  
 				$('#ticketRow3Title' + num).remove();
 				$('#row5Separator' + num).remove();
 				
                // enable the "add" button
                $('#btnAdd').removeAttr('disabled');
 
                // if only one element remains, disable the "remove" button
                if (num-1 == 1)
                    $('#btnDel').attr('disabled','disabled');
            });
        });
}
</script>
	
<body >
  <div class="ticketReviewMain">
	<div id="appTitle">Ticket Reviews</div>
	
	<!--This div contains the search box, employee information and ticket Review.  This div holds everything inside the page.-->
	<div id="contentPos"> 
		<div id = "searchAndEmployeeInfo">
			<div id = "empSearch">
				<!-------------------Funtion to perfom the search of employees. Also it displays the list dynamically-------->	
					<?php employeeList(); ?>
				<!-------------------------------------End of "menuPos" div----------------------------------------------------->
			</div>
			<div id = "contactInfo"> <h3>Employee Information:</h3>
				    <div id="picturePos"> <img src='<?php echo getenv("BYU_PI_PHOTO")."?n=".$employeeReviewed['netID']; ?>' alt='Employee Picture' height='145' width='145' /> </div> 
					<div id ="firstNamePos"><h3> First Name: </h3></div> <div id="fNameOutput"><h3><center><?php echo $employeeReviewed['firstName']; ?></center></h3></div>
					<div id ="lastNamePos"><h3>Last Name: </h3>   </div> <div id="lNameOutput"><h3><center><?php echo $employeeReviewed['lastName']; ?></center></h3>   </div>
					<div id ="netIdPos"><h3>NetId:</h3>   </div>  <div id="netIdOutput"> <h3><center><?php echo $employeeReviewed['netID']; ?></center></h3>  </div>
			</div>
		</div>
		<div id = "ticketReviewTable" style="width:97%;"> <h3>Add Ticket Review:  </h3>

			<!--Form to submit the ticket review to the database.-->
			<FORM METHOD="POST" action="index.php" NAME="ticketReviewForm" onsubmit="return validateForm()">
			    <TABLE BORDER="1" style="width:94%;" id='ticketReviewTableEntry'>
			   
			       <TR style="width:95%" id='ticketRow1Title1' class='clonedRow1Title'>
				    <TH class="deliniateTopLeft">Ticket#</TH><TH class="deliniate">Ticket Date</TH><TH class="deliniate">Requestor</TH><TH class="deliniate">Contact Info</TH><TH class="deliniate">Service/Symptom Category</TH>
				    <TH class="deliniate">Ticket Source</TH><TH class="deliniate">Priority</TH><TH class="deliniateTopRight">KB/Source</TH>
			  	</TR> 
				
				<TR id="row2Input1" style="width:100%" class="clonedInput">
					<TD class="deliniateLeft"><INPUT TYPE="TEXT" NAME="ticketNum1" id="ticketNum1"  class="deliniateLeftTicket" maxlength="10" SIZE="8" PLACEHOLDER="INC0034567"></TD>
			    		<TD><INPUT TYPE="TEXT" NAME="ticketDate1" id="ticketDate1" SIZE="7" PLACEHOLDER="YYYY-MM-DD" ></TD>					
					<TD style="width:5%;"><select name="requestor1" id="requestor1">
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						</select> 
					</TD>
					<TD><select name="contactInfo1"id="contactInfo1" style="width: 100%;">
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						</select>
					</TD>
					<TD><select name="serviceOrSymtomCat1" id="serviceOrSymtomCat1" >
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					    </select>	
					</TD>
					<TD><select name="ticketSource1" id="research1">
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						</select>
					</TD>
					 <TD><select name="priority1" id="priority1">
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						</select>
					</TD>
					<TD class = "deliniateRight"><select name="kbOrSource1" id="kbOrSource1">
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						</select>
					</TD>					
					
				</TR>
				<TR style="width:100%" id='ticketRow3Title1' class='clonedRow3Title'>
					<TH class="deliniateLeft">Work Order#</TH><TH>Template</TH><TH>Troubleshooting</TH><TH>Closure Codes</TH><TH>Professionalism</TH><TH class="deliniateRight" colspan="3">Comments</TH>
				</TR>
				<TR id="row4Input1" style="width:100%" class="clonedInput2">
					  <TD class="deliniateLeft"><select name="workOrder1" id="workOrder1">
						<option selected value="NA">N/A</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					  	</select>
					   </TD>
					   <TD><select name="template1" id="template1">
						<option selected value="NA">N/A</option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					  	</select>
					   </TD>
					   <TD><select name="troubleshooting1" id="troubleshooting1">
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					  	</select>
					   </TD>
					   <TD><select name="closureCodes1" id="closureCodes1" style="width: 100%;">
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					  	</select>
					   </TD>
					   <TD><select name="professionalism1" id="professionalism1">
						<option value="Yes">Yes</option>
						<option value="No">No</option>
					  	</select>
					   </TD>
					   <TD colspan="3" class="deliniateRight"><textarea rows="1" cols="45" name="comment1" id="comment1" style="resize:both;  max-height:300px; min-height:30px;  max-width:343px; min-width:343px;"></textarea></TD>
				</TR>
				<TR id="row5Separator1" style="width:100%" class="clonedSeparator1">
					<td id="separatorCell1" style="height:28px;" colspan="8" class="deliniate"></td>
				</TR> 

			    <div>
				<input type="button" id="btnAdd" value="add row" />
				<input type="button" id="btnDel" value="remove row" />
				<INPUT TYPE="SUBMIT" VALUE="Submit" NAME="submitReview" style="float: right;">
				<?php 
				if (($_SERVER['REQUEST_METHOD'] == "POST"))
				{
					  ////Display this in a new window. 
					if($addSuccessfulMes!='')
					{
						echo $addSuccessfulMes;
				
					}	
				}
			?>   
			    </div>
				<br />
			    </TABLE> 
			<INPUT TYPE="HIDDEN" VALUE="<?php echo $employeeReviewed['netID']; ?>" name="employeeReviewedNetID" />
			<P></P>
			</FORM>
			<p class="sansserif" id="success">
			 
				<div id="ticketSubmissionSummary" >
					
				</div>
    			</p>
			
		</div>
	  
	</div> 
  	<!------------------------------------------------End of contentPos div------------------------------------------------>
	
  <!--"Main" div ends below-->
  </div>
</body>
</html>
<?php
}
else
{
	echo "<h1 style='text-align: center;'>You are not authorized to view this site.<br /><br />If you believe this is an error please contact your manager and request permissions.<br /><br />If you are the manager please contact the OIT Network OPS Development Team to resolve the issue. <br /><br />Thank You!</h1>";
}
require('../includes/includeAtEnd.php');
?>
