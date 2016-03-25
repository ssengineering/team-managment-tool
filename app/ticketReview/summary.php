<?php
require('../includes/includeme.php');

//php code here

?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="ticketStyle.css" />
<body bgcolor=#E3E3E3>
	<div class="main">		
			<div id="appTitle">Ticket Summary</div>
			<FORM METHOD="POST" action="summary.php" NAME="summaryReviewForm" onsubmit="return validateForm()">
			<TABLE BORDER="1" style="width:100%">
			  <TR style="width:100%">
			    <TH>Ticket #</TH><TH>Employee</TH><TH>Team Leader</TH><TH>Start Date</TH><TH>End Date</TH>
			  </TR>
			  <TR style="width:100%">
			    <TD><INPUT TYPE="TEXT" NAME="ticketNum" SIZE="5" PLACEHOLDER="1234567" onkeyup="clearSuccessMess()"></TD>
			    <TD><INPUT TYPE="TEXT" NAME="employee" SIZE="7" PLACEHOLDER="YYYY-MM-DD"></TD>
			    <TD><INPUT TYPE="TEXT" NAME="teamLeader" SIZE="7" PLACEHOLDER="YYYY-MM-DD"></TD>
			    <TD><INPUT TYPE="TEXT" NAME="ticketDateStart" SIZE="7" PLACEHOLDER="YYYY-MM-DD"><?php calendar("summaryReviewForm","ticketDateStart")?></TD>
			    <TD><INPUT TYPE="TEXT" NAME="ticketDateEnd" SIZE="7" PLACEHOLDER="YYYY-MM-DD"><?php calendar("summaryReviewForm","ticketDateEnd")?></TD>
			  </TR>
			</TABLE>
			<INPUT TYPE="HIDDEN" VALUE="<?php echo $employeeReviewed['netID']; ?>" name="employeeReviewedNetID" />
			<P><div id ="submitButtonPos"><INPUT TYPE="SUBMIT" VALUE="Submit" NAME="submitReview"></div></P>
			</FORM>
	</div>
</body>
</head>
</html>

<?php
require('../includes/includeAtEnd.php');
?>
