<?php //ticketReviewLog.php This is for searching for ticket reviews that have been submitted
require('../includes/includeme.php');

if(can("access", "dc200fff-46a6-4744-92aa-76fd017049a5"))//ticketReview resource
{
?>

<SCRIPT LANGUAGE="JavaScript" SRC="ticketReviewJSFunctions.js"></SCRIPT>
<style type="text/css">
.center
{
	margin:auto;
	width:100%;
	text-align:center;
}
.tableCenter
{ 
margin-left: auto;
margin-right: auto;
}
td.cellCenter
{
	text-align:center;
	
}
.cellWidth
{
	width:14%;
	text-align:center;
}
</style>
<h1 class='center'>Ticket Review Log</h1><br />
<h4 class="center">DIRECTIONS: Select or fill in as much information as you know for your search.  <br /> The resutls will appear dynamically ordered by date.
If you need an advance search.   Click <a href="#" onClick="showAdvancedSearch();">"Advanced Search."</a></h4>
<br />
<div>
<form id='searchInfo'>
<TABLE BORDER="1" id='basicSearch' class="tableCenter">
	<div id="input2" style="margin-bottom:4px;">
		<TR>
		<TH colspan='2'>Employee</TH><TH>Ticket #</TH><TH>Start Date</TH><TH>End Date</TH>	
		</TR>
		<TR>
		<TD colspan='2'><?php echo "<select id='employee' name='employee'>";
			employeeFillCurrentArea();
			echo "</select>"; ?></TD>
		<TD ><INPUT TYPE="TEXT" NAME="ticketNum1" id="ticketNum1" maxlength="10" SIZE="8" PLACEHOLDER="INC0034567"></TD>
		<TD><INPUT TYPE="TEXT" NAME="ticketDate1" id="ticketDate1"  SIZE="8" PLACEHOLDER="YYYY-MM-DD" value="" ></TD>
			<TD><INPUT TYPE="TEXT" NAME="ticketDate2" id="ticketDate2"  SIZE="8" PLACEHOLDER="YYYY-MM-DD" value="" ></TD>
		</TR>
 </table>

<table BORDER="1" style="width:85%; display:none; margin-left:7%;" id="advancedSearch">
	   <TR id='ticketRow1Title1' class='clonedRow1Title'>
		<TH>Requestor</TH><TH>Contact Info</TH><TH>Service/Symptom Category</TH>
		<TH>Ticket Source</TH><TH>Priority</TH><TH>KB/Source</TH><TH>Work Order#</TH>
	</TR> 
	
	<TR id="row2Input1" style="width:100%">
					
		<TD class="cellWidth"><select name="requestor1" id="requestor1">
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select> 
		</TD>
		<TD class="cellWidth"><select name="contactInfo1"id="contactInfo1">
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		</TD>
		<TD class="cellWidth"><select name="serviceOrSymtomCat1" id="serviceOrSymtomCat1" >
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>	
		</TD>
		<TD class="cellWidth"><select name="ticketSource1" id="research1">
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		</TD>
		 <TD class="cellWidth"><select name="priority1" id="priority1">
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		</TD>
		<TD class="cellWidth"><select name="kbOrSource1" id="kbOrSource1">
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		</TD>					
		<TD class="cellWidth"><select name="workOrder1" id="workOrder1">
			<option selected value="NA">NA</option>
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		</TD>		
		

	</TR>
	<TR id='ticketRow3Title1'>
		<TH>Template</TH><TH>Trouble-shooting</TH><TH>Closure Codes</TH><TH>Professionalism</TH><TH colspan="4">Comments</TH>
	</TR>
	<TR id="row4Input1" style="width:100%" class="clonedInput2">
		  
		   <TD class="cellWidth"><select name="template1" id="template1">
			<option selected value="NA">NA</option>
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		   </TD>
		   <TD class="cellWidth"><select name="troubleshooting1" id="troubleshooting1">
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		   </TD>
		   <TD class="cellWidth"><select name="closureCodes1" id="closureCodes1" >
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		   </TD>
		   <TD class="cellWidth"><select name="professionalism1" id="professionalism1">
			<option value="Yes">Yes</option>
			<option value="No">No</option>
			</select>
		   </TD>
		   <TD colspan="3" class="cellWidth"><textarea rows="1" cols="45" name="comment1" id="comment1" style="resize:both;  max-height:300px; min-height:30px;  max-width:343px; min-width:343px;"></textarea></TD>
	</TR>
	</TABLE> 
</form>
</div>
<div id='results'>

</div>

<?php 
}
else
{
	echo "<h1 style='text-align: center;'>You are not authorized to view this site.<br /><br />If you believe this is an error please contact your manager and request permissions.<br /><br />If you are the manager please contact the OIT Network OPS Development Team to resolve the issue. <br /><br />Thank You!</h1>";
}
require('editTicketForm.php');
require('../includes/includeAtEnd.php');
?>
